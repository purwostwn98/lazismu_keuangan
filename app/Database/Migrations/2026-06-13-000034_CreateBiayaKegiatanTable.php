<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBiayaKegiatanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'jurnal_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'nama_kegiatan'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'lokasi'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'tgl_berangkat'   => ['type' => 'DATETIME', 'null' => true],
            'tgl_kembali'     => ['type' => 'DATETIME', 'null' => true],
            'uraian_kegiatan' => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('jurnal_id');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('biaya_kegiatan');
    }

    public function down()
    {
        $this->forge->dropTable('biaya_kegiatan');
    }
}
