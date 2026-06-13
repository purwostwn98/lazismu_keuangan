<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KategoriDonaturSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Parent categories
        $this->db->table('kategori_donatur')->insertBatch([
            ['kode' => 'DOSKAR',     'nama' => 'Dosen & Karyawan UMS',     'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'NON_DOSKAR', 'nama' => 'Non Dosen & Karyawan UMS', 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $doskarId    = $this->db->table('kategori_donatur')->where('kode', 'DOSKAR')->get()->getRow()->id;
        $nonDoskarId = $this->db->table('kategori_donatur')->where('kode', 'NON_DOSKAR')->get()->getRow()->id;

        $this->db->table('kategori_donatur')->insertBatch([
            ['kode' => 'DOSKAR_POTONG_GAJI', 'nama' => 'Doskar UMS - Potong Gaji', 'parent_id' => $doskarId,    'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'DOSKAR_MANDIRI',      'nama' => 'Doskar UMS - Mandiri',     'parent_id' => $doskarId,    'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'NON_DOSKAR_INDIVIDU', 'nama' => 'Non Doskar - Individu',    'parent_id' => $nonDoskarId, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'NON_DOSKAR_LEMBAGA',  'nama' => 'Non Doskar - Lembaga',     'parent_id' => $nonDoskarId, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
