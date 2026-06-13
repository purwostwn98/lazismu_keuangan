<?php

namespace App\Models;

use CodeIgniter\Model;

class AkunModel extends Model
{
    protected $table      = 'akun';
    protected $primaryKey = 'id';

    protected $allowedFields = ['nomor_akun', 'nama_akun', 'parent_id', 'level', 'tipe', 'is_header'];
    protected $useTimestamps = true;

    public function getByTipe(string $tipe): array
    {
        return $this->where('tipe', $tipe)
                    ->where('is_header', 0)
                    ->orderBy('nomor_akun', 'ASC')
                    ->findAll();
    }

    public function getForSelect(string $tipe): array
    {
        $rows = $this->getByTipe($tipe);
        $result = [];
        foreach ($rows as $r) {
            $result[$r['id']] = $r['nomor_akun'] . ' — ' . $r['nama_akun'];
        }
        return $result;
    }
}
