<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdDanaToMappingAkunPenyaluran extends Migration
{
    public function up()
    {
        $this->forge->addColumn('mapping_akun_penyaluran', [
            'id_dana' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'sumber_aplikasi',
            ],
        ]);

        // Isi id_dana untuk data mylazismu yang sudah ada
        $this->db->query('UPDATE mapping_akun_penyaluran SET id_dana = 1 WHERE sumber_aplikasi = "mylazismu" AND id_eksternal BETWEEN 1 AND 8');
        $this->db->query('UPDATE mapping_akun_penyaluran SET id_dana = 3 WHERE sumber_aplikasi = "mylazismu" AND id_eksternal BETWEEN 9 AND 16');
        $this->db->query('UPDATE mapping_akun_penyaluran SET id_dana = 4 WHERE sumber_aplikasi = "mylazismu" AND id_eksternal = 17');
        $this->db->query('UPDATE mapping_akun_penyaluran SET id_dana = 2 WHERE sumber_aplikasi = "mylazismu" AND id_eksternal BETWEEN 18 AND 25');
    }

    public function down()
    {
        $this->forge->dropColumn('mapping_akun_penyaluran', 'id_dana');
    }
}
