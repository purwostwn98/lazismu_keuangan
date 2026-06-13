<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenerimaManaatTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'kode'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 150],
            // Golongan penerima zakat (8 asnaf)
            'asnaf'      => ['type' => 'ENUM', 'constraint' => ['fakir', 'miskin', 'amil', 'muallaf', 'riqab', 'gharimin', 'fisabilillah', 'ibnu_sabil'], 'null' => true],
            'tipe'       => ['type' => 'ENUM', 'constraint' => ['lembaga', 'individu'], 'default' => 'individu'],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'no_hp'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'alamat'     => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('penerima_manfaat');
    }

    public function down()
    {
        $this->forge->dropTable('penerima_manfaat');
    }
}
