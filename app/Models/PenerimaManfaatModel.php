<?php

namespace App\Models;

use CodeIgniter\Model;

class PenerimaManfaatModel extends Model
{
    protected $table      = 'penerima_manfaat';
    protected $primaryKey = 'id';

    protected $allowedFields = ['kode', 'nama', 'tipe', 'asnaf', 'email', 'no_hp', 'alamat'];
    protected $useTimestamps = true;

    public function search(string $keyword): array
    {
        return $this->groupStart()
                    ->like('nama', $keyword)
                    ->orLike('kode', $keyword)
                    ->groupEnd()
                    ->orderBy('nama', 'ASC')
                    ->findAll(30);
    }

    public function getAll(): array
    {
        return $this->orderBy('nama', 'ASC')->findAll();
    }
}
