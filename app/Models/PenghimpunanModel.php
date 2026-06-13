<?php

namespace App\Models;

use CodeIgniter\Model;

class PenghimpunanModel extends Model
{
    protected $table      = 'penghimpunan';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'periode_id', 'donatur_id', 'kategori_id', 'jenis_zis', 'jumlah', 'jurnal_id',
    ];
    protected $useTimestamps = true;

    public const JENIS_ZIS_LABELS = [
        'zakat_maal_ternak'        => 'Zakat Maal — Ternak',
        'zakat_maal_emas'          => 'Zakat Maal — Emas',
        'zakat_maal_perak'         => 'Zakat Maal — Perak',
        'zakat_maal_perniagaan'    => 'Zakat Maal — Perniagaan',
        'zakat_maal_pertanian'     => 'Zakat Maal — Pertanian',
        'zakat_maal_hadiah'        => 'Zakat Maal — Hadiah',
        'zakat_maal_profesi'       => 'Zakat Maal — Profesi',
        'zakat_maal_simpanan'      => 'Zakat Maal — Simpanan',
        'zakat_fitrah'             => 'Zakat Fitrah',
        'zakat_bagi_hasil'         => 'Bagi Hasil — Zakat',
        'infak_terikat'            => 'Infak Terikat',
        'infak_tidak_terikat_umum' => 'Infak Tidak Terikat (Umum)',
        'infak_kotak'              => 'Infak Kotak',
        'infak_sabtu_seribu'       => 'Infak Sabtu Seribu',
        'infak_bagi_hasil'         => 'Bagi Hasil — Infak',
        'dana_non_halal'           => 'Dana Non Halal',
    ];

    public const JENIS_ZIS_GROUPS = [
        'Zakat Maal' => [
            'zakat_maal_ternak', 'zakat_maal_emas', 'zakat_maal_perak',
            'zakat_maal_perniagaan', 'zakat_maal_pertanian', 'zakat_maal_hadiah',
            'zakat_maal_profesi', 'zakat_maal_simpanan',
        ],
        'Zakat Fitrah'   => ['zakat_fitrah'],
        'Bagi Hasil'     => ['zakat_bagi_hasil', 'infak_bagi_hasil'],
        'Infak'          => ['infak_terikat', 'infak_tidak_terikat_umum', 'infak_kotak', 'infak_sabtu_seribu'],
        'Dana Non Halal' => ['dana_non_halal'],
    ];

    public function getDaftar(array $filter = []): array
    {
        $builder = $this->db->table('penghimpunan ph')
            ->select('ph.id, ph.jenis_zis, ph.jumlah, ph.jurnal_id,
                      j.nomor_jurnal, j.tanggal, j.uraian, j.jenis_dana_id,
                      per.nama AS nama_periode, per.is_tutup,
                      d.nama AS nama_donatur, d.kode AS kode_donatur,
                      kd.nama AS nama_kategori,
                      rb.nama AS nama_rekening')
            ->join('jurnal j',            'j.id = ph.jurnal_id', 'left')
            ->join('periode per',         'per.id = ph.periode_id', 'left')
            ->join('donatur d',           'd.id = ph.donatur_id', 'left')
            ->join('kategori_donatur kd', 'kd.id = ph.kategori_id', 'left')
            ->join('jurnal_detail jdet',  'jdet.jurnal_id = j.id AND jdet.debet > 0', 'left')
            ->join('rekening_bank rb',    'rb.id = jdet.rekening_bank_id', 'left')
            ->orderBy('j.tanggal', 'DESC')
            ->orderBy('ph.id', 'DESC');

        if (!empty($filter['periode_id'])) {
            $builder->where('ph.periode_id', (int) $filter['periode_id']);
        } elseif (!empty($filter['tahun'])) {
            $builder->where('per.tahun', (int) $filter['tahun']);
        }
        if (!empty($filter['jenis_group']) && is_array($filter['jenis_group'])) {
            $builder->whereIn('ph.jenis_zis', $filter['jenis_group']);
        }
        if (!empty($filter['q'])) {
            $builder->groupStart()
                ->like('d.nama', $filter['q'])
                ->orLike('j.nomor_jurnal', $filter['q'])
                ->orLike('j.uraian', $filter['q'])
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Monthly totals per jenis_zis for a given year.
     * Returns: ['jenis_zis' => [1 => total, ..., 12 => total]]
     */
    public function getLaporanBulanan(int $tahun): array
    {
        $rows = $this->db->table('penghimpunan ph')
            ->select('per.bulan, ph.jenis_zis, SUM(ph.jumlah) AS total')
            ->join('periode per', 'per.id = ph.periode_id')
            ->where('per.tahun', $tahun)
            ->groupBy('per.bulan, ph.jenis_zis')
            ->orderBy('per.bulan', 'ASC')
            ->get()->getResultArray();

        $result = [];
        foreach ($rows as $r) {
            $result[$r['jenis_zis']][(int)$r['bulan']] = (float)$r['total'];
        }
        return $result;
    }

    public function getSummaryByPeriode(int $periodeId = 0): array
    {
        $builder = $this->db->table('penghimpunan')
            ->select('jenis_zis, SUM(jumlah) AS total')
            ->groupBy('jenis_zis');

        if ($periodeId > 0) {
            $builder->where('periode_id', $periodeId);
        }

        $rows   = $builder->get()->getResultArray();
        $result = [];
        foreach ($rows as $r) {
            $result[$r['jenis_zis']] = (float) $r['total'];
        }
        return $result;
    }
}