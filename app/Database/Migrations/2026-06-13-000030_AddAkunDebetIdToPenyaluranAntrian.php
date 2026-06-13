<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAkunDebetIdToPenyaluranAntrian extends Migration
{
    public function up()
    {
        $this->forge->addColumn('penyaluran_antrian', [
            'akun_debet_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'nomor_akun_penyaluran',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('penyaluran_antrian', 'akun_debet_id');
    }
}
