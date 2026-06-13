<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJenisDanaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'kode'        => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 80],
            // Persentase bagian amil: 0.1250 = 12.5% (zakat), 0.2000 = 20% (infak)
            'rasio_amil'  => ['type' => 'DECIMAL', 'constraint' => '5,4', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('jenis_dana');
    }

    public function down()
    {
        $this->forge->dropTable('jenis_dana');
    }
}
