<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPenyaluranAntrianAkunMapping extends Migration
{
    public function up()
    {
        // Ganti no_akun_penyaluran (VARCHAR) → id_penyaluran_eksternal (INT)
        $this->forge->modifyColumn('penyaluran_antrian', [
            'no_akun_penyaluran' => [
                'name'     => 'id_penyaluran_eksternal',
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);

        // Tambah kolom hasil resolusi mapping
        $this->forge->addColumn('penyaluran_antrian', [
            'nomor_akun_penyaluran' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'id_penyaluran_eksternal',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('penyaluran_antrian', 'nomor_akun_penyaluran');

        $this->forge->modifyColumn('penyaluran_antrian', [
            'id_penyaluran_eksternal' => [
                'name'       => 'no_akun_penyaluran',
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ]);
    }
}
