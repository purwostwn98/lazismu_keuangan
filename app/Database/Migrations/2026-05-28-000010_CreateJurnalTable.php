<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJurnalTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nomor_jurnal'     => ['type' => 'VARCHAR', 'constraint' => 40],
            'tanggal'          => ['type' => 'DATE'],
            'periode_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'jenis_dana_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'jenis_transaksi'  => ['type' => 'ENUM', 'constraint' => ['penerimaan', 'penyaluran', 'biaya', 'transfer', 'jurnal_umum']],
            'uraian'           => ['type' => 'TEXT'],
            'keterangan'       => ['type' => 'TEXT', 'null' => true],
            'donatur_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'penerima_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'program_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'total_debet'      => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'total_kredit'     => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'created_by'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_jurnal');
        $this->forge->addKey('periode_id');
        $this->forge->addKey('jenis_dana_id');
        $this->forge->addKey('donatur_id');
        $this->forge->addKey('penerima_id');
        $this->forge->addKey('program_id');
        $this->forge->addKey('tanggal');
        $this->forge->addForeignKey('periode_id', 'periode', 'id');
        $this->forge->addForeignKey('jenis_dana_id', 'jenis_dana', 'id');
        $this->forge->addForeignKey('donatur_id', 'donatur', 'id');
        $this->forge->addForeignKey('penerima_id', 'penerima_manfaat', 'id');
        $this->forge->addForeignKey('program_id', 'program_penyaluran', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');
        $this->forge->createTable('jurnal');
    }

    public function down()
    {
        $this->forge->dropTable('jurnal');
    }
}
