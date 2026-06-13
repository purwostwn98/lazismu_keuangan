<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddJenisDanaIdToPiutang extends Migration
{
    public function up()
    {
        $this->forge->addColumn('piutang', [
            'jenis_dana_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'jenis',
            ],
        ]);
        $this->db->query('ALTER TABLE piutang ADD CONSTRAINT fk_piutang_jenis_dana FOREIGN KEY (jenis_dana_id) REFERENCES jenis_dana(id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE piutang DROP FOREIGN KEY fk_piutang_jenis_dana');
        $this->forge->dropColumn('piutang', 'jenis_dana_id');
    }
}
