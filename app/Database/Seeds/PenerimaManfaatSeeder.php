<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PenerimaManfaatSeeder extends Seeder
{
    public function run()
    {
        $now  = date('Y-m-d H:i:s');
        $data = [
            ['kode' => 'PM00001', 'nama' => 'Ahmad Fauzi',          'asnaf' => 'fakir',        'no_hp' => '081234567801', 'alamat' => 'Jl. Mawar No. 1, Semarang'],
            ['kode' => 'PM00002', 'nama' => 'Siti Aminah',          'asnaf' => 'miskin',       'no_hp' => '081234567802', 'alamat' => 'Jl. Melati No. 5, Semarang'],
            ['kode' => 'PM00003', 'nama' => 'Budi Santoso',         'asnaf' => 'fakir',        'no_hp' => '081234567803', 'alamat' => 'Jl. Kenanga No. 3, Ungaran'],
            ['kode' => 'PM00004', 'nama' => 'Dewi Rahayu',          'asnaf' => 'miskin',       'no_hp' => null,           'alamat' => 'Jl. Anggrek No. 7, Semarang'],
            ['kode' => 'PM00005', 'nama' => 'Hasan Basri',          'asnaf' => 'gharimin',     'no_hp' => '081234567805', 'alamat' => 'Jl. Nusa Indah No. 2, Demak'],
            ['kode' => 'PM00006', 'nama' => 'Fatimah Zahra',        'asnaf' => 'muallaf',      'no_hp' => '081234567806', 'alamat' => 'Jl. Cempaka No. 9, Semarang'],
            ['kode' => 'PM00007', 'nama' => 'Rizki Hidayat',        'asnaf' => 'fisabilillah', 'no_hp' => '081234567807', 'alamat' => 'Jl. Dahlia No. 11, Kendal'],
            ['kode' => 'PM00008', 'nama' => 'Nur Hidayah',          'asnaf' => 'ibnu_sabil',   'no_hp' => null,           'alamat' => null],
            ['kode' => 'PM00009', 'nama' => 'Yusuf Maulana',        'asnaf' => 'fakir',        'no_hp' => '081234567809', 'alamat' => 'Jl. Mangga No. 4, Semarang'],
            ['kode' => 'PM00010', 'nama' => 'Halimah Tusadiyah',    'asnaf' => 'miskin',       'no_hp' => '081234567810', 'alamat' => 'Jl. Pisang No. 6, Mranggen'],
            ['kode' => 'PM00011', 'nama' => 'Sulaiman Hakim',       'asnaf' => 'riqab',        'no_hp' => null,           'alamat' => null],
            ['kode' => 'PM00012', 'nama' => 'Aisyah Putri',         'asnaf' => 'fakir',        'no_hp' => '081234567812', 'alamat' => 'Jl. Rambutan No. 8, Semarang'],
            ['kode' => 'PM00013', 'nama' => 'Muhajirin Al-Farisi',  'asnaf' => 'ibnu_sabil',   'no_hp' => '081234567813', 'alamat' => null],
            ['kode' => 'PM00014', 'nama' => 'Romlah Khasanah',      'asnaf' => 'miskin',       'no_hp' => '081234567814', 'alamat' => 'Jl. Duku No. 12, Banyumanik'],
            ['kode' => 'PM00015', 'nama' => 'Irfan Hamdani',        'asnaf' => 'fisabilillah', 'no_hp' => null,           'alamat' => 'Jl. Durian No. 3, Tembalang'],
        ];

        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        $this->db->table('penerima_manfaat')->insertBatch($data);
    }
}