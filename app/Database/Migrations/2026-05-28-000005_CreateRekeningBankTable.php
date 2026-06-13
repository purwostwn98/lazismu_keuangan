<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRekeningBankTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nama'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'nomor_rekening'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'bank'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'jenis_dana_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'akun_id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'saldo_awal'      => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'is_aktif'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('jenis_dana_id');
        $this->forge->addKey('akun_id');
        $this->forge->addForeignKey('jenis_dana_id', 'jenis_dana', 'id');
        $this->forge->addForeignKey('akun_id', 'akun', 'id');
        $this->forge->createTable('rekening_bank');
    }

    public function down()
    {
        $this->forge->dropTable('rekening_bank');
    }
}
