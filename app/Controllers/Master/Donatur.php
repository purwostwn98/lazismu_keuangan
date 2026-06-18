<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\DonaturModel;
use App\Models\KategoriDonaturModel;
use App\Models\UserModel;

class Donatur extends BaseController
{
    private DonaturModel         $model;
    private KategoriDonaturModel $kategoriModel;
    private UserModel            $userModel;

    public function __construct()
    {
        $this->model         = new DonaturModel();
        $this->kategoriModel = new KategoriDonaturModel();
        $this->userModel     = new UserModel();
    }

    public function dashboard(): string
    {
        $db = \Config\Database::connect();

        $tahunList = array_values(array_column(
            $db->query("SELECT DISTINCT p.tahun FROM periode p JOIN penghimpunan ph ON ph.periode_id = p.id ORDER BY p.tahun DESC")->getResultArray(),
            'tahun'
        ));
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        $tahun = (int) ($this->request->getGet('tahun') ?? $tahunList[0]);

        // ── Stat cards ────────────────────────────────────────────────
        $totalAktif = (int) $db->query(
            "SELECT COUNT(*) AS n FROM donatur WHERE is_aktif = 1"
        )->getRow()->n;

        $row = $db->query("
            SELECT
                COUNT(DISTINCT ph.donatur_id) AS donatur_berdonasi,
                COALESCE(SUM(ph.jumlah), 0)   AS total_donasi
            FROM penghimpunan ph
            JOIN periode p ON p.id = ph.periode_id
            WHERE p.tahun = ? AND ph.donatur_id IS NOT NULL
        ", [$tahun])->getRow();

        $donaturBerdonasi = (int)   ($row->donatur_berdonasi ?? 0);
        $totalDonasi      = (float) ($row->total_donasi      ?? 0);
        $rataRata         = $donaturBerdonasi > 0 ? $totalDonasi / $donaturBerdonasi : 0;

        // ── Trend donasi bulanan ──────────────────────────────────────
        $trendRows = $db->query("
            SELECT p.bulan, COALESCE(SUM(ph.jumlah), 0) AS total
            FROM penghimpunan ph
            JOIN periode p ON p.id = ph.periode_id
            WHERE p.tahun = ?
            GROUP BY p.bulan
            ORDER BY p.bulan
        ", [$tahun])->getResultArray();

        $trendBulanan = array_fill(1, 12, 0.0);
        foreach ($trendRows as $r) {
            $trendBulanan[(int)$r['bulan']] = (float)$r['total'];
        }

        // ── Distribusi per kategori parent ────────────────────────────
        $kategoriRows = $db->query("
            SELECT
                COALESCE(kp.nama, kc.nama) AS parent_nama,
                COALESCE(SUM(ph.jumlah), 0) AS total
            FROM penghimpunan ph
            JOIN kategori_donatur kc   ON kc.id = ph.kategori_id
            LEFT JOIN kategori_donatur kp ON kp.id = kc.parent_id
            JOIN periode p             ON p.id  = ph.periode_id
            WHERE p.tahun = ?
            GROUP BY parent_nama
            ORDER BY total DESC
        ", [$tahun])->getResultArray();

        // ── Top 10 donatur terbesar tahun ini ─────────────────────────
        $topDonatur = $db->query("
            SELECT
                d.nama, d.kode,
                COALESCE(kd.nama, '—') AS kategori,
                d.jenis,
                COUNT(ph.id)           AS jumlah_transaksi,
                SUM(ph.jumlah)         AS total_donasi,
                MAX(j.tanggal)         AS last_donasi
            FROM penghimpunan ph
            JOIN donatur d             ON d.id  = ph.donatur_id
            LEFT JOIN kategori_donatur kd ON kd.id = d.kategori_id
            JOIN jurnal j              ON j.id  = ph.jurnal_id
            JOIN periode p             ON p.id  = ph.periode_id
            WHERE p.tahun = ? AND ph.donatur_id IS NOT NULL
            GROUP BY d.id, d.nama, d.kode, kd.nama, d.jenis
            ORDER BY total_donasi DESC
            LIMIT 10
        ", [$tahun])->getResultArray();

        // ── 10 donatur terbaru berdonasi ──────────────────────────────
        $terbaru = $db->query("
            SELECT
                d.nama, d.kode,
                COALESCE(kd.nama, '—') AS kategori,
                d.jenis,
                ph.jumlah,
                j.tanggal,
                j.uraian
            FROM penghimpunan ph
            JOIN donatur d             ON d.id  = ph.donatur_id
            LEFT JOIN kategori_donatur kd ON kd.id = d.kategori_id
            JOIN jurnal j              ON j.id  = ph.jurnal_id
            JOIN periode p             ON p.id  = ph.periode_id
            WHERE p.tahun = ? AND ph.donatur_id IS NOT NULL
            ORDER BY j.tanggal DESC, ph.id DESC
            LIMIT 10
        ", [$tahun])->getResultArray();

        return view('master/donatur/dashboard', [
            'pageTitle'         => 'Dashboard Donatur',
            'tahun'             => $tahun,
            'tahunList'         => $tahunList,
            'totalAktif'        => $totalAktif,
            'donaturBerdonasi'  => $donaturBerdonasi,
            'totalDonasi'       => $totalDonasi,
            'rataRata'          => $rataRata,
            'trendBulanan'      => $trendBulanan,
            'kategoriRows'      => $kategoriRows,
            'topDonatur'        => $topDonatur,
            'terbaru'           => $terbaru,
        ]);
    }

    public function index(): string
    {
        $donatur  = $this->model->getWithKategori();
        $total    = count($donatur);
        $individu = count(array_filter($donatur, fn($d) => $d['jenis'] === 'individu'));
        $lembaga  = $total - $individu;
        $aktif    = count(array_filter($donatur, fn($d) => $d['is_aktif'] == 1));

        // Map donatur_id → user info untuk tampilan status akun
        $users = $this->userModel->where('is_muzaki', 1)->where('donatur_id IS NOT NULL')->findAll();
        $akunMap = [];
        foreach ($users as $u) {
            $akunMap[$u['donatur_id']] = $u;
        }

        return view('master/donatur/index', [
            'pageTitle' => 'Donatur / Muzakki',
            'donatur'   => $donatur,
            'total'     => $total,
            'individu'  => $individu,
            'lembaga'   => $lembaga,
            'aktif'     => $aktif,
            'kategori'  => $this->kategoriModel->getGrouped(),
            'akunMap'   => $akunMap,
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

        // Hapus akun login jika ada
        $this->userModel->where('donatur_id', $id)->delete();

        $this->model->delete($id);
        return redirect()->to('master/donatur')
            ->with('success', "Donatur {$donatur['nama']} berhasil dihapus.");
    }

    public function buatAkun(int $id)
    {
        $donatur = $this->model->find($id);
        if (! $donatur) {
            return redirect()->to('master/donatur')->with('error', 'Donatur tidak ditemukan.');
        }

        // Cek apakah sudah punya akun
        $existing = $this->userModel->where('donatur_id', $id)->first();
        if ($existing) {
            return redirect()->to('master/donatur')
                ->with('error', "Donatur {$donatur['nama']} sudah memiliki akun login (username: {$existing['username']}).");
        }

        $username = trim($this->request->getPost('username') ?? '');
        $password = $this->request->getPost('password') ?? '';
        $email    = trim($this->request->getPost('email') ?? $donatur['email'] ?? '');

        if (! $username || strlen($username) < 3) {
            return redirect()->to('master/donatur')
                ->with('error', 'Username minimal 3 karakter.');
        }
        if (! preg_match('/^[a-z0-9_-]+$/i', $username)) {
            return redirect()->to('master/donatur')
                ->with('error', 'Username hanya boleh huruf, angka, tanda hubung, dan underscore.');
        }
        if (strlen($password) < 6) {
            return redirect()->to('master/donatur')
                ->with('error', 'Password minimal 6 karakter.');
        }
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('master/donatur')
                ->with('error', 'Email tidak valid.');
        }

        if ($this->userModel->usernameExists($username)) {
            return redirect()->to('master/donatur')
                ->with('error', "Username '{$username}' sudah digunakan.");
        }
        if ($this->userModel->emailExists($email)) {
            return redirect()->to('master/donatur')
                ->with('error', "Email '{$email}' sudah digunakan.");
        }

        $this->userModel->skipValidation(true)->insert([
            'nama'       => $donatur['nama'],
            'email'      => $email,
            'username'   => $username,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'role'       => 'bendahara',
            'is_muzaki'  => 1,
            'is_mustahik'=> 0,
            'donatur_id' => $id,
            'is_aktif'   => 1,
        ]);

        return redirect()->to('master/donatur')
            ->with('success', "Akun login untuk {$donatur['nama']} berhasil dibuat (username: {$username}).");
    }

    public function hapusAkun(int $id)
    {
        $donatur = $this->model->find($id);
        if (! $donatur) {
            return redirect()->to('master/donatur')->with('error', 'Donatur tidak ditemukan.');
        }

        $deleted = $this->userModel->where('donatur_id', $id)->delete();
        if (! $deleted) {
            return redirect()->to('master/donatur')
                ->with('error', "Donatur {$donatur['nama']} tidak memiliki akun login.");
        }

        return redirect()->to('master/donatur')
            ->with('success', "Akun login {$donatur['nama']} berhasil dihapus.");
    }
}