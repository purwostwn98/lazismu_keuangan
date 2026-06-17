<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAmilToJenisZisEnum extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE penghimpunan
            MODIFY COLUMN jenis_zis
            ENUM(
                'zakat_maal_ternak','zakat_maal_emas','zakat_maal_perak',
                'zakat_maal_perniagaan','zakat_maal_pertanian','zakat_maal_hadiah',
                'zakat_maal_profesi','zakat_maal_simpanan','zakat_fitrah',
                'zakat_bagi_hasil','infak_terikat','infak_tidak_terikat_umum',
                'infak_kotak','infak_sabtu_seribu','infak_bagi_hasil',
                'dana_non_halal',
                'amil_zakat','amil_infak'
            ) NOT NULL
        ");
    }

    public function down()
    {
        $this->db->query("DELETE FROM penghimpunan WHERE jenis_zis IN ('amil_zakat','amil_infak')");
        $this->db->query("
            ALTER TABLE penghimpunan
            MODIFY COLUMN jenis_zis
            ENUM(
                'zakat_maal_ternak','zakat_maal_emas','zakat_maal_perak',
                'zakat_maal_perniagaan','zakat_maal_pertanian','zakat_maal_hadiah',
                'zakat_maal_profesi','zakat_maal_simpanan','zakat_fitrah',
                'zakat_bagi_hasil','infak_terikat','infak_tidak_terikat_umum',
                'infak_kotak','infak_sabtu_seribu','infak_bagi_hasil','dana_non_halal'
            ) NOT NULL
        ");
    }
}
