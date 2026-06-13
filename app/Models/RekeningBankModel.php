<?php

namespace App\Models;

use CodeIgniter\Model;

class RekeningBankModel extends Model
{
    protected $table      = 'rekening_bank';
    protected $primaryKey = 'id';

    protected $allowedFields = ['nama', 'nomor_rekening', 'bank', 'jenis_dana_id', 'akun_id', 'saldo_awal', 'is_aktif'];
    protected $useTimestamps = true;

    public function getAllWithRelasi(): array
    {
        return $this->select('rekening_bank.*, jenis_dana.nama AS nama_dana, akun.nomor_akun, akun.nama_akun')
                    ->join('jenis_dana', 'jenis_dana.id = rekening_bank.jenis_dana_id')
                    ->join('akun',       'akun.id = rekening_bank.akun_id')
                    ->orderBy('jenis_dana.id, rekening_bank.nama', 'ASC')
                    ->findAll();
    }

    public function getByJenisDana(int $jenisDanaId): array
    {
        return $this->where('jenis_dana_id', $jenisDanaId)
                    ->where('is_aktif', 1)
                    ->orderBy('nama', 'ASC')
                    ->findAll();
    }

    public function getAktif(): array
    {
        return $this->select('rekening_bank.*, jenis_dana.nama AS nama_dana')
                    ->join('jenis_dana', 'jenis_dana.id = rekening_bank.jenis_dana_id')
                    ->where('rekening_bank.is_aktif', 1)
                    ->orderBy('jenis_dana.id, rekening_bank.nama', 'ASC')
                    ->findAll();
    }
}
