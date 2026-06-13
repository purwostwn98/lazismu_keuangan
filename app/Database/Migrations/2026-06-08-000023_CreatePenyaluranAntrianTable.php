<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenyaluranAntrianTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'sumber'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ref_eksternal'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tanggal'        => ['type' => 'DATE', 'null' => false],
            'jenis_dana_id'  => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'program_nama'   => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'program_ext_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'nama_penerima'  => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'penerima_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'jumlah'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => false, 'default' => 0],
            'uraian'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'keterangan'     => ['type' => 'TEXT', 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'verified', 'rejected'], 'default' => 'pending'],
            'jurnal_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'catatan'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('tanggal');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id', 'SET NULL', 'SET NULL');

        $this->forge->createTable('penyaluran_antrian', true);
    }

    public function down()
    {
        $this->forge->dropTable('penyaluran_antrian', true);
    }
}
