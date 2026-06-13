<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisDanaModel extends Model
{
    protected $table      = 'jenis_dana';
    protected $primaryKey = 'id';

    protected $allowedFields = ['kode', 'nama', 'rasio_amil'];
    protected $useTimestamps = true;

    public function getAll(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    public function getByKode(string $kode): array|null
    {
        return $this->where('kode', $kode)->first();
    }
}
