<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJurnalDetailTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'jurnal_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'akun_id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'rekening_bank_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'uraian'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'debet'            => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'kredit'           => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('jurnal_id');
        $this->forge->addKey('akun_id');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('akun_id', 'akun', 'id');
        $this->forge->addForeignKey('rekening_bank_id', 'rekening_bank', 'id');
        $this->forge->createTable('jurnal_detail');
    }

    public function down()
    {
        $this->forge->dropTable('jurnal_detail');
    }
}
