<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFileBuktiToBiayaKegiatan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('biaya_kegiatan', [
            'file_bukti' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
                'after'      => 'uraian_kegiatan',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('biaya_kegiatan', 'file_bukti');
    }
}
