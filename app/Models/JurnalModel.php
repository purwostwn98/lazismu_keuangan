<?php

namespace App\Models;

use CodeIgniter\Model;

class JurnalModel extends Model
{
    protected $table      = 'jurnal';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nomor_jurnal', 'tanggal', 'periode_id', 'jenis_dana_id',
        'jenis_transaksi', 'uraian', 'keterangan',
        'donatur_id', 'penerima_id', 'program_id',
        'total_debet', 'total_kredit', 'created_by',
        'ref_jurnal_id',
    ];
    protected $useTimestamps = true;

    public function generateNomor(string $jenis = 'penyaluran'): string
    {
        $prefix = match ($jenis) {
            'penerimaan'  => 'PNR',
            'penyaluran'  => 'PSL',
            'biaya'       => 'BYA',
            'koreksi'     => 'KRK',
            'transfer'    => 'TRF',
            'piutang'     => 'PIU',
            default       => 'JRN',
        };

        $ym  = date('Ym');
        $last = $this->db
            ->table($this->table)
            ->select('nomor_jurnal')
            ->like('nomor_jurnal', "{$prefix}/{$ym}/", 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last['nomor_jurnal']);
            $seq   = ((int) end($parts)) + 1;
        }

        return sprintf('%s/%s/%04d', $prefix, $ym, $seq);
    }

    public function getPenyaluran(array $filter = []): array
    {
        $builder = $this->db->table('jurnal j')
            ->select('j.*, jd.nama AS nama_dana, p.nama AS nama_periode, pm.nama AS nama_penerima')
            ->join('jenis_dana jd', 'jd.id = j.jenis_dana_id', 'left')
            ->join('periode p', 'p.id = j.periode_id', 'left')
            ->join('penerima_manfaat pm', 'pm.id = j.penerima_id', 'left')
            ->where('j.jenis_transaksi', 'penyaluran')
            ->orderBy('j.tanggal DESC, j.id DESC');

        if (!empty($filter['jenis_dana_id'])) {
            $builder->where('j.jenis_dana_id', $filter['jenis_dana_id']);
        }
        if (!empty($filter['periode_id'])) {
            $builder->where('j.periode_id', $filter['periode_id']);
        } elseif (!empty($filter['tahun'])) {
            $builder->where('p.tahun', (int) $filter['tahun']);
        }
        if (!empty($filter['q'])) {
            $builder->groupStart()
                    ->like('j.uraian', $filter['q'])
                    ->orLike('j.nomor_jurnal', $filter['q'])
                    ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function storePenyaluran(array $header, array $details): int
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Insert header
        $this->insert($header);
        $jurnalId = $this->getInsertID();

        // Insert detail rows
        $now = date('Y-m-d H:i:s');
        foreach ($details as &$d) {
            $d['jurnal_id']  = $jurnalId;
            $d['created_at'] = $now;
            $d['updated_at'] = $now;
        }
        $db->table('jurnal_detail')->insertBatch($details);

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan jurnal penyaluran.');
        }

        return $jurnalId;
    }

    public function getWithDetail(int $id): array
    {
        $header = $this->db->table('jurnal j')
            ->select('j.*, jd.nama AS nama_dana, p.nama AS nama_periode, pm.nama AS nama_penerima')
            ->join('jenis_dana jd', 'jd.id = j.jenis_dana_id', 'left')
            ->join('periode p', 'p.id = j.periode_id', 'left')
            ->join('penerima_manfaat pm', 'pm.id = j.penerima_id', 'left')
            ->where('j.id', $id)
            ->get()->getRowArray();

        if (!$header) return [];

        $details = $this->db->table('jurnal_detail jd')
            ->select('jd.*, a.nomor_akun, a.nama_akun, a.tipe')
            ->join('akun a', 'a.id = jd.akun_id', 'left')
            ->where('jd.jurnal_id', $id)
            ->get()->getResultArray();

        return ['header' => $header, 'details' => $details];
    }

    public function getBiayaWithDetail(int $id): array
    {
        $header = $this->db->table('jurnal j')
            ->select('j.*, jd.nama AS nama_dana, jd.kode AS kode_dana, p.nama AS nama_periode, p.is_tutup')
            ->join('jenis_dana jd', 'jd.id = j.jenis_dana_id', 'left')
            ->join('periode p',     'p.id  = j.periode_id',    'left')
            ->where('j.id', $id)
            ->where('j.jenis_transaksi', 'biaya')
            ->get()->getRowArray();

        if (!$header) return [];

        $details = $this->db->table('jurnal_detail d')
            ->select('d.*, a.nomor_akun, a.nama_akun, a.tipe, rb.nama AS nama_rekening, rb.bank AS nama_bank')
            ->join('akun a',          'a.id  = d.akun_id',          'left')
            ->join('rekening_bank rb', 'rb.id = d.rekening_bank_id', 'left')
            ->where('d.jurnal_id', $id)
            ->orderBy('d.debet DESC, d.id ASC')
            ->get()->getResultArray();

        return ['header' => $header, 'details' => $details];
    }
}
