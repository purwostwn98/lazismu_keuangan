<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDonaturTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'kode'         => ['type' => 'VARCHAR', 'constraint' => 30],
            'nama'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'jenis'        => ['type' => 'ENUM', 'constraint' => ['individu', 'lembaga'], 'default' => 'individu'],
            'kategori_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'nip'          => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'comment' => 'NIP untuk dosen/karyawan UMS'],
            'no_hp'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'alamat'       => ['type' => 'TEXT', 'null' => true],
            'is_aktif'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->addKey('kategori_id');
        $this->forge->addForeignKey('kategori_id', 'kategori_donatur', 'id');
        $this->forge->createTable('donatur');
    }

    public function down()
    {
        $this->forge->dropTable('donatur');
    }
}
