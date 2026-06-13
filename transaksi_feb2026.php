<?php
/**
 * Script Transaksi Februari 2026
 * Zakat, Infak Sedekah, Amil
 *
 * Jalankan sekali: php transaksi_feb2026.php
 */

$db = new PDO(
    'mysql:host=host.docker.internal;dbname=lazismu_keuangan;charset=utf8mb4',
    'lazismu',
    'lazismu2024',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$periodeId = 2;  // Februari 2026
$now       = date('Y-m-d H:i:s');

// ── Rekening bank lookup: rb_id → [akun_id, jenis_dana_id] ─────────────
$rek = [
    1  => ['akun_id' => 5,   'jenis_dana_id' => 1], // Kas Zakat
    2  => ['akun_id' => 6,   'jenis_dana_id' => 3], // Kas Infak Sedekah
    3  => ['akun_id' => 7,   'jenis_dana_id' => 4], // Kas Amil
    9  => ['akun_id' => 15,  'jenis_dana_id' => 1], // Jateng Syariah Zakat
    10 => ['akun_id' => 16,  'jenis_dana_id' => 1], // BMT AUM Zakat
    13 => ['akun_id' => 20,  'jenis_dana_id' => 3], // Jateng Syariah Infak
    14 => ['akun_id' => 21,  'jenis_dana_id' => 3], // BMT AUM Infak
    31 => ['akun_id' => 338, 'jenis_dana_id' => 4], // BMT AUM Amil
    32 => ['akun_id' => 339, 'jenis_dana_id' => 4], // Jateng Syariah Amil
];

// ── Counter nomor jurnal ─────────────────────────────────────────────────
$c = ['PNR' => 0, 'PSL' => 0, 'BYA' => 0, 'TRF' => 0];
function nextNomor(array &$c, string $type): string {
    return sprintf('%s/202602/%04d', $type, ++$c[$type]);
}

// ── Helper: insert jurnal + detail ────────────────────────────────────────
function insertJurnal(PDO $db, string $now, int $periodeId, string $nomor,
                      string $tanggal, string $jenisTrx, int $jenisDanaId,
                      string $uraian, ?string $ket, float $jumlah, array $details): int
{
    $db->prepare("
        INSERT INTO jurnal (nomor_jurnal,tanggal,periode_id,jenis_dana_id,
            jenis_transaksi,uraian,keterangan,total_debet,total_kredit,created_at,updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ")->execute([$nomor,$tanggal,$periodeId,$jenisDanaId,$jenisTrx,$uraian,$ket,$jumlah,$jumlah,$now,$now]);

    $jId = (int) $db->lastInsertId();
    $stmt = $db->prepare("
        INSERT INTO jurnal_detail (jurnal_id,akun_id,rekening_bank_id,uraian,debet,kredit,created_at,updated_at)
        VALUES (?,?,?,?,?,?,?,?)
    ");
    foreach ($details as $d) {
        $stmt->execute([$jId, $d['akun_id'], $d['rb_id'] ?? null, $d['uraian'] ?? $uraian, $d['debet'], $d['kredit'], $now, $now]);
    }
    return $jId;
}

// ============================================================
// DATA PENERIMAAN
// ============================================================
// Kolom: [tanggal, jenis_dana_id, rb_debet, akun_kredit_id, jumlah, uraian, keterangan]
$penerimaan = [
    // ── ZAKAT ──────────────────────────────────────────────────────────────
    ['2026-02-02', 1,  9, 147,       48001.00, 'Penerimaan Bagi Hasil Rek Zakat',              'Bagi Hasil Zakat - Jateng Syariah'],
    ['2026-02-03', 1,  9, 141,       50000.00, 'Penerimaan Zakat Maal',                        'an Bapak'],
    ['2026-02-03', 1,  9, 141, 130666523.00,   'Penerimaan Zakat - Hak Kelola Kantor Layanan', null],
    ['2026-02-09', 1,  9, 141,          40.00, 'Penerimaan Zakat Maal',                        '-'],
    ['2026-02-10', 1, 10, 147,       91244.00, 'Penerimaan Bagi Hasil Tab Berjangka Zakat',    'Bagi Hasil Zakat - Deposito di BMT'],
    ['2026-02-19', 1,  1, 141,     4000000.00, 'Penerimaan Zakat Maal',                        'zakat an'],
    ['2026-02-27', 1,  9, 141,     2500000.00, 'Penerimaan Zakat Maal',                        'zakat an'],
    ['2026-02-28', 1, 10, 147,       22582.00, 'Penerimaan Bagi Hasil Rek Zakat',              'Bagi Hasil Zakat - BMT AUM'],
    // ── INFAK SEDEKAH TIDAK TERIKAT (jenis_dana_id=3) ──────────────────────
    ['2026-02-02', 3, 13, 180,       20535.00, 'Penerimaan Bagi Hasil Rek Infak Sedekah Tidak Terikat', 'Bagi Hasil Infak Sedekah - Jateng Syariah'],
    ['2026-02-02', 3, 13, 170,        5000.00, 'Penerimaan Infak Sedekah',                     'An.'],
    ['2026-02-02', 3,  2, 170,     5044000.00, 'Penerimaan Infak Sedekah',                     'An. Mentoring UMS'],
    ['2026-02-03', 3,  2, 170,     1218000.00, 'Penerimaan Infak Sedekah',                     'An. Mentoring UMS'],
    ['2026-02-03', 3, 13, 170,    10825094.00, 'Penerimaan Infak Sedekah - Hak Kelola Kantor Layanan', null],
    ['2026-02-19', 3, 13, 170,       20000.00, 'Penerimaan Infak Sedekah',                     'An.'],
    ['2026-02-21', 3, 13, 170,       20000.00, 'Penerimaan Infak Sedekah',                     'An.'],
    ['2026-02-23', 3, 13, 170,       14000.00, 'Penerimaan Infak Sedekah',                     'An.'],
    ['2026-02-26', 3, 13, 170,        1000.00, 'Penerimaan Infak Sedekah',                     'An.'],
    ['2026-02-26', 3, 13, 170,      135000.00, 'Penerimaan Infak Sedekah',                     'An.'],
    ['2026-02-28', 3, 14, 180,        9161.00, 'Penerimaan Bagi Hasil Rek Infak Sedekah Tidak Terikat', 'Bagi Hasil Infak Sedekah - BMT'],
    // ── INFAK TERIKAT (jenis_dana_id=2) ────────────────────────────────────
    // Penerimaan IT Sosial masuk ke BMT Infak (rb=14, akun=21)
    // akun kredit: 155 = 40201004 Penerimaan Infak Terikat - Sosial
    ['2026-02-24', 2, 14, 155,    70000000.00, 'Penerimaan Infak Terikat - Sosial',            'Sosial Ramadhan BMT'],
    // ── AMIL ────────────────────────────────────────────────────────────────
    ['2026-02-01', 4, 32, 195,       15566.00, 'Penerimaan Bagi Hasil Rek Amil',               'Bagi Hasil Rekening Amil - Jateng Syariah'],
    ['2026-02-25', 4,  3, 189,    16554600.00, 'Bagian Amil dari Dana Zakat',                  '12,5% dr total pemasukan Jan 2026'],
    ['2026-02-25', 4,  3, 190,     3303800.00, 'Bagian Amil dari Dana Infak Sedekah',          '20% dr total pemasukan Infak Sedekah Jan 2026'],
    ['2026-02-28', 4, 31, 195,       21629.00, 'Penerimaan Bagi Hasil Rek Amil',               'Bagi Hasil Rekening Amil - BMT'],
];

// ============================================================
// DATA BIAYA
// ============================================================
// Kolom: [tanggal, jenis_dana_id, akun_debet_id, rb_kredit, jumlah, uraian, keterangan]
$biaya = [
    // ── ZAKAT ───────────────────────────────────────────────────────────────
    ['2026-02-02', 1, 257,  9,    17100.00, 'Biaya Administrasi Bank Zakat',             'Administrasi & pajak Jateng Syariah'],
    ['2026-02-10', 1, 257, 10,     2281.00, 'Biaya Administrasi Bank Zakat',             'Administrasi & pajak BMT'],
    ['2026-02-28', 1, 257, 10,      565.00, 'Biaya Administrasi Bank Zakat',             'Administrasi & pajak BMT AUM'],
    // ── INFAK SEDEKAH ────────────────────────────────────────────────────────
    ['2026-02-02', 3, 259, 13,    11607.00, 'Biaya Administrasi Bank Infak Sedekah',     'Administrasi & pajak Jateng Syariah'],
    ['2026-02-28', 3, 259, 14,      229.00, 'Biaya Administrasi Bank Infak Sedekah',     'Administrasi & pajak BMT'],
    // ── AMIL ────────────────────────────────────────────────────────────────
    ['2026-02-01', 4, 313, 32,     3113.00, 'Biaya Administrasi Bank Amil',              'Administrasi & pajak Rekening Amil - Jateng Syariah'],
    ['2026-02-06', 4, 315,  3,   264500.00, 'Biaya Administrasi dan Umum',               'pembuatan laporan'],
    ['2026-02-07', 4, 312,  3,  1340000.00, 'Biaya Rapat/Koordinasi',                   'rakorsus'],
    ['2026-02-07', 4, 287,  3,    99500.00, 'Biaya TALI (Tlp, Air, Listrik & Internet)', 'tup op pulsa'],
    ['2026-02-23', 4, 266, 31,   300000.00, 'Biaya Amil/Pegawai',                       'Tunjangan Hari Tua (Sugeng 200k, Sugiharto 100k)'],
    ['2026-02-25', 4, 264,  3, 10207950.00, 'Biaya Amil/Pegawai',                       'honor Staff'],
    ['2026-02-25', 4, 293,  3,   300000.00, 'Biaya Pemeliharaan Aktiva',                'Kebersihan kantor - pak warso'],
    ['2026-02-27', 4, 312,  3,  1300000.00, 'Biaya Rapat/Koordinasi',                   'Rapat bulanan'],
    ['2026-02-28', 4, 313, 31,      541.00, 'Biaya Administrasi Bank Amil',              'Administrasi & pajak Rekening Amil - BMT'],
];

// ============================================================
// DATA PENYALURAN
// ============================================================
// Kolom: [tanggal, jenis_dana_id, akun_debet_id, rb_kredit, jumlah, uraian, keterangan]
$penyaluran = [
    // ── ZAKAT (semua dari Kas Zakat rb=1) ──────────────────────────────────
    ['2026-02-02', 1, 213, 1,   3000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'IGABA colomadu'],
    // 05/02 beasiswa kader sang surya (10 orang)
    ['2026-02-05', 1, 214, 1,   4270000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an M. hammam D'],
    ['2026-02-05', 1, 214, 1,    650000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an M. fahmi F'],
    ['2026-02-05', 1, 214, 1,   2961000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Berlian PP'],
    ['2026-02-05', 1, 214, 1,   1204000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Anita SD'],
    ['2026-02-05', 1, 214, 1,   3460000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an M. Fariz AW'],
    ['2026-02-05', 1, 214, 1,   1204000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Zahra K'],
    ['2026-02-05', 1, 214, 1,   1880000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an M. Farhan'],
    ['2026-02-05', 1, 214, 1,   2780000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Okthia V'],
    ['2026-02-05', 1, 214, 1,   3460000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Melano PP'],
    ['2026-02-05', 1, 214, 1,   2970000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Sekar AP'],
    ['2026-02-05', 1, 208, 1,   5400000.00, 'Penyaluran Zakat - Miskin',      "Pengajian Al-Ma'un"],
    // 06/02
    ['2026-02-06', 1, 214, 1,   2810000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Yulistiya RS'],
    ['2026-02-06', 1, 214, 1,   3242500.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Happy M'],
    ['2026-02-06', 1, 214, 1,   4352500.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Muh Adam M'],
    ['2026-02-06', 1, 214, 1,   4480000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Hafsyah FP'],
    ['2026-02-06', 1, 213, 1,   1500000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Ponpes Daarul Arqom'],
    ['2026-02-06', 1, 214, 1,   1000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'Beasiswa Peduli Sumatra an. Allul pakih'],
    // 07/02
    ['2026-02-07', 1, 214, 1,   3462500.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Muh Harwiansyah'],
    ['2026-02-07', 1, 214, 1,   3292500.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Andina TS'],
    ['2026-02-07', 1, 214, 1,   4390000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an fatimah NA'],
    ['2026-02-07', 1, 214, 1,   3462500.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Muh Baihakhi'],
    // 09-14/02
    ['2026-02-09', 1, 213, 1,  10500000.00, 'Penyaluran Zakat - Fii Sabilillah', 'SMP Muh PK Praci, wonogiri'],
    ['2026-02-11', 1, 214, 1,   1000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'Beasiswa Peduli Sumatra an. M Robby H'],
    ['2026-02-13', 1, 213, 1,   1000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'MADIM Nusukan'],
    ['2026-02-13', 1, 214, 1,   1000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'Beasiswa Peduli Sumatra an. M Arya'],
    ['2026-02-14', 1, 214, 1,    650000.00, 'Penyaluran Zakat - Ibnu Sabil', 'beasiswa kader sang surya an Alviando A'],
    // 19-20/02
    ['2026-02-19', 1, 214, 1,    590000.00, 'Penyaluran Zakat - Ibnu Sabil', 'ibnusabil bpk razali ke jakarta (tiket & uang saku)'],
    ['2026-02-20', 1, 214, 1,   1000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'Beasiswa Peduli Sumatra an. Tiffany L'],
    ['2026-02-20', 1, 214, 1,   1000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'Beasiswa Peduli Sumatra an. Shandika NA'],
    ['2026-02-20', 1, 214, 1,   1000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'Beasiswa Peduli Sumatra an. Rizki R'],
    ['2026-02-20', 1, 214, 1,   1000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'Beasiswa Peduli Sumatra an. M Arya'],
    ['2026-02-20', 1, 213, 1,   7500000.00, 'Penyaluran Zakat - Fii Sabilillah', 'IGABA surakarta'],
    ['2026-02-20', 1, 213, 1,   4000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'IGABA kartasura'],
    // 24-26/02
    ['2026-02-24', 1, 208, 1,   4860000.00, 'Penyaluran Zakat - Miskin',      "Pengajian Al-Ma'un"],
    ['2026-02-25', 1, 209, 1,  16554600.00, 'Penyaluran Zakat - Amil',        'Hak Amil atas Zakat (12,5%) Jan 2026'],
    ['2026-02-25', 1, 213, 1,  10000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'kmkw masjid ar rahim thp 2'],
    ['2026-02-25', 1, 208, 1,   4860000.00, 'Penyaluran Zakat - Miskin',      'Pengajian majelis ntaklim an nisa gonilan'],
    ['2026-02-25', 1, 208, 1,   4860000.00, 'Penyaluran Zakat - Miskin',      'PRA gonilan'],
    ['2026-02-25', 1, 214, 1,   2080000.00, 'Penyaluran Zakat - Ibnu Sabil', 'BKUI UMS'],
    ['2026-02-26', 1, 214, 1,   5600000.00, 'Penyaluran Zakat - Ibnu Sabil', 'SMP Muh 2 KTS'],
    ['2026-02-26', 1, 214, 1,   3000000.00, 'Penyaluran Zakat - Ibnu Sabil', 'IGABA Colomadu'],
    // ── INFAK TIDAK TERIKAT (jenis_dana_id=3, dari Kas Infak rb=2) ──────────
    ['2026-02-09', 3, 236, 2,   1190000.00, 'Penyaluran Infak Tidak Terikat - Pendidikan', 'opr penyaluran ke wonogiri'],
    ['2026-02-25', 3, 241, 2,   3303800.00, 'Penyaluran Infak Tidak Terikat - Ujrah Amil', 'Hak Amil Atas Infak Sedekah (20%) Jan 2026'],
    // ── INFAK TERIKAT (jenis_dana_id=2, dari Kas Infak rb=2) ────────────────
    // akun 224 = 50201005 Penyaluran Infak Terikat - Kemanusiaan
    // akun 221 = 50201002 Penyaluran Infak Terikat - Sosial
    ['2026-02-23', 2, 224, 2,   5560000.00, 'Penyaluran Infak Terikat - Kemanusiaan', null],
    ['2026-02-24', 2, 221, 2,  70000000.00, 'Penyaluran Infak Terikat - Sosial',      null],
];

// ============================================================
// DATA TRANSFER/MUTASI
// ============================================================
// Kolom: [tanggal, rb_asal, rb_tujuan, jumlah, uraian, keterangan]
$transfer = [
    // ── Internal ZAKAT ──────────────────────────────────────────────────────
    ['2026-02-02',  10,  1,   3000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-05',   9,  1,  65359000.00, 'Tarik tunai JS Zakat ke Kas Zakat',    null],
    ['2026-02-05',  10,  1,   5400000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-06',  10,  1,   1500000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-06',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-06',  10,  1,   2000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-10',  10,  1,   3285000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-11',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-13',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-13',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-19',   1, 10,   4000000.00, 'Setor tunai Kas Zakat ke BMT Zakat',   'Setor tunai'],
    ['2026-02-19',  10,  1,    590000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-20',   9,  1,  79013000.00, 'Tarik tunai JS Zakat ke Kas Zakat',    null],
    ['2026-02-20',   1, 10,  79013000.00, 'Setor tunai Kas Zakat ke BMT Zakat',   'Setor tunai'],
    ['2026-02-20',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-20',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-20',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-20',  10,  1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-20',  10,  1,   7500000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-20',  10,  1,   4000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-23',  10,  1,   5560000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   'Tarik tunai'],
    ['2026-02-24',  10,  1,   4860000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-25',  10,  1,  10000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-25',  10,  1,   1600000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-25',  10,  1,   1600000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-25',  10,  1,   2080000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-26',  10,  1,   5600000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-26',  10,  1,   3000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   null],
    ['2026-02-27',  10,  1,  19652800.00, 'Tarik tunai BMT Zakat ke Kas Zakat',   'Tarik tunai'],
    // ── Internal INFAK ──────────────────────────────────────────────────────
    ['2026-02-02',   2, 14,   5044000.00, 'Setor tunai Kas Infak ke BMT Infak',   'setor tunai'],
    ['2026-02-03',   2, 14,   1195000.00, 'Setor tunai Kas Infak ke BMT Infak',   'setor tunai'],
    ['2026-02-09',  14,  2,  10500000.00, 'Tarik tunai BMT Infak ke Kas Infak',   'Tarik tunai'],
    ['2026-02-13',   2, 14,     23000.00, 'Setor tunai Kas Infak ke BMT Infak',   'setor tunai'],
    ['2026-02-20',  13,  2,  15200000.00, 'Tarik tunai JS Infak ke Kas Infak',    'Tarik Tunai'],
    ['2026-02-20',   2, 14,  15200000.00, 'Setor tunai Kas Infak ke BMT Infak',   'setor tunai'],
    ['2026-02-24',  14,  2,  70000000.00, 'Tarik tunai BMT Infak ke Kas Infak',   'Tarik tunai'],
    // ── Internal AMIL ───────────────────────────────────────────────────────
    ['2026-02-27',   3, 31,   3285600.00, 'Setor tunai Kas Amil ke BMT Amil',     null],
    // ── Cross-dana ──────────────────────────────────────────────────────────
    ['2026-02-09',   2,  1,  10500000.00, 'Talangan Dana Infak ke Kas Zakat',     'talangan untuk zakat'],
    ['2026-02-23',   1,  2,   5560000.00, 'Pengembalian Talangan ke Kas Infak',   'mengganti talangan kas infaq'],
    ['2026-02-25',   1,  2,  12000000.00, 'Pengembalian Talangan ke Kas Infak',   'mengganti talangan kas infaq'],
    ['2026-02-25',   2,  3,  10000000.00, 'Talangan Dana Infak ke Kas Amil',      'talangan untuk kas amil'],
];

// ============================================================
// EKSEKUSI
// ============================================================
$totalInserted = 0;

$db->beginTransaction();
try {
    // ── Idempoten: hapus jika sudah pernah dijalankan ────────────────────
    $existing = $db->query("SELECT id FROM jurnal WHERE nomor_jurnal LIKE 'PNR/202602/%' OR nomor_jurnal LIKE 'PSL/202602/%' OR nomor_jurnal LIKE 'BYA/202602/%' OR nomor_jurnal LIKE 'TRF/202602/%'")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($existing)) {
        $inIds = implode(',', $existing);
        $db->exec("DELETE FROM jurnal_detail WHERE jurnal_id IN ($inIds)");
        $db->exec("DELETE FROM jurnal WHERE id IN ($inIds)");
        echo "Hapus " . count($existing) . " jurnal Feb 2026 lama (idempoten).\n";
    }

    echo "\n=== PENERIMAAN (" . count($penerimaan) . " entri) ===\n";
    foreach ($penerimaan as $t) {
        [$tgl, $danaId, $rbD, $akunK, $jml, $uraian, $ket] = $t;
        $nomor = nextNomor($c, 'PNR');
        insertJurnal($db, $now, $periodeId, $nomor, $tgl, 'penerimaan', $danaId, $uraian, $ket, $jml, [
            ['akun_id' => $rek[$rbD]['akun_id'], 'rb_id' => $rbD, 'debet' => $jml, 'kredit' => 0],
            ['akun_id' => $akunK, 'rb_id' => null, 'debet' => 0, 'kredit' => $jml],
        ]);
        printf("  %s  %-60s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
        $totalInserted++;
    }

    echo "\n=== BIAYA (" . count($biaya) . " entri) ===\n";
    foreach ($biaya as $t) {
        [$tgl, $danaId, $akunD, $rbK, $jml, $uraian, $ket] = $t;
        $nomor = nextNomor($c, 'BYA');
        insertJurnal($db, $now, $periodeId, $nomor, $tgl, 'biaya', $danaId, $uraian, $ket, $jml, [
            ['akun_id' => $akunD, 'rb_id' => null, 'debet' => $jml, 'kredit' => 0],
            ['akun_id' => $rek[$rbK]['akun_id'], 'rb_id' => $rbK, 'debet' => 0, 'kredit' => $jml],
        ]);
        printf("  %s  %-60s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
        $totalInserted++;
    }

    echo "\n=== PENYALURAN (" . count($penyaluran) . " entri) ===\n";
    foreach ($penyaluran as $t) {
        [$tgl, $danaId, $akunD, $rbK, $jml, $uraian, $ket] = $t;
        $nomor = nextNomor($c, 'PSL');
        insertJurnal($db, $now, $periodeId, $nomor, $tgl, 'penyaluran', $danaId, $uraian, $ket, $jml, [
            ['akun_id' => $akunD, 'rb_id' => null, 'debet' => $jml, 'kredit' => 0],
            ['akun_id' => $rek[$rbK]['akun_id'], 'rb_id' => $rbK, 'debet' => 0, 'kredit' => $jml],
        ]);
        printf("  %s  %-60s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
        $totalInserted++;
    }

    echo "\n=== TRANSFER (" . count($transfer) . " entri) ===\n";
    foreach ($transfer as $t) {
        [$tgl, $rbAsal, $rbTujuan, $jml, $uraian, $ket] = $t;
        $nomor  = nextNomor($c, 'TRF');
        $danaId = $rek[$rbAsal]['jenis_dana_id'];
        insertJurnal($db, $now, $periodeId, $nomor, $tgl, 'transfer', $danaId, $uraian, $ket, $jml, [
            ['akun_id' => $rek[$rbTujuan]['akun_id'], 'rb_id' => $rbTujuan, 'debet' => $jml, 'kredit' => 0],
            ['akun_id' => $rek[$rbAsal]['akun_id'],   'rb_id' => $rbAsal,   'debet' => 0,    'kredit' => $jml],
        ]);
        printf("  %s  %-60s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
        $totalInserted++;
    }

    $db->commit();
    echo "\n=== SELESAI: $totalInserted jurnal berhasil diinsert ===\n";

} catch (Throwable $e) {
    $db->rollBack();
    echo "\n!!! GAGAL: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================
// VERIFIKASI SALDO DANA
// ============================================================
echo "\n--- Verifikasi saldo dana Februari 2026 ---\n";
// Ambil saldo_akhir Januari 2026 dari saldo_dana (bila sudah ditutup) atau hitung dari jurnal
$prevSaldo = $db->query("
    SELECT jd.kode,
        COALESCE(
            (SELECT sd.saldo_akhir FROM saldo_dana sd JOIN periode p ON p.id=sd.periode_id
             WHERE sd.jenis_dana_id=jd.id AND p.tahun=2026 AND p.bulan=1),
            (
                SELECT COALESCE(
                    (SELECT sd2.saldo_akhir FROM saldo_dana sd2 JOIN periode p2 ON p2.id=sd2.periode_id
                     WHERE sd2.jenis_dana_id=jd.id AND p2.tahun=2025 AND p2.bulan=12),
                    0
                ) +
                COALESCE(SUM(CASE WHEN j.jenis_transaksi='penerimaan' THEN j.total_debet ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN j.jenis_transaksi IN ('penyaluran','biaya') THEN j.total_debet ELSE 0 END),0)
                FROM jurnal j WHERE j.jenis_dana_id=jd.id AND j.periode_id=1
            )
        ) AS saldo_jan
    FROM jenis_dana jd
    WHERE jd.id IN (1,2,3,4)
")->fetchAll(PDO::FETCH_KEY_PAIR);

$rows = $db->query("
    SELECT jd.kode,
        COALESCE(SUM(CASE WHEN j.jenis_transaksi='penerimaan' THEN j.total_debet ELSE 0 END),0) AS penerimaan,
        COALESCE(SUM(CASE WHEN j.jenis_transaksi='penyaluran' THEN j.total_debet ELSE 0 END),0) AS penyaluran,
        COALESCE(SUM(CASE WHEN j.jenis_transaksi='biaya'      THEN j.total_debet ELSE 0 END),0) AS biaya
    FROM jenis_dana jd
    LEFT JOIN jurnal j ON j.jenis_dana_id=jd.id AND j.periode_id=2
    WHERE jd.id IN (1,2,3,4)
    GROUP BY jd.id, jd.kode
    ORDER BY jd.id
")->fetchAll(PDO::FETCH_ASSOC);

$target = [
    'ZAKAT'    => 91635957,
    'INFAK_T'  => null,
    'INFAK_TT' => null,
    'AMIL'     => 56840948,
];

printf("\n%-12s %15s %15s %15s %15s %15s %s\n", 'Dana','Saldo Jan','Penerimaan','Penyaluran+Biaya','Saldo Feb','Target','OK?');
foreach ($rows as $r) {
    $jan   = $prevSaldo[$r['kode']] ?? 0;
    $feb   = $jan + $r['penerimaan'] - $r['penyaluran'] - $r['biaya'];
    $tgt   = $target[$r['kode']] ?? null;
    $ok    = ($tgt === null) ? '—' : (abs($feb - $tgt) < 1 ? '✓' : '✗ MISMATCH');
    printf("%-12s %15s %15s %15s %15s %15s %s\n",
        $r['kode'],
        number_format($jan,0,',','.'),
        number_format($r['penerimaan'],0,',','.'),
        number_format($r['penyaluran']+$r['biaya'],0,',','.'),
        number_format($feb,0,',','.'),
        $tgt !== null ? number_format($tgt,0,',','.') : '-',
        $ok
    );
}

// ============================================================
// VERIFIKASI SALDO REKENING
// ============================================================
echo "\n--- Verifikasi saldo rekening bank Februari 2026 ---\n";
$rows2 = $db->query("
    SELECT rb.id, rb.nama,
        rb.saldo_awal,
        COALESCE((
            SELECT SUM(jd2.debet - jd2.kredit)
            FROM jurnal_detail jd2
            JOIN jurnal j2 ON j2.id=jd2.jurnal_id
            WHERE jd2.rekening_bank_id=rb.id AND j2.periode_id IN (1,2)
        ),0) AS mutasi_jan_feb
    FROM rekening_bank rb
    WHERE rb.id IN (1,2,3,9,10,13,14,31,32)
    ORDER BY rb.id
")->fetchAll(PDO::FETCH_ASSOC);

$targetRek = [
    1  => 2463850,   // Kas Zakat
    2  => 2009150,   // Kas Infak
    3  => 14246750,  // Kas Amil
    9  => 4479827,   // JS Zakat
    10 => 13012780,  // BMT Zakat
    13 => 13018171,  // JS Infak
    14 => 15411955,  // BMT Infak
    31 => 21283008,  // BMT Amil
    32 => 36188970,  // JS Amil
];

printf("\n%-5s %-35s %15s %15s %15s %s\n", 'ID','Rekening','Saldo Awal','Mutasi Jan-Feb','Saldo Akhir','OK?');
foreach ($rows2 as $r) {
    $akhir = $r['saldo_awal'] + $r['mutasi_jan_feb'];
    $tgt   = $targetRek[$r['id']] ?? null;
    $ok    = ($tgt !== null && abs($akhir - $tgt) < 1) ? '✓' : '✗';
    printf("%-5d %-35s %15s %15s %15s %s\n",
        $r['id'], $r['nama'],
        number_format($r['saldo_awal'],0,',','.'),
        number_format($r['mutasi_jan_feb'],0,',','.'),
        number_format($akhir,0,',','.'),
        $ok . ($tgt !== null ? ' (target: '.number_format($tgt,0,',','.').')' : '')
    );
}
