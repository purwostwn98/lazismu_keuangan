<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'username'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'password'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'                => ['type' => 'ENUM', 'constraint' => ['admin', 'bendahara', 'manajer', 'auditor'], 'default' => 'bendahara'],
            'is_muzaki'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'comment' => '1 = juga sebagai muzaki/donatur'],
            'is_mustahik'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'comment' => '1 = juga sebagai mustahik/penerima'],
            'donatur_id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
            'penerima_manfaat_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
            'is_aktif'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_login'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
