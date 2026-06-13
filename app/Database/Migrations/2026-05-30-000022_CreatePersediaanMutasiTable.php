<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersediaanMutasiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nomor_mutasi'  => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'persediaan_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'periode_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'tanggal'       => ['type' => 'DATE', 'null' => false],
            'jenis'         => ['type' => 'ENUM', 'constraint' => ['masuk', 'keluar'], 'null' => false],
            'sub_jenis'     => ['type' => 'ENUM', 'constraint' => ['penerimaan_natura', 'pembelian', 'penyaluran', 'pemakaian'], 'null' => false],
            'kuantitas'     => ['type' => 'DECIMAL', 'constraint' => '18,3', 'null' => false],
            'nilai_satuan'  => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'total_nilai'   => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'akun_lawan_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'rekening_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'jurnal_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'uraian'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'keterangan'    => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_mutasi');
        $this->forge->addKey('persediaan_id');
        $this->forge->addKey('periode_id');
        $this->forge->addForeignKey('persediaan_id', 'persediaan', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('periode_id', 'periode', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('akun_lawan_id', 'akun', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('rekening_id', 'rekening_bank', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id', 'SET NULL', 'SET NULL');

        $this->forge->createTable('persediaan_mutasi', true);
    }

    public function down()
    {
        $this->forge->dropTable('persediaan_mutasi', true);
    }
}
