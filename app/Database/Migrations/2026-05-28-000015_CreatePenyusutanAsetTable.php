<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenyusutanAsetTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'aset_tetap_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'periode_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'jumlah_penyusutan'=> ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'jurnal_id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['aset_tetap_id', 'periode_id']);
        $this->forge->addForeignKey('aset_tetap_id', 'aset_tetap', 'id');
        $this->forge->addForeignKey('periode_id', 'periode', 'id');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id');
        $this->forge->createTable('penyusutan_aset');
    }

    public function down()
    {
        $this->forge->dropTable('penyusutan_aset');
    }
}
