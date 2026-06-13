<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\RekeningBankModel;
use App\Models\JenisDanaModel;
use App\Models\AkunModel;

class RekeningBank extends BaseController
{
    private RekeningBankModel $model;
    private JenisDanaModel    $jenisDanaModel;
    private AkunModel         $akunModel;

    public function __construct()
    {
        $this->model          = new RekeningBankModel();
        $this->jenisDanaModel = new JenisDanaModel();
        $this->akunModel      = new AkunModel();
    }

    public function index(): string
    {
        $data = $this->model
            ->select('rekening_bank.*, jenis_dana.nama AS nama_dana, akun.nomor_akun, akun.nama_akun')
            ->join('jenis_dana', 'jenis_dana.id = rekening_bank.jenis_dana_id')
            ->join('akun',       'akun.id = rekening_bank.akun_id')
            ->orderBy('jenis_dana.id, rekening_bank.nama', 'ASC')
            ->findAll();

        $total   = count($data);
        $aktif   = count(array_filter($data, fn($r) => $r['is_aktif'] == 1));
        $nonAktif = $total - $aktif;

        return view('master/rekening/index', [
            'title'      => 'Rekening Bank',
            'rekening'   => $data,
            'total'      => $total,
            'aktif'      => $aktif,
            'nonAktif'   => $nonAktif,
            'jenisDana'  => $this->jenisDanaModel->getAll(),
            'akunKas'    => $this->akunModel
                                ->where('nomor_akun >=', '11101001')
                                ->where('nomor_akun <=', '11102999')
                                ->where('is_header', 0)
                                ->orderBy('nomor_akun', 'ASC')
                                ->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'nama'          => 'required|max_length[150]',
            'bank'          => 'required|max_length[100]',
            'jenis_dana_id' => 'required|is_natural_no_zero',
            'akun_id'       => 'required|is_natural_no_zero',
            'saldo_awal'    => 'permit_empty|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saldoRaw = str_replace(['.', ','], ['', '.'], $this->request->getPost('saldo_awal') ?? '0');

        $this->model->insert([
            'nama'          => $this->request->getPost('nama'),
            'nomor_rekening' => $this->request->getPost('nomor_rekening'),
            'bank'          => $this->request->getPost('bank'),
            'jenis_dana_id' => $this->request->getPost('jenis_dana_id'),
            'akun_id'       => $this->request->getPost('akun_id'),
            'saldo_awal'    => (float) $saldoRaw,
            'is_aktif'      => 1,
        ]);

        return redirect()->to('master/rekening')->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $rekening = $this->model->find($id);
        if (! $rekening) {
            return redirect()->to('master/rekening')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama'          => 'required|max_length[150]',
            'bank'          => 'required|max_length[100]',
            'jenis_dana_id' => 'required|is_natural_no_zero',
            'akun_id'       => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saldoRaw = str_replace(['.', ','], ['', '.'], $this->request->getPost('saldo_awal') ?? '0');

        $this->model->update($id, [
            'nama'           => $this->request->getPost('nama'),
            'nomor_rekening' => $this->request->getPost('nomor_rekening'),
            'bank'           => $this->request->getPost('bank'),
            'jenis_dana_id'  => $this->request->getPost('jenis_dana_id'),
            'akun_id'        => $this->request->getPost('akun_id'),
            'saldo_awal'     => (float) $saldoRaw,
            'is_aktif'       => (int) ($this->request->getPost('is_aktif') ?? 0),
        ]);

        return redirect()->to('master/rekening')->with('success', 'Rekening berhasil diperbarui.');
    }

    public function toggleAktif(int $id)
    {
        $rekening = $this->model->find($id);
        if (! $rekening) {
            return redirect()->to('master/rekening')->with('error', 'Data tidak ditemukan.');
        }

        $this->model->update($id, ['is_aktif' => $rekening['is_aktif'] ? 0 : 1]);

        $status = $rekening['is_aktif'] ? 'dinonaktifkan' : 'diaktifkan';
        return redirect()->to('master/rekening')->with('success', "Rekening berhasil {$status}.");
    }
}
