<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPenerimaInfoToPenyaluranAntrian extends Migration
{
    public function up()
    {
        $this->forge->addColumn('penyaluran_antrian', [
            'tipe_penerima' => [
                'type'       => 'ENUM',
                'constraint' => ['individu', 'lembaga'],
                'null'       => true,
                'after'      => 'nama_penerima',
            ],
            'nik_nomor_lembaga' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'tipe_penerima',
            ],
            'no_akun_penyaluran' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'nik_nomor_lembaga',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('penyaluran_antrian', ['tipe_penerima', 'nik_nomor_lembaga', 'no_akun_penyaluran']);
    }
}
