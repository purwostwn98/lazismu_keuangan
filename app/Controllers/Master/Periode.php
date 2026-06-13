<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\PeriodeModel;

class Periode extends BaseController
{
    private PeriodeModel $model;

    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
        5 => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct()
    {
        $this->model = new PeriodeModel();
    }

    public function index(): string
    {
        $periode = $this->model->orderBy('tahun', 'DESC')->orderBy('bulan', 'DESC')->findAll();
        $total   = count($periode);
        $aktif   = count(array_filter($periode, fn($p) => $p['is_tutup'] == 0));
        $tutup   = $total - $aktif;

        $tahunList = array_unique(array_column($periode, 'tahun'));
        rsort($tahunList);

        return view('master/periode/index', [
            'pageTitle'  => 'Periode Akuntansi',
            'periode'    => $periode,
            'total'      => $total,
            'aktif'      => $aktif,
            'tutup'      => $tutup,
            'tahunList'  => $tahunList,
            'bulanNames' => self::BULAN,
        ]);
    }

    public function store()
    {
        $rules = [
            'bulan' => 'required|in_list[1,2,3,4,5,6,7,8,9,10,11,12]',
            'tahun' => 'required|is_natural_no_zero|min_length[4]|max_length[4]',
            'nama'  => 'required|max_length[30]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bulan = (int) $this->request->getPost('bulan');
        $tahun = (int) $this->request->getPost('tahun');

        if ($this->model->where('bulan', $bulan)->where('tahun', $tahun)->first()) {
            return redirect()->back()->withInput()
                ->with('errors', ['bulan' => 'Periode ' . self::BULAN[$bulan] . " {$tahun} sudah ada."]);
        }

        $this->model->insert([
            'bulan'    => $bulan,
            'tahun'    => $tahun,
            'nama'     => trim($this->request->getPost('nama')),
            'is_tutup' => 0,
        ]);

        return redirect()->to('master/periode')->with('success', 'Periode berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $periode = $this->model->find($id);
        if (! $periode) {
            return redirect()->to('master/periode')->with('error', 'Data tidak ditemukan.');
        }

        $rules = ['nama' => 'required|max_length[30]'];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, ['nama' => trim($this->request->getPost('nama'))]);

        return redirect()->to('master/periode')->with('success', 'Nama periode berhasil diperbarui.');
    }

    public function tutup(int $id)
    {
        $periode = $this->model->find($id);
        if (! $periode) {
            return redirect()->to('master/periode')->with('error', 'Data tidak ditemukan.');
        }

        $newStatus  = $periode['is_tutup'] ? 0 : 1;
        $keterangan = $newStatus ? 'ditutup' : 'dibuka kembali';

        $db    = \Config\Database::connect();
        $tahun = (int) $periode['tahun'];
        $bulan = (int) $periode['bulan'];

        $db->transStart();

        if ($newStatus === 1) {
            // ── Hitung saldo_awal: kumulatif semua jurnal sebelum periode ini ──
            // Filter periode lewat subquery di ON agar LEFT JOIN tidak ikutkan
            // jurnal yang periodenya tidak memenuhi syarat (bug jika filter di JOIN periode)
            $saldoAwalRows = $db->query("
                SELECT jd.id AS jenis_dana_id,
                    COALESCE(
                        SUM(CASE WHEN j.jenis_transaksi = 'penerimaan' THEN j.total_debet ELSE 0 END)
                      - SUM(CASE WHEN j.jenis_transaksi IN ('penyaluran','biaya') THEN j.total_debet ELSE 0 END)
                    , 0) AS saldo_awal
                FROM jenis_dana jd
                LEFT JOIN jurnal j ON j.jenis_dana_id = jd.id
                    AND j.jenis_transaksi IN ('penerimaan','penyaluran','biaya')
                    AND j.periode_id IN (
                        SELECT p.id FROM periode p
                        WHERE p.tahun < ? OR (p.tahun = ? AND p.bulan < ?)
                    )
                GROUP BY jd.id
            ", [$tahun, $tahun, $bulan])->getResultArray();

            // ── Hitung transaksi periode ini ──
            $thisRows = $db->query("
                SELECT jd.id AS jenis_dana_id,
                    COALESCE(SUM(CASE WHEN j.jenis_transaksi = 'penerimaan' THEN j.total_debet ELSE 0 END), 0) AS total_penerimaan,
                    COALESCE(SUM(CASE WHEN j.jenis_transaksi = 'penyaluran' THEN j.total_debet ELSE 0 END), 0) AS total_penyaluran,
                    COALESCE(SUM(CASE WHEN j.jenis_transaksi = 'biaya'      THEN j.total_debet ELSE 0 END), 0) AS total_biaya
                FROM jenis_dana jd
                LEFT JOIN jurnal j ON j.jenis_dana_id = jd.id
                    AND j.periode_id = ?
                    AND j.jenis_transaksi IN ('penerimaan','penyaluran','biaya')
                GROUP BY jd.id
            ", [$id])->getResultArray();

            // Bangun map jenis_dana_id → nilai
            $saldoAwalMap = [];
            foreach ($saldoAwalRows as $r) {
                $saldoAwalMap[(int) $r['jenis_dana_id']] = (float) $r['saldo_awal'];
            }
            $thisMap = [];
            foreach ($thisRows as $r) {
                $thisMap[(int) $r['jenis_dana_id']] = $r;
            }

            $jenisDanaList = $db->table('jenis_dana')->get()->getResultArray();
            $now           = date('Y-m-d H:i:s');
            $inserts       = [];

            foreach ($jenisDanaList as $jd) {
                $jdId      = (int) $jd['id'];
                $sa        = $saldoAwalMap[$jdId] ?? 0.0;
                $pen       = (float) ($thisMap[$jdId]['total_penerimaan'] ?? 0);
                $psl       = (float) ($thisMap[$jdId]['total_penyaluran'] ?? 0);
                $bya       = (float) ($thisMap[$jdId]['total_biaya']      ?? 0);
                $inserts[] = [
                    'periode_id'       => $id,
                    'jenis_dana_id'    => $jdId,
                    'saldo_awal'       => $sa,
                    'total_penerimaan' => $pen,
                    'total_penyaluran' => $psl,
                    'total_biaya'      => $bya,
                    'saldo_akhir'      => $sa + $pen - $psl - $bya,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }

            // Hapus snapshot lama jika re-close, lalu insert baru
            $db->table('saldo_dana')->where('periode_id', $id)->delete();
            $db->table('saldo_dana')->insertBatch($inserts);

        } else {
            // ── Membuka kembali: hapus snapshot saldo_dana ──
            $db->table('saldo_dana')->where('periode_id', $id)->delete();
        }

        $this->model->update($id, ['is_tutup' => $newStatus]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('master/periode')
                ->with('error', 'Gagal memproses penutupan periode. Silakan coba kembali.');
        }

        $msg = $newStatus
            ? "Periode {$periode['nama']} berhasil ditutup dan saldo dana telah dikunci."
            : "Periode {$periode['nama']} berhasil dibuka kembali. Snapshot saldo dihapus.";

        return redirect()->to('master/periode')->with('success', $msg);
    }

    public function delete(int $id)
    {
        $periode = $this->model->find($id);
        if (! $periode) {
            return redirect()->to('master/periode')->with('error', 'Data tidak ditemukan.');
        }

        $db      = \Config\Database::connect();
        $totalRef = array_sum([
            $db->table('jurnal')->where('periode_id', $id)->countAllResults(),
            $db->table('penghimpunan')->where('periode_id', $id)->countAllResults(),
            $db->table('saldo_dana')->where('periode_id', $id)->countAllResults(),
            $db->table('penyusutan_aset')->where('periode_id', $id)->countAllResults(),
        ]);

        if ($totalRef > 0) {
            return redirect()->to('master/periode')
                ->with('error', "Periode tidak dapat dihapus karena sudah memiliki {$totalRef} data terkait.");
        }

        $this->model->delete($id);

        return redirect()->to('master/periode')
            ->with('success', "Periode {$periode['nama']} berhasil dihapus.");
    }
}