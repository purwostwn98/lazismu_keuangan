<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBiayaPenerimaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'jurnal_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'urutan'     => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'nominal'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('jurnal_id');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('biaya_penerima');
    }

    public function down()
    {
        $this->forge->dropTable('biaya_penerima');
    }
}
