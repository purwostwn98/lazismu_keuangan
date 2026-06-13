<?php

namespace App\Controllers;

use App\Models\PiutangModel;
use App\Models\JurnalModel;
use App\Models\JenisDanaModel;
use App\Models\PeriodeModel;
use App\Models\RekeningBankModel;

class Piutang extends BaseController
{
    private PiutangModel      $model;
    private JurnalModel       $jurnalModel;
    private JenisDanaModel    $jenisDanaModel;
    private PeriodeModel      $periodeModel;
    private RekeningBankModel $rekeningModel;

    public function __construct()
    {
        $this->model          = new PiutangModel();
        $this->jurnalModel    = new JurnalModel();
        $this->jenisDanaModel = new JenisDanaModel();
        $this->periodeModel   = new PeriodeModel();
        $this->rekeningModel  = new RekeningBankModel();
    }

    // ── Daftar Piutang ───────────────────────────────────────
    public function index(): string
    {
        $status = $this->request->getGet('status') ?? '';
        $jenis  = $this->request->getGet('jenis')  ?? '';
        $q      = $this->request->getGet('q')      ?? '';

        $daftar = $this->model->getDaftar(['status' => $status, 'jenis' => $jenis, 'q' => $q]);

        $totalPokok    = array_sum(array_column($daftar, 'jumlah_pokok'));
        $totalTerbayar = array_sum(array_column($daftar, 'jumlah_terbayar'));
        $totalSisa     = array_sum(array_column($daftar, 'sisa_piutang'));
        $countAktif    = count(array_filter($daftar, fn($r) => $r['status'] === 'aktif'));

        return view('piutang/index', [
            'pageTitle'  => 'Daftar Piutang',
            'daftar'     => $daftar,
            'filter'     => compact('status', 'jenis', 'q'),
            'jenisLabels'=> PiutangModel::$jenisLabels,
            'totalPokok' => $totalPokok,
            'totalTerbayar' => $totalTerbayar,
            'totalSisa'  => $totalSisa,
            'countAktif' => $countAktif,
        ]);
    }

    // ── Form Input Piutang Baru ──────────────────────────────
    public function input(): string
    {
        $penerimaList = $this->_getPenerimaList();
        $periodeAktif = $this->periodeModel->findByTanggal(date('Y-m-d'));

        return view('piutang/input', [
            'pageTitle'    => 'Input Piutang',
            'penerimaList' => $penerimaList,
            'periodeList'  => $this->periodeModel->getAktif(),
            'periodeAktif' => $periodeAktif,
            'jenisDanaList'=> $this->jenisDanaModel->getAll(),
            'rekeningList' => $this->rekeningModel->getAktif(),
            'jenisLabels'  => PiutangModel::$jenisLabels,
        ]);
    }

