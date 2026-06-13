<?php
/**
 * Backfill tabel penghimpunan dari jurnal penerimaan yang sudah ada.
 * Jalankan sekali: php backfill_penghimpunan.php
 */

$db = new PDO(
    'mysql:host=host.docker.internal;dbname=lazismu_keuangan;charset=utf8mb4',
    'lazismu',
    'lazismu2024',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$now = date('Y-m-d H:i:s');

// Ambil kategori_id default (kategori pertama yang ada)
$defaultKategoriId = (int) $db->query("SELECT id FROM kategori_donatur ORDER BY id ASC LIMIT 1")->fetchColumn();
if (!$defaultKategoriId) {
    echo "GAGAL: tidak ada data di tabel kategori_donatur.\n";
    exit(1);
}
echo "Menggunakan kategori_id default: $defaultKategoriId\n";

// Pemetaan jenis_zis berdasarkan jenis_dana_id dan uraian
function guessJenisZis(int $jenisDanaId, string $uraian): ?string
{
    $uraian = strtolower($uraian);

    if ($jenisDanaId === 1) { // ZAKAT
        if (str_contains($uraian, 'bagi hasil')) return 'zakat_bagi_hasil';
        if (str_contains($uraian, 'fitrah'))     return 'zakat_fitrah';
        if (str_contains($uraian, 'maal'))       return 'zakat_maal_profesi';
        return 'zakat_maal_profesi';
    }

    if ($jenisDanaId === 2) { // INFAK TERIKAT
        return 'infak_terikat';
    }

    if ($jenisDanaId === 3) { // INFAK TIDAK TERIKAT / SEDEKAH
        if (str_contains($uraian, 'bagi hasil')) return 'infak_bagi_hasil';
        if (str_contains($uraian, 'kotak'))      return 'infak_kotak';
        if (str_contains($uraian, 'sabtu'))      return 'infak_sabtu_seribu';
        return 'infak_tidak_terikat_umum';
    }

    // jenis_dana_id 4 (AMIL) dan lainnya = bukan penerimaan ZIS dari donatur, skip
    return null;
}

// Ambil semua jurnal penerimaan yang belum ada di penghimpunan
$rows = $db->query("
    SELECT j.id AS jurnal_id, j.periode_id, j.jenis_dana_id, j.uraian, j.total_debet AS jumlah
    FROM jurnal j
    WHERE j.jenis_transaksi = 'penerimaan'
      AND NOT EXISTS (
          SELECT 1 FROM penghimpunan ph WHERE ph.jurnal_id = j.id
      )
    ORDER BY j.tanggal ASC, j.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "Tidak ada jurnal penerimaan yang perlu di-backfill.\n";
    exit(0);
}

echo "Ditemukan " . count($rows) . " jurnal penerimaan tanpa penghimpunan.\n\n";

$stmt = $db->prepare("
    INSERT INTO penghimpunan (periode_id, donatur_id, kategori_id, jenis_zis, jumlah, jurnal_id, created_at, updated_at)
    VALUES (?, NULL, ?, ?, ?, ?, ?, ?)
");

$inserted = 0;
$skipped  = 0;

$db->beginTransaction();
try {
    foreach ($rows as $r) {
        $jenisZis = guessJenisZis((int)$r['jenis_dana_id'], $r['uraian']);

        if ($jenisZis === null) {
            echo "  SKIP  jurnal_id={$r['jurnal_id']}  dana_id={$r['jenis_dana_id']}  \"{$r['uraian']}\"\n";
            $skipped++;
            continue;
        }

        $stmt->execute([
            $r['periode_id'],
            $defaultKategoriId,
            $jenisZis,
            $r['jumlah'],
            $r['jurnal_id'],
            $now,
            $now,
        ]);

        printf("  OK    jurnal_id=%-4d  %-28s  Rp %s\n",
            $r['jurnal_id'], $jenisZis, number_format($r['jumlah'], 0, ',', '.'));
        $inserted++;
    }

    $db->commit();
    echo "\n=== SELESAI: $inserted diinsert, $skipped dilewati (bukan ZIS). ===\n";
} catch (Throwable $e) {
    $db->rollBack();
    echo "\n!!! GAGAL: " . $e->getMessage() . "\n";
    exit(1);
}