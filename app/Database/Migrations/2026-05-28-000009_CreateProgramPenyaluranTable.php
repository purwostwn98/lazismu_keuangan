<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProgramPenyaluranTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'kode'          => ['type' => 'VARCHAR', 'constraint' => 30],
            'nama'          => ['type' => 'VARCHAR', 'constraint' => 200],
            'jenis_dana_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'kategori'      => ['type' => 'ENUM', 'constraint' => ['ekonomi', 'sosial', 'pendidikan', 'kesehatan', 'kemanusiaan', 'keagamaan', 'pkbl_csr', 'lainnya'], 'null' => true],
            'asnaf'         => ['type' => 'ENUM', 'constraint' => ['fakir', 'miskin', 'amil', 'muallaf', 'riqab', 'gharimin', 'fisabilillah', 'ibnu_sabil'], 'null' => true],
            'is_aktif'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->addKey('jenis_dana_id');
        $this->forge->addForeignKey('jenis_dana_id', 'jenis_dana', 'id');
        $this->forge->createTable('program_penyaluran');
    }

    public function down()
    {
        $this->forge->dropTable('program_penyaluran');
    }
}
