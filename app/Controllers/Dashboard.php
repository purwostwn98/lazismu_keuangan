<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $db = \Config\Database::connect();

        // Semua periode yang punya transaksi (untuk dropdown)
        $periodeList = $db->query("
            SELECT p.id, p.bulan, p.tahun, p.nama
            FROM periode p
            WHERE EXISTS (SELECT 1 FROM jurnal j WHERE j.periode_id = p.id)
            ORDER BY p.tahun DESC, p.bulan DESC
        ")->getResultArray();

        // Periode aktif default: parameter GET atau periode terakhir ada data
        $periodeParam = $this->request->getGet('periode_id');
        if ($periodeParam && ctype_digit((string)$periodeParam)) {
            $selectedPeriode = $db->query(
                "SELECT id, bulan, tahun, nama FROM periode WHERE id = ?",
                [(int)$periodeParam]
            )->getRow();
        }
        if (empty($selectedPeriode) && !empty($periodeList)) {
            $selectedPeriode = (object) $periodeList[0];
        }

        $bulan = $selectedPeriode ? (int)$selectedPeriode->bulan : (int)date('n');
        $tahun = $selectedPeriode ? (int)$selectedPeriode->tahun : (int)date('Y');

        // ── Stat cards: penerimaan bulan ini per jenis dana ──
        $penBulanIni = $db->query("
            SELECT jd.kode, SUM(j.total_debet) AS total
            FROM jurnal j
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            JOIN periode p     ON p.id  = j.periode_id
            WHERE j.jenis_transaksi = 'penerimaan'
              AND p.bulan = ? AND p.tahun = ?
            GROUP BY jd.kode
        ", [$bulan, $tahun])->getResultArray();

        $penMap = array_column($penBulanIni, 'total', 'kode');

        $totalZakat      = (float)($penMap['ZAKAT']    ?? 0);
        $totalInfak      = (float)($penMap['INFAK_T']  ?? 0)
                         + (float)($penMap['INFAK_TT'] ?? 0);

        // Penyaluran + biaya bulan ini
        $totalPenyaluran = (float)($db->query("
            SELECT COALESCE(SUM(j.total_debet), 0) AS total
            FROM jurnal j
            JOIN periode p ON p.id = j.periode_id
            WHERE j.jenis_transaksi IN ('penyaluran', 'biaya')
              AND p.bulan = ? AND p.tahun = ?
        ", [$bulan, $tahun])->getRow()->total ?? 0);

        // Saldo dana kumulatif (all time)
        $totalSaldoDana = (float)($db->query("
            SELECT
                SUM(CASE WHEN jenis_transaksi = 'penerimaan' THEN total_debet ELSE 0 END) -
                SUM(CASE WHEN jenis_transaksi IN ('penyaluran','biaya') THEN total_debet ELSE 0 END)
                AS saldo
            FROM jurnal
        ")->getRow()->saldo ?? 0);

        // ── Saldo per jenis dana (tahun berjalan) ────────────
        $sdRows = $db->query("
            SELECT
                jd.kode,
                jd.nama,
                SUM(CASE WHEN j.jenis_transaksi = 'penerimaan'              THEN j.total_debet ELSE 0 END) AS penerimaan,
                SUM(CASE WHEN j.jenis_transaksi IN ('penyaluran','biaya')   THEN j.total_debet ELSE 0 END) AS penyaluran
            FROM jurnal j
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            JOIN periode p     ON p.id  = j.periode_id
            WHERE p.tahun = ?
            GROUP BY jd.id, jd.kode, jd.nama
            ORDER BY jd.id
        ", [$tahun])->getResultArray();

        // Pastikan dana utama selalu muncul meski belum ada transaksi
        $danaUtama = [
            'ZAKAT'    => 'Dana Zakat',
            'INFAK_T'  => 'Dana Infak Terikat',
            'INFAK_TT' => 'Dana Infak Tidak Terikat',
            'AMIL'     => 'Dana Amil',
            'CSR'      => 'Dana CSR',
        ];
        $sdMap = [];
        foreach ($sdRows as $r) {
            $sdMap[$r['kode']] = $r;
        }
        $saldoPerDana = [];
        foreach ($danaUtama as $kode => $nama) {
            $pen = (float)($sdMap[$kode]['penerimaan'] ?? 0);
            $psl = (float)($sdMap[$kode]['penyaluran'] ?? 0);
            $saldoPerDana[] = [
                'kode'       => $kode,
                'nama'       => $sdMap[$kode]['nama'] ?? $nama,
                'penerimaan' => $pen,
                'penyaluran' => $psl,
                'saldo'      => $pen - $psl,
            ];
        }

        // ── 10 transaksi jurnal terakhir ─────────────────────
        $transaksiTerakhir = $db->query("
            SELECT j.id, j.nomor_jurnal, j.tanggal, j.uraian,
                   j.total_debet, j.jenis_transaksi,
                   jd.nama AS nama_dana
            FROM jurnal j
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            ORDER BY j.tanggal DESC, j.id DESC
            LIMIT 10
        ")->getResultArray();

        // ── 5 donatur terbaru berdasarkan transaksi terakhir ──
        $donaturTerbaru = $db->query("
            SELECT d.nama, d.kode AS kode_donatur,
                   kd.nama AS kategori,
                   SUM(ph.jumlah) AS total_donasi,
                   MAX(ph.created_at) AS last_transaksi
            FROM penghimpunan ph
            JOIN donatur d         ON d.id  = ph.donatur_id
            JOIN kategori_donatur kd ON kd.id = d.kategori_id
            WHERE ph.donatur_id IS NOT NULL
            GROUP BY d.id, d.nama, d.kode, kd.nama
            ORDER BY last_transaksi DESC
            LIMIT 5
        ")->getResultArray();

        // ── Info ringkas ──────────────────────────────────────
        $jumlahDonaturAktif = (int)$db->query(
            "SELECT COUNT(*) AS n FROM donatur WHERE is_aktif = 1"
        )->getRow()->n;

        $periodeAktif = $db->query("
            SELECT nama FROM periode
            WHERE is_tutup = 0 AND tahun = ? AND bulan = ?
            LIMIT 1
        ", [$tahun, $bulan])->getRow();

        return view('dashboard/index', [
            'pageTitle'          => 'Dashboard',
            'bulan'              => $bulan,
            'tahun'              => $tahun,
            'periodeList'        => $periodeList,
            'selectedPeriodeId'  => $selectedPeriode->id ?? null,
            'namaBulanTahun'     => \IntlDateFormatter::formatObject(
                                      new \DateTime("$tahun-$bulan-01"),
                                      'MMMM yyyy', 'id_ID'
                                   ),
            // Stat cards
            'totalZakat'         => $totalZakat,
            'totalInfak'         => $totalInfak,
            'totalPenyaluran'    => $totalPenyaluran,
            'totalSaldoDana'     => $totalSaldoDana,
            // Tables
            'saldoPerDana'       => $saldoPerDana,
            'transaksiTerakhir'  => $transaksiTerakhir,
            'donaturTerbaru'     => $donaturTerbaru,
            // Info
            'jumlahDonaturAktif' => $jumlahDonaturAktif,
            'periodeAktif'       => $periodeAktif->nama ?? null,
        ]);
    }
}
