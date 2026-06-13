<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\AkunModel;

class Akun extends BaseController
{
    private AkunModel $model;

    public function __construct()
    {
        $this->model = new AkunModel();
    }

    public function index(): string
    {
        $akun     = $this->model->orderBy('nomor_akun', 'ASC')->findAll();
        $total    = count($akun);
        $headers  = count(array_filter($akun, fn($a) => $a['is_header'] == 1));
        $postable = $total - $headers;

        $byTipe = [];
        foreach ($akun as $a) {
            $byTipe[$a['tipe']] = ($byTipe[$a['tipe']] ?? 0) + 1;
        }

        return view('master/akun/index', [
            'pageTitle'     => 'Bagan Akun (CoA)',
            'akun'          => $akun,
            'total'         => $total,
            'headers'       => $headers,
            'postable'      => $postable,
            'byTipe'        => $byTipe,
            'parentOptions' => $this->model
                ->where('is_header', 1)
                ->orderBy('nomor_akun', 'ASC')
                ->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'nomor_akun' => 'required|max_length[10]|is_unique[akun.nomor_akun]',
            'nama_akun'  => 'required|max_length[200]',
            'tipe'       => 'required|in_list[aset,liabilitas,saldo_dana,penerimaan,penyaluran,biaya]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $parentId = ((int) $this->request->getPost('parent_id')) ?: null;
        $level    = 1;
        if ($parentId) {
            $parent = $this->model->find($parentId);
            $level  = ($parent['level'] ?? 0) + 1;
        }

        $this->model->insert([
            'nomor_akun' => trim($this->request->getPost('nomor_akun')),
            'nama_akun'  => $this->request->getPost('nama_akun'),
            'tipe'       => $this->request->getPost('tipe'),
            'parent_id'  => $parentId,
            'level'      => $level,
            'is_header'  => (int) ($this->request->getPost('is_header') ?? 0),
        ]);

        return redirect()->to('master/akun')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $akun = $this->model->find($id);
        if (! $akun) {
            return redirect()->to('master/akun')->with('error', 'Akun tidak ditemukan.');
        }

        $rules = [
            'nomor_akun' => "required|max_length[10]|is_unique[akun.nomor_akun,id,{$id}]",
            'nama_akun'  => 'required|max_length[200]',
            'tipe'       => 'required|in_list[aset,liabilitas,saldo_dana,penerimaan,penyaluran,biaya]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $parentId = ((int) $this->request->getPost('parent_id')) ?: null;
        // Prevent self-reference
        if ($parentId === $id) {
            $parentId = null;
        }
        $level = 1;
        if ($parentId) {
            $parent = $this->model->find($parentId);
            $level  = ($parent['level'] ?? 0) + 1;
        }

        $this->model->update($id, [
            'nomor_akun' => trim($this->request->getPost('nomor_akun')),
            'nama_akun'  => $this->request->getPost('nama_akun'),
            'tipe'       => $this->request->getPost('tipe'),
            'parent_id'  => $parentId,
            'level'      => $level,
            'is_header'  => (int) ($this->request->getPost('is_header') ?? 0),
        ]);

        return redirect()->to('master/akun')->with('success', 'Akun berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $akun = $this->model->find($id);
        if (! $akun) {
            return redirect()->to('master/akun')->with('error', 'Akun tidak ditemukan.');
        }

        $children = $this->model->where('parent_id', $id)->countAllResults();
        if ($children > 0) {
            return redirect()->to('master/akun')
                ->with('error', "Akun tidak dapat dihapus karena memiliki {$children} sub-akun.");
        }

        $db         = \Config\Database::connect();
        $rekeningRef = $db->table('rekening_bank')->where('akun_id', $id)->countAllResults();
        if ($rekeningRef > 0) {
            return redirect()->to('master/akun')
                ->with('error', 'Akun tidak dapat dihapus karena digunakan oleh rekening bank.');
        }

        $this->model->delete($id);
        return redirect()->to('master/akun')
            ->with('success', "Akun {$akun['nomor_akun']} — {$akun['nama_akun']} berhasil dihapus.");
    }
}