<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePiutangCicilanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'piutang_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'tanggal'    => ['type' => 'DATE'],
            'jumlah'     => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'jurnal_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('piutang_id');
        $this->forge->addForeignKey('piutang_id', 'piutang', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id');
        $this->forge->createTable('piutang_cicilan');
    }

    public function down()
    {
        $this->forge->dropTable('piutang_cicilan');
    }
}
