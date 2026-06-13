<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdProgramTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_program' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'id_kategori_program' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'nama_program' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi_program' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            // 0=Lainnya, 1=Individu, 2=Sekolah, 3=Usaha, 4=Masjid
            'jenis_formulir' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'status_program' => [
                'type'       => 'TINYINT',
                'constraint' => 4,
                'default'    => 1,
            ],
        ]);

        $this->forge->addPrimaryKey('id_program');
        $this->forge->addKey('id_kategori_program');
        $this->forge->addForeignKey('id_kategori_program', 'ad_kategori_program', 'id_kategori_program', '', 'CASCADE');
        $this->forge->createTable('ad_program', true);
    }

    public function down()
    {
        $this->forge->dropTable('ad_program', true);
    }
}
