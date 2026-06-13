<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipeEmailToPenerimaManfaat extends Migration
{
    public function up()
    {
        $this->forge->addColumn('penerima_manfaat', [
            'tipe'  => [
                'type'       => 'ENUM',
                'constraint' => ['lembaga', 'individu'],
                'default'    => 'individu',
                'after'      => 'asnaf',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'tipe',
            ],
        ]);

        $this->db->query('ALTER TABLE penerima_manfaat ADD UNIQUE KEY uq_penerima_email (email)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE penerima_manfaat DROP INDEX uq_penerima_email');
        $this->forge->dropColumn('penerima_manfaat', ['tipe', 'email']);
    }
}
