<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDtPilarTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pilar' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'nama_pilar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi_pilar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
        ]);

        $this->forge->addPrimaryKey('id_pilar');
        $this->forge->createTable('dt_pilar', true);
    }

    public function down()
    {
        $this->forge->dropTable('dt_pilar', true);
    }
}
