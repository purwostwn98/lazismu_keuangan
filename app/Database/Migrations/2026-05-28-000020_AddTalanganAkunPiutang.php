<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTalanganAkunPiutang extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // Parent 11203000 (id=46), level=5 → anak level=6
        $this->db->table('akun')->insertBatch([
            [
                'nomor_akun'  => '11203004',
                'nama_akun'   => 'Talangan Dana Zakat',
                'parent_id'   => 46,
                'level'       => 6,
                'tipe'        => 'aset',
                'is_header'   => 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nomor_akun'  => '11203005',
                'nama_akun'   => 'Talangan Dana Infaq',
                'parent_id'   => 46,
                'level'       => 6,
                'tipe'        => 'aset',
                'is_header'   => 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    public function down()
    {
        $this->db->table('akun')
            ->whereIn('nomor_akun', ['11203004', '11203005'])
            ->delete();
    }
}
