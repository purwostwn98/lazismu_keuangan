<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMappingAkunPenyaluranTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nomor_akun'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'id_eksternal'     => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'sumber_aplikasi'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'id_dana'          => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'keterangan'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_eksternal', 'sumber_aplikasi'], 'uq_mapping_eksternal');

        $this->forge->createTable('mapping_akun_penyaluran', true);
    }

    public function down()
    {
        $this->forge->dropTable('mapping_akun_penyaluran', true);
    }
}
