<?php

namespace App\Controllers\Donatur;

use App\Controllers\BaseController;
use App\Models\PenghimpunanModel;

class Portal extends BaseController
{
    private int $donaturId;

    public function __construct()
    {
        $donaturId = session()->get('donatur_id');
        if (! $donaturId) {
            // Donatur account not linked to a donatur record
            session()->destroy();
            header('Location: ' . base_url('login'));
            exit;
        }
        $this->donaturId = (int) $donaturId;
    }

    public function index(): string
    {
        $db    = \Config\Database::connect();
        $tahunList = array_values(array_column(
            $db->query("
                SELECT DISTINCT p.tahun
                FROM penghimpunan ph
                JOIN periode p ON p.id = ph.periode_id
                WHERE ph.donatur_id = ?
                ORDER BY p.tahun DESC
            ", [$this->donaturId])->getResultArray(),
            'tahun'
        ));
        if (empty($tahunList)) $tahunList = [(int) date('Y')];

        $tahun = (int) ($this->request->getGet('tahun') ?? $tahunList[0]);

        // ── Profil donatur ────────────────────────────────────────────
        $donatur = $db->query("
            SELECT d.*, kc.nama AS kategori_nama,
                   kp.nama AS kategori_parent
            FROM donatur d
            LEFT JOIN kategori_donatur kc ON kc.id = d.kategori_id
            LEFT JOIN kategori_donatur kp ON kp.id = kc.parent_id
            WHERE d.id = ?
        ", [$this->donaturId])->getRow();

        // ── Statistik keseluruhan ─────────────────────────────────────
        $statsAll = $db->query("
            SELECT COUNT(*) AS jumlah_trx, COALESCE(SUM(ph.jumlah), 0) AS total_donasi
            FROM penghimpunan ph
            WHERE ph.donatur_id = ?
        ", [$this->donaturId])->getRow();

        // ── Statistik tahun ini ───────────────────────────────────────
        $statsTahun = $db->query("
            SELECT COUNT(*) AS jumlah_trx, COALESCE(SUM(ph.jumlah), 0) AS total_donasi
            FROM penghimpunan ph
            JOIN periode p ON p.id = ph.periode_id
            WHERE ph.donatur_id = ? AND p.tahun = ?
        ", [$this->donaturId, $tahun])->getRow();

        // ── Trend bulanan tahun ini ───────────────────────────────────
        $trendRows = $db->query("
            SELECT p.bulan, COALESCE(SUM(ph.jumlah), 0) AS total
            FROM penghimpunan ph
            JOIN periode p ON p.id = ph.periode_id
            WHERE ph.donatur_id = ? AND p.tahun = ?
            GROUP BY p.bulan
            ORDER BY p.bulan
        ", [$this->donaturId, $tahun])->getResultArray();

        $trendBulanan = array_fill(1, 12, 0.0);
        foreach ($trendRows as $r) {
            $trendBulanan[(int)$r['bulan']] = (float)$r['total'];
        }

        // ── Ringkasan per jenis dana (total tahun ini) ───────────────
        $ringkasanDana = $db->query("
            SELECT jd.id, jd.kode, jd.nama,
                   COALESCE(SUM(ph.jumlah), 0) AS total
            FROM penghimpunan ph
            JOIN jurnal j      ON j.id  = ph.jurnal_id
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            JOIN periode p     ON p.id  = ph.periode_id
            WHERE ph.donatur_id = ? AND p.tahun = ?
            GROUP BY jd.id, jd.kode, jd.nama
            ORDER BY jd.id
        ", [$this->donaturId, $tahun])->getResultArray();

        // ── Breakdown bulanan per jenis dana ──────────────────────────
        $breakdownRows = $db->query("
            SELECT jd.kode, p.bulan, COALESCE(SUM(ph.jumlah), 0) AS total
            FROM penghimpunan ph
            JOIN jurnal j      ON j.id  = ph.jurnal_id
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            JOIN periode p     ON p.id  = ph.periode_id
            WHERE ph.donatur_id = ? AND p.tahun = ?
            GROUP BY jd.kode, p.bulan
            ORDER BY jd.id, p.bulan
        ", [$this->donaturId, $tahun])->getResultArray();

        // Susun ke [kode => [bulan => total]]
        $breakdownBulanan = [];
        foreach ($ringkasanDana as $d) {
            $breakdownBulanan[$d['kode']] = array_fill(1, 12, 0.0);
        }
        foreach ($breakdownRows as $r) {
            if (isset($breakdownBulanan[$r['kode']])) {
                $breakdownBulanan[$r['kode']][(int)$r['bulan']] = (float)$r['total'];
            }
        }

        // ── Riwayat donasi tahun ini ──────────────────────────────────
        $riwayat = $db->query("
            SELECT
                ph.jumlah,
                ph.jenis_zis,
                j.nomor_jurnal,
                j.tanggal,
                j.uraian,
                jd.nama AS nama_dana,
                jd.kode AS kode_dana,
                p.bulan, p.tahun
            FROM penghimpunan ph
            JOIN jurnal j      ON j.id  = ph.jurnal_id
            JOIN jenis_dana jd ON jd.id = j.jenis_dana_id
            JOIN periode p     ON p.id  = ph.periode_id
            WHERE ph.donatur_id = ? AND p.tahun = ?
            ORDER BY j.tanggal DESC, ph.id DESC
        ", [$this->donaturId, $tahun])->getResultArray();

        return view('donatur/portal', [
            'pageTitle'        => 'Portal Donatur',
            'donatur'          => $donatur,
            'tahun'            => $tahun,
            'tahunList'        => $tahunList,
            'statsAll'         => $statsAll,
            'statsTahun'       => $statsTahun,
            'trendBulanan'     => $trendBulanan,
            'ringkasanDana'    => $ringkasanDana,
            'breakdownBulanan' => $breakdownBulanan,
            'riwayat'          => $riwayat,
            'jenisZisLabels'   => \App\Models\PenghimpunanModel::JENIS_ZIS_LABELS,
        ]);
    }

    public function profil(): string
    {
        $db      = \Config\Database::connect();
        $donatur = $db->query("
            SELECT d.*, kc.nama AS kategori_nama, kp.nama AS kategori_parent
            FROM donatur d
            LEFT JOIN kategori_donatur kc ON kc.id = d.kategori_id
            LEFT JOIN kategori_donatur kp ON kp.id = kc.parent_id
            WHERE d.id = ?
        ", [$this->donaturId])->getRow();

        $success = session('success') ?? '';
        $error   = session('error')   ?? '';

        return view('donatur/profil', [
            'pageTitle' => 'Profil Saya',
            'donatur'   => $donatur,
            'success'   => $success,
            'error'     => $error,
        ]);
    }

    public function updateProfil()
    {
        $rules = [
            'no_hp' => 'permit_empty|max_length[20]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'alamat'=> 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $db = \Config\Database::connect();
        $db->table('donatur')->where('id', $this->donaturId)->update([
            'no_hp'  => trim($this->request->getPost('no_hp')  ?? '') ?: null,
            'email'  => trim($this->request->getPost('email')  ?? '') ?: null,
            'alamat' => trim($this->request->getPost('alamat') ?? '') ?: null,
        ]);

        return redirect()->to('donatur/profil')->with('success', 'Profil berhasil diperbarui.');
    }

    public function gantiPassword()
    {
        $newPass    = $this->request->getPost('password_baru')    ?? '';
        $konfirmasi = $this->request->getPost('password_konfirm') ?? '';
        $lamanya    = $this->request->getPost('password_lama')    ?? '';

        if (strlen($newPass) < 6) {
            return redirect()->to('donatur/profil')->with('error', 'Password baru minimal 6 karakter.');
        }
        if ($newPass !== $konfirmasi) {
            return redirect()->to('donatur/profil')->with('error', 'Konfirmasi password tidak cocok.');
        }

        $db   = \Config\Database::connect();
        $user = $db->table('users')
            ->where('id', session()->get('user_id'))
            ->get()->getRow();

        if (! $user || ! password_verify($lamanya, $user->password)) {
            return redirect()->to('donatur/profil')->with('error', 'Password lama tidak sesuai.');
        }

        $db->table('users')
            ->where('id', session()->get('user_id'))
            ->update(['password' => password_hash($newPass, PASSWORD_DEFAULT)]);

        return redirect()->to('donatur/profil')->with('success', 'Password berhasil diubah.');
    }
}
