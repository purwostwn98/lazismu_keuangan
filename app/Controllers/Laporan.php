<?php

namespace App\Controllers;

use App\Models\LaporanModel;

class Laporan extends BaseController
{
    private const BULAN = [
        1  => 'Januari',   2  => 'Februari', 3  => 'Maret',    4  => 'April',
        5  => 'Mei',       6  => 'Juni',      7  => 'Juli',     8  => 'Agustus',
        9  => 'September', 10 => 'Oktober',  11  => 'November', 12 => 'Desember',
    ];

    private LaporanModel $model;

    public function __construct()
    {
        $this->model = new LaporanModel();
    }

    // ── Laporan Posisi Keuangan ──────────────────────────────
    public function posisiKeuangan(): string
    {
        $tahun         = (int)($this->request->getGet('tahun') ?? date('Y'));
        $availableYears = $this->model->getAvailableYears();
        if (empty($availableYears)) {
            $availableYears = [(int)date('Y')];
        }

        // ── Load data ─────────────────────────────────────────
        $rekeningData = $this->model->getRekeningBalancePerMonth($tahun);
        $saldoDana    = $this->model->getSaldoDanaPerMonth($tahun);
        $piutang      = $this->model->getPiutangPerMonth($tahun);
        $asetTetap    = $this->model->getAsetTetap();
        $liabilitas   = $this->model->getLiabilitasPerMonth($tahun);
        $persediaan   = $this->model->getAkunGroupBalancePerMonth($tahun, '1130');
        $uangMuka     = $this->model->getAkunGroupBalancePerMonth($tahun, '1140');
        $biayaDimuka  = $this->model->getAkunGroupBalancePerMonth($tahun, '1150');

        // ── Classify rekening by akun prefix ──────────────────
        $kasRekening   = [];
        $bankRekening  = [];
        $simkaRekening = [];
        $simkaNomors   = ['11102051','11102052','11102053','11102054'];

        foreach ($rekeningData as $r) {
            $nomor = $r['nomor_akun'];
            if (str_starts_with($nomor, '11101')) {
                $kasRekening[] = $r;
            } elseif (in_array($nomor, $simkaNomors)) {
                $simkaRekening[] = $r;
            } elseif (str_starts_with($nomor, '11102')) {
                $bankRekening[] = $r;
            }
        }

        // ── Helper: sum multiple month arrays ─────────────────
        $sum12 = static function (array ...$arrays): array {
            $res = array_fill(1, 12, 0.0);
            foreach ($arrays as $arr) {
                for ($b = 1; $b <= 12; $b++) {
                    $res[$b] += (float)($arr[$b] ?? 0);
                }
            }
            return $res;
        };

        $fill12 = static fn(float $v): array => array_fill(1, 12, $v);

        // ── Aggregated subtotals ───────────────────────────────
        $kasColBulan   = empty($kasRekening)   ? array_fill(1,12,0.0) : $sum12(...array_column($kasRekening,  'bulan'));
        $bankColBulan  = empty($bankRekening)  ? array_fill(1,12,0.0) : $sum12(...array_column($bankRekening, 'bulan'));
        $simkaColBulan = empty($simkaRekening) ? array_fill(1,12,0.0) : $sum12(...array_column($simkaRekening,'bulan'));

        $jumlahAsetLancar = $sum12(
            $kasColBulan, $bankColBulan,
            $piutang, $persediaan, $uangMuka, $biayaDimuka,
            $simkaColBulan
        );

        // Aset tetap: static value repeated for all 12 months
        $atHp    = $fill12($asetTetap['tetap']['hp']);
        $atAkum  = $fill12($asetTetap['tetap']['akum']);
        $atNilai = $fill12($asetTetap['tetap']['hp'] - $asetTetap['tetap']['akum']);

        $akHp    = $fill12($asetTetap['kelolaan']['hp']);
        $akAkum  = $fill12($asetTetap['kelolaan']['akum']);
        $akNilai = $fill12($asetTetap['kelolaan']['hp'] - $asetTetap['kelolaan']['akum']);

        $jumlahAset = $sum12($jumlahAsetLancar, $atNilai, $akNilai);

        // Liabilitas
        $jumlahLiabilitas = $sum12($liabilitas['pendek'], $liabilitas['panjang']);

        // Saldo dana per jenis
        $sdZakat = [];
        $sdInfak = [];
        $sdAmil  = [];
        $sdCsr   = [];
        for ($b = 1; $b <= 12; $b++) {
            $sdZakat[$b] = ($saldoDana['ZAKAT'][$b]    ?? 0);
            $sdInfak[$b] = ($saldoDana['INFAK_T'][$b]  ?? 0) + ($saldoDana['INFAK_TT'][$b] ?? 0);
            $sdAmil[$b]  = ($saldoDana['AMIL'][$b]     ?? 0);
            $sdCsr[$b]   = ($saldoDana['CSR'][$b]      ?? 0);
        }
        $jumlahSaldoDana = $sum12($sdZakat, $sdInfak, $sdAmil, $sdCsr);
        $jumlahKewajiban = $sum12($jumlahLiabilitas, $jumlahSaldoDana);

        // ── Build rows ────────────────────────────────────────
        $rows = [];
        $R    = static function (string $type, string $label, int $indent, array $values = []): array {
            return compact('type', 'label', 'indent', 'values');
        };

        // ─── ASET ─────────────────────────────────────────────
        $rows[] = $R('title',   'ASET', 0);
        $rows[] = $R('section', 'Aset Lancar', 1);

        $rows[] = $R('group', 'Kas', 1);
        foreach ($kasRekening as $r) {
            $rows[] = $R('data', $r['nama'], 2, $r['bulan']);
        }

        $rows[] = $R('group', 'Bank', 1);
        foreach ($bankRekening as $r) {
            $rows[] = $R('data', $r['nama'], 2, $r['bulan']);
        }

        $rows[] = $R('data', 'Piutang',             1, $piutang);
        $rows[] = $R('data', 'Persediaan',           1, $persediaan);
        $rows[] = $R('data', 'Uang Muka',            1, $uangMuka);
        $rows[] = $R('data', 'Biaya Dibayar Dimuka', 1, $biayaDimuka);

        $rows[] = $R('group', 'Investasi', 1);
        foreach ($simkaRekening as $r) {
            $rows[] = $R('data', $r['nama'], 2, $r['bulan']);
        }

        $rows[] = $R('subtotal', 'Jumlah Aset Lancar', 2, $jumlahAsetLancar);

        $rows[] = $R('section', 'Aset Tetap', 1);
        $rows[] = $R('data',    'Aset Tetap',            2, $atHp);
        $rows[] = $R('data',    'Akumulasi Penyusutan',   2, $atAkum);
        $rows[] = $R('subtotal','Nilai Buku',             2, $atNilai);

        $rows[] = $R('section', 'Aset Kelolaan', 1);
        $rows[] = $R('data',    'Aset Kelolaan',          2, $akHp);
        $rows[] = $R('data',    'Akumulasi Penyusutan',   2, $akAkum);
        $rows[] = $R('subtotal','Nilai Buku',             2, $akNilai);

        $rows[] = $R('total', 'JUMLAH ASET', 1, $jumlahAset);
        $rows[] = $R('spacer', '', 0);

        // ─── LIABILITAS DAN SALDO DANA ────────────────────────
        $rows[] = $R('title',   'LIABILITAS DAN SALDO DANA', 0);
        $rows[] = $R('section', 'LIABILITAS', 1);
        $rows[] = $R('data',    'Liabilitas Jangka Pendek',  2, $liabilitas['pendek']);
        $rows[] = $R('data',    'Liabilitas Jangka Panjang', 2, $liabilitas['panjang']);
        $rows[] = $R('subtotal','Jumlah Liabilitas',         2, $jumlahLiabilitas);

        $rows[] = $R('section', 'SALDO DANA', 1);
        $rows[] = $R('data',    'Zakat',          2, $sdZakat);
        $rows[] = $R('data',    'Infak Sedekah',  2, $sdInfak);
        $rows[] = $R('data',    'Amil',           2, $sdAmil);
        $rows[] = $R('data',    'CSR',            2, $sdCsr);
        $rows[] = $R('subtotal','Jumlah Saldo Dana', 2, $jumlahSaldoDana);

        $rows[] = $R('note',  '* Akum. Penyusutan Liabilitas & Saldo Dana', 0, array_fill(1,12,0.0));
        $rows[] = $R('total', 'JUMLAH KEWAJIBAN DAN SALDO DANA', 1, $jumlahKewajiban);

        return view('laporan/posisi_keuangan', [
            'pageTitle'      => 'Laporan Posisi Keuangan',
            'tahun'          => $tahun,
            'availableYears' => $availableYears,
            'bulanNames'     => self::BULAN,
            'rows'           => $rows,
        ]);
    }

