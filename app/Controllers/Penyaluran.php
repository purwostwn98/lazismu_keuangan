<?php

namespace App\Controllers;

use App\Models\JurnalModel;
use App\Models\JenisDanaModel;
use App\Models\PeriodeModel;
use App\Models\AkunModel;
use App\Models\RekeningBankModel;
use App\Models\PenerimaManfaatModel;
use App\Models\Ext\AdProgramModel;
use App\Models\Ext\AdKategoriProgramModel;

class Penyaluran extends BaseController
{
    private JurnalModel          $jurnalModel;
    private JenisDanaModel       $jenisDanaModel;
    private PeriodeModel         $periodeModel;
    private AkunModel            $akunModel;
    private RekeningBankModel    $rekeningModel;
    private PenerimaManfaatModel $penerimaModel;
    private AdProgramModel       $programModel;

    public function __construct()
    {
        $this->jurnalModel    = new JurnalModel();
        $this->jenisDanaModel = new JenisDanaModel();
        $this->periodeModel   = new PeriodeModel();
        $this->akunModel      = new AkunModel();
        $this->rekeningModel  = new RekeningBankModel();
        $this->penerimaModel  = new PenerimaManfaatModel();
        $this->programModel   = new AdProgramModel();
    }

    // ── Daftar Penyaluran ─────────────────────────────────────
    public function index(): string
    {
        $allPeriode = $this->periodeModel->orderBy('tahun DESC, bulan ASC')->findAll();
        $tahunList  = array_values(array_unique(array_column($allPeriode, 'tahun')));
        rsort($tahunList);
        $tahunTerbaru = $tahunList[0] ?? (int) date('Y');

        $adaParam = $this->request->getGet('tahun')   !== null
                 || $this->request->getGet('periode') !== null
                 || $this->request->getGet('dana')    !== null
                 || $this->request->getGet('q')       !== null;

        $tahun     = $adaParam ? (int) ($this->request->getGet('tahun')   ?? 0) : $tahunTerbaru;
        $periodeId = (int) ($this->request->getGet('periode') ?? 0);

        $filter = [
            'q'             => $this->request->getGet('q'),
            'jenis_dana_id' => $this->request->getGet('dana'),
            'periode_id'    => $periodeId ?: null,
            'tahun'         => ($periodeId === 0 && $tahun > 0) ? $tahun : null,
        ];

        $penyaluran  = $this->jurnalModel->getPenyaluran($filter);
        $jenisDana   = $this->jenisDanaModel->getAll();

        $totalJumlah = array_sum(array_column($penyaluran, 'total_debet'));

        return view('penyaluran/index', [
            'pageTitle'   => 'Daftar Penyaluran',
            'breadcrumb'  => ['Penyaluran' => null, 'Daftar Penyaluran' => null],
            'penyaluran'  => $penyaluran,
            'jenisDana'   => $jenisDana,
            'tahunList'   => $tahunList,
            'periodeList' => $allPeriode,
            'totalJumlah' => $totalJumlah,
            'filter'      => [
                'tahun'         => $tahun,
                'periode_id'    => $periodeId,
                'jenis_dana_id' => $this->request->getGet('dana'),
                'q'             => $this->request->getGet('q'),
            ],
        ]);
    }

    // ── Form Input ────────────────────────────────────────────
    public function input(): string
    {
        $jenisDana  = $this->jenisDanaModel->getAll();
        $periodeList = $this->periodeModel->getAktif();
        $penerima   = $this->penerimaModel->getAll();
        $akunPenyaluran = $this->akunModel->getByTipe('penyaluran');
        $rekening   = $this->rekeningModel->getAktif();
        $programs   = $this->programModel->getWithKategori();

        // Periode aktif default = bulan ini
        $periodeAktif = $this->periodeModel->findByTanggal(date('Y-m-d'));

        return view('penyaluran/input', [
            'pageTitle'      => 'Input Penyaluran',
            'breadcrumb'     => ['Penyaluran' => base_url('penyaluran'), 'Input Penyaluran' => null],
            'jenisDana'      => $jenisDana,
            'periodeList'    => $periodeList,
            'periodeAktif'   => $periodeAktif,
            'penerima'       => $penerima,
            'akunPenyaluran' => $akunPenyaluran,
            'rekening'       => $rekening,
            'programs'       => $programs,
        ]);
    }

