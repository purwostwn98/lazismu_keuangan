<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenghimpunanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'periode_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'donatur_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'kategori_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            // Jenis ZIS sesuai sheet PENGHIMPUNAN
            'jenis_zis'    => [
                'type'       => 'ENUM',
                'constraint' => [
                    'zakat_maal_ternak', 'zakat_maal_emas', 'zakat_maal_perak',
                    'zakat_maal_perniagaan', 'zakat_maal_pertanian', 'zakat_maal_hadiah',
                    'zakat_maal_profesi', 'zakat_maal_simpanan', 'zakat_fitrah',
                    'zakat_bagi_hasil', 'infak_terikat', 'infak_tidak_terikat_umum',
                    'infak_kotak', 'infak_sabtu_seribu', 'infak_bagi_hasil', 'dana_non_halal',
                ],
            ],
            'jumlah'       => ['type' => 'DECIMAL', 'constraint' => '18,2', 'default' => 0],
            'jurnal_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('periode_id');
        $this->forge->addKey('donatur_id');
        $this->forge->addKey('kategori_id');
        $this->forge->addForeignKey('periode_id', 'periode', 'id');
        $this->forge->addForeignKey('donatur_id', 'donatur', 'id');
        $this->forge->addForeignKey('kategori_id', 'kategori_donatur', 'id');
        $this->forge->addForeignKey('jurnal_id', 'jurnal', 'id');
        $this->forge->createTable('penghimpunan');
    }

    public function down()
    {
        $this->forge->dropTable('penghimpunan');
    }
}
