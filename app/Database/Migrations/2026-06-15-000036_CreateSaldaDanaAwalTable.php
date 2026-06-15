<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaldaDanaAwalTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'tahun'         => ['type' => 'SMALLINT', 'constraint' => 6, 'unsigned' => true],
            'jenis_dana_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'saldo'         => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tahun', 'jenis_dana_id']);
        $this->forge->addForeignKey('jenis_dana_id', 'jenis_dana', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('saldo_dana_awal');
    }

    public function down()
    {
        $this->forge->dropTable('saldo_dana_awal');
    }
}
