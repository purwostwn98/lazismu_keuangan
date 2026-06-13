<?php

namespace App\Models;

use CodeIgniter\Model;

class DonaturModel extends Model
{
    protected $table         = 'donatur';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['kode', 'nama', 'jenis', 'kategori_id', 'nip', 'no_hp', 'email', 'alamat', 'is_aktif'];
    protected $useTimestamps = true;

    public function getWithKategori(): array
    {
        return $this->select('donatur.*, kategori_donatur.nama AS nama_kategori')
            ->join('kategori_donatur', 'kategori_donatur.id = donatur.kategori_id', 'left')
            ->orderBy('donatur.nama', 'ASC')
            ->findAll();
    }

    public function autoKode(): string
    {
        $count = $this->countAll() + 1;
        do {
            $kode = 'DON' . str_pad($count, 5, '0', STR_PAD_LEFT);
            $count++;
        } while ($this->where('kode', $kode)->countAllResults() > 0);

        return $kode;
    }
}