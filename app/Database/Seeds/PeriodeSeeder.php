<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PeriodeSeeder extends Seeder
{
    public function run()
    {
        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $now  = date('Y-m-d H:i:s');
        $data = [];

        foreach ($bulanNama as $bulan => $nama) {
            $data[] = [
                'bulan'      => $bulan,
                'tahun'      => 2026,
                'nama'       => $nama . ' 2026',
                'is_tutup'   => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->db->table('periode')->insertBatch($data);
    }
}
