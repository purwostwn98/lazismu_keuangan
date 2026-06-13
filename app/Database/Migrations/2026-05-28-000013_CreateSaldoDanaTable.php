<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaldoDanaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'periode_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'jenis_dana_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'saldo_awal'        => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'total_penerimaan'  => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'total_penyaluran'  => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'total_biaya'       => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'saldo_akhir'       => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['periode_id', 'jenis_dana_id']);
        $this->forge->addForeignKey('periode_id', 'periode', 'id');
        $this->forge->addForeignKey('jenis_dana_id', 'jenis_dana', 'id');
        $this->forge->createTable('saldo_dana');
    }

    public function down()
    {
        $this->forge->dropTable('saldo_dana');
    }
}
