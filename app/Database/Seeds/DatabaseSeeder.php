<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('JenisDanaSeeder');
        $this->call('AkunSeeder');
        $this->call('KategoriDonaturSeeder');
        $this->call('PeriodeSeeder');
        $this->call('RekeningBankSeeder');
        $this->call('PenerimaManfaatSeeder');
    }
}
