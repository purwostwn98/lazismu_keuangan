<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodeModel extends Model
{
    protected $table      = 'periode';
    protected $primaryKey = 'id';

    protected $allowedFields = ['bulan', 'tahun', 'nama', 'is_tutup'];
    protected $useTimestamps = true;

    public function getAktif(): array
    {
        return $this->where('is_tutup', 0)->orderBy('tahun DESC, bulan DESC')->findAll();
    }

    public function findByTanggal(string $tanggal): array|null
    {
        $dt = new \DateTime($tanggal);
        return $this->where('bulan', (int) $dt->format('n'))
                    ->where('tahun', (int) $dt->format('Y'))
                    ->first();
    }
}
