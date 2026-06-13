<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPiutangToJurnalEnum extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE jurnal
            MODIFY COLUMN jenis_transaksi
            ENUM('penerimaan','penyaluran','biaya','transfer','jurnal_umum','piutang')
            NOT NULL
        ");
    }

    public function down()
    {
        // Hapus data piutang dulu agar tidak error saat revert enum
        $this->db->query("DELETE FROM jurnal WHERE jenis_transaksi = 'piutang'");
        $this->db->query("
            ALTER TABLE jurnal
            MODIFY COLUMN jenis_transaksi
            ENUM('penerimaan','penyaluran','biaya','transfer','jurnal_umum')
            NOT NULL
        ");
    }
}
