<?php

namespace App\Models\Ext;

use CodeIgniter\Model;

class AdProgramModel extends Model
{
    protected $DBGroup    = 'default';
    protected $table      = 'ad_program';
    protected $primaryKey = 'id_program';

    protected $allowedFields = [
        'id_kategori_program',
        'nama_program',
        'deskripsi_program',
        'jenis_formulir',
        'status_program',
    ];

    protected $useTimestamps = false;

    public function getAktif(): array
    {
        return $this->where('status_program', 1)
                    ->orderBy('nama_program', 'ASC')
                    ->findAll();
    }

    public function getWithKategori(): array
    {
        return $this->db
            ->table('ad_program p')
            ->select('p.id_program, p.nama_program, p.deskripsi_program, p.jenis_formulir, p.status_program, k.id_kategori_program, k.nama_kategori')
            ->join('ad_kategori_program k', 'k.id_kategori_program = p.id_kategori_program', 'left')
            ->where('p.status_program', 1)
            ->orderBy('k.nama_kategori, p.nama_program', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getByKategori(int $kategoriId): array
    {
        return $this->where('id_kategori_program', $kategoriId)
                    ->where('status_program', 1)
                    ->orderBy('nama_program', 'ASC')
                    ->findAll();
    }
}
