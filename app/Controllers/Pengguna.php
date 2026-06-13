<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\DonaturModel;
use App\Models\PenerimaManfaatModel;

class Pengguna extends BaseController
{
    private UserModel          $model;
    private DonaturModel       $donaturModel;
    private PenerimaManfaatModel $penerimaModel;

    public function __construct()
    {
        $this->model         = new UserModel();
        $this->donaturModel  = new DonaturModel();
        $this->penerimaModel = new PenerimaManfaatModel();
    }

    public function index(): string
    {
        $users   = $this->model->getWithRelasi();
        $donatur = $this->donaturModel->orderBy('nama')->findAll();
        $penerima = $this->penerimaModel->orderBy('nama')->findAll();

        $total   = count($users);
        $aktif   = count(array_filter($users, fn($u) => $u['is_aktif'] == 1));
        $muzaki  = count(array_filter($users, fn($u) => $u['is_muzaki'] == 1));
        $mustahik = count(array_filter($users, fn($u) => $u['is_mustahik'] == 1));

        return view('pengguna/index', [
            'pageTitle' => 'Manajemen Pengguna',
            'users'     => $users,
            'donatur'   => $donatur,
            'penerima'  => $penerima,
            'total'     => $total,
            'aktif'     => $aktif,
            'muzaki'    => $muzaki,
            'mustahik'  => $mustahik,
        ]);
    }

    public function store()
    {
        $post = $this->request->getPost();

        $rules = [
            'nama'     => 'required|max_length[100]',
            'email'    => 'required|valid_email|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]|alpha_dash',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,bendahara,manajer,auditor]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('open_modal', 'modalTambah');
        }

        if ($this->model->usernameExists($post['username'])) {
            return redirect()->back()->withInput()
                ->with('errors', ['username' => 'Username sudah digunakan.'])
                ->with('open_modal', 'modalTambah');
        }
        if ($this->model->emailExists($post['email'])) {
            return redirect()->back()->withInput()
                ->with('errors', ['email' => 'Email sudah digunakan.'])
                ->with('open_modal', 'modalTambah');
        }

        $this->model->insert([
            'nama'                => $post['nama'],
            'email'               => $post['email'],
            'username'            => $post['username'],
            'password'            => password_hash($post['password'], PASSWORD_DEFAULT),
            'role'                => $post['role'],
            'is_muzaki'           => isset($post['is_muzaki']) ? 1 : 0,
            'is_mustahik'         => isset($post['is_mustahik']) ? 1 : 0,
            'donatur_id'          => ($post['donatur_id'] ?? '') !== '' ? (int)$post['donatur_id'] : null,
            'penerima_manfaat_id' => ($post['penerima_manfaat_id'] ?? '') !== '' ? (int)$post['penerima_manfaat_id'] : null,
            'is_aktif'            => 1,
        ]);

        return redirect()->to('pengguna')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('pengguna')->with('error', 'Pengguna tidak ditemukan.');
        }

        $post = $this->request->getPost();

        $rules = [
            'nama'  => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'role'  => 'required|in_list[admin,bendahara,manajer,auditor]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('open_modal', 'edit_' . $id);
        }

        if ($this->model->emailExists($post['email'], $id)) {
            return redirect()->back()->withInput()
                ->with('errors', ['email' => 'Email sudah digunakan pengguna lain.'])
                ->with('open_modal', 'edit_' . $id);
        }

        $data = [
            'nama'                => $post['nama'],
            'email'               => $post['email'],
            'role'                => $post['role'],
            'is_muzaki'           => isset($post['is_muzaki']) ? 1 : 0,
            'is_mustahik'         => isset($post['is_mustahik']) ? 1 : 0,
            'donatur_id'          => ($post['donatur_id'] ?? '') !== '' ? (int)$post['donatur_id'] : null,
            'penerima_manfaat_id' => ($post['penerima_manfaat_id'] ?? '') !== '' ? (int)$post['penerima_manfaat_id'] : null,
            'is_aktif'            => isset($post['is_aktif']) ? 1 : 0,
        ];

        if (($post['password'] ?? '') !== '') {
            if (strlen($post['password']) < 6) {
                return redirect()->back()->withInput()
                    ->with('errors', ['password' => 'Password minimal 6 karakter.'])
                    ->with('open_modal', 'edit_' . $id);
            }
            $data['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        }

        $this->model->update($id, $data);

        return redirect()->to('pengguna')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleAktif(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('pengguna')->with('error', 'Pengguna tidak ditemukan.');
        }
        $this->model->update($id, ['is_aktif' => $user['is_aktif'] ? 0 : 1]);
        $status = $user['is_aktif'] ? 'dinonaktifkan' : 'diaktifkan';
        return redirect()->to('pengguna')->with('success', "Pengguna berhasil {$status}.");
    }

    public function delete(int $id)
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('pengguna')->with('error', 'Pengguna tidak ditemukan.');
        }
        $this->model->delete($id);
        return redirect()->to('pengguna')->with('success', 'Pengguna berhasil dihapus.');
    }
}
