<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAsetTetapTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'kode'                   => ['type' => 'VARCHAR', 'constraint' => 30],
            'nama'                   => ['type' => 'VARCHAR', 'constraint' => 200],
            'jenis'                  => ['type' => 'ENUM', 'constraint' => ['tanah', 'bangunan', 'kendaraan', 'peralatan']],
            // Dana pemilik aset: amil=aset operasional, zakat/infak/wakaf=aset kelolaan
            'jenis_kepemilikan'      => ['type' => 'ENUM', 'constraint' => ['amil', 'zakat', 'infak', 'wakaf'], 'default' => 'amil'],
            'tanggal_perolehan'      => ['type' => 'DATE'],
            'harga_perolehan'        => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'umur_ekonomis_bulan'    => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'metode_penyusutan'      => ['type' => 'ENUM', 'constraint' => ['garis_lurus', 'saldo_menurun'], 'default' => 'garis_lurus'],
            'akumulasi_penyusutan'   => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'nilai_buku'             => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'is_aktif'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'keterangan'             => ['type' => 'TEXT', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('aset_tetap');
    }

    public function down()
    {
        $this->forge->dropTable('aset_tetap');
    }
}
