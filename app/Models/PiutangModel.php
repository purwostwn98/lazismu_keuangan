<?php

namespace App\Models;

use CodeIgniter\Model;

class PiutangModel extends Model
{
    protected $table      = 'piutang';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nomor_piutang',
        'penerima_id',
        'jenis',
        'jumlah_pokok',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'jumlah_terbayar',
        'sisa_piutang',
        'status',
        'jenis_dana_id',
        'jurnal_id',
        'keterangan',
    ];
    protected $useTimestamps = true;

    public static array $jenisLabels = [
        'qardul_hasan_amil'     => 'Qardul Hasan (Amil)',
        'qardul_hasan_non_amil' => 'Qardul Hasan (Non-Amil / Mustahiq)',
        'penyaluran'            => 'Piutang Penyaluran',
        'talangan_amil'         => 'Talangan Dana Amil',
        'talangan_zakat'        => 'Talangan Dana Zakat',
        'talangan_infaq'        => 'Talangan Dana Infaq',
    ];

    // nomor_akun piutang per jenis (dicari saat runtime)
    public static array $jenisAkunNomor = [
        'qardul_hasan_amil'     => '11201001',
        'qardul_hasan_non_amil' => '11201002',
        'penyaluran'            => '11202000',
        'talangan_amil'         => '11203000',
        'talangan_zakat'        => '11203004',
        'talangan_infaq'        => '11203005',
    ];

    // Jenis dana default per jenis piutang (kode jenis_dana)
    public static array $jenisDanaDefault = [
        'talangan_zakat'        => 'ZAKAT',
        'talangan_infaq'        => 'INFAK_TT',
        'qardul_hasan_amil'     => 'AMIL',
        'talangan_amil'         => 'AMIL',
    ];

    public function generateNomor(): string
    {
        $ym   = date('Ym');
        $last = $this->db->table($this->table)
            ->like('nomor_piutang', "PIU/{$ym}/", 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last['nomor_piutang']);
            $seq   = ((int) end($parts)) + 1;
        }
        return sprintf('PIU/%s/%04d', $ym, $seq);
    }

    public function getDaftar(array $filter = []): array
    {
        $builder = $this->db->table('piutang p')
            ->select('p.*, pm.nama AS nama_penerima, pm.asnaf, jd.nama AS nama_dana, jd.kode AS kode_dana')
            ->join('penerima_manfaat pm', 'pm.id = p.penerima_id', 'left')
            ->join('jenis_dana jd',       'jd.id = p.jenis_dana_id', 'left')
            ->orderBy('p.tanggal_pinjam', 'DESC');

        if (!empty($filter['status'])) {
            $builder->where('p.status', $filter['status']);
        }
        if (!empty($filter['jenis'])) {
            $builder->where('p.jenis', $filter['jenis']);
        }
        if (!empty($filter['q'])) {
            $builder->groupStart()
                ->like('pm.nama', $filter['q'])
                ->orLike('p.nomor_piutang', $filter['q'])
                ->groupEnd();
        }
        return $builder->get()->getResultArray();
    }

    public function getDetail(int $id): array
    {
        $piutang = $this->db->table('piutang p')
            ->select('p.*, pm.nama AS nama_penerima, pm.asnaf, pm.no_hp, pm.alamat, jd.nama AS nama_dana, jd.kode AS kode_dana')
            ->join('penerima_manfaat pm', 'pm.id = p.penerima_id', 'left')
            ->join('jenis_dana jd',       'jd.id = p.jenis_dana_id', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (!$piutang) return [];

        $cicilan = $this->db->table('piutang_cicilan pc')
            ->select('pc.*, j.nomor_jurnal')
            ->join('jurnal j', 'j.id = pc.jurnal_id', 'left')
            ->where('pc.piutang_id', $id)
            ->orderBy('pc.tanggal', 'ASC')
            ->get()->getResultArray();

        return ['piutang' => $piutang, 'cicilan' => $cicilan];
    }
}
