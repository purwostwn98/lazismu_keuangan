<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRefJurnalIdToJurnal extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE jurnal
            ADD COLUMN ref_jurnal_id INT UNSIGNED NULL DEFAULT NULL,
            ADD CONSTRAINT fk_jurnal_ref
                FOREIGN KEY (ref_jurnal_id) REFERENCES jurnal(id) ON DELETE SET NULL
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE jurnal DROP FOREIGN KEY fk_jurnal_ref");
        $this->db->query("ALTER TABLE jurnal DROP COLUMN ref_jurnal_id");
    }
}
