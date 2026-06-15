<?php

namespace App\Models;

class LaporanModel
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Rekening_bank per-month running balance for a given year.
     * Balance = saldo_awal + cumulative (debet - kredit) through each month.
     * Returns: [rekening_id => ['id','nama','nomor_akun','dana_kode','bulan'=>[1..12]]]
     */
    public function getRekeningBalancePerMonth(int $tahun): array
    {
        $rekenings = $this->db->table('rekening_bank rb')
            ->select('rb.id, rb.nama, rb.saldo_awal, a.nomor_akun, jdt.kode AS dana_kode')
            ->join('akun a',        'a.id = rb.akun_id',        'left')
            ->join('jenis_dana jdt','jdt.id = rb.jenis_dana_id','left')
            ->orderBy('a.nomor_akun', 'ASC')
            ->get()->getResultArray();

        if (empty($rekenings)) return [];

        $movements = $this->db->table('jurnal_detail jd')
            ->select('jd.rekening_bank_id, p.bulan, SUM(jd.debet) AS dbt, SUM(jd.kredit) AS krd')
            ->join('jurnal j',   'j.id = jd.jurnal_id')
            ->join('periode p',  'p.id = j.periode_id')
            ->where('p.tahun', $tahun)
            ->where('jd.rekening_bank_id >', 0)
            ->groupBy('jd.rekening_bank_id, p.bulan')
            ->get()->getResultArray();

        $movMap = [];
        foreach ($movements as $m) {
            $movMap[(int)$m['rekening_bank_id']][(int)$m['bulan']] = [
                'dbt' => (float)$m['dbt'],
                'krd' => (float)$m['krd'],
            ];
        }

        $result = [];
        foreach ($rekenings as $rek) {
            $id      = (int)$rek['id'];
            $running = (float)$rek['saldo_awal'];
            $balans  = [];
            for ($b = 1; $b <= 12; $b++) {
                $running += ($movMap[$id][$b]['dbt'] ?? 0) - ($movMap[$id][$b]['krd'] ?? 0);
                $balans[$b] = $running;
            }
            $result[$id] = [
                'id'         => $id,
                'nama'       => $rek['nama'],
                'nomor_akun' => $rek['nomor_akun'] ?? '',
                'dana_kode'  => $rek['dana_kode']  ?? '',
                'bulan'      => $balans,
            ];
        }
        return $result;
    }

    /**
     * Saldo dana per jenis_dana kode per bulan — dihitung langsung dari jurnal.
     * Saldo = kumulatif penerimaan − penyaluran − biaya per dana per bulan.
     * Jika tabel saldo_dana terisi (proses tutup periode), data itu yang dipakai.
     * Returns: ['ZAKAT' => [1 => saldo, ..., 12 => saldo], ...]
     */
    public function getSaldoDanaPerMonth(int $tahun): array
    {
        // Saldo_akhir Desember tahun sebelumnya sebagai titik awal
        $prevRows = $this->db->table('saldo_dana sd')
            ->select('sd.saldo_akhir, jd.kode')
            ->join('jenis_dana jd', 'jd.id = sd.jenis_dana_id')
            ->join('periode p',     'p.id = sd.periode_id')
            ->where('p.tahun', $tahun - 1)
            ->where('p.bulan', 12)
            ->get()->getResultArray();

        $saldoAwal = [];
        foreach ($prevRows as $r) {
            $saldoAwal[$r['kode']] = (float)$r['saldo_akhir'];
        }

        // Saldo awal manual (dari tabel saldo_dana_awal) — hanya untuk kode yang belum ada dari saldo_dana
        $manualRows = $this->db->table('saldo_dana_awal sa')
            ->select('sa.saldo, jd.kode')
            ->join('jenis_dana jd', 'jd.id = sa.jenis_dana_id')
            ->where('sa.tahun', $tahun)
            ->get()->getResultArray();
        foreach ($manualRows as $r) {
            if (!isset($saldoAwal[$r['kode']])) {
                $saldoAwal[$r['kode']] = (float)$r['saldo'];
            }
        }

        // Saldo_dana resmi untuk periode yang sudah ditutup di tahun ini
        $sdRows = $this->db->table('saldo_dana sd')
            ->select('sd.saldo_akhir, jd.kode, p.bulan')
            ->join('jenis_dana jd', 'jd.id = sd.jenis_dana_id')
            ->join('periode p',     'p.id = sd.periode_id')
            ->where('p.tahun', $tahun)
            ->get()->getResultArray();

        $sdMap = [];
        foreach ($sdRows as $r) {
            $sdMap[$r['kode']][(int)$r['bulan']] = (float)$r['saldo_akhir'];
        }

        // Pergerakan jurnal per bulan untuk periode yang belum ditutup
        $jRows = $this->db->table('jurnal j')
            ->select('jdt.kode, p.bulan, j.jenis_transaksi, SUM(j.total_debet) AS total')
            ->join('jenis_dana jdt', 'jdt.id = j.jenis_dana_id')
            ->join('periode p',      'p.id = j.periode_id')
            ->where('p.tahun', $tahun)
            ->whereIn('j.jenis_transaksi', ['penerimaan', 'penyaluran', 'biaya'])
            ->groupBy('jdt.kode, p.bulan, j.jenis_transaksi')
            ->get()->getResultArray();

        $movMap = [];
        foreach ($jRows as $r) {
            $movMap[$r['kode']][(int)$r['bulan']][$r['jenis_transaksi']] = (float)$r['total'];
        }

        $result = [];
        foreach (['ZAKAT','INFAK_T','INFAK_TT','AMIL','CSR','WAKAF','KAS_KECIL'] as $kode) {
            $running = $saldoAwal[$kode] ?? 0.0;
            for ($b = 1; $b <= 12; $b++) {
                if (isset($sdMap[$kode][$b])) {
                    // Periode ditutup: pakai saldo_akhir resmi
                    $running = $sdMap[$kode][$b];
                } else {
                    // Periode belum ditutup: hitung dari jurnal
                    $masuk  =  ($movMap[$kode][$b]['penerimaan'] ?? 0);
                    $keluar = ($movMap[$kode][$b]['penyaluran']  ?? 0)
                            + ($movMap[$kode][$b]['biaya']        ?? 0);
                    $running += $masuk - $keluar;
                }
                $result[$kode][$b] = $running;
            }
        }
        return $result;
    }

    /**
     * Outstanding piutang (sisa_piutang aktif) as of end of each month.
     * Returns: [1 => total, ..., 12 => total]
     */
    public function getPiutangPerMonth(int $tahun): array
    {
        $result = [];
        for ($b = 1; $b <= 12; $b++) {
            $lastDay = date('Y-m-t', mktime(0, 0, 0, $b, 1, $tahun));
            $row = $this->db->table('piutang')
                ->selectSum('sisa_piutang')
                ->where('tanggal_pinjam <=', $lastDay)
                ->where('status', 'aktif')
                ->get()->getRow();
            $result[$b] = (float)($row->sisa_piutang ?? 0);
        }
        return $result;
    }

    /**
     * Current aset_tetap totals split by kepemilikan.
     * Returns: ['tetap' => ['hp','akum'], 'kelolaan' => ['hp','akum']]
     */
    public function getAsetTetap(): array
    {
        $amil = $this->db->table('aset_tetap')
            ->selectSum('harga_perolehan',    'hp')
            ->selectSum('akumulasi_penyusutan','akum')
            ->where('jenis_kepemilikan', 'amil')
            ->where('is_aktif', 1)
            ->get()->getRow();

        $kel = $this->db->table('aset_tetap')
            ->selectSum('harga_perolehan',    'hp')
            ->selectSum('akumulasi_penyusutan','akum')
            ->whereIn('jenis_kepemilikan', ['zakat','infak','wakaf'])
            ->where('is_aktif', 1)
            ->get()->getRow();

        return [
            'tetap'    => ['hp' => (float)($amil->hp ?? 0), 'akum' => (float)($amil->akum ?? 0)],
            'kelolaan' => ['hp' => (float)($kel->hp  ?? 0), 'akum' => (float)($kel->akum  ?? 0)],
        ];
    }

    /**
     * Cumulative balance of akun under a given nomor_akun prefix (aset type: debet - kredit).
     * Returns: [1 => balance, ..., 12 => balance]
     */
    public function getAkunGroupBalancePerMonth(int $tahun, string $prefix): array
    {
        $movements = $this->db->table('jurnal_detail jd')
            ->select('p.bulan, SUM(jd.debet) AS dbt, SUM(jd.kredit) AS krd')
            ->join('jurnal j',  'j.id = jd.jurnal_id')
            ->join('periode p', 'p.id = j.periode_id')
            ->join('akun a',    'a.id = jd.akun_id')
            ->where('p.tahun', $tahun)
            ->where('a.is_header', 0)
            ->like('a.nomor_akun', $prefix, 'after')
            ->groupBy('p.bulan')
            ->get()->getResultArray();

        $movMap = [];
        foreach ($movements as $m) {
            $movMap[(int)$m['bulan']] = [(float)$m['dbt'], (float)$m['krd']];
        }

        $result  = [];
        $running = 0.0;
        for ($b = 1; $b <= 12; $b++) {
            $running += ($movMap[$b][0] ?? 0) - ($movMap[$b][1] ?? 0);
            $result[$b] = $running;
        }
        return $result;
    }

    /**
     * Cumulative liabilitas balance (kredit - debet) per month,
     * split into Jangka Pendek (201xxxxx) and Jangka Panjang (202xxxxx).
     * Returns: ['pendek' => [1..12], 'panjang' => [1..12]]
     */
    public function getLiabilitasPerMonth(int $tahun): array
    {
        $movements = $this->db->table('jurnal_detail jd')
            ->select('a.nomor_akun, p.bulan, SUM(jd.debet) AS dbt, SUM(jd.kredit) AS krd')
            ->join('jurnal j',  'j.id = jd.jurnal_id')
            ->join('periode p', 'p.id = j.periode_id')
            ->join('akun a',    'a.id = jd.akun_id')
            ->where('p.tahun', $tahun)
            ->where('a.is_header', 0)
            ->like('a.nomor_akun', '20', 'after')
            ->groupBy('a.nomor_akun, p.bulan')
            ->get()->getResultArray();

        $pendek  = array_fill(1, 12, 0.0);
        $panjang = array_fill(1, 12, 0.0);

        foreach ($movements as $m) {
            $b   = (int)$m['bulan'];
            $val = (float)$m['krd'] - (float)$m['dbt'];
            if (str_starts_with($m['nomor_akun'], '201')) {
                $pendek[$b]  += $val;
            } else {
                $panjang[$b] += $val;
            }
        }

        // Compute cumulative running balance per month
        $cpd = $cpj = 0.0;
        $cumPendek = $cumPanjang = [];
        for ($b = 1; $b <= 12; $b++) {
            $cpd += $pendek[$b];
            $cpj += $panjang[$b];
            $cumPendek[$b]  = $cpd;
            $cumPanjang[$b] = $cpj;
        }

        return ['pendek' => $cumPendek, 'panjang' => $cumPanjang];
    }

    /**
     * Perubahan dana per jenis_dana per bulan.
     * Returns: [kode => [1..12 => ['pen','psl','bya','saldo_awal','delta','saldo_akhir']]]
     */
    public function getPerubahanDana(int $tahun): array
    {
        $allKodes = ['ZAKAT','INFAK_T','INFAK_TT','AMIL','CSR','WAKAF'];

        // ── 1. Saldo awal tahun dari saldo_dana Desember tahun lalu ──────
        $prevSdRows = $this->db->query("
            SELECT jd.kode, sd.saldo_akhir
            FROM saldo_dana sd
            JOIN jenis_dana jd ON jd.id = sd.jenis_dana_id
            JOIN periode p     ON p.id  = sd.periode_id
            WHERE p.tahun = ? AND p.bulan = 12
        ", [$tahun - 1])->getResultArray();

        $saldoAwalTahun = [];
        foreach ($prevSdRows as $r) {
            $saldoAwalTahun[$r['kode']] = (float)$r['saldo_akhir'];
        }

        // ── 1b. Saldo awal manual dari tabel saldo_dana_awal ─────────────
        // (hanya dipakai untuk kode yang belum ada dari saldo_dana)
        $manualSdRows = $this->db->table('saldo_dana_awal sa')
            ->select('sa.saldo, jd.kode')
            ->join('jenis_dana jd', 'jd.id = sa.jenis_dana_id')
            ->where('sa.tahun', $tahun)
            ->get()->getResultArray();
        foreach ($manualSdRows as $r) {
            if (!isset($saldoAwalTahun[$r['kode']])) {
                $saldoAwalTahun[$r['kode']] = (float)$r['saldo'];
            }
        }

        // ── 2. Fallback: hitung dari semua jurnal sebelum tahun ini ──────
        // (untuk dana yang tidak punya record saldo_dana Desember lalu maupun saldo_dana_awal)
        $kodesMissing = array_values(array_diff($allKodes, array_keys($saldoAwalTahun)));
        if (!empty($kodesMissing)) {
            $ph = implode(',', array_fill(0, count($kodesMissing), '?'));
            $prevRows = $this->db->query("
                SELECT jd.kode, j.jenis_transaksi, SUM(j.total_debet) AS total
                FROM jurnal j
                JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
                JOIN periode p     ON p.id  = j.periode_id
                WHERE p.tahun < ?
                  AND jd.kode IN ($ph)
                  AND j.jenis_transaksi IN ('penerimaan','penyaluran','biaya')
                GROUP BY jd.kode, j.jenis_transaksi
            ", array_merge([$tahun], $kodesMissing))->getResultArray();

            $prevMap = [];
            foreach ($prevRows as $r) {
                $prevMap[$r['kode']][$r['jenis_transaksi']] = (float)$r['total'];
            }
            foreach ($kodesMissing as $kode) {
                $pen = $prevMap[$kode]['penerimaan'] ?? 0.0;
                $psl = $prevMap[$kode]['penyaluran'] ?? 0.0;
                $bya = $prevMap[$kode]['biaya']      ?? 0.0;
                $saldoAwalTahun[$kode] = $pen - $psl - $bya;
            }
        }

        // ── 3. Transaksi tahun ini per kode per bulan ────────────────────
        $rows = $this->db->query("
            SELECT jd.kode, p.bulan, j.jenis_transaksi, SUM(j.total_debet) AS total
            FROM jurnal j
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            JOIN periode p     ON p.id  = j.periode_id
            WHERE p.tahun = ?
              AND j.jenis_transaksi IN ('penerimaan','penyaluran','biaya')
            GROUP BY jd.kode, p.bulan, j.jenis_transaksi
        ", [$tahun])->getResultArray();

        $movMap = [];
        foreach ($rows as $r) {
            $movMap[$r['kode']][(int)$r['bulan']][$r['jenis_transaksi']] = (float)$r['total'];
        }

        // ── 4. Hitung saldo_awal / delta / saldo_akhir per bulan ─────────
        $result = [];
        foreach ($allKodes as $kode) {
            $saldo = $saldoAwalTahun[$kode] ?? 0.0;
            for ($b = 1; $b <= 12; $b++) {
                $pen   = (float)($movMap[$kode][$b]['penerimaan'] ?? 0);
                $psl   = (float)($movMap[$kode][$b]['penyaluran'] ?? 0);
                $bya   = (float)($movMap[$kode][$b]['biaya']      ?? 0);
                $delta = $pen - $psl - $bya;
                $result[$kode][$b] = [
                    'saldo_awal'  => $saldo,
                    'pen'         => $pen,
                    'psl'         => $psl,
                    'bya'         => $bya,
                    'delta'       => $delta,
                    'saldo_akhir' => $saldo + $delta,
                ];
                $saldo += $delta;
            }
        }
        return $result;
    }

    /**
     * Arus kas per bulan: operasi, investasi, pendanaan + saldo kas.
     * Returns: ['pen'=>[kode=>[1..12]], 'psl'=>..., 'bya'=>...,
     *           'inv_masuk'=>[1..12], 'inv_keluar'=>[1..12],
     *           'pend_masuk'=>[1..12], 'pend_keluar'=>[1..12],
     *           'saldo_kas_awal'=>[1..12], 'saldo_kas_akhir'=>[1..12]]
     */
    public function getArusKas(int $tahun): array
    {
        // Operasi: penerimaan, penyaluran, biaya per jenis_dana per bulan
        $opRows = $this->db->query("
            SELECT jd.kode, p.bulan, j.jenis_transaksi, SUM(j.total_debet) AS total
            FROM jurnal j
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            JOIN periode p     ON p.id  = j.periode_id
            WHERE p.tahun = ?
              AND j.jenis_transaksi IN ('penerimaan','penyaluran','biaya')
            GROUP BY jd.kode, p.bulan, j.jenis_transaksi
        ", [$tahun])->getResultArray();

        $pen = $psl = $bya = [];
        foreach ($opRows as $r) {
            $b = (int)$r['bulan'];
            $v = (float)$r['total'];
            if ($r['jenis_transaksi'] === 'penerimaan')      $pen[$r['kode']][$b] = $v;
            elseif ($r['jenis_transaksi'] === 'penyaluran')  $psl[$r['kode']][$b] = $v;
            else                                              $bya[$r['kode']][$b] = $v;
        }

        // Investasi: akun aset tetap/kelolaan (12xxxxx)
        // kredit = penjualan (masuk), debet = pembelian (keluar)
        $invRows = $this->db->query("
            SELECT p.bulan, SUM(jd.kredit) AS masuk, SUM(jd.debet) AS keluar
            FROM jurnal_detail jd
            JOIN jurnal j  ON j.id  = jd.jurnal_id
            JOIN periode p ON p.id  = j.periode_id
            JOIN akun a    ON a.id  = jd.akun_id
            WHERE p.tahun = ? AND a.nomor_akun LIKE '12%'
            GROUP BY p.bulan
        ", [$tahun])->getResultArray();

        $invMasuk = $invKeluar = array_fill(1, 12, 0.0);
        foreach ($invRows as $r) {
            $b = (int)$r['bulan'];
            $invMasuk[$b]  = (float)$r['masuk'];
            $invKeluar[$b] = (float)$r['keluar'];
        }

        // Pendanaan: akun liabilitas (20xxxxx)
        // kredit = hutang masuk, debet = hutang dibayar
        $pendRows = $this->db->query("
            SELECT p.bulan, SUM(jd.kredit) AS masuk, SUM(jd.debet) AS keluar
            FROM jurnal_detail jd
            JOIN jurnal j  ON j.id  = jd.jurnal_id
            JOIN periode p ON p.id  = j.periode_id
            JOIN akun a    ON a.id  = jd.akun_id
            WHERE p.tahun = ? AND a.nomor_akun LIKE '20%'
            GROUP BY p.bulan
        ", [$tahun])->getResultArray();

        $pendMasuk = $pendKeluar = array_fill(1, 12, 0.0);
        foreach ($pendRows as $r) {
            $b = (int)$r['bulan'];
            $pendMasuk[$b]  = (float)$r['masuk'];
            $pendKeluar[$b] = (float)$r['keluar'];
        }

        // Saldo kas+bank (rekening 11101x + 11102x kecuali SIMKA)
        $simkaNomors = ['11102051','11102052','11102053','11102054'];
        $rekenings   = $this->db->table('rekening_bank rb')
            ->select('rb.id, rb.saldo_awal, a.nomor_akun')
            ->join('akun a', 'a.id = rb.akun_id', 'left')
            ->get()->getResultArray();

        $kasRek = array_values(array_filter($rekenings, function ($r) use ($simkaNomors) {
            $n = $r['nomor_akun'] ?? '';
            return (str_starts_with($n, '11101') || str_starts_with($n, '11102'))
                && !in_array($n, $simkaNomors);
        }));

        $monthlyNet = array_fill(1, 12, 0.0);
        if (!empty($kasRek)) {
            $rekIds       = implode(',', array_map(fn($r) => (int)$r['id'], $kasRek));
            $movRows      = $this->db->query("
                SELECT jd.rekening_bank_id, p.bulan, SUM(jd.debet) AS dbt, SUM(jd.kredit) AS krd
                FROM jurnal_detail jd
                JOIN jurnal j  ON j.id  = jd.jurnal_id
                JOIN periode p ON p.id  = j.periode_id
                WHERE p.tahun = ? AND jd.rekening_bank_id IN ($rekIds)
                GROUP BY jd.rekening_bank_id, p.bulan
            ", [$tahun])->getResultArray();

            $movByRek = [];
            foreach ($movRows as $m) {
                $movByRek[(int)$m['rekening_bank_id']][(int)$m['bulan']] = [
                    'd' => (float)$m['dbt'], 'k' => (float)$m['krd'],
                ];
            }
            foreach ($kasRek as $rek) {
                $id = (int)$rek['id'];
                for ($b = 1; $b <= 12; $b++) {
                    $monthlyNet[$b] += ($movByRek[$id][$b]['d'] ?? 0)
                                     - ($movByRek[$id][$b]['k'] ?? 0);
                }
            }
        }

        $saldoAwalTotal  = (float)array_sum(array_column($kasRek, 'saldo_awal'));
        $saldoKasAwal    = [];
        $saldoKasAkhir   = [];
        $running         = $saldoAwalTotal;
        for ($b = 1; $b <= 12; $b++) {
            $saldoKasAwal[$b]  = $running;
            $running          += $monthlyNet[$b];
            $saldoKasAkhir[$b] = $running;
        }

        return [
            'pen'             => $pen,
            'psl'             => $psl,
            'bya'             => $bya,
            'inv_masuk'       => $invMasuk,
            'inv_keluar'      => $invKeluar,
            'pend_masuk'      => $pendMasuk,
            'pend_keluar'     => $pendKeluar,
            'saldo_kas_awal'  => $saldoKasAwal,
            'saldo_kas_akhir' => $saldoKasAkhir,
        ];
    }

    /**
     * Returns list of unique tahun values from periode table (descending).
     */
    public function getAvailableYears(): array
    {
        $rows = $this->db->table('periode')
            ->select('tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'DESC')
            ->get()->getResultArray();

        return array_column($rows, 'tahun');
    }
}