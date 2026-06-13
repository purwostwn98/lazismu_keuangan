<?php

namespace App\Controllers;

use App\Models\JurnalModel;
use App\Models\PeriodeModel;
use App\Models\RekeningBankModel;

class Mutasi extends BaseController
{
    private JurnalModel       $jurnalModel;
    private PeriodeModel      $periodeModel;
    private RekeningBankModel $rekeningModel;

    public function __construct()
    {
        $this->jurnalModel   = new JurnalModel();
        $this->periodeModel  = new PeriodeModel();
        $this->rekeningModel = new RekeningBankModel();
    }

    // ── Daftar Mutasi ────────────────────────────────────────
    public function index(): string
    {
        $db = \Config\Database::connect();

        $rows = $db->table('jurnal j')
            ->select([
                'j.id', 'j.nomor_jurnal', 'j.tanggal', 'j.uraian',
                'j.total_debet AS jumlah', 'j.periode_id',
                'p.nama AS nama_periode', 'p.is_tutup',
                'jd.nama AS nama_dana',
                'rb_asal.nama AS rekening_asal',
                'rb_tujuan.nama AS rekening_tujuan',
            ])
            ->join('periode p',      'p.id = j.periode_id',     'left')
            ->join('jenis_dana jd',  'jd.id = j.jenis_dana_id', 'left')
            // sub-join untuk rekening asal (kredit) dan tujuan (debet)
            ->join(
                '(SELECT jurnal_id, MIN(rekening_bank_id) AS rb_id FROM jurnal_detail WHERE kredit > 0 AND rekening_bank_id IS NOT NULL GROUP BY jurnal_id) jdc',
                'jdc.jurnal_id = j.id', 'left'
            )
            ->join(
                '(SELECT jurnal_id, MIN(rekening_bank_id) AS rb_id FROM jurnal_detail WHERE debet > 0 AND rekening_bank_id IS NOT NULL GROUP BY jurnal_id) jdd',
                'jdd.jurnal_id = j.id', 'left'
            )
            ->join('rekening_bank rb_asal',   'rb_asal.id = jdc.rb_id',  'left')
            ->join('rekening_bank rb_tujuan', 'rb_tujuan.id = jdd.rb_id', 'left')
            ->where('j.jenis_transaksi', 'transfer')
            ->orderBy('j.tanggal', 'DESC')
            ->orderBy('j.id',      'DESC')
            ->get()->getResultArray();

        return view('mutasi/index', [
            'pageTitle' => 'Mutasi / Transfer Rekening',
            'daftar'    => $rows,
        ]);
    }

    // ── Form Input ───────────────────────────────────────────
    public function input(): string
    {
        $periodeAktif = $this->periodeModel->findByTanggal(date('Y-m-d'));

        return view('mutasi/input', [
            'pageTitle'    => 'Input Mutasi / Transfer',
            'periodeList'  => $this->periodeModel->getAktif(),
            'periodeAktif' => $periodeAktif,
            'rekeningList' => $this->rekeningModel->getAktif(),
        ]);
    }

    // ── Simpan ───────────────────────────────────────────────
    public function store()
    {
        $rules = [
            'tanggal'           => 'required|valid_date',
            'periode_id'        => 'required|is_natural_no_zero',
            'rekening_asal_id'  => 'required|is_natural_no_zero',
            'rekening_tujuan_id'=> 'required|is_natural_no_zero',
            'jumlah'            => 'required|decimal|greater_than[0]',
            'uraian'            => 'required|min_length[3]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $asalId   = (int) $this->request->getPost('rekening_asal_id');
        $tujuanId = (int) $this->request->getPost('rekening_tujuan_id');

        if ($asalId === $tujuanId) {
            return redirect()->back()->withInput()
                ->with('error', 'Rekening asal dan tujuan tidak boleh sama.');
        }

        $periodeId = (int) $this->request->getPost('periode_id');
        $periode   = $this->periodeModel->find($periodeId);
        if (! $periode || $periode['is_tutup']) {
            return redirect()->back()->withInput()
                ->with('error', 'Periode sudah ditutup atau tidak valid.');
        }

        $rekAsal   = $this->rekeningModel->find($asalId);
        $rekTujuan = $this->rekeningModel->find($tujuanId);
        if (! $rekAsal || ! $rekTujuan) {
            return redirect()->back()->withInput()
                ->with('error', 'Rekening tidak ditemukan.');
        }

        $jumlah     = (float) $this->request->getPost('jumlah');
        $tanggal    = $this->request->getPost('tanggal');
        $uraian     = $this->request->getPost('uraian');
        $keterangan = trim($this->request->getPost('keterangan') ?? '') ?: null;

        $nomorJurnal = $this->jurnalModel->generateNomor('transfer');
        $now         = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->transStart();

        $this->jurnalModel->insert([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal'         => $tanggal,
            'periode_id'      => $periodeId,
            'jenis_dana_id'   => $rekAsal['jenis_dana_id'],
            'jenis_transaksi' => 'transfer',
            'uraian'          => $uraian,
            'keterangan'      => $keterangan,
            'total_debet'     => $jumlah,
            'total_kredit'    => $jumlah,
            'created_by'      => null,
        ]);
        $jurnalId = $this->jurnalModel->getInsertID();

        $db->table('jurnal_detail')->insertBatch([
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => (int) $rekTujuan['akun_id'],
                'rekening_bank_id' => $tujuanId,
                'uraian'           => $uraian,
                'debet'            => $jumlah,
                'kredit'           => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => (int) $rekAsal['akun_id'],
                'rekening_bank_id' => $asalId,
                'uraian'           => $uraian,
                'debet'            => 0,
                'kredit'           => $jumlah,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan. Silakan coba kembali.');
        }

        return redirect()->to('mutasi')
            ->with('success', "Mutasi {$nomorJurnal} berhasil disimpan.");
    }

    // ── Hapus ─────────────────────────────────────────────────
    public function delete(int $id)
    {
        $jurnal = $this->jurnalModel->find($id);
        if (! $jurnal || $jurnal['jenis_transaksi'] !== 'transfer') {
            return redirect()->to('mutasi')->with('error', 'Data tidak ditemukan.');
        }

        $periode = $this->periodeModel->find($jurnal['periode_id']);
        if ($periode && $periode['is_tutup']) {
            return redirect()->to('mutasi')
                ->with('error', 'Periode sudah ditutup. Data tidak dapat dihapus.');
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $db->table('jurnal')->where('id', $id)->delete();
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('mutasi')->with('error', 'Gagal menghapus data.');
        }

        return redirect()->to('mutasi')->with('success', 'Mutasi berhasil dihapus.');
    }
}