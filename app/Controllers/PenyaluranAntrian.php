<?php

namespace App\Controllers;

use App\Models\PenyaluranAntrianModel;
use App\Models\JurnalModel;
use App\Models\PeriodeModel;
use App\Models\AkunModel;
use App\Models\RekeningBankModel;
use App\Models\PenerimaManfaatModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class PenyaluranAntrian extends BaseController
{
    private PenyaluranAntrianModel $model;
    private JurnalModel            $jurnalModel;
    private PeriodeModel           $periodeModel;
    private AkunModel              $akunModel;
    private RekeningBankModel      $rekeningModel;
    private PenerimaManfaatModel   $penerimaModel;

    public function __construct()
    {
        $this->model        = new PenyaluranAntrianModel();
        $this->jurnalModel  = new JurnalModel();
        $this->periodeModel = new PeriodeModel();
        $this->akunModel      = new AkunModel();
        $this->rekeningModel  = new RekeningBankModel();
        $this->penerimaModel  = new PenerimaManfaatModel();
    }

    // ── Daftar Antrian ────────────────────────────────────────
    public function index(): string
    {
        $status  = $this->request->getGet('status') ?? '';
        $filter  = in_array($status, ['pending', 'verified', 'rejected']) ? ['status' => $status] : [];
        $antrian = $this->model->getDaftar($filter);
        $counts  = $this->model->countByStatus();

        return view('penyaluran/antrian/index', [
            'pageTitle'    => 'Antrian Penyaluran',
            'breadcrumb'   => ['Penyaluran' => null, 'Antrian' => null],
            'antrian'      => $antrian,
            'statusFilter' => $status,
            'counts'       => $counts,
        ]);
    }

    // ── Detail + Form Verifikasi ──────────────────────────────
    public function show(int $id): string
    {
        $record = $this->model->getDetail($id);
        if (!$record) {
            throw new PageNotFoundException("Antrian #{$id} tidak ditemukan.");
        }

        return view('penyaluran/antrian/show', [
            'pageTitle'      => 'Detail Antrian #' . $id,
            'breadcrumb'     => ['Penyaluran' => null, 'Antrian' => base_url('penyaluran/antrian'), 'Detail' => null],
            'record'         => $record,
            'periodeList'    => $this->periodeModel->getAktif(),
            'periodeAktif'   => $this->periodeModel->findByTanggal($record['tanggal']),
            'akunPenyaluran' => $this->akunModel->getByTipe('penyaluran'),
            'rekening'       => $this->rekeningModel->getAktif(),
            'penerima'       => $this->penerimaModel->getAll(),
        ]);
    }

    // ── Proses Verifikasi ─────────────────────────────────────
    public function verifikasi(int $id)
    {
        $record = $this->model->find($id);
        if (!$record || $record['status'] !== 'pending') {
            return redirect()->to('penyaluran/antrian')
                ->with('error', 'Data tidak ditemukan atau sudah diproses.');
        }

        if (!$this->validate([
            'periode_id'    => 'required|is_natural_no_zero',
            'akun_debet_id' => 'required|is_natural_no_zero',
            'rekening_id'   => 'required|is_natural_no_zero',
        ])) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $periodeId   = (int) $this->request->getPost('periode_id');
        $akunDebetId = (int) $this->request->getPost('akun_debet_id');
        $rekeningId  = (int) $this->request->getPost('rekening_id');
        $penerimaId  = ((int) ($this->request->getPost('penerima_id') ?? 0)) ?: null;
        $catatan     = trim($this->request->getPost('catatan') ?? '') ?: null;

        $periode = $this->periodeModel->find($periodeId);
        if (!$periode || $periode['is_tutup']) {
            return redirect()->back()->withInput()
                ->with('error', 'Periode sudah ditutup atau tidak ditemukan.');
        }

        $rekening = $this->rekeningModel->find($rekeningId);
        if (!$rekening) {
            return redirect()->back()->withInput()
                ->with('error', 'Rekening bank tidak ditemukan.');
        }

        $jumlah      = (float) $record['jumlah'];
        $nomorJurnal = $this->jurnalModel->generateNomor('penyaluran');
        $now         = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->transStart();

        $this->jurnalModel->insert([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal'         => $record['tanggal'],
            'periode_id'      => $periodeId,
            'jenis_dana_id'   => $record['jenis_dana_id'],
            'jenis_transaksi' => 'penyaluran',
            'uraian'          => $record['uraian'],
            'keterangan'      => $record['keterangan'],
            'penerima_id'     => $penerimaId,
            'program_id'      => null,
            'total_debet'     => $jumlah,
            'total_kredit'    => $jumlah,
            'created_by'      => null,
        ]);
        $jurnalId = $this->jurnalModel->getInsertID();

        $db->table('jurnal_detail')->insertBatch([
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => $akunDebetId,
                'rekening_bank_id' => null,
                'uraian'           => $record['uraian'],
                'debet'            => $jumlah,
                'kredit'           => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => (int) $rekening['akun_id'],
                'rekening_bank_id' => $rekeningId,
                'uraian'           => $record['uraian'],
                'debet'            => 0,
                'kredit'           => $jumlah,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        $this->model->update($id, [
            'status'      => 'verified',
            'jurnal_id'   => $jurnalId,
            'penerima_id' => $penerimaId,
            'catatan'     => $catatan,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()
                ->with('error', 'Gagal memproses verifikasi. Silakan coba kembali.');
        }

        return redirect()->to('penyaluran/antrian')
            ->with('success', "Antrian #{$id} berhasil diverifikasi. Jurnal <strong>{$nomorJurnal}</strong> telah dibuat.");
    }

    // ── Tolak ─────────────────────────────────────────────────
    public function tolak(int $id)
    {
        $record = $this->model->find($id);
        if (!$record || $record['status'] !== 'pending') {
            return redirect()->to('penyaluran/antrian')
                ->with('error', 'Data tidak ditemukan atau sudah diproses.');
        }

        $catatan = trim($this->request->getPost('catatan') ?? '') ?: 'Ditolak oleh admin';

        $this->model->update($id, [
            'status'  => 'rejected',
            'catatan' => $catatan,
        ]);

        return redirect()->to('penyaluran/antrian')
            ->with('success', "Antrian #{$id} telah ditolak.");
    }

    // ── Hapus (hanya status rejected) ────────────────────────────
    public function hapus(int $id)
    {
        $record = $this->model->find($id);
        if (!$record || $record['status'] !== 'rejected') {
            return redirect()->to('penyaluran/antrian')
                ->with('error', 'Data tidak ditemukan atau hanya antrian yang ditolak yang dapat dihapus.');
        }

        $this->model->delete($id);

        return redirect()->to('penyaluran/antrian?status=rejected')
            ->with('success', "Antrian #{$id} berhasil dihapus.");
    }

    // ── API: Terima Data dari Sistem Eksternal ─────────────────
    // Dilindungi oleh BasicAuthFilter — tidak perlu cek auth manual di sini.
    public function apiTerima(): \CodeIgniter\HTTP\ResponseInterface
    {
        $body = $this->request->getJSON(true) ?? $this->request->getPost();

        // Manual validation for flexibility with JSON body
        $errors = [];
        if (empty($body['tanggal']) || !strtotime($body['tanggal'])) {
            $errors['tanggal'] = 'Tanggal wajib diisi dan harus valid (YYYY-MM-DD).';
        }
        if (empty($body['uraian'])) {
            $errors['uraian'] = 'Uraian wajib diisi.';
        }
        if (!isset($body['jumlah']) || (float)$body['jumlah'] <= 0) {
            $errors['jumlah'] = 'Jumlah harus lebih dari 0.';
        }

        if ($errors) {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'errors' => $errors]);
        }

        $tipePenerima = $body['tipe_penerima'] ?? null;
        if (!in_array($tipePenerima, ['individu', 'lembaga'], true)) {
            $tipePenerima = null;
        }

        $db = \Config\Database::connect();

        // ── 1. Resolusi nomor akun, akun_debet_id, dan jenis dana dari mapping ──
        $idPenyaluranEksternal = isset($body['id_penyaluran_eksternal']) ? (int) $body['id_penyaluran_eksternal'] : null;
        $nomorAkunPenyaluran   = null;
        $akunDebetId           = null;
        $jenisDanaId           = isset($body['jenis_dana_id']) ? (int) $body['jenis_dana_id'] : null;

        if ($idPenyaluranEksternal && !empty($body['sumber'])) {
            $mapping = $db->table('mapping_akun_penyaluran')
                ->where('id_eksternal',    $idPenyaluranEksternal)
                ->where('sumber_aplikasi', $body['sumber'])
                ->get()->getRowArray();

            if ($mapping) {
                $nomorAkunPenyaluran = $mapping['nomor_akun'];
                if (!empty($mapping['id_dana'])) {
                    $jenisDanaId = (int) $mapping['id_dana'];
                }
                // Cari id akun berdasarkan nomor_akun
                $akun = $db->table('akun')
                    ->where('nomor_akun', $mapping['nomor_akun'])
                    ->get()->getRowArray();
                $akunDebetId = $akun['id'] ?? null;
            }
        }

        if (!$jenisDanaId) {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'errors' => [
                    'jenis_dana_id' => 'Jenis dana tidak dapat ditentukan. Kirim jenis_dana_id atau pastikan id_penyaluran_eksternal terdaftar di mapping.',
                ]]);
        }

        // ── 2. Resolusi penerima_id dari nik_nomor_lembaga ──────────────────────
        $nikNomorLembaga = isset($body['nik_nomor_lembaga']) ? trim($body['nik_nomor_lembaga']) : null;
        $penerimaId      = null;

        if ($nikNomorLembaga) {
            $penerima = $db->table('penerima_manfaat')
                ->where('kode', $nikNomorLembaga)
                ->get()->getRowArray();

            if ($penerima) {
                $penerimaId = $penerima['id'];
            } else {
                // Auto-create penerima baru dari data yang tersedia
                $now        = date('Y-m-d H:i:s');
                $penerimaId = $this->penerimaModel->insert([
                    'kode'       => $nikNomorLembaga,
                    'nama'       => $body['nama_penerima'] ?? $nikNomorLembaga,
                    'tipe'       => $tipePenerima ?? 'individu',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $id = $this->model->insert([
            'sumber'                  => $body['sumber'] ?? null,
            'ref_eksternal'           => $body['ref_eksternal'] ?? null,
            'tanggal'                 => date('Y-m-d', strtotime($body['tanggal'])),
            'jenis_dana_id'           => $jenisDanaId,
            'program_nama'            => $body['program_nama'] ?? null,
            'program_ext_id'          => isset($body['program_ext_id']) ? (int) $body['program_ext_id'] : null,
            'nama_penerima'           => $body['nama_penerima'] ?? null,
            'tipe_penerima'           => $tipePenerima,
            'nik_nomor_lembaga'       => $nikNomorLembaga,
            'id_penyaluran_eksternal' => $idPenyaluranEksternal,
            'nomor_akun_penyaluran'   => $nomorAkunPenyaluran,
            'akun_debet_id'           => $akunDebetId,
            'penerima_id'             => $penerimaId,
            'jumlah'                  => (float) $body['jumlah'],
            'uraian'                  => $body['uraian'],
            'keterangan'              => $body['keterangan'] ?? null,
            'status'                  => 'pending',
        ]);

        return $this->response->setStatusCode(201)
            ->setJSON([
                'success' => true,
                'id'      => $id,
                'message' => 'Data antrian penyaluran berhasil diterima dan menunggu verifikasi admin.',
            ]);
    }
}