    // ── Simpan ────────────────────────────────────────────────
    public function store()
    {
        $rules = [
            'tanggal'         => 'required|valid_date',
            'periode_id'      => 'required|is_natural_no_zero',
            'jenis_dana_id'   => 'required|is_natural_no_zero',
            'akun_debet_id'   => 'required|is_natural_no_zero',
            'rekening_id'     => 'required|is_natural_no_zero',
            'jumlah'          => 'required|decimal|greater_than[0]',
            'uraian'          => 'required|min_length[3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $jumlah      = (float) str_replace(['Rp', '.', ',', ' '], ['', '', '.', ''], $this->request->getPost('jumlah'));
        $rekeningId  = (int) $this->request->getPost('rekening_id');
        $akunDebetId = (int) $this->request->getPost('akun_debet_id');

        // Ambil akun_id dari rekening bank (untuk kredit)
        $rekening = $this->rekeningModel->find($rekeningId);
        if (!$rekening) {
            return redirect()->back()->withInput()->with('error', 'Rekening bank tidak ditemukan.');
        }

        $nomorJurnal = $this->jurnalModel->generateNomor('penyaluran');

        $header = [
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal'        => $this->request->getPost('tanggal'),
            'periode_id'     => (int) $this->request->getPost('periode_id'),
            'jenis_dana_id'  => (int) $this->request->getPost('jenis_dana_id'),
            'jenis_transaksi' => 'penyaluran',
            'uraian'         => $this->request->getPost('uraian'),
            'keterangan'     => $this->request->getPost('keterangan'),
            'penerima_id'    => $this->request->getPost('penerima_id') ?: null,
            'program_id'     => null,
            'total_debet'    => $jumlah,
            'total_kredit'   => $jumlah,
            'created_by'     => null,
        ];

        $details = [
            // Debet: akun penyaluran
            [
                'akun_id'          => $akunDebetId,
                'rekening_bank_id' => null,
                'uraian'           => $this->request->getPost('uraian'),
                'debet'            => $jumlah,
                'kredit'           => 0,
            ],
            // Kredit: kas/rekening bank
            [
                'akun_id'          => (int) $rekening['akun_id'],
                'rekening_bank_id' => $rekeningId,
                'uraian'           => $this->request->getPost('uraian'),
                'debet'            => 0,
                'kredit'           => $jumlah,
            ],
        ];

        try {
            $id = $this->jurnalModel->storePenyaluran($header, $details);
            return redirect()->to(base_url('penyaluran'))
                             ->with('success', "Penyaluran {$nomorJurnal} berhasil disimpan.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                             ->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // ── Detail ────────────────────────────────────────────────
    public function show(int $id): string
    {
        $data = $this->jurnalModel->getWithDetail($id);
        if (empty($data) || $data['header']['jenis_transaksi'] !== 'penyaluran') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Penyaluran #{$id} tidak ditemukan.");
        }

        return view('penyaluran/show', [
            'pageTitle'  => 'Detail Penyaluran',
            'breadcrumb' => ['Penyaluran' => base_url('penyaluran'), 'Detail' => null],
            'header'     => $data['header'],
            'details'    => $data['details'],
        ]);
    }

    // ── AJAX: Rekening by Jenis Dana ──────────────────────────
    public function ajaxRekening(): \CodeIgniter\HTTP\ResponseInterface
    {
        $jenisDanaId = (int) $this->request->getGet('jenis_dana_id');
        $rows = $this->rekeningModel->getByJenisDana($jenisDanaId);
        return $this->response->setJSON($rows);
    }

    // ── AJAX: Program by Jenis Dana (ext DB) ──────────────────
    public function ajaxProgram(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rows = $this->programModel->getWithKategori();
        return $this->response->setJSON($rows);
    }

    // ── AJAX: Search Penerima ─────────────────────────────────
    public function ajaxPenerima(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q    = $this->request->getGet('q') ?? '';
        $rows = $this->penerimaModel->search($q);
        return $this->response->setJSON($rows);
    }
}
