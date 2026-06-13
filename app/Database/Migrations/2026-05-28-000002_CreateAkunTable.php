<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAkunTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nomor_akun' => ['type' => 'VARCHAR', 'constraint' => 10],
            'nama_akun'  => ['type' => 'VARCHAR', 'constraint' => 200],
            'parent_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'level'      => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 1],
            // 1=Aset 2=Liabilitas 3=SaldoDana 4=Penerimaan 5=Penyaluran 6=Biaya
            'tipe'       => ['type' => 'ENUM', 'constraint' => ['aset', 'liabilitas', 'saldo_dana', 'penerimaan', 'penyaluran', 'biaya']],
            'is_header'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'comment' => 'Header/group account, tidak dapat diposting'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_akun');
        $this->forge->addKey('parent_id');
        $this->forge->createTable('akun');

        $this->db->query('ALTER TABLE akun ADD CONSTRAINT fk_akun_parent FOREIGN KEY (parent_id) REFERENCES akun(id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE akun DROP FOREIGN KEY fk_akun_parent');
        $this->forge->dropTable('akun');
    }
}
