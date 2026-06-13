<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMappingAkunIdEksternalToInt extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('mapping_akun_penyaluran', [
            'id_eksternal' => [
                'name'     => 'id_eksternal',
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('mapping_akun_penyaluran', [
            'id_eksternal' => [
                'name'       => 'id_eksternal',
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
        ]);
    }
}
