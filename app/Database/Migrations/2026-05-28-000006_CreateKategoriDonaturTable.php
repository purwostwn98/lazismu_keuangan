<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKategoriDonaturTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'kode'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'parent_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->addKey('parent_id');
        $this->forge->createTable('kategori_donatur');

        $this->db->query('ALTER TABLE kategori_donatur ADD CONSTRAINT fk_katdon_parent FOREIGN KEY (parent_id) REFERENCES kategori_donatur(id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE kategori_donatur DROP FOREIGN KEY fk_katdon_parent');
        $this->forge->dropTable('kategori_donatur');
    }
}
