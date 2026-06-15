<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKoreksiToJurnalEnum extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE jurnal
            MODIFY COLUMN jenis_transaksi
            ENUM('penerimaan','penyaluran','biaya','transfer','jurnal_umum','piutang','koreksi')
            NOT NULL
        ");
    }

    public function down()
    {
        $this->db->query("DELETE FROM jurnal WHERE jenis_transaksi = 'koreksi'");
        $this->db->query("
            ALTER TABLE jurnal
            MODIFY COLUMN jenis_transaksi
            ENUM('penerimaan','penyaluran','biaya','transfer','jurnal_umum','piutang')
            NOT NULL
        ");
    }
}
