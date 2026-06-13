<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersediaanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kode_barang'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'nama_barang'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'satuan'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Kg'],
            'akun_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'jenis_dana_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'stok_masuk'    => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'stok_keluar'   => ['type' => 'DECIMAL', 'constraint' => '18,3', 'default' => 0],
            'nilai_per_satuan' => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'keterangan'    => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_barang');
        $this->forge->addForeignKey('akun_id', 'akun', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('jenis_dana_id', 'jenis_dana', 'id', 'SET NULL', 'SET NULL');

        $this->forge->createTable('persediaan', true);
    }

    public function down()
    {
        $this->forge->dropTable('persediaan', true);
    }
}
