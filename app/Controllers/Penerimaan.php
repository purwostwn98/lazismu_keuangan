<?php

namespace App\Controllers;

use App\Models\PenghimpunanModel;
use App\Models\JurnalModel;
use App\Models\JenisDanaModel;
use App\Models\PeriodeModel;
use App\Models\AkunModel;
use App\Models\RekeningBankModel;
use App\Models\DonaturModel;
use App\Models\KategoriDonaturModel;

class Penerimaan extends BaseController
{
    private PenghimpunanModel  $model;
    private JurnalModel        $jurnalModel;
    private JenisDanaModel     $jenisDanaModel;
    private PeriodeModel       $periodeModel;
    private AkunModel          $akunModel;
    private RekeningBankModel  $rekeningModel;
    private DonaturModel       $donaturModel;
    private KategoriDonaturModel $kategoriModel;

    public function __construct()
    {
        $this->model          = new PenghimpunanModel();
        $this->jurnalModel    = new JurnalModel();
        $this->jenisDanaModel = new JenisDanaModel();
        $this->periodeModel   = new PeriodeModel();
        $this->akunModel      = new AkunModel();
        $this->rekeningModel  = new RekeningBankModel();
        $this->donaturModel   = new DonaturModel();
        $this->kategoriModel  = new KategoriDonaturModel();
    }

    // ── Daftar Penerimaan ────────────────────────────────────
    public function index(): string
    {
        $allPeriodeTahun = $this->periodeModel->orderBy('tahun DESC, bulan ASC')->findAll();
        $tahunList       = array_values(array_unique(array_column($allPeriodeTahun, 'tahun')));
        rsort($tahunList);
        $tahunTerbaru    = $tahunList[0] ?? (int) date('Y');

        // Default ke tahun terbaru jika belum ada parameter sama sekali di URL
        $adaParam = $this->request->getGet('tahun') !== null
                 || $this->request->getGet('periode') !== null
                 || $this->request->getGet('group') !== null
                 || $this->request->getGet('q') !== null;

        $tahun      = $adaParam ? (int) ($this->request->getGet('tahun')  ?? 0) : $tahunTerbaru;
        $periodeId  = (int) ($this->request->getGet('periode') ?? 0);
        $jenisGroup = $this->request->getGet('group') ?? '';
        $q          = $this->request->getGet('q') ?? '';

        $filter = ['q' => $q];
        if ($periodeId > 0) {
            $filter['periode_id'] = $periodeId;
        } elseif ($tahun > 0) {
            $filter['tahun'] = $tahun;
        }
        if ($jenisGroup !== '' && isset(PenghimpunanModel::JENIS_ZIS_GROUPS[$jenisGroup])) {
            $filter['jenis_group'] = PenghimpunanModel::JENIS_ZIS_GROUPS[$jenisGroup];
        }

        $daftar = $this->model->getDaftar($filter);

        $totalJumlah = array_sum(array_column($daftar, 'jumlah'));
        $totalZakat  = array_sum(array_map(
            fn($r) => str_starts_with($r['jenis_zis'], 'zakat') ? (float)$r['jumlah'] : 0,
            $daftar
        ));
        $totalInfak  = array_sum(array_map(
            fn($r) => str_starts_with($r['jenis_zis'], 'infak') ? (float)$r['jumlah'] : 0,
            $daftar
        ));

        return view('penerimaan/index', [
            'pageTitle'   => 'Daftar Penerimaan ZIS',
            'daftar'      => $daftar,
            'tahunList'   => $tahunList,
            'periodeList' => $allPeriodeTahun,
            'totalJumlah' => $totalJumlah,
            'totalZakat'  => $totalZakat,
            'totalInfak'  => $totalInfak,
            'labels'      => PenghimpunanModel::JENIS_ZIS_LABELS,
            'groups'      => array_keys(PenghimpunanModel::JENIS_ZIS_GROUPS),
            'filter'      => ['tahun' => $tahun, 'periode' => $periodeId, 'group' => $jenisGroup, 'q' => $q],
        ]);
    }

