<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RestrukturKategoriDonatur extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');
        $db  = $this->db;

        $db->query('SET FOREIGN_KEY_CHECKS=0');

        // ── 1. Hapus semua kategori lama ─────────────────────────────
        $db->query('DELETE FROM kategori_donatur');
        $db->query('ALTER TABLE kategori_donatur AUTO_INCREMENT = 1');

        // ── 2. Insert parent baru ─────────────────────────────────────
        $db->table('kategori_donatur')->insertBatch([
            ['kode' => 'INDIVIDU',        'nama' => 'Individu',        'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'ENTITAS_LEMBAGA', 'nama' => 'Entitas/Lembaga', 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'BAGI_HASIL',      'nama' => 'Bagi Hasil',      'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $idIndividu = $db->table('kategori_donatur')->where('kode', 'INDIVIDU')->get()->getRow()->id;
        $idLembaga  = $db->table('kategori_donatur')->where('kode', 'ENTITAS_LEMBAGA')->get()->getRow()->id;
        $idBagiHsl  = $db->table('kategori_donatur')->where('kode', 'BAGI_HASIL')->get()->getRow()->id;

        // ── 3. Insert children ────────────────────────────────────────
        $db->table('kategori_donatur')->insertBatch([
            // Individu
            ['kode' => 'DOSKAR_POTONG_GAJI',  'nama' => 'Doskar UMS Potong Gaji',  'parent_id' => $idIndividu, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'DOSKAR_MANDIRI',       'nama' => 'Doskar UMS Mandiri',       'parent_id' => $idIndividu, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'NON_DOSKAR_UMS',       'nama' => 'Non Doskar UMS',           'parent_id' => $idIndividu, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'DSKL_DOSKAR_UMS',      'nama' => 'DSKL Doskar UMS',          'parent_id' => $idIndividu, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'DSKL_NON_DOSKAR_UMS',  'nama' => 'DSKL Non Doskar UMS',      'parent_id' => $idIndividu, 'created_at' => $now, 'updated_at' => $now],
            // Entitas/Lembaga
            ['kode' => 'LEMBAGA_UMS',          'nama' => 'UMS',                      'parent_id' => $idLembaga,  'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'LEMBAGA_NON_UMS',      'nama' => 'Non UMS',                  'parent_id' => $idLembaga,  'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'KOTAK_INFAK',          'nama' => 'Kotak Infak',              'parent_id' => $idLembaga,  'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'MENTORING_SABTU_SERIBU','nama' => 'Mentoring/Sabtu Seribu',  'parent_id' => $idLembaga,  'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'DSKL_UMS',             'nama' => 'DSKL UMS',                 'parent_id' => $idLembaga,  'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'DSKL_NON_UMS',         'nama' => 'DSKL Non UMS',             'parent_id' => $idLembaga,  'created_at' => $now, 'updated_at' => $now],
            // Bagi Hasil
            ['kode' => 'BAGI_HASIL_TABUNGAN',  'nama' => 'Bagi Hasil Tabungan',      'parent_id' => $idBagiHsl,  'created_at' => $now, 'updated_at' => $now],
        ]);

        $idDoskarPG    = $db->table('kategori_donatur')->where('kode', 'DOSKAR_POTONG_GAJI')->get()->getRow()->id;
        $idLembagaNonUms = $db->table('kategori_donatur')->where('kode', 'LEMBAGA_NON_UMS')->get()->getRow()->id;

        // ── 4. Remap penghimpunan (NOT NULL — wajib di-update) ────────
        // Lama id=1 (Dosen & Karyawan UMS / parent) → Doskar UMS Potong Gaji
        // Lama id=3 (Doskar UMS - Potong Gaji)       → Doskar UMS Potong Gaji
        // Lama id=6 (Non Doskar - Lembaga)            → Non UMS
        $db->query("UPDATE penghimpunan SET kategori_id = {$idDoskarPG}    WHERE kategori_id IN (1, 3)");
        $db->query("UPDATE penghimpunan SET kategori_id = {$idLembagaNonUms} WHERE kategori_id = 6");
        // Sisa id yang tidak punya data (2,4,5,7,8) tidak perlu di-update

        // ── 5. Remap donatur (nullable — set NULL jika tidak ada mapping) ─
        $db->query("UPDATE donatur SET kategori_id = {$idDoskarPG} WHERE kategori_id = 3");
        $db->query("UPDATE donatur SET kategori_id = NULL WHERE kategori_id NOT IN (SELECT id FROM kategori_donatur)");

        $db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // Tidak ada rollback otomatis karena data lama sudah tidak ada
        throw new \RuntimeException('Rollback tidak didukung untuk migrasi restruktur kategori donatur.');
    }
}
