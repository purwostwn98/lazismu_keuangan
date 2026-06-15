<?php

namespace App\Controllers;

use App\Models\JurnalModel;
use App\Models\JenisDanaModel;
use App\Models\PeriodeModel;
use App\Models\AkunModel;
use App\Models\RekeningBankModel;

class Jurnal extends BaseController
{
    private JurnalModel       $jurnalModel;
    private JenisDanaModel    $jenisDanaModel;
    private PeriodeModel      $periodeModel;
    private AkunModel         $akunModel;
    private RekeningBankModel $rekeningModel;

    public function __construct()
    {
        $this->jurnalModel    = new JurnalModel();
        $this->jenisDanaModel = new JenisDanaModel();
        $this->periodeModel   = new PeriodeModel();
        $this->akunModel      = new AkunModel();
        $this->rekeningModel  = new RekeningBankModel();
    }

    // ── Buku Jurnal ──────────────────────────────────────────
    public function index(): string
    {
        $periodeId = (int)($this->request->getGet('periode') ?? 0);
        $jenisTrx  = $this->request->getGet('jenis') ?? '';
        $jenisDana = (int)($this->request->getGet('dana') ?? 0);
        $q         = $this->request->getGet('q') ?? '';

        $db = \Config\Database::connect();

        $where  = 'WHERE 1=1';
        $params = [];
        if ($periodeId > 0)  { $where .= ' AND j.periode_id = ?';       $params[] = $periodeId; }
        if ($jenisTrx !== '') { $where .= ' AND j.jenis_transaksi = ?';  $params[] = $jenisTrx; }
        if ($jenisDana > 0)  { $where .= ' AND j.jenis_dana_id = ?';    $params[] = $jenisDana; }
        if ($q !== '') {
            $where .= ' AND (j.nomor_jurnal LIKE ? OR j.uraian LIKE ?)';
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }

        $rows = $db->query("
            SELECT j.id AS jurnal_id, j.nomor_jurnal, j.tanggal, j.jenis_transaksi,
                   j.uraian, j.keterangan, j.total_debet, j.total_kredit,
                   dana.nama AS nama_dana, dana.kode AS kode_dana,
                   p.nama AS nama_periode, p.is_tutup,
                   det.id AS det_id, a.nomor_akun, a.nama_akun,
                   det.debet, det.kredit, det.uraian AS uraian_det,
                   rb.nama AS nama_rekening
            FROM jurnal j
            JOIN jenis_dana dana ON dana.id = j.jenis_dana_id
            JOIN periode p       ON p.id    = j.periode_id
            LEFT JOIN jurnal_detail det ON det.jurnal_id = j.id
            LEFT JOIN akun a            ON a.id  = det.akun_id
            LEFT JOIN rekening_bank rb  ON rb.id = det.rekening_bank_id
            {$where}
            ORDER BY j.tanggal DESC, j.id DESC, det.id ASC
        ", $params)->getResultArray();

        // Group flat rows by jurnal_id
        $jurnalMap = [];
        foreach ($rows as $r) {
            $jid = $r['jurnal_id'];
            if (!isset($jurnalMap[$jid])) {
                $jurnalMap[$jid] = [
                    'id'              => $jid,
                    'nomor_jurnal'    => $r['nomor_jurnal'],
                    'tanggal'         => $r['tanggal'],
                    'jenis_transaksi' => $r['jenis_transaksi'],
                    'uraian'          => $r['uraian'],
                    'keterangan'      => $r['keterangan'],
                    'total_debet'     => (float)$r['total_debet'],
                    'total_kredit'    => (float)$r['total_kredit'],
                    'nama_dana'       => $r['nama_dana'],
                    'kode_dana'       => $r['kode_dana'],
                    'nama_periode'    => $r['nama_periode'],
                    'is_tutup'        => (bool)$r['is_tutup'],
                    'details'         => [],
                ];
            }
            if ($r['det_id']) {
                $jurnalMap[$jid]['details'][] = [
                    'nomor_akun'    => $r['nomor_akun'],
                    'nama_akun'     => $r['nama_akun'],
                    'debet'         => (float)$r['debet'],
                    'kredit'        => (float)$r['kredit'],
                    'uraian_det'    => $r['uraian_det'],
                    'nama_rekening' => $r['nama_rekening'],
                ];
            }
        }
        $jurnalList  = array_values($jurnalMap);
        $totalDebet  = array_sum(array_column($jurnalList, 'total_debet'));
        $totalKredit = array_sum(array_column($jurnalList, 'total_kredit'));

        return view('jurnal/index', [
            'pageTitle'     => 'Buku Jurnal',
            'jurnalList'    => $jurnalList,
            'periodeList'   => $this->periodeModel->orderBy('tahun DESC, bulan DESC')->findAll(),
            'jenisDanaList' => $this->jenisDanaModel->getAll(),
            'totalDebet'    => $totalDebet,
            'totalKredit'   => $totalKredit,
            'filter'        => ['periode' => $periodeId, 'jenis' => $jenisTrx, 'dana' => $jenisDana, 'q' => $q],
        ]);
    }

    // ── Form Input Jurnal ────────────────────────────────────
    public function input(): string
    {
        $periodeAktif = $this->periodeModel->findByTanggal(date('Y-m-d'));

        return view('jurnal/input', [
            'pageTitle'     => 'Input Jurnal',
            'periodeList'   => $this->periodeModel->getAktif(),
            'periodeAktif'  => $periodeAktif,
            'jenisDanaList' => $this->jenisDanaModel->getAll(),
            'akunList'      => $this->akunModel->where('is_header', 0)->orderBy('nomor_akun', 'ASC')->findAll(),
            'rekeningList'  => $this->rekeningModel->getAktif(),
        ]);
    }

    // ── Simpan Jurnal ─────────────────────────────────────────
    public function store()
    {
        $rules = [
            'tanggal'         => 'required|valid_date',
            'periode_id'      => 'required|is_natural_no_zero',
            'jenis_dana_id'   => 'required|is_natural_no_zero',
            'jenis_transaksi' => 'required|in_list[biaya,jurnal_umum,koreksi]',
            'uraian'          => 'required|min_length[3]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodeId = (int)$this->request->getPost('periode_id');
        $periode   = $this->periodeModel->find($periodeId);
        if (! $periode || $periode['is_tutup']) {
            return redirect()->back()->withInput()
                ->with('error', 'Periode sudah ditutup atau tidak ditemukan.');
        }

        $akunIds    = (array)($this->request->getPost('akun_id')     ?? []);
        $debets     = (array)($this->request->getPost('debet')       ?? []);
        $kredits    = (array)($this->request->getPost('kredit')      ?? []);
        $uraianDets = (array)($this->request->getPost('uraian_det')  ?? []);
        $rekIds     = (array)($this->request->getPost('rekening_id') ?? []);

        $details     = [];
        $totalDebet  = 0.0;
        $totalKredit = 0.0;
        $now         = date('Y-m-d H:i:s');

        foreach ($akunIds as $i => $akunId) {
            $d = (float)str_replace(['.', ','], ['', '.'], $debets[$i]  ?? '0');
            $k = (float)str_replace(['.', ','], ['', '.'], $kredits[$i] ?? '0');
            if ((int)$akunId === 0 && $d == 0 && $k == 0) continue;
            if ((int)$akunId === 0) {
                return redirect()->back()->withInput()
                    ->with('error', 'Baris ' . ($i + 1) . ': Akun harus dipilih.');
            }
            $totalDebet  += $d;
            $totalKredit += $k;
            $details[] = [
                'akun_id'          => (int)$akunId,
                'rekening_bank_id' => ($rekIds[$i] ?? 0) ? (int)$rekIds[$i] : null,
                'uraian'           => ($uraianDets[$i] ?? '') ?: null,
                'debet'            => $d,
                'kredit'           => $k,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (count($details) < 2) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimum 2 baris detail jurnal diperlukan.');
        }
        if (abs($totalDebet - $totalKredit) > 0.01) {
            return redirect()->back()->withInput()->with('error', sprintf(
                'Jurnal tidak balance. Total Debet %s ≠ Total Kredit %s.',
                number_format($totalDebet, 0, ',', '.'),
                number_format($totalKredit, 0, ',', '.')
            ));
        }

        $jenisTrx    = $this->request->getPost('jenis_transaksi');
        $nomorJurnal = $this->jurnalModel->generateNomor($jenisTrx);

        $db = \Config\Database::connect();
        $db->transStart();

        $this->jurnalModel->insert([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal'         => $this->request->getPost('tanggal'),
            'periode_id'      => $periodeId,
            'jenis_dana_id'   => (int)$this->request->getPost('jenis_dana_id'),
            'jenis_transaksi' => $jenisTrx,
            'uraian'          => $this->request->getPost('uraian'),
            'keterangan'      => trim($this->request->getPost('keterangan') ?? '') ?: null,
            'total_debet'     => $totalDebet,
            'total_kredit'    => $totalKredit,
            'created_by'      => null,
        ]);
        $jurnalId = $this->jurnalModel->getInsertID();

        foreach ($details as &$det) {
            $det['jurnal_id'] = $jurnalId;
        }
        $db->table('jurnal_detail')->insertBatch($details);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan jurnal.');
        }

        return redirect()->to('jurnal')->with('success', "Jurnal {$nomorJurnal} berhasil disimpan.");
    }

    // ── Hapus Jurnal ─────────────────────────────────────────
    public function delete(int $id)
    {
        $jurnal = $this->jurnalModel->find($id);
        if (! $jurnal) {
            return redirect()->to('jurnal')->with('error', 'Jurnal tidak ditemukan.');
        }

        if (! in_array($jurnal['jenis_transaksi'], ['biaya', 'jurnal_umum', 'koreksi'])) {
            return redirect()->to('jurnal')
                ->with('error', 'Jurnal ini dikelola oleh modul lain dan tidak dapat dihapus dari sini.');
        }

        $periode = $this->periodeModel->find($jurnal['periode_id']);
        if ($periode && $periode['is_tutup']) {
            return redirect()->to('jurnal')
                ->with('error', 'Periode sudah ditutup. Jurnal tidak dapat dihapus.');
        }

        \Config\Database::connect()->table('jurnal')->where('id', $id)->delete();

        return redirect()->to('jurnal')
            ->with('success', "Jurnal {$jurnal['nomor_jurnal']} berhasil dihapus.");
    }
}