    // ── Form Input ───────────────────────────────────────────
    public function input(): string
    {
        $periodeAktif = $this->periodeModel->findByTanggal(date('Y-m-d'));

        return view('penerimaan/input', [
            'pageTitle'       => 'Input Penerimaan ZIS',
            'periodeList'     => $this->periodeModel->getAktif(),
            'periodeAktif'    => $periodeAktif,
            'donaturList'     => $this->donaturModel->where('is_aktif', 1)->orderBy('nama', 'ASC')->findAll(),
            'kategoriList'    => $this->kategoriModel->getGrouped(),
            'jenisDanaList'   => $this->jenisDanaModel->getAll(),
            'rekeningList'    => $this->rekeningModel->getAktif(),
            'akunPenerimaan'  => $this->akunModel->getByTipe('penerimaan'),
            'jenisZisLabels'  => PenghimpunanModel::JENIS_ZIS_LABELS,
            'jenisZisGroups'  => PenghimpunanModel::JENIS_ZIS_GROUPS,
        ]);
    }

    // ── Simpan ───────────────────────────────────────────────
    public function store()
    {
        $rules = [
            'tanggal'            => 'required|valid_date',
            'periode_id'         => 'required|is_natural_no_zero',
            'kategori_id'        => 'required|is_natural_no_zero',
            'jenis_zis'          => 'required|in_list[' . implode(',', array_keys(PenghimpunanModel::JENIS_ZIS_LABELS)) . ']',
            'jenis_dana_id'      => 'required|is_natural_no_zero',
            'jumlah'             => 'required|decimal|greater_than[0]',
            'rekening_id'        => 'required|is_natural_no_zero',
            'akun_penerimaan_id' => 'required|is_natural_no_zero',
            'uraian'             => 'required|min_length[3]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $periodeId  = (int) $this->request->getPost('periode_id');
        $rekeningId = (int) $this->request->getPost('rekening_id');

        // Periode must not be closed
        $periode = $this->periodeModel->find($periodeId);
        if (! $periode || $periode['is_tutup']) {
            return redirect()->back()->withInput()
                ->with('error', 'Periode sudah ditutup atau tidak ditemukan. Penerimaan tidak dapat disimpan.');
        }

        // Get rekening's GL account for debet
        $rekening = $this->rekeningModel->find($rekeningId);
        if (! $rekening) {
            return redirect()->back()->withInput()
                ->with('error', 'Rekening bank tidak ditemukan.');
        }

        $jumlah             = (float) $this->request->getPost('jumlah');
        $akunPenerimaanId   = (int)   $this->request->getPost('akun_penerimaan_id');
        $donaturId          = ((int) $this->request->getPost('donatur_id')) ?: null;
        $kategoriId         = (int)   $this->request->getPost('kategori_id');
        $jenisZis           = $this->request->getPost('jenis_zis');
        $jenisDanaId        = (int)   $this->request->getPost('jenis_dana_id');
        $tanggal            = $this->request->getPost('tanggal');
        $uraian             = $this->request->getPost('uraian');
        $keterangan         = trim($this->request->getPost('keterangan') ?? '') ?: null;

        $nomorJurnal = $this->jurnalModel->generateNomor('penerimaan');
        $now         = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Insert jurnal header
        $this->jurnalModel->insert([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal'         => $tanggal,
            'periode_id'      => $periodeId,
            'jenis_dana_id'   => $jenisDanaId,
            'jenis_transaksi' => 'penerimaan',
            'uraian'          => $uraian,
            'keterangan'      => $keterangan,
            'donatur_id'      => $donaturId,
            'total_debet'     => $jumlah,
            'total_kredit'    => $jumlah,
            'created_by'      => null,
        ]);
        $jurnalId = $this->jurnalModel->getInsertID();

        // 2. Insert jurnal detail: Debet kas/rekening, Kredit akun penerimaan
        $db->table('jurnal_detail')->insertBatch([
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => (int) $rekening['akun_id'],
                'rekening_bank_id' => $rekeningId,
                'uraian'           => $uraian,
                'debet'            => $jumlah,
                'kredit'           => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => $akunPenerimaanId,
                'rekening_bank_id' => null,
                'uraian'           => $uraian,
                'debet'            => 0,
                'kredit'           => $jumlah,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // 3. Insert penghimpunan
        $this->model->insert([
            'periode_id'  => $periodeId,
            'donatur_id'  => $donaturId,
            'kategori_id' => $kategoriId,
            'jenis_zis'   => $jenisZis,
            'jumlah'      => $jumlah,
            'jurnal_id'   => $jurnalId,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan. Silakan coba kembali.');
        }

        return redirect()->to('penerimaan')
            ->with('success', "Penerimaan {$nomorJurnal} berhasil disimpan.");
    }

    // ── Laporan Penghimpunan ─────────────────────────────────
    public function laporan(): string
    {
        $tahunList = array_values(array_column(
            $this->periodeModel->select('tahun')->groupBy('tahun')->orderBy('tahun', 'DESC')->findAll(),
            'tahun'
        ));
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        $tahun = (int) ($this->request->getGet('tahun') ?? $tahunList[0]);

        $data = $this->model->getLaporanByDanaKategori($tahun);

        $bulanNames = [
            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
            7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des',
        ];

        $zero12     = array_fill(1, 12, 0.0);
        $rows       = [];
        $grandTotal = $zero12;
        $danaTotal  = [];

        foreach ($data as $danaId => $dana) {
            $danaTot = $zero12;

            $rows[] = ['type' => 'section', 'label' => strtoupper($dana['nama']), 'vals' => $zero12];

            foreach ($dana['parents'] as $parentId => $parent) {
                $parentTot = $zero12;

                $rows[] = ['type' => 'subsection', 'label' => $parent['nama'], 'vals' => $zero12];

                foreach ($parent['children'] as $child) {
                    $rows[] = ['type' => 'data', 'label' => $child['nama'], 'vals' => $child['bulan']];
                    for ($b = 1; $b <= 12; $b++) $parentTot[$b] += $child['bulan'][$b] ?? 0.0;
                }

                $rows[] = ['type' => 'subtotal', 'label' => 'Jumlah ' . $parent['nama'], 'vals' => $parentTot];
                for ($b = 1; $b <= 12; $b++) $danaTot[$b] += $parentTot[$b];
            }

            $rows[] = ['type' => 'dana_total', 'label' => 'TOTAL ' . strtoupper($dana['nama']), 'vals' => $danaTot];
            $rows[] = ['type' => 'spacer',     'label' => '', 'vals' => $zero12];

            $danaTotal[$dana['kode']] = array_sum($danaTot);
            for ($b = 1; $b <= 12; $b++) $grandTotal[$b] += $danaTot[$b];
        }

        $rows[] = ['type' => 'total', 'label' => 'GRAND TOTAL PENGHIMPUNAN', 'vals' => $grandTotal];

        return view('penerimaan/laporan', [
            'pageTitle'   => 'Laporan Penghimpunan ZIS',
            'breadcrumb'  => ['Penerimaan' => base_url('penerimaan'), 'Laporan Penghimpunan' => null],
            'tahun'       => $tahun,
            'tahunList'   => $tahunList,
            'bulanNames'  => $bulanNames,
            'rows'        => $rows,
            'grandTotal'  => $grandTotal,
            'danaTotal'   => $danaTotal,
            'totalZakat'  => $danaTotal['ZAKAT']    ?? 0,
            'totalInfak'  => ($danaTotal['INFAK_T'] ?? 0) + ($danaTotal['INFAK_TT'] ?? 0),
            'totalAll'    => array_sum($grandTotal),
        ]);
    }

    // ── Hapus ─────────────────────────────────────────────────
    public function delete(int $id)
    {
        $record = $this->model->find($id);
        if (! $record) {
            return redirect()->to('penerimaan')->with('error', 'Data tidak ditemukan.');
        }

        // Check if periode is locked
        $periode = $this->periodeModel->find($record['periode_id']);
        if ($periode && $periode['is_tutup']) {
            return redirect()->to('penerimaan')
                ->with('error', 'Periode sudah ditutup. Data penerimaan tidak dapat dihapus.');
        }

        $jurnalId = $record['jurnal_id'];

        $db = \Config\Database::connect();
        $db->transStart();

        // Delete penghimpunan first (removes FK ref to jurnal)
        $this->model->delete($id);

        // Delete jurnal header (jurnal_detail cascades automatically)
        if ($jurnalId) {
            $db->table('jurnal')->where('id', $jurnalId)->delete();
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('penerimaan')
                ->with('error', 'Gagal menghapus data.');
        }

        return redirect()->to('penerimaan')
            ->with('success', 'Data penerimaan berhasil dihapus.');
    }
}