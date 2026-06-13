<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JenisDanaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['kode' => 'ZAKAT',    'nama' => 'Dana Zakat',             'rasio_amil' => 0.1250],
            ['kode' => 'INFAK_T',  'nama' => 'Dana Infak Terikat',     'rasio_amil' => 0.2000],
            ['kode' => 'INFAK_TT', 'nama' => 'Dana Infak Tidak Terikat','rasio_amil' => 0.2000],
            ['kode' => 'AMIL',     'nama' => 'Dana Amil',              'rasio_amil' => null],
            ['kode' => 'CSR',      'nama' => 'Dana CSR',               'rasio_amil' => null],
            ['kode' => 'WAKAF',    'nama' => 'Dana Wakaf',             'rasio_amil' => null],
            ['kode' => 'KAS_KECIL','nama' => 'Dana Kas Kecil',        'rasio_amil' => null],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        $this->db->table('jenis_dana')->insertBatch($data);
    }
}
