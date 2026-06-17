<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLembagaKategoriDonatur extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('kategori_donatur')->insertBatch([
            ['kode' => 'LEMBAGA_UMS',     'nama' => 'Lembaga UMS',     'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['kode' => 'LEMBAGA_NON_UMS', 'nama' => 'Lembaga Non UMS', 'parent_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        $this->db->table('kategori_donatur')
            ->whereIn('kode', ['LEMBAGA_UMS', 'LEMBAGA_NON_UMS'])
            ->delete();
    }
}
