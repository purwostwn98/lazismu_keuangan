<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdKategoriProgramTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kategori_program' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'nama_kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi_kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'id_pilar' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'status_kategori' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
        ]);

        $this->forge->addPrimaryKey('id_kategori_program');
        $this->forge->addKey('id_pilar');
        $this->forge->createTable('ad_kategori_program', true);
    }

    public function down()
    {
        $this->forge->dropTable('ad_kategori_program', true);
    }
}