    // ── Simpan Piutang Baru ──────────────────────────────────
    public function store()
    {
        $rules = [
            'penerima_id'  => 'required|is_natural_no_zero',
            'jenis'        => 'required|in_list[qardul_hasan_amil,qardul_hasan_non_amil,penyaluran,talangan_amil,talangan_zakat,talangan_infaq]',
            'jumlah_pokok' => 'required|decimal|greater_than[0]',
            'tanggal_pinjam'=> 'required|valid_date',
            'periode_id'   => 'required|is_natural_no_zero',
            'jenis_dana_id'=> 'required|is_natural_no_zero',
            'rekening_id'  => 'required|is_natural_no_zero',
            'uraian'       => 'required|min_length[3]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodeId = (int) $this->request->getPost('periode_id');
        $periode   = $this->periodeModel->find($periodeId);
        if (! $periode || $periode['is_tutup']) {
            return redirect()->back()->withInput()->with('error', 'Periode sudah ditutup atau tidak ditemukan.');
        }

        $rekeningId = (int) $this->request->getPost('rekening_id');
        $rekening   = $this->rekeningModel->find($rekeningId);
        if (! $rekening) {
            return redirect()->back()->withInput()->with('error', 'Rekening tidak ditemukan.');
        }

        $jenis        = $this->request->getPost('jenis');
        $jumlah       = (float) str_replace(['.', ','], ['', '.'], $this->request->getPost('jumlah_pokok'));
        $jenisDanaId  = (int) $this->request->getPost('jenis_dana_id');
        $tanggal      = $this->request->getPost('tanggal_pinjam');
        $uraian       = $this->request->getPost('uraian');
        $keterangan   = trim($this->request->getPost('keterangan') ?? '') ?: null;
        $jatuhTempo   = $this->request->getPost('tanggal_jatuh_tempo') ?: null;
        $penerimaId   = (int) $this->request->getPost('penerima_id');

        // Cari akun piutang berdasarkan jenis
        $nomorAkun  = PiutangModel::$jenisAkunNomor[$jenis];
        $akunPiutang = \Config\Database::connect()
            ->table('akun')->where('nomor_akun', $nomorAkun)->get()->getRowArray();
        if (! $akunPiutang) {
            return redirect()->back()->withInput()->with('error', "Akun piutang {$nomorAkun} tidak ditemukan di master akun.");
        }

        $nomorPiutang = $this->model->generateNomor();
        $nomorJurnal  = $this->jurnalModel->generateNomor('piutang');
        $now          = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Buat jurnal: Debit Piutang / Kredit Kas-Bank
        $this->jurnalModel->insert([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal'         => $tanggal,
            'periode_id'      => $periodeId,
            'jenis_dana_id'   => $jenisDanaId,
            'jenis_transaksi' => 'piutang',
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
                'akun_id'          => (int) $akunPiutang['id'],
                'rekening_bank_id' => null,
                'uraian'           => $uraian,
                'debet'            => $jumlah,
                'kredit'           => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => (int) $rekening['akun_id'],
                'rekening_bank_id' => $rekeningId,
                'uraian'           => $uraian,
                'debet'            => 0,
                'kredit'           => $jumlah,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // 2. Insert piutang
        $this->model->insert([
            'nomor_piutang'       => $nomorPiutang,
            'penerima_id'         => $penerimaId,
            'jenis'               => $jenis,
            'jumlah_pokok'        => $jumlah,
            'tanggal_pinjam'      => $tanggal,
            'tanggal_jatuh_tempo' => $jatuhTempo,
            'jumlah_terbayar'     => 0,
            'sisa_piutang'        => $jumlah,
            'status'              => 'aktif',
            'jenis_dana_id'       => $jenisDanaId,
            'jurnal_id'           => $jurnalId,
            'keterangan'          => $keterangan,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan piutang.');
        }

        return redirect()->to('piutang/' . $this->model->getInsertID())
            ->with('success', "Piutang {$nomorPiutang} berhasil disimpan. Jurnal {$nomorJurnal} dibuat otomatis.");
    }

    // ── Detail Piutang ───────────────────────────────────────
    public function show(int $id): string
    {
        $detail = $this->model->getDetail($id);
        if (empty($detail)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Piutang #{$id} tidak ditemukan.");
        }

        $periodeAktif = $this->periodeModel->findByTanggal(date('Y-m-d'));

        return view('piutang/show', [
            'pageTitle'    => 'Detail Piutang — ' . $detail['piutang']['nomor_piutang'],
            'piutang'      => $detail['piutang'],
            'cicilan'      => $detail['cicilan'],
            'periodeList'  => $this->periodeModel->getAktif(),
            'periodeAktif' => $periodeAktif,
            'rekeningList' => $this->rekeningModel->getAktif(),
            'jenisLabels'  => PiutangModel::$jenisLabels,
        ]);
    }

    // ── Bayar Cicilan ────────────────────────────────────────
    public function bayar(int $id)
    {
        $piutang = $this->model->find($id);
        if (! $piutang) {
            return redirect()->to('piutang')->with('error', 'Piutang tidak ditemukan.');
        }
        if ($piutang['status'] !== 'aktif') {
            return redirect()->to('piutang/' . $id)->with('error', 'Piutang sudah lunas atau dihapus-buku.');
        }

        $rules = [
            'jumlah'     => 'required|decimal|greater_than[0]',
            'tanggal'    => 'required|valid_date',
            'periode_id' => 'required|is_natural_no_zero',
            'rekening_id'=> 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('piutang/' . $id)->withInput()->with('errors', $this->validator->getErrors());
        }

        $jumlah     = (float) str_replace(['.', ','], ['', '.'], $this->request->getPost('jumlah'));
        $periodeId  = (int) $this->request->getPost('periode_id');
        $rekeningId = (int) $this->request->getPost('rekening_id');
        $tanggal    = $this->request->getPost('tanggal');
        $keterangan = trim($this->request->getPost('keterangan') ?? '') ?: null;

        if ($jumlah > (float) $piutang['sisa_piutang'] + 0.01) {
            return redirect()->to('piutang/' . $id)
                ->with('error', sprintf(
                    'Jumlah bayar (Rp %s) melebihi sisa piutang (Rp %s).',
                    number_format($jumlah, 0, ',', '.'),
                    number_format($piutang['sisa_piutang'], 0, ',', '.')
                ));
        }

        $periode  = $this->periodeModel->find($periodeId);
        if (! $periode || $periode['is_tutup']) {
            return redirect()->to('piutang/' . $id)->with('error', 'Periode sudah ditutup atau tidak ditemukan.');
        }

        $rekening = $this->rekeningModel->find($rekeningId);
        if (! $rekening) {
            return redirect()->to('piutang/' . $id)->with('error', 'Rekening tidak ditemukan.');
        }

        // Cari akun piutang berdasarkan jenis
        $nomorAkun   = PiutangModel::$jenisAkunNomor[$piutang['jenis']];
        $akunPiutang = \Config\Database::connect()
            ->table('akun')->where('nomor_akun', $nomorAkun)->get()->getRowArray();

        $nomorJurnal    = $this->jurnalModel->generateNomor('piutang');
        $uraianBayar    = 'Pembayaran ' . PiutangModel::$jenisLabels[$piutang['jenis']];
        $now            = date('Y-m-d H:i:s');
        $sisaBaru       = (float) $piutang['sisa_piutang'] - $jumlah;
        $terbayarBaru   = (float) $piutang['jumlah_terbayar'] + $jumlah;
        $statusBaru     = $sisaBaru <= 0.01 ? 'lunas' : 'aktif';

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Jurnal: Debit Kas/Bank / Kredit Piutang
        $this->jurnalModel->insert([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal'         => $tanggal,
            'periode_id'      => $periodeId,
            'jenis_dana_id'   => (int) $piutang['jenis_dana_id'],
            'jenis_transaksi' => 'piutang',
            'uraian'          => $uraianBayar . ' — ' . $keterangan,
            'keterangan'      => $keterangan,
            'total_debet'     => $jumlah,
            'total_kredit'    => $jumlah,
            'created_by'      => null,
        ]);
        $jurnalId = $this->jurnalModel->getInsertID();

        $db->table('jurnal_detail')->insertBatch([
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => (int) $rekening['akun_id'],
                'rekening_bank_id' => $rekeningId,
                'uraian'           => $uraianBayar,
                'debet'            => $jumlah,
                'kredit'           => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'jurnal_id'        => $jurnalId,
                'akun_id'          => (int) $akunPiutang['id'],
                'rekening_bank_id' => null,
                'uraian'           => $uraianBayar,
                'debet'            => 0,
                'kredit'           => $jumlah,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);

        // 2. Insert cicilan
        $db->table('piutang_cicilan')->insert([
            'piutang_id' => $id,
            'tanggal'    => $tanggal,
            'jumlah'     => $jumlah,
            'jurnal_id'  => $jurnalId,
            'keterangan' => $keterangan,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Update piutang
        $this->model->update($id, [
            'jumlah_terbayar' => $terbayarBaru,
            'sisa_piutang'    => max(0, $sisaBaru),
            'status'          => $statusBaru,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('piutang/' . $id)->with('error', 'Gagal menyimpan pembayaran.');
        }

        $msg = $statusBaru === 'lunas'
            ? "Pembayaran berhasil. Piutang {$piutang['nomor_piutang']} sudah LUNAS."
            : sprintf('Pembayaran Rp %s berhasil. Sisa piutang: Rp %s.',
                number_format($jumlah, 0, ',', '.'),
                number_format(max(0, $sisaBaru), 0, ',', '.'));

        return redirect()->to('piutang/' . $id)->with('success', $msg);
    }

    // ── Hapus Buku (write-off) ───────────────────────────────
    public function hapusBuku(int $id)
    {
        $piutang = $this->model->find($id);
        if (! $piutang || $piutang['status'] !== 'aktif') {
            return redirect()->to('piutang')->with('error', 'Piutang tidak ditemukan atau bukan berstatus aktif.');
        }

        $this->model->update($id, ['status' => 'hapus_buku']);

        return redirect()->to('piutang/' . $id)
            ->with('success', "Piutang {$piutang['nomor_piutang']} berhasil dihapus buku (write-off).");
    }

    // ── Hapus Piutang ────────────────────────────────────────
    public function delete(int $id)
    {
        $piutang = $this->model->find($id);
        if (! $piutang) {
            return redirect()->to('piutang')->with('error', 'Piutang tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $hasCicilan = $db->table('piutang_cicilan')->where('piutang_id', $id)->countAllResults();
        if ($hasCicilan > 0) {
            return redirect()->to('piutang')->with('error', 'Piutang tidak dapat dihapus karena sudah ada pembayaran cicilan.');
        }

        $jurnalId = $piutang['jurnal_id'] ?? null;

        if ($jurnalId) {
            $jurnal  = $this->jurnalModel->find($jurnalId);
            $periode = $jurnal ? $this->periodeModel->find($jurnal['periode_id']) : null;
            if ($periode && $periode['is_tutup']) {
                return redirect()->to('piutang')->with('error', 'Periode jurnal sudah ditutup. Piutang tidak dapat dihapus.');
            }
        }

        // Hapus piutang dulu agar FK piutang.jurnal_id tidak menghalangi hapus jurnal
        $this->model->delete($id);

        // Baru hapus jurnal (jurnal_detail cascade otomatis)
        if ($jurnalId) {
            $db->table('jurnal')->where('id', $jurnalId)->delete();
        }

        return redirect()->to('piutang')->with('success', "Piutang {$piutang['nomor_piutang']} berhasil dihapus.");
    }

    // ── Helper ───────────────────────────────────────────────
    private function _getPenerimaList(): array
    {
        return \Config\Database::connect()
            ->table('penerima_manfaat')
            ->orderBy('nama', 'ASC')
            ->get()->getResultArray();
    }
}
