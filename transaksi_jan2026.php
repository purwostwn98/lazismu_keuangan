<?php
/**
 * Script Transaksi Januari 2026
 * Zakat, Infak Sedekah, Amil
 *
 * Jalankan sekali: php transaksi_jan2026.php
 */

$db = new PDO(
    'mysql:host=host.docker.internal;dbname=lazismu_keuangan;charset=utf8mb4',
    'lazismu',
    'lazismu2024',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$periodeId = 1;  // Januari 2026
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
    return sprintf('%s/202601/%04d', $type, ++$c[$type]);
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
    ['2026-01-01', 1,  9, 147,  154104.00, 'Penerimaan Bagi Hasil Rek Zakat',           'Bagi Hasil Zakat - Jateng Syariah'],
    ['2026-01-12', 1, 10, 147,   91253.00, 'Penerimaan Bagi Hasil Tab Berjangka Zakat', 'Bagi Hasil Zakat - Deposito di BMT'],
    ['2026-01-12', 1,  9, 141,   10500.00, 'Penerimaan Zakat Maal',                     'an Bapak'],
    ['2026-01-15', 1,  9, 141,  500000.00, 'Penerimaan Zakat Maal',                     'an Bapak'],
    ['2026-01-21', 1,  9, 141,    5000.00, 'Penerimaan Zakat Maal',                     'an Bapak'],
    ['2026-01-31', 1, 10, 147,   51204.00, 'Penerimaan Bagi Hasil Rek Zakat',           'Bagi Hasil Zakat - BMT AUM'],
    // ── INFAK SEDEKAH ───────────────────────────────────────────────────────
    ['2026-01-01', 3, 13, 180,   98386.00, 'Penerimaan Bagi Hasil Rek Infak Sedekah Tidak Terikat', 'Bagi Hasil Infak Sedekah - Jateng Syariah'],
    ['2026-01-02', 3, 13, 170,    5000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-02', 3, 13, 170,  100000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-06', 3, 13, 170, 3000000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-06', 3, 13, 170, 2000000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-15', 3, 13, 170,    5000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-17', 3, 13, 170,   10000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-24', 3, 13, 170,   10000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-31', 3, 13, 170,  425000.00, 'Penerimaan Infak Sedekah', null],
    ['2026-01-31', 3, 14, 180,   40736.00, 'Penerimaan Bagi Hasil Rek Infak Sedekah Tidak Terikat', 'Bagi Hasil Infak Sedekah - BMT'],
    // ── AMIL ────────────────────────────────────────────────────────────────
    ['2026-01-01', 4, 32, 195,   17733.00, 'Penerimaan Bagi Hasil Rek Amil',        'Bagi Hasil Rekening Amil - Jateng Syariah'],
    ['2026-01-25', 4,  3, 189, 16453100.00,'Bagian Amil dari Dana Zakat',            '12,5% dari total pemasukan Zakat Des 2025'],
    ['2026-01-25', 4,  3, 190, 8632550.00, 'Bagian Amil dari Dana Infak Sedekah',   '20% dari total pemasukan Infak Sedekah Des 2025'],
    ['2026-01-31', 4, 31, 195,   19823.00, 'Penerimaan Bagi Hasil Rek Amil',        'Bagi Hasil Rekening Amil - BMT'],
];

// ============================================================
// DATA BIAYA
// ============================================================
// Kolom: [tanggal, jenis_dana_id, akun_debet_id, rb_kredit, jumlah, uraian, keterangan]
$biaya = [
    // ── ZAKAT (Biaya Admin Bank 50500001=id257) ─────────────────────────────
    ['2026-01-01', 1, 257,  9,   38321.00, 'Biaya Administrasi Bank Zakat', 'Administrasi & pajak Jateng Syariah'],
    ['2026-01-12', 1, 257, 10,    2281.00, 'Biaya Administrasi Bank Zakat', 'Administrasi & pajak BMT'],
    ['2026-01-31', 1, 257, 10,    1280.00, 'Biaya Administrasi Bank Zakat', 'Administrasi & pajak BMT AUM'],
    // ── INFAK (Biaya Admin Bank 50500003=id259) ─────────────────────────────
    ['2026-01-01', 3, 259, 13,   27066.00, 'Biaya Administrasi Bank Infak Sedekah', 'Administrasi & pajak Jateng Syariah'],
    ['2026-01-31', 3, 259, 14,    1018.00, 'Biaya Administrasi Bank Infak Sedekah', 'Administrasi & pajak BMT'],
    // ── AMIL ────────────────────────────────────────────────────────────────
    // 60700011 Admin Bank Amil, 60100003 THR, 60700010 Rapat, 60500002 Pemeliharaan
    // 60100001 Gaji, 60700013 Lain-lain, 60400001 TALI
    ['2026-01-01', 4, 313, 32,    3547.00, 'Biaya Administrasi Bank Amil',  'Administrasi & pajak Rekening Amil - Jateng Syariah'],
    ['2026-01-01', 4, 293,  3,  300000.00, 'Biaya Pemeliharaan Aktiva',     'Kebersihan kantor - pak warso (Des 2025)'],
    ['2026-01-12', 4, 266, 31,  300000.00, 'Biaya Amil/Pegawai',            'Tunjangan Hari Tua (Sugeng 200k, Sugiharto 100k)'],
    ['2026-01-20', 4, 312,  3, 1239100.00, 'Biaya Rapat/Koordinasi',        'Perpisahan dengan Mbak Eva'],
    ['2026-01-25', 4, 264,  3,10207950.00, 'Biaya Amil/Pegawai',            'Honor Staff'],
    ['2026-01-25', 4, 312,  3,11785400.00, 'Biaya Rapat/Koordinasi',        'Rakor di Wonosobo'],
    ['2026-01-27', 4, 315,  3,  431600.00, 'Biaya Administrasi dan Umum',   null],
    ['2026-01-27', 4, 287,  3,  102000.00, 'Biaya TALI (Tlp, Air, Listrik & Internet)', 'Tup Op Pulsa'],
    ['2026-01-31', 4, 315,  3,  271600.00, 'Biaya Administrasi dan Umum',   null],
    ['2026-01-31', 4, 313, 31,     496.00, 'Biaya Administrasi Bank Amil',  'Administrasi & pajak Rekening Amil - BMT'],
];

// ============================================================
// DATA PENYALURAN
// ============================================================
// Kolom: [tanggal, jenis_dana_id, akun_debet_id, rb_kredit, jumlah, uraian, keterangan]
$penyaluran = [
    // ── ZAKAT (semua dari Kas Zakat rb=1) ──────────────────────────────────
    // Ibnu Sabil = id214, Fii Sabilillah = id213, Fakir = id207, Miskin = id208, Amil = id209
    ['2026-01-02', 1, 214, 1,  1344000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa kader sang surya an. Orvin AE'],
    ['2026-01-02', 1, 214, 1,  3523000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa kader sang surya an. Vemas AS'],
    ['2026-01-05', 1, 213, 1,  1500000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Ponpes Daarul Arqom'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. M Robby H'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. Allul Pakih'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. Rizki R'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. Tiffany L'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. M Abyaz RA'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. Shandika NA'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. M Arya'],
    ['2026-01-05', 1, 214, 1,  1000000.00, 'Penyaluran Zakat - Ibnu Sabil',     'Beasiswa Peduli Sumatra an. Wafa SAZ'],
    ['2026-01-06', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Masjid Al Mustaqim Standar KTS'],
    ['2026-01-08', 1, 214, 1,  2080000.00, 'Penyaluran Zakat - Ibnu Sabil',     'BKUI UMS'],
    ['2026-01-09', 1, 214, 1,  2080000.00, 'Penyaluran Zakat - Ibnu Sabil',     'BKUI UMS'],
    ['2026-01-13', 1, 207, 1, 38367500.00, 'Penyaluran Zakat - Fakir',          'PDA - MKS SKA'],
    ['2026-01-14', 1, 213, 1,  4000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'IGABA Kartasura'],
    ['2026-01-22', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'TK PK Aisyiyah Ar Rahman Karanggede'],
    ['2026-01-22', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Panti Asuhan Yatim Kadipiro'],
    ['2026-01-22', 1, 213, 1,  1000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'MADIM Nusukan'],
    ['2026-01-25', 1, 209, 1, 16453100.00, 'Penyaluran Zakat - Amil',           'Hak Amil atas Zakat (12,5%) Des 2025'],
    ['2026-01-28', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Masjid Zakariya'],
    ['2026-01-28', 1, 213, 1, 10000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'KMKW Masjid AR Rahim thp 1'],
    ['2026-01-28', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Masjid Husnul Khatimah Laweyan'],
    ['2026-01-29', 1, 208, 1,   750000.00, 'Penyaluran Zakat - Miskin',         'Bantuan beli kruk PRM Gumpang'],
    ['2026-01-29', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Mushola Al Ikhlas Gumpang'],
    ['2026-01-29', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'Mushola Aisyiyah Waru Baki'],
    ['2026-01-29', 1, 213, 1,  5000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'MTs Muh Waru Baki'],
    ['2026-01-30', 1, 213, 1, 15000000.00, 'Penyaluran Zakat - Fii Sabilillah', 'BA Aisyiyah Gayam Bendosari'],
    // ── INFAK SEDEKAH (dari Kas Infak rb=2) ────────────────────────────────
    // IT Sosial=id235, IT Pendidikan=id236, IT Kesehatan=id237, IT Ujrah Amil=id241
    ['2026-01-05', 3, 235, 2,  1807400.00, 'Penyaluran Infak Tidak Terikat - Sosial',     'Opr penyaluran beasiswa'],
    ['2026-01-07', 3, 235, 2,  2250000.00, 'Penyaluran Infak Tidak Terikat - Sosial',     'Pondok HNS'],
    ['2026-01-08', 3, 236, 2,  1130500.00, 'Penyaluran Infak Tidak Terikat - Pendidikan', 'Survey'],
    ['2026-01-22', 3, 237, 2,  3000000.00, 'Penyaluran Infak Tidak Terikat - Kesehatan',  'Bantuan Berobat'],
    ['2026-01-22', 3, 236, 2,   466950.00, 'Penyaluran Infak Tidak Terikat - Pendidikan', 'Opr penyaluran pendidikan ke Karanggede'],
    ['2026-01-25', 3, 241, 2,  8632550.00, 'Penyaluran Infak Tidak Terikat - Ujrah Amil', 'Hak Amil atas Infak Sedekah (20%) Des 2025'],
    ['2026-01-28', 3, 236, 2,   571500.00, 'Penyaluran Infak Tidak Terikat - Pendidikan', 'Opr penyaluran ke Masaran'],
    ['2026-01-29', 3, 236, 2,   600000.00, 'Penyaluran Infak Tidak Terikat - Pendidikan', 'Opr penyaluran ke Baki'],
    ['2026-01-31', 3, 236, 2,   587500.00, 'Penyaluran Infak Tidak Terikat - Pendidikan', 'Opr penyaluran ke Baki'],
];

// ============================================================
// DATA TRANSFER/MUTASI
// ============================================================
// Kolom: [tanggal, rb_asal, rb_tujuan, jumlah, uraian, keterangan]
$transfer = [
    // ── Internal ZAKAT ──────────────────────────────────────────────────────
    ['2026-01-02', 10, 1,   1344000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-05', 10, 1,   1500000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-09',  9, 1, 113038000.00, 'Tarik tunai JS Zakat ke Kas Zakat',   null],
    ['2026-01-09',  1,10, 113038000.00, 'Setor tunai Kas Zakat ke BMT Zakat',  null],
    ['2026-01-09', 10, 1,   4160000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-13', 10, 1,  40000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-14', 10, 1,   4000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-22', 10, 1,   5000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-22', 10, 1,   5000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-22', 10, 1,   1000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-28', 10, 1,   5000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-28', 10, 1,  10000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-28', 10, 1,   5000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-29', 10, 1,    750000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-29', 10, 1,   5000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-29', 10, 1,   5000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    ['2026-01-29', 10, 1,   5000000.00, 'Tarik tunai BMT Zakat ke Kas Zakat',  null],
    // ── Internal INFAK ──────────────────────────────────────────────────────
    ['2026-01-02', 14, 2,   5000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-05', 14, 2,   3000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-05', 14, 2,   8000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-06', 14, 2,   5000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-07', 14, 2,   3000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-07', 14, 2,   2250000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-09', 13, 2,  11890000.00, 'Tarik tunai JS Infak ke Kas Infak',   null],
    ['2026-01-09',  2,14,  11890000.00, 'Setor tunai Kas Infak ke BMT Infak',  null],
    ['2026-01-22', 14, 2,  20000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-22', 14, 2,   5000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-22', 14, 2,   3000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    ['2026-01-30', 14, 2,  15000000.00, 'Tarik tunai BMT Infak ke Kas Infak',  null],
    // ── Cross-dana: Kas Infak → Kas Zakat (talangan) ───────────────────────
    ['2026-01-05',  2, 1,  15000000.00, 'Talangan Dana Infak ke Kas Zakat',    'Talangan untuk operasional zakat'],
    ['2026-01-25',  2, 1,  12000000.00, 'Talangan Dana Infak ke Kas Zakat',    'Talangan dari kas infak'],
    ['2026-01-30',  2, 1,  20000000.00, 'Talangan Dana Infak ke Kas Zakat',    'Talangan untuk zakat'],
];

// ============================================================
// EKSEKUSI
// ============================================================
$totalInserted = 0;

$db->beginTransaction();
try {
    // ── Idempoten: hapus jika sudah pernah dijalankan ────────────────────
    $existing = $db->query("SELECT id FROM jurnal WHERE nomor_jurnal LIKE 'PNR/202601/%' OR nomor_jurnal LIKE 'PSL/202601/%' OR nomor_jurnal LIKE 'BYA/202601/%' OR nomor_jurnal LIKE 'TRF/202601/%'")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($existing)) {
        $inIds = implode(',', $existing);
        $db->exec("DELETE FROM jurnal_detail WHERE jurnal_id IN ($inIds)");
        $db->exec("DELETE FROM jurnal WHERE id IN ($inIds)");
        echo "Hapus " . count($existing) . " jurnal Jan 2026 lama (idempoten).\n";
    }

    echo "\n=== PENERIMAAN (" . count($penerimaan) . " entri) ===\n";
    foreach ($penerimaan as $t) {
        [$tgl, $danaId, $rbD, $akunK, $jml, $uraian, $ket] = $t;
        $nomor = nextNomor($c, 'PNR');
        insertJurnal($db, $now, $periodeId, $nomor, $tgl, 'penerimaan', $danaId, $uraian, $ket, $jml, [
            ['akun_id' => $rek[$rbD]['akun_id'], 'rb_id' => $rbD, 'debet' => $jml, 'kredit' => 0],
            ['akun_id' => $akunK, 'rb_id' => null, 'debet' => 0, 'kredit' => $jml],
        ]);
        printf("  %s  %-55s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
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
        printf("  %s  %-55s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
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
        printf("  %s  %-55s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
        $totalInserted++;
    }

    echo "\n=== TRANSFER (" . count($transfer) . " entri) ===\n";
    foreach ($transfer as $t) {
        [$tgl, $rbAsal, $rbTujuan, $jml, $uraian, $ket] = $t;
        $nomor = nextNomor($c, 'TRF');
        $danaId = $rek[$rbAsal]['jenis_dana_id']; // dana dari rekening asal
        insertJurnal($db, $now, $periodeId, $nomor, $tgl, 'transfer', $danaId, $uraian, $ket, $jml, [
            ['akun_id' => $rek[$rbTujuan]['akun_id'], 'rb_id' => $rbTujuan, 'debet' => $jml, 'kredit' => 0],
            ['akun_id' => $rek[$rbAsal]['akun_id'],   'rb_id' => $rbAsal,   'debet' => 0,    'kredit' => $jml],
        ]);
        printf("  %s  %-55s  Rp %s\n", $nomor, $uraian, number_format($jml, 0, ',', '.'));
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
// VERIFIKASI
// ============================================================
echo "\n--- Verifikasi saldo dana Januari 2026 ---\n";
$rows = $db->query("
    SELECT jd.kode,
        COALESCE(SUM(CASE WHEN j.jenis_transaksi='penerimaan' THEN j.total_debet ELSE 0 END),0) AS penerimaan,
        COALESCE(SUM(CASE WHEN j.jenis_transaksi='penyaluran' THEN j.total_debet ELSE 0 END),0) AS penyaluran,
        COALESCE(SUM(CASE WHEN j.jenis_transaksi='biaya'      THEN j.total_debet ELSE 0 END),0) AS biaya
    FROM jenis_dana jd
    LEFT JOIN jurnal j ON j.jenis_dana_id=jd.id AND j.periode_id=1
    GROUP BY jd.id, jd.kode
    ORDER BY jd.id
")->fetchAll(PDO::FETCH_ASSOC);

printf("%-12s %15s %15s %15s %15s\n", 'Dana','Penerimaan','Penyaluran','Biaya','Saldo_Jan');
foreach ($rows as $r) {
    if ($r['penerimaan'] == 0 && $r['penyaluran'] == 0 && $r['biaya'] == 0) continue;
    printf("%-12s %15s %15s %15s %15s\n",
        $r['kode'],
        number_format($r['penerimaan'],0,',','.'),
        number_format($r['penyaluran'],0,',','.'),
        number_format($r['biaya'],0,',','.'),
        number_format($r['penerimaan']-$r['penyaluran']-$r['biaya'],0,',','.')
    );
}

echo "\n--- Verifikasi saldo rekening Januari 2026 ---\n";
$rows2 = $db->query("
    SELECT rb.id, rb.nama,
        rb.saldo_awal,
        COALESCE(SUM(jd.debet-jd.kredit),0) AS mutasi
    FROM rekening_bank rb
    LEFT JOIN jurnal_detail jd ON jd.rekening_bank_id=rb.id
    LEFT JOIN jurnal j ON j.id=jd.jurnal_id AND j.periode_id=1
    WHERE rb.id IN (1,2,3,9,10,13,14,27,31,32)
    GROUP BY rb.id, rb.nama, rb.saldo_awal
    ORDER BY rb.id
")->fetchAll(PDO::FETCH_ASSOC);
printf("%-4s %-35s %15s %15s %15s\n", 'ID','Nama','Saldo Awal','Mutasi','Saldo Akhir');
foreach ($rows2 as $r) {
    $saldoAkhir = $r['saldo_awal'] + $r['mutasi'];
    printf("%-4d %-35s %15s %15s %15s\n", $r['id'], $r['nama'],
        number_format($r['saldo_awal'],0,',','.'),
        number_format($r['mutasi'],0,',','.'),
        number_format($saldoAkhir,0,',','.')
    );
}
