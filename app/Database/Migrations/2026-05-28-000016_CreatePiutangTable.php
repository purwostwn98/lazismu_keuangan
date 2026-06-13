<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePiutangTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nomor_piutang'     => ['type' => 'VARCHAR', 'constraint' => 40],
            'penerima_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'jenis'             => ['type' => 'ENUM', 'constraint' => ['qardul_hasan_amil', 'qardul_hasan_non_amil', 'penyaluran', 'talangan_amil']],
            'jumlah_pokok'      => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'tanggal_pinjam'    => ['type' => 'DATE'],
            'tanggal_jatuh_tempo' => ['type' => 'DATE', 'null' => true],
            'jumlah_terbayar'   => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'sisa_piutang'      => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'status'            => ['type' => 'ENUM', 'constraint' => ['aktif', 'lunas', 'hapus_buku'], 'default' => 'aktif'],
            'jurnal_id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'keterangan'        => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_piutang');
        $this->forge->addKey('penerima_id');
        $this->forge->addForeignKey('penerima_id', 'penerima_manfaat', 'id');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id');
        $this->forge->createTable('piutang');
    }

    public function down()
    {
        $this->forge->dropTable('piutang');
    }
}
