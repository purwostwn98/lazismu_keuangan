<?php

namespace App\Models\Ext;

use CodeIgniter\Model;

class AdKategoriProgramModel extends Model
{
    protected $DBGroup    = 'default';
    protected $table      = 'ad_kategori_program';
    protected $primaryKey = 'id_kategori_program';

    protected $allowedFields = [
        'nama_kategori',
        'deskripsi_kategori',
        'id_pilar',
        'status_kategori',
    ];

    protected $useTimestamps = false;

    public function getAktif(): array
    {
        return $this->where('status_kategori', 1)
                    ->orderBy('nama_kategori', 'ASC')
                    ->findAll();
    }
}
