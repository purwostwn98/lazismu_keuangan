<?php

namespace App\Models;

use CodeIgniter\Model;

class PersediaanModel extends Model
{
    protected $table      = 'persediaan';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'kode_barang', 'nama_barang', 'satuan', 'akun_id',
        'jenis_dana_id', 'stok_masuk', 'stok_keluar',
        'nilai_per_satuan', 'keterangan',
    ];
    protected $useTimestamps = true;

    // Mapping sub_jenis → jenis_transaksi jurnal
    public static array $jurnalJenis = [
        'penerimaan_natura' => 'penerimaan',
        'pembelian'         => 'jurnal_umum',
        'penyaluran'        => 'penyaluran',
        'pemakaian'         => 'biaya',
    ];

    public static array $subJenisLabel = [
        'penerimaan_natura' => 'Penerimaan Natura / Barang',
        'pembelian'         => 'Pembelian / Pengadaan',
        'penyaluran'        => 'Penyaluran ke Mustahiq',
        'pemakaian'         => 'Pemakaian Operasional',
    ];

    public function generateKode(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();
        $seq  = $last ? ((int) preg_replace('/\D/', '', $last['kode_barang'])) + 1 : 1;
        return sprintf('BRG-%04d', $seq);
    }

    public function generateNomorMutasi(string $jenis): string
    {
        $prefix = $jenis === 'masuk' ? 'PSM' : 'PSK';
        $ym     = date('Ym');
        $last   = $this->db->table('persediaan_mutasi')
            ->like('nomor_mutasi', "{$prefix}/{$ym}/", 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last['nomor_mutasi']);
            $seq   = ((int) end($parts)) + 1;
        }
        return sprintf('%s/%s/%04d', $prefix, $ym, $seq);
    }

    public function getDaftar(array $filter = []): array
    {
        $builder = $this->db->table('persediaan p')
            ->select('p.*, a.nomor_akun, a.nama_akun, jd.nama AS nama_dana, jd.kode AS kode_dana,
                      (p.stok_masuk - p.stok_keluar) AS stok_akhir,
                      ((p.stok_masuk - p.stok_keluar) * p.nilai_per_satuan) AS nilai_stok')
            ->join('akun a',     'a.id = p.akun_id', 'left')
            ->join('jenis_dana jd', 'jd.id = p.jenis_dana_id', 'left')
            ->orderBy('p.nama_barang', 'ASC');

        if (!empty($filter['q'])) {
            $builder->groupStart()
                ->like('p.nama_barang', $filter['q'])
                ->orLike('p.kode_barang', $filter['q'])
                ->groupEnd();
        }
        if (!empty($filter['jenis_dana_id'])) {
            $builder->where('p.jenis_dana_id', $filter['jenis_dana_id']);
        }
        return $builder->get()->getResultArray();
    }

    public function getDetail(int $id): array
    {
        $item = $this->db->table('persediaan p')
            ->select('p.*, a.nomor_akun, a.nama_akun, jd.nama AS nama_dana, jd.kode AS kode_dana,
                      (p.stok_masuk - p.stok_keluar) AS stok_akhir,
                      ((p.stok_masuk - p.stok_keluar) * p.nilai_per_satuan) AS nilai_stok')
            ->join('akun a',     'a.id = p.akun_id', 'left')
            ->join('jenis_dana jd', 'jd.id = p.jenis_dana_id', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (!$item) return [];

        $mutasi = $this->db->table('persediaan_mutasi pm')
            ->select('pm.*, a.nama_akun AS nama_akun_lawan, j.nomor_jurnal, pr.nama AS nama_periode')
            ->join('akun a',    'a.id = pm.akun_lawan_id', 'left')
            ->join('jurnal j',  'j.id = pm.jurnal_id', 'left')
            ->join('periode pr','pr.id = pm.periode_id', 'left')
            ->where('pm.persediaan_id', $id)
            ->orderBy('pm.tanggal', 'ASC')
            ->get()->getResultArray();

        return ['item' => $item, 'mutasi' => $mutasi];
    }
}
