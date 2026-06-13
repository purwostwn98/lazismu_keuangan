<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\PenerimaManfaatModel;

class PenerimaManfaat extends BaseController
{
    private PenerimaManfaatModel $model;

    public function __construct()
    {
        $this->model = new PenerimaManfaatModel();
    }

    public function index(): string
    {
        $penerima = $this->model->orderBy('nama', 'ASC')->findAll();
        $total    = count($penerima);

        $byAsnaf = [];
        $tanpaAsnaf = 0;
        foreach ($penerima as $p) {
            if ($p['asnaf']) {
                $byAsnaf[$p['asnaf']] = ($byAsnaf[$p['asnaf']] ?? 0) + 1;
            } else {
                $tanpaAsnaf++;
            }
        }

        return view('master/penerima/index', [
            'pageTitle'  => 'Penerima Manfaat',
            'penerima'   => $penerima,
            'total'      => $total,
            'byAsnaf'    => $byAsnaf,
            'tanpaAsnaf' => $tanpaAsnaf,
        ]);
    }

    public function store()
    {
        $rules = [
            'nama'  => 'required|max_length[150]',
            'tipe'  => 'required|in_list[individu,lembaga]',
            'asnaf' => 'permit_empty|in_list[fakir,miskin,amil,muallaf,riqab,gharimin,fisabilillah,ibnu_sabil]',
            'email' => 'permit_empty|valid_email|max_length[150]|is_unique[penerima_manfaat.email]',
            'no_hp' => 'permit_empty|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kode = trim($this->request->getPost('kode') ?? '');
        if ($kode === '') {
            $kode = $this->autoKode();
        }

        if ($this->model->where('kode', $kode)->countAllResults() > 0) {
            return redirect()->back()->withInput()
                ->with('errors', ['kode' => "Kode '{$kode}' sudah digunakan."]);
        }

        $email = trim($this->request->getPost('email') ?? '') ?: null;

        $this->model->insert([
            'kode'   => $kode,
            'nama'   => $this->request->getPost('nama'),
            'tipe'   => $this->request->getPost('tipe'),
            'asnaf'  => $this->request->getPost('asnaf') ?: null,
            'email'  => $email,
            'no_hp'  => trim($this->request->getPost('no_hp') ?? '') ?: null,
            'alamat' => trim($this->request->getPost('alamat') ?? '') ?: null,
        ]);

        return redirect()->to('master/penerima')->with('success', 'Penerima manfaat berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $penerima = $this->model->find($id);
        if (! $penerima) {
            return redirect()->to('master/penerima')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama'  => 'required|max_length[150]',
            'kode'  => "required|max_length[30]|is_unique[penerima_manfaat.kode,id,{$id}]",
            'tipe'  => 'required|in_list[individu,lembaga]',
            'asnaf' => 'permit_empty|in_list[fakir,miskin,amil,muallaf,riqab,gharimin,fisabilillah,ibnu_sabil]',
            'email' => "permit_empty|valid_email|max_length[150]|is_unique[penerima_manfaat.email,id,{$id}]",
            'no_hp' => 'permit_empty|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = trim($this->request->getPost('email') ?? '') ?: null;

        $this->model->update($id, [
            'kode'   => trim($this->request->getPost('kode')),
            'nama'   => $this->request->getPost('nama'),
            'tipe'   => $this->request->getPost('tipe'),
            'asnaf'  => $this->request->getPost('asnaf') ?: null,
            'email'  => $email,
            'no_hp'  => trim($this->request->getPost('no_hp') ?? '') ?: null,
            'alamat' => trim($this->request->getPost('alamat') ?? '') ?: null,
        ]);

        return redirect()->to('master/penerima')->with('success', 'Data penerima manfaat berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $penerima = $this->model->find($id);
        if (! $penerima) {
            return redirect()->to('master/penerima')->with('error', 'Data tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $refJurnal  = $db->table('jurnal')->where('penerima_id', $id)->countAllResults();
        $refPiutang = $db->table('piutang')->where('penerima_id', $id)->countAllResults();

        if ($refJurnal + $refPiutang > 0) {
            return redirect()->to('master/penerima')
                ->with('error', 'Penerima tidak dapat dihapus karena sudah memiliki data jurnal atau piutang.');
        }

        $this->model->delete($id);
        return redirect()->to('master/penerima')
            ->with('success', "Penerima {$penerima['nama']} berhasil dihapus.");
    }

    private function autoKode(): string
    {
        $count = $this->model->countAll() + 1;
        do {
            $kode = 'PM' . str_pad($count, 5, '0', STR_PAD_LEFT);
            $count++;
        } while ($this->model->where('kode', $kode)->countAllResults() > 0);

        return $kode;
    }
}