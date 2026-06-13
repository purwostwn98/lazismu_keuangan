<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\DonaturModel;
use App\Models\KategoriDonaturModel;

class Donatur extends BaseController
{
    private DonaturModel         $model;
    private KategoriDonaturModel $kategoriModel;

    public function __construct()
    {
        $this->model         = new DonaturModel();
        $this->kategoriModel = new KategoriDonaturModel();
    }

    public function index(): string
    {
        $donatur  = $this->model->getWithKategori();
        $total    = count($donatur);
        $individu = count(array_filter($donatur, fn($d) => $d['jenis'] === 'individu'));
        $lembaga  = $total - $individu;
        $aktif    = count(array_filter($donatur, fn($d) => $d['is_aktif'] == 1));

        return view('master/donatur/index', [
            'pageTitle' => 'Donatur / Muzakki',
            'donatur'   => $donatur,
            'total'     => $total,
            'individu'  => $individu,
            'lembaga'   => $lembaga,
            'aktif'     => $aktif,
            'kategori'  => $this->kategoriModel->getGrouped(),
        ]);
    }

    public function store()
    {
        $rules = [
            'nama'        => 'required|max_length[150]',
            'jenis'       => 'required|in_list[individu,lembaga]',
            'no_hp'       => 'permit_empty|max_length[20]',
            'email'       => 'permit_empty|valid_email|max_length[100]',
            'kategori_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kode = trim($this->request->getPost('kode') ?? '');
        if ($kode === '') {
            $nip = trim($this->request->getPost('nip') ?? '');
            $kode = $nip !== '' ? $nip : $this->model->autoKode();
        }

        // Validate uniqueness of kode
        if ($this->model->where('kode', $kode)->countAllResults() > 0) {
            return redirect()->back()->withInput()
                ->with('errors', ['kode' => "Kode '{$kode}' sudah digunakan."]);
        }

        $this->model->insert([
            'kode'        => $kode,
            'nama'        => $this->request->getPost('nama'),
            'jenis'       => $this->request->getPost('jenis'),
            'kategori_id' => ((int) $this->request->getPost('kategori_id')) ?: null,
            'nip'         => trim($this->request->getPost('nip') ?? '') ?: null,
            'no_hp'       => trim($this->request->getPost('no_hp') ?? '') ?: null,
            'email'       => trim($this->request->getPost('email') ?? '') ?: null,
            'alamat'      => trim($this->request->getPost('alamat') ?? '') ?: null,
            'is_aktif'    => 1,
        ]);

        return redirect()->to('master/donatur')->with('success', 'Donatur berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $donatur = $this->model->find($id);
        if (! $donatur) {
            return redirect()->to('master/donatur')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama'        => 'required|max_length[150]',
            'jenis'       => 'required|in_list[individu,lembaga]',
            'kode'        => "required|max_length[30]|is_unique[donatur.kode,id,{$id}]",
            'no_hp'       => 'permit_empty|max_length[20]',
            'email'       => 'permit_empty|valid_email|max_length[100]',
            'kategori_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'kode'        => trim($this->request->getPost('kode')),
            'nama'        => $this->request->getPost('nama'),
            'jenis'       => $this->request->getPost('jenis'),
            'kategori_id' => ((int) $this->request->getPost('kategori_id')) ?: null,
            'nip'         => trim($this->request->getPost('nip') ?? '') ?: null,
            'no_hp'       => trim($this->request->getPost('no_hp') ?? '') ?: null,
            'email'       => trim($this->request->getPost('email') ?? '') ?: null,
            'alamat'      => trim($this->request->getPost('alamat') ?? '') ?: null,
            'is_aktif'    => (int) ($this->request->getPost('is_aktif') ?? 0),
        ]);

        return redirect()->to('master/donatur')->with('success', 'Data donatur berhasil diperbarui.');
    }

    public function toggleAktif(int $id)
    {
        $donatur = $this->model->find($id);
        if (! $donatur) {
            return redirect()->to('master/donatur')->with('error', 'Data tidak ditemukan.');
        }

        $this->model->update($id, ['is_aktif' => $donatur['is_aktif'] ? 0 : 1]);
        $status = $donatur['is_aktif'] ? 'dinonaktifkan' : 'diaktifkan';

        return redirect()->to('master/donatur')->with('success', "Donatur berhasil {$status}.");
    }

    public function delete(int $id)
    {
        $donatur = $this->model->find($id);
        if (! $donatur) {
            return redirect()->to('master/donatur')->with('error', 'Data tidak ditemukan.');
        }

        $db  = \Config\Database::connect();
        $ref = $db->table('penghimpunan')->where('donatur_id', $id)->countAllResults();
        if ($ref > 0) {
            return redirect()->to('master/donatur')
                ->with('error', "Donatur tidak dapat dihapus karena memiliki {$ref} data penerimaan.");
        }

        $this->model->delete($id);
        return redirect()->to('master/donatur')
            ->with('success', "Donatur {$donatur['nama']} berhasil dihapus.");
    }
}