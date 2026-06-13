<?php

namespace App\Models;

use CodeIgniter\Model;

class PenyaluranAntrianModel extends Model
{
    protected $table      = 'penyaluran_antrian';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'sumber', 'ref_eksternal', 'tanggal', 'jenis_dana_id',
        'program_nama', 'program_ext_id', 'nama_penerima', 'tipe_penerima',
        'nik_nomor_lembaga', 'id_penyaluran_eksternal', 'nomor_akun_penyaluran', 'akun_debet_id', 'penerima_id',
        'jumlah', 'uraian', 'keterangan', 'status', 'jurnal_id', 'catatan',
    ];
    protected $useTimestamps = true;

    public function getDaftar(array $filter = []): array
    {
        $builder = $this->db->table('penyaluran_antrian pa')
            ->select('pa.*, jd.nama AS nama_dana')
            ->join('jenis_dana jd', 'jd.id = pa.jenis_dana_id', 'left')
            ->orderBy('pa.created_at', 'DESC');

        if (!empty($filter['status'])) {
            $builder->where('pa.status', $filter['status']);
        }

        return $builder->get()->getResultArray();
    }

    public function getDetail(int $id): ?array
    {
        $row = $this->db->table('penyaluran_antrian pa')
            ->select('pa.*, jd.nama AS nama_dana, pm.nama AS nama_penerima_master, j.nomor_jurnal')
            ->join('jenis_dana jd',       'jd.id = pa.jenis_dana_id', 'left')
            ->join('penerima_manfaat pm', 'pm.id = pa.penerima_id',   'left')
            ->join('jurnal j',            'j.id  = pa.jurnal_id',     'left')
            ->where('pa.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    public function countByStatus(): array
    {
        $rows = $this->db->table('penyaluran_antrian')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()->getResultArray();

        $result = ['pending' => 0, 'verified' => 0, 'rejected' => 0];
        foreach ($rows as $r) {
            $result[$r['status']] = (int) $r['total'];
        }
        return $result;
    }
}
