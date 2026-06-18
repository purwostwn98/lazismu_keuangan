<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/login', ['pageTitle' => 'Login']);
    }

    public function login()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Username dan password wajib diisi.');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $model = new UserModel();
        $user  = $model->where('username', $username)->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()
                ->with('error', 'Username atau password salah.');
        }

        if (! $user['is_aktif']) {
            return redirect()->back()->withInput()
                ->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        $isMuzaki  = (bool) $user['is_muzaki'];
        $donaturId = $user['donatur_id'] ?? null;

        session()->set([
            'logged_in'    => true,
            'user_id'      => $user['id'],
            'user_nama'    => $user['nama'],
            'user_username'=> $user['username'],
            'user_email'   => $user['email'],
            'user_role'    => $user['role'],
            'is_muzaki'    => $isMuzaki,
            'is_mustahik'  => (bool) $user['is_mustahik'],
            'donatur_id'   => $donaturId,
        ]);

        $model->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        // Akun donatur: is_muzaki=1 dan punya donatur_id → portal donatur
        if ($isMuzaki && $donaturId) {
            return redirect()->to(base_url('donatur/portal'));
        }

        return redirect()->to(base_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