    // ── Laporan Perubahan Dana ───────────────────────────────
    public function perubahanDana(): string
    {
        $tahun          = (int)($this->request->getGet('tahun') ?? date('Y'));
        $availableYears = $this->model->getAvailableYears();
        if (empty($availableYears)) $availableYears = [(int)date('Y')];

        $pd = $this->model->getPerubahanDana($tahun);

        // ── Helper: ekstrak satu field per bulan dari $pd[kode] ──
        $col = static function (array $pd, string $kode, string $field): array {
            $res = [];
            for ($b = 1; $b <= 12; $b++) {
                $res[$b] = $pd[$kode][$b][$field] ?? 0.0;
            }
            return $res;
        };

        // ── Helper: sum beberapa kode, satu field ─────────────
        $sumCols = static function (array $pd, array $kodes, string $field) use ($col): array {
            $res = array_fill(1, 12, 0.0);
            foreach ($kodes as $k) {
                $arr = $col($pd, $k, $field);
                for ($b = 1; $b <= 12; $b++) $res[$b] += $arr[$b];
            }
            return $res;
        };

        $R = static function (string $type, string $label, int $indent, array $values = []): array {
            return compact('type', 'label', 'indent', 'values');
        };

        $rows = [];

        // ── Fungsi pembangun section per dana ─────────────────
        $addDanaSection = function (
            string $label,       // e.g. 'DANA ZAKAT'
            string $saldoLabel,  // e.g. 'Saldo Akhir Dana Zakat'
            array  $penArr,      // penerimaan per bulan
            array  $pslArr,      // penyaluran per bulan
            array  $byaArr,      // biaya per bulan
            array  $deltaArr,    // surplus/defisit per bulan
            array  $saldoAwal,   // saldo awal per bulan
            array  $saldoAkhir   // saldo akhir per bulan
        ) use (&$rows, $R): void {
            $rows[] = $R('section', $label, 0);

            $rows[] = $R('group',   'PENERIMAAN', 1);
            $rows[] = $R('data',    'Jumlah Penerimaan', 2, $penArr);
            $rows[] = $R('subtotal','Jumlah Penerimaan ' . $label, 1, $penArr);

            $rows[] = $R('group',   'PENYALURAN', 1);
            $rows[] = $R('data',    'Jumlah Penyaluran', 2, $pslArr);
            if (array_sum($byaArr) != 0) {
                $rows[] = $R('data', 'Biaya Pengelolaan', 2, $byaArr);
            }
            $rows[] = $R('subtotal','Jumlah Penyaluran ' . $label, 1,
                         array_combine(range(1,12), array_map(fn($b) => $pslArr[$b] + $byaArr[$b], range(1,12))));

            $rows[] = $R('subtotal', 'Surplus (Defisit)', 1, $deltaArr);
            $rows[] = $R('data',     'Saldo Awal', 1, $saldoAwal);
            $rows[] = $R('total',    $saldoLabel, 0, $saldoAkhir);
            $rows[] = $R('spacer',   '', 0);
        };

        // ── DANA ZAKAT ────────────────────────────────────────
        $addDanaSection(
            'DANA ZAKAT', 'Saldo Akhir Dana Zakat',
            $col($pd,'ZAKAT','pen'),     $col($pd,'ZAKAT','psl'),
            $col($pd,'ZAKAT','bya'),     $col($pd,'ZAKAT','delta'),
            $col($pd,'ZAKAT','saldo_awal'), $col($pd,'ZAKAT','saldo_akhir')
        );

        // ── DANA INFAK (gabungan INFAK_T + INFAK_TT) ─────────
        $infakPen    = $sumCols($pd, ['INFAK_T','INFAK_TT'], 'pen');
        $infakPsl    = $sumCols($pd, ['INFAK_T','INFAK_TT'], 'psl');
        $infakBya    = $sumCols($pd, ['INFAK_T','INFAK_TT'], 'bya');
        $infakDelta  = $sumCols($pd, ['INFAK_T','INFAK_TT'], 'delta');
        $infakSaldoAwal  = $sumCols($pd, ['INFAK_T','INFAK_TT'], 'saldo_awal');
        $infakSaldoAkhir = $sumCols($pd, ['INFAK_T','INFAK_TT'], 'saldo_akhir');

        // Infak section dengan sub-breakdown terikat vs tidak terikat
        $rows[] = $R('section', 'DANA INFAK / SEDEKAH', 0);

        $rows[] = $R('group', 'PENERIMAAN', 1);
        $rows[] = $R('data',  'Penerimaan Infak Terikat',       2, $col($pd,'INFAK_T', 'pen'));
        $rows[] = $R('data',  'Penerimaan Infak Tidak Terikat', 2, $col($pd,'INFAK_TT','pen'));
        $rows[] = $R('subtotal', 'Jumlah Penerimaan Dana Infak', 1, $infakPen);

        $rows[] = $R('group', 'PENYALURAN', 1);
        $rows[] = $R('group', 'Infak Terikat', 2);
        $rows[] = $R('data',  'Jumlah Penyaluran Infak Terikat', 3, $col($pd,'INFAK_T','psl'));
        $rows[] = $R('group', 'Infak Tidak Terikat', 2);
        $rows[] = $R('data',  'Jumlah Penyaluran Infak Tidak Terikat', 3, $col($pd,'INFAK_TT','psl'));
        $infakTotPsl = array_combine(range(1,12),
            array_map(fn($b) => $infakPsl[$b] + $infakBya[$b], range(1,12)));
        $rows[] = $R('subtotal', 'Jumlah Penyaluran Dana Infak', 1, $infakTotPsl);

        $rows[] = $R('subtotal', 'Surplus (Defisit)', 1, $infakDelta);
        $rows[] = $R('data',     'Saldo Awal', 1, $infakSaldoAwal);
        $rows[] = $R('total',    'SALDO AKHIR DANA INFAK', 0, $infakSaldoAkhir);
        $rows[] = $R('spacer',   '', 0);

        // ── DANA CSR ──────────────────────────────────────────
        $addDanaSection(
            'DANA CSR', 'SALDO AKHIR DANA CSR',
            $col($pd,'CSR','pen'),    $col($pd,'CSR','psl'),
            $col($pd,'CSR','bya'),    $col($pd,'CSR','delta'),
            $col($pd,'CSR','saldo_awal'), $col($pd,'CSR','saldo_akhir')
        );

        // ── DANA AMIL ─────────────────────────────────────────
        $addDanaSection(
            'DANA AMIL', 'SALDO AKHIR DANA AMIL',
            $col($pd,'AMIL','pen'),   $col($pd,'AMIL','psl'),
            $col($pd,'AMIL','bya'),   $col($pd,'AMIL','delta'),
            $col($pd,'AMIL','saldo_awal'), $col($pd,'AMIL','saldo_akhir')
        );

        // ── TOTAL SEMUA DANA ──────────────────────────────────
        $allKodes     = ['ZAKAT','INFAK_T','INFAK_TT','AMIL','CSR'];
        $totalPen     = $sumCols($pd, $allKodes, 'pen');
        $totalPsl     = $sumCols($pd, $allKodes, 'psl');
        $totalBya     = $sumCols($pd, $allKodes, 'bya');
        $totalDelta   = $sumCols($pd, $allKodes, 'delta');
        $totalSA      = $sumCols($pd, $allKodes, 'saldo_awal');
        $totalSAkhir  = $sumCols($pd, $allKodes, 'saldo_akhir');
        $totalTotPsl  = array_combine(range(1,12),
            array_map(fn($b) => $totalPsl[$b] + $totalBya[$b], range(1,12)));

        $rows[] = $R('section',  'TOTAL SEMUA DANA', 0);
        $rows[] = $R('subtotal', 'Total Penerimaan',         1, $totalPen);
        $rows[] = $R('subtotal', 'Total Penyaluran & Biaya', 1, $totalTotPsl);
        $rows[] = $R('subtotal', 'Surplus (Defisit) Bersih', 1, $totalDelta);
        $rows[] = $R('data',     'Saldo Awal',               1, $totalSA);
        $rows[] = $R('total',    'SALDO AKHIR SEMUA DANA',   0, $totalSAkhir);

        return view('laporan/perubahan_dana', [
            'pageTitle'      => 'Laporan Perubahan Dana',
            'tahun'          => $tahun,
            'availableYears' => $availableYears,
            'bulanNames'     => self::BULAN,
            'rows'           => $rows,
        ]);
    }

