<?php
/**
 * Script penyesuaian saldo awal per 31 Desember 2025
 * Dijalankan sekali via: php penyesuaian_saldo_awal_2026.php
 *
 * Apa yang dilakukan:
 * 1. Insert 3 jurnal penerimaan pembuka di Desember 2025 (Zakat, Infak, Amil)
 * 2. Tutup periode Desember 2025 → mengisi tabel saldo_dana
 * 3. Update rekening_bank.saldo_awal sesuai posisi 31 Des 2025
 */

$db = new PDO(
    'mysql:host=host.docker.internal;dbname=lazismu_keuangan;charset=utf8mb4',
    'lazismu',
    'lazismu2024',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$now       = date('Y-m-d H:i:s');
$tanggal   = '2025-12-31';
$periodeId = 13; // Desember 2025

// ──────────────────────────────────────────────
// 1. Data jurnal penyesuaian pembuka
// ──────────────────────────────────────────────
// saldo_akhir per dana dari Excel "Saldo Bulan Lalu" Jan 2026
$jurnalList = [
    [
        'nomor'         => 'ADJ-202512-ZKT',
        'jenis_dana_id' => 1,   // ZAKAT
        'uraian'        => 'Jurnal Saldo Pembuka Zakat per 31 Desember 2025',
        'total'         => 244_891_034.00,
        'akun_debet_id' => 5,   // Kas Zakat (11101001)
        'akun_kredit_id'=> 149, // Penerimaan Zakat Non Tunai (40100010)
    ],
    [
        'nomor'         => 'ADJ-202512-INF',
        'jenis_dana_id' => 3,   // INFAK_TT
        'uraian'        => 'Jurnal Saldo Pembuka Infak Sedekah per 31 Desember 2025',
        'total'         => 24_771_764.00,
        'akun_debet_id' => 6,   // Kas Infak Sedekah (11101002)
        'akun_kredit_id'=> 170, // Penerimaan Infak Sedekah (40202001)
    ],
    [
        'nomor'         => 'ADJ-202512-AML',
        'jenis_dana_id' => 4,   // AMIL
        'uraian'        => 'Jurnal Saldo Pembuka Amil per 31 Desember 2025',
        'total'         => 50_279_444.00,
        'akun_debet_id' => 7,   // Kas Amil (11101003)
        'akun_kredit_id'=> 196, // Penerimaan Amil Lain-lain (40400008)
    ],
];

// ──────────────────────────────────────────────
// 2. Saldo awal rekening bank per 31 Des 2025
//    (digunakan Laporan Arus Kas & Posisi Keuangan)
// ──────────────────────────────────────────────
$rekeningUpdate = [
    1  => 1_566_750.00,   // Kas Zakat
    2  => 1_299_350.00,   // Kas Infak Sedekah
    3  =>   437_900.00,   // Kas Amil
    9  => 128_011_080.00, // Jateng Syariah - Zakat
    10 =>   3_693_704.00, // BMT Amanah Ummah - Zakat
    13 =>  23_452_829.00, // Jateng Syariah - Infak Sedekah
    14 =>  61_761_305.00, // BMT Amanah Ummah - Infak Sedekah
    27 =>  20_000_000.00, // SIMKA Zakat
    31 =>  18_556_993.00, // BMT Amanah Ummah Amil
    32 =>  36_162_331.00, // Jateng Syariah Amil
];

// ──────────────────────────────────────────────
// EKSEKUSI
// ──────────────────────────────────────────────
$db->beginTransaction();
try {
    // === A. Hapus jurnal pembuka lama (idempotent) ===
    $nomorList = array_column($jurnalList, 'nomor');
    $in = implode(',', array_fill(0, count($nomorList), '?'));
    $stmt = $db->prepare("SELECT id FROM jurnal WHERE nomor_jurnal IN ($in)");
    $stmt->execute($nomorList);
    $oldIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($oldIds)) {
        $inIds = implode(',', $oldIds);
        $db->exec("DELETE FROM jurnal_detail WHERE jurnal_id IN ($inIds)");
        $db->exec("DELETE FROM jurnal WHERE id IN ($inIds)");
        echo "Hapus " . count($oldIds) . " jurnal pembuka lama.\n";
    }

    // === B. Insert jurnal penerimaan pembuka ===
    $stmtJ = $db->prepare("
        INSERT INTO jurnal
            (nomor_jurnal, tanggal, periode_id, jenis_dana_id, jenis_transaksi,
             uraian, keterangan, total_debet, total_kredit, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'penerimaan', ?, 'Penyesuaian saldo awal tahun 2026', ?, ?, ?, ?)
    ");

    $stmtD = $db->prepare("
        INSERT INTO jurnal_detail (jurnal_id, akun_id, uraian, debet, kredit, created_at, updated_at)
        VALUES (?, ?, ?, ?, 0, ?, ?),
               (?, ?, ?, 0, ?, ?, ?)
    ");

    foreach ($jurnalList as $j) {
        $stmtJ->execute([
            $j['nomor'], $tanggal, $periodeId, $j['jenis_dana_id'],
            $j['uraian'], $j['total'], $j['total'], $now, $now,
        ]);
        $jurId = (int) $db->lastInsertId();

        // 2 baris detail: debet & kredit sekaligus
        $db->prepare("
            INSERT INTO jurnal_detail (jurnal_id, akun_id, uraian, debet, kredit, created_at, updated_at)
            VALUES (?, ?, ?, ?, 0, ?, ?),
                   (?, ?, ?, 0, ?, ?, ?)
        ")->execute([
            $jurId, $j['akun_debet_id'],  $j['uraian'], $j['total'], $now, $now,
            $jurId, $j['akun_kredit_id'], $j['uraian'], $j['total'], $now, $now,
        ]);

        printf("  Jurnal %s  Rp %s  [jenis_dana_id=%d]\n",
            $j['nomor'], number_format($j['total'], 0, ',', '.'), $j['jenis_dana_id']);
    }

    // === C. Tutup Desember 2025: isi saldo_dana ===
    // Hapus snapshot lama jika ada
    $db->prepare("DELETE FROM saldo_dana WHERE periode_id = ?")->execute([$periodeId]);

    // Hitung saldo_awal (transaksi sebelum Des 2025) — untuk Des 2025 = 0 karena tak ada periode sebelumnya
    $saldoAwalRows = $db->query("
        SELECT jd.id AS jenis_dana_id,
            COALESCE(
                SUM(CASE WHEN j.jenis_transaksi = 'penerimaan' THEN j.total_debet ELSE 0 END)
              - SUM(CASE WHEN j.jenis_transaksi IN ('penyaluran','biaya') THEN j.total_debet ELSE 0 END)
            , 0) AS saldo_awal
        FROM jenis_dana jd
        LEFT JOIN jurnal j ON j.jenis_dana_id = jd.id
            AND j.jenis_transaksi IN ('penerimaan','penyaluran','biaya')
            AND j.periode_id IN (
                SELECT p.id FROM periode p
                WHERE p.tahun < 2025 OR (p.tahun = 2025 AND p.bulan < 12)
            )
        GROUP BY jd.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Hitung transaksi periode Desember 2025 saja
    $thisRows = $db->query("
        SELECT jd.id AS jenis_dana_id,
            COALESCE(SUM(CASE WHEN j.jenis_transaksi = 'penerimaan' THEN j.total_debet ELSE 0 END), 0) AS total_penerimaan,
            COALESCE(SUM(CASE WHEN j.jenis_transaksi = 'penyaluran' THEN j.total_debet ELSE 0 END), 0) AS total_penyaluran,
            COALESCE(SUM(CASE WHEN j.jenis_transaksi = 'biaya'      THEN j.total_debet ELSE 0 END), 0) AS total_biaya
        FROM jenis_dana jd
        LEFT JOIN jurnal j ON j.jenis_dana_id = jd.id
            AND j.periode_id = $periodeId
            AND j.jenis_transaksi IN ('penerimaan','penyaluran','biaya')
        GROUP BY jd.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    $saMap   = array_column($saldoAwalRows, 'saldo_awal', 'jenis_dana_id');
    $thisMap = array_column($thisRows, null, 'jenis_dana_id');

    $jenisDanaAll = $db->query("SELECT id FROM jenis_dana")->fetchAll(PDO::FETCH_COLUMN);

    $stmtSd = $db->prepare("
        INSERT INTO saldo_dana
            (periode_id, jenis_dana_id, saldo_awal, total_penerimaan, total_penyaluran, total_biaya, saldo_akhir, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($jenisDanaAll as $jdId) {
        $sa  = (float) ($saMap[$jdId] ?? 0);
        $pen = (float) ($thisMap[$jdId]['total_penerimaan'] ?? 0);
        $psl = (float) ($thisMap[$jdId]['total_penyaluran'] ?? 0);
        $bya = (float) ($thisMap[$jdId]['total_biaya']      ?? 0);
        $stmtSd->execute([$periodeId, $jdId, $sa, $pen, $psl, $bya, $sa + $pen - $psl - $bya, $now, $now]);
    }

    // Set periode Desember 2025 menjadi tutup
    $db->prepare("UPDATE periode SET is_tutup = 1, updated_at = ? WHERE id = ?")->execute([$now, $periodeId]);
    echo "\nPeriode Desember 2025 ditutup. Tabel saldo_dana diisi.\n";

    // === D. Update rekening_bank.saldo_awal ===
    $stmtR = $db->prepare("UPDATE rekening_bank SET saldo_awal = ?, updated_at = ? WHERE id = ?");
    foreach ($rekeningUpdate as $id => $saldo) {
        $stmtR->execute([$saldo, $now, $id]);
    }
    echo "Saldo awal " . count($rekeningUpdate) . " rekening bank diperbarui.\n";

    $db->commit();
    echo "\n=== SELESAI — Penyesuaian saldo awal berhasil. ===\n";

} catch (Throwable $e) {
    $db->rollBack();
    echo "\n!!! GAGAL: " . $e->getMessage() . "\n";
    exit(1);
}

// === Verifikasi ===
echo "\n--- Verifikasi saldo_dana Desember 2025 ---\n";
$rows = $db->query("
    SELECT jd.kode, sd.saldo_awal, sd.total_penerimaan, sd.saldo_akhir
    FROM saldo_dana sd
    JOIN jenis_dana jd ON jd.id = sd.jenis_dana_id
    WHERE sd.periode_id = $periodeId
    ORDER BY jd.id
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    if ($r['saldo_akhir'] > 0) {
        printf("  %-10s  saldo_akhir = Rp %s\n", $r['kode'], number_format($r['saldo_akhir'], 0, ',', '.'));
    }
}

echo "\n--- Verifikasi rekening_bank.saldo_awal ---\n";
$rows2 = $db->query("
    SELECT rb.id, rb.nama, rb.saldo_awal, a.nomor_akun
    FROM rekening_bank rb JOIN akun a ON a.id=rb.akun_id
    WHERE rb.id IN (1,2,3,9,10,13,14,27,31,32)
    ORDER BY a.nomor_akun
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $r) {
    printf("  id=%-3d %-35s  Rp %s\n", $r['id'], $r['nama'], number_format($r['saldo_awal'], 0, ',', '.'));
}