    // ── Laporan Arus Kas ─────────────────────────────────────
    public function arusKas(): string
    {
        $tahun          = (int)($this->request->getGet('tahun') ?? date('Y'));
        $availableYears = $this->model->getAvailableYears();
        if (empty($availableYears)) $availableYears = [(int)date('Y')];

        $ak = $this->model->getArusKas($tahun);

        // ── Helpers ───────────────────────────────────────────
        // Ambil nilai per bulan untuk satu kode+map
        $get = static function (array $map, string $kode): array {
            $res = array_fill(1, 12, 0.0);
            for ($b = 1; $b <= 12; $b++) {
                $res[$b] = (float)($map[$kode][$b] ?? 0);
            }
            return $res;
        };
        // Jumlahkan beberapa kode
        $sum = static function (array $map, array $kodes) use ($get): array {
            $res = array_fill(1, 12, 0.0);
            foreach ($kodes as $k) {
                $arr = $get($map, $k);
                for ($b = 1; $b <= 12; $b++) $res[$b] += $arr[$b];
            }
            return $res;
        };
        // Kurangkan dua array (a - b)
        $sub = static function (array $a, array $b): array {
            $res = [];
            for ($i = 1; $i <= 12; $i++) $res[$i] = ($a[$i] ?? 0) - ($b[$i] ?? 0);
            return $res;
        };
        // Tambah dua array
        $add = static function (array $a, array $b): array {
            $res = [];
            for ($i = 1; $i <= 12; $i++) $res[$i] = ($a[$i] ?? 0) + ($b[$i] ?? 0);
            return $res;
        };

        $R = static function (string $type, string $label, int $indent, array $values = []): array {
            return compact('type', 'label', 'indent', 'values');
        };

        // ── Operasi ───────────────────────────────────────────
        $penZakat  = $get($ak['pen'], 'ZAKAT');
        $penInfak  = $sum($ak['pen'], ['INFAK_T','INFAK_TT']);
        $penCsr    = $get($ak['pen'], 'CSR');
        $penAmil   = $get($ak['pen'], 'AMIL');
        $penWakaf  = $get($ak['pen'], 'WAKAF');
        $totPen    = $sum($ak['pen'], ['ZAKAT','INFAK_T','INFAK_TT','CSR','AMIL','WAKAF']);

        $pslZakat  = $get($ak['psl'], 'ZAKAT');
        $pslInfakT = $get($ak['psl'], 'INFAK_T');
        $pslInfakTT= $get($ak['psl'], 'INFAK_TT');
        $pslCsr    = $get($ak['psl'], 'CSR');
        $byaAmil   = $sum($ak['bya'], ['AMIL','ZAKAT','INFAK_T','INFAK_TT','CSR']);
        $totPengeluaran = $add($add($add($add($pslZakat, $pslInfakT), $pslInfakTT), $pslCsr), $byaAmil);

        $surplusOperasi = $sub($totPen, $totPengeluaran);

        // ── Investasi ─────────────────────────────────────────
        $surplusInvestasi = $sub($ak['inv_masuk'], $ak['inv_keluar']);

        // ── Pendanaan ─────────────────────────────────────────
        $surplusPendanaan = $sub($ak['pend_masuk'], $ak['pend_keluar']);

        // ── Kenaikan Kas & Saldo ──────────────────────────────
        $kenaikKas = $add($add($surplusOperasi, $surplusInvestasi), $surplusPendanaan);

        // ── Build rows ────────────────────────────────────────
        $rows = [];

        // — AKTIVITAS OPERASI —
        $rows[] = $R('section', 'Arus Kas Dari Aktivitas Operasi', 0);
        $rows[] = $R('group',   'PENERIMAAN', 1);
        $rows[] = $R('data',    'Penerimaan Zakat',            2, $penZakat);
        $rows[] = $R('data',    'Penerimaan Infak / Sedekah',  2, $penInfak);
        $rows[] = $R('data',    'Penerimaan CSR',              2, $penCsr);
        $rows[] = $R('data',    'Penerimaan Amil',             2, $penAmil);
        if (array_sum($penWakaf) != 0) {
            $rows[] = $R('data', 'Penerimaan Wakaf',           2, $penWakaf);
        }
        $rows[] = $R('subtotal','Jumlah Penerimaan',           1, $totPen);

        $rows[] = $R('group',   'PENGELUARAN', 1);
        $rows[] = $R('data',    'Penyaluran Dana Zakat',                  2, $pslZakat);
        $rows[] = $R('data',    'Penyaluran Dana Infak Sedekah Terikat',  2, $pslInfakT);
        $rows[] = $R('data',    'Penyaluran Dana Infak Sedekah Tidak Terikat', 2, $pslInfakTT);
        $rows[] = $R('data',    'Penyaluran CSR',                         2, $pslCsr);
        if (array_sum($byaAmil) != 0) {
            $rows[] = $R('data', 'Biaya Pengelolaan / Amil',              2, $byaAmil);
        }
        $rows[] = $R('subtotal','Jumlah Pengeluaran Dana',     1, $totPengeluaran);
        $rows[] = $R('total',   'Surplus (Defisit) Aktivitas Operasi', 0, $surplusOperasi);
        $rows[] = $R('spacer',  '', 0);

        // — AKTIVITAS INVESTASI —
        $rows[] = $R('section', 'Arus Kas Dari Aktivitas Investasi', 0);
        $rows[] = $R('group',   'PENERIMAAN', 1);
        $rows[] = $R('data',    'Penjualan Aset Tetap / Kelolaan',   2, $ak['inv_masuk']);
        $rows[] = $R('subtotal','Jumlah Penerimaan',                 1, $ak['inv_masuk']);
        $rows[] = $R('group',   'PENGELUARAN', 1);
        $rows[] = $R('data',    'Pembelian Aset Tetap / Kelolaan',   2, $ak['inv_keluar']);
        $rows[] = $R('subtotal','Jumlah Pengeluaran',                1, $ak['inv_keluar']);
        $rows[] = $R('total',   'Surplus (Defisit) Aktivitas Investasi', 0, $surplusInvestasi);
        $rows[] = $R('spacer',  '', 0);

        // — AKTIVITAS PENDANAAN —
        $rows[] = $R('section', 'Arus Kas Dari Aktivitas Pendanaan', 0);
        $rows[] = $R('group',   'PENERIMAAN', 1);
        $rows[] = $R('data',    'Hutang / Titipan Dana Masuk',   2, $ak['pend_masuk']);
        $rows[] = $R('subtotal','Jumlah Penerimaan',             1, $ak['pend_masuk']);
        $rows[] = $R('group',   'PENGELUARAN', 1);
        $rows[] = $R('data',    'Pembayaran Hutang / Titipan',   2, $ak['pend_keluar']);
        $rows[] = $R('subtotal','Jumlah Pengeluaran',            1, $ak['pend_keluar']);
        $rows[] = $R('total',   'Surplus (Defisit) Aktivitas Pendanaan', 0, $surplusPendanaan);
        $rows[] = $R('spacer',  '', 0);

        // — SALDO KAS —
        $rows[] = $R('subtotal', 'Kenaikan (Penurunan) Kas',  0, $kenaikKas);
        $rows[] = $R('data',     'Saldo Kas Awal Bulan',      0, $ak['saldo_kas_awal']);
        $rows[] = $R('total',    'SALDO KAS AKHIR BULAN',     0, $ak['saldo_kas_akhir']);

        return view('laporan/arus_kas', [
            'pageTitle'      => 'Laporan Arus Kas',
            'tahun'          => $tahun,
            'availableYears' => $availableYears,
            'bulanNames'     => self::BULAN,
            'rows'           => $rows,
        ]);
    }
}