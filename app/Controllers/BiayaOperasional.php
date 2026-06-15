<?php

namespace App\Controllers;

use App\Models\JurnalModel;
use App\Models\JenisDanaModel;
use App\Models\PeriodeModel;
use App\Models\AkunModel;
use App\Models\RekeningBankModel;

class BiayaOperasional extends BaseController
{
    private JurnalModel       $jurnalModel;
    private JenisDanaModel    $jenisDanaModel;
    private PeriodeModel      $periodeModel;
    private AkunModel         $akunModel;
    private RekeningBankModel $rekeningModel;

    public function __construct()
    {
        $this->jurnalModel    = new JurnalModel();
        $this->jenisDanaModel = new JenisDanaModel();
        $this->periodeModel   = new PeriodeModel();
        $this->akunModel      = new AkunModel();
        $this->rekeningModel  = new RekeningBankModel();
    }

    // ── Daftar Biaya Operasional ──────────────────────────────
    public function index(): string
    {
        $periodeId = (int)($this->request->getGet('periode') ?? 0);
        $jenisDana = (int)($this->request->getGet('dana')    ?? 0);
        $q         = trim($this->request->getGet('q')        ?? '');

        $db    = \Config\Database::connect();
        $where = "WHERE j.jenis_transaksi = 'biaya'";
        $params = [];

        if ($periodeId > 0) {
            $where .= ' AND j.periode_id = ?';
            $params[] = $periodeId;
        }
        if ($jenisDana > 0) {
            $where .= ' AND j.jenis_dana_id = ?';
            $params[] = $jenisDana;
        }
        if ($q !== '') {
            $where .= ' AND (j.nomor_jurnal LIKE ? OR j.uraian LIKE ? OR bk.nama_kegiatan LIKE ?)';
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }

        $rows = $db->query("
            SELECT j.id, j.nomor_jurnal, j.tanggal, j.uraian, j.keterangan,
                   j.total_debet,
                   dana.nama AS nama_dana, dana.kode AS kode_dana,
                   p.nama AS nama_periode, p.is_tutup,
                   rb.nama AS nama_rekening, rb.bank AS nama_bank,
                   bk.nama_kegiatan, bk.lokasi
            FROM jurnal j
            JOIN jenis_dana dana ON dana.id = j.jenis_dana_id
            JOIN periode p       ON p.id    = j.periode_id
            LEFT JOIN (
                SELECT jurnal_id, MIN(rekening_bank_id) AS rekening_bank_id
                FROM jurnal_detail
                WHERE kredit > 0
                GROUP BY jurnal_id
            ) krd ON krd.jurnal_id = j.id
            LEFT JOIN rekening_bank rb ON rb.id = krd.rekening_bank_id
            LEFT JOIN biaya_kegiatan bk ON bk.jurnal_id = j.id
            {$where}
            ORDER BY j.tanggal DESC, j.id DESC
        ", $params)->getResultArray();

        $totalBiaya       = array_sum(array_column($rows, 'total_debet'));
        $jumlahTransaksi  = count($rows);

        return view('biaya/index', [
            'pageTitle'       => 'Biaya Operasional',
            'biayaList'       => $rows,
            'totalBiaya'      => $totalBiaya,
            'jumlahTransaksi' => $jumlahTransaksi,
            'periodeList'     => $this->periodeModel->orderBy('tahun DESC, bulan DESC')->findAll(),
            'jenisDanaList'   => $this->jenisDanaModel->getAll(),
            'filter'          => ['periode' => $periodeId, 'dana' => $jenisDana, 'q' => $q],
        ]);
    }

    // ── Form Input ────────────────────────────────────────────
    public function input(): string
    {
        $periodeAktif = $this->periodeModel->findByTanggal(date('Y-m-d'));

        return view('biaya/input', [
            'pageTitle'    => 'Input Biaya Operasional',
            'periodeList'  => $this->periodeModel->getAktif(),
            'periodeAktif' => $periodeAktif,
            'rekeningList' => $this->rekeningModel->getAllWithRelasi(),
            'akunList'     => $this->akunModel
                ->where('is_header', 0)
                ->orderBy('nomor_akun', 'ASC')
                ->findAll(),
        ]);
    }

    // ── Simpan ────────────────────────────────────────────────
    public function store()
    {
        $rules = [
            'tanggal'     => 'required|valid_date',
            'periode_id'  => 'required|is_natural_no_zero',
            'rekening_id' => 'required|is_natural_no_zero',
            'uraian'      => 'required|min_length[3]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodeId = (int)$this->request->getPost('periode_id');
        $periode   = $this->periodeModel->find($periodeId);
        if (! $periode || $periode['is_tutup']) {
            return redirect()->back()->withInput()
                ->with('error', 'Periode sudah ditutup atau tidak ditemukan.');
        }

        $rekeningId = (int)$this->request->getPost('rekening_id');
        $rekening   = \Config\Database::connect()
            ->table('rekening_bank rb')
            ->select('rb.*, jd.nama AS nama_dana')
            ->join('jenis_dana jd', 'jd.id = rb.jenis_dana_id', 'left')
            ->where('rb.id', $rekeningId)
            ->get()->getRowArray();

        if (! $rekening) {
            return redirect()->back()->withInput()->with('error', 'Rekening tidak ditemukan.');
        }

        $akunIds    = (array)($this->request->getPost('akun_id')    ?? []);
        $uraianDets = (array)($this->request->getPost('uraian_det') ?? []);
        $jumlahArr  = (array)($this->request->getPost('jumlah')     ?? []);

        $details = [];
        $total   = 0.0;
        $now     = date('Y-m-d H:i:s');

        foreach ($akunIds as $i => $akunId) {
            $jml = (float)str_replace(['.', ','], ['', '.'], $jumlahArr[$i] ?? '0');
            if ((int)$akunId === 0 || $jml <= 0) continue;
            $total += $jml;
            $details[] = [
                'akun_id'          => (int)$akunId,
                'rekening_bank_id' => null,
                'uraian'           => ($uraianDets[$i] ?? '') ?: null,
                'debet'            => $jml,
                'kredit'           => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (empty($details)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal 1 baris pengeluaran diperlukan.');
        }

        // Baris kredit dari rekening
        $details[] = [
            'akun_id'          => (int)$rekening['akun_id'],
            'rekening_bank_id' => $rekeningId,
            'uraian'           => 'Sumber Dana: ' . $rekening['nama'],
            'debet'            => 0,
            'kredit'           => $total,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];

        $nomorJurnal = $this->jurnalModel->generateNomor('biaya');

        // Detail kegiatan
        $namaKegiatan   = trim($this->request->getPost('nama_kegiatan') ?? '');
        $lokasi         = trim($this->request->getPost('lokasi') ?? '');
        $tglBerangkat   = $this->request->getPost('tgl_berangkat');
        $tglKembali     = $this->request->getPost('tgl_kembali');
        $uraianKegiatan = trim($this->request->getPost('uraian_kegiatan') ?? '');

        $tglBerangkat = $tglBerangkat ? date('Y-m-d H:i:s', strtotime($tglBerangkat)) : null;
        $tglKembali   = $tglKembali   ? date('Y-m-d H:i:s', strtotime($tglKembali))   : null;

        $db = \Config\Database::connect();
        $db->transStart();

        $this->jurnalModel->insert([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal'         => $this->request->getPost('tanggal'),
            'periode_id'      => $periodeId,
            'jenis_dana_id'   => (int)$rekening['jenis_dana_id'],
            'jenis_transaksi' => 'biaya',
            'uraian'          => $this->request->getPost('uraian'),
            'keterangan'      => trim($this->request->getPost('keterangan') ?? '') ?: null,
            'total_debet'     => $total,
            'total_kredit'    => $total,
            'created_by'      => session()->get('user_id') ?: null,
        ]);
        $jurnalId = $this->jurnalModel->getInsertID();

        foreach ($details as &$det) {
            $det['jurnal_id'] = $jurnalId;
        }
        $db->table('jurnal_detail')->insertBatch($details);

        // Simpan detail kegiatan (selalu dibuat agar dapat diedit nanti)
        $db->table('biaya_kegiatan')->insert([
            'jurnal_id'       => $jurnalId,
            'nama_kegiatan'   => $namaKegiatan   ?: null,
            'lokasi'          => $lokasi         ?: null,
            'tgl_berangkat'   => $tglBerangkat,
            'tgl_kembali'     => $tglKembali,
            'uraian_kegiatan' => $uraianKegiatan ?: null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        // Simpan catatan penerima (opsional)
        $penerimaNames    = (array)($this->request->getPost('penerima_nama')    ?? []);
        $penerimaNominals = (array)($this->request->getPost('penerima_nominal') ?? []);
        $penerimaRows     = [];
        foreach ($penerimaNames as $i => $nama) {
            $nama    = trim($nama);
            $nominal = (float)str_replace(['.', ','], ['', '.'], $penerimaNominals[$i] ?? '0');
            if ($nama === '') continue;
            $penerimaRows[] = [
                'jurnal_id'  => $jurnalId,
                'urutan'     => count($penerimaRows) + 1,
                'nama'       => $nama,
                'nominal'    => $nominal,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (! empty($penerimaRows)) {
            $db->table('biaya_penerima')->insertBatch($penerimaRows);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan biaya operasional. Silakan coba lagi.');
        }

        return redirect()->to('biaya/' . $jurnalId)
            ->with('success', "Biaya operasional {$nomorJurnal} berhasil disimpan.");
    }

    // ── Form Edit ─────────────────────────────────────────────
    public function edit(int $id)
    {
        $data = $this->jurnalModel->getBiayaWithDetail($id);
        if (empty($data) || ($data['header']['jenis_transaksi'] ?? '') !== 'biaya') {
            return redirect()->to('biaya')->with('error', 'Data tidak ditemukan.');
        }

        $periode = $this->periodeModel->find($data['header']['periode_id']);
        if ($periode && $periode['is_tutup']) {
            return redirect()->to('biaya/' . $id)
                ->with('error', 'Periode sudah ditutup. Data tidak dapat diedit.');
        }

        $db       = \Config\Database::connect();
        $kegiatan = $db->table('biaya_kegiatan')
            ->where('jurnal_id', $id)->get()->getRowArray() ?? [];
        $penerima = $db->table('biaya_penerima')
            ->where('jurnal_id', $id)->orderBy('urutan', 'ASC')->get()->getResultArray();

        $debetRows = array_values(array_filter(
            $data['details'], fn($d) => (float)$d['debet'] > 0
        ));
        $kreditRow = array_values(array_filter(
            $data['details'], fn($d) => (float)$d['kredit'] > 0
        ))[0] ?? null;

        return view('biaya/edit', [
            'pageTitle'       => 'Edit Biaya Operasional',
            'header'          => $data['header'],
            'debetRows'       => $debetRows,
            'kreditRow'       => $kreditRow,
            'kegiatan'        => $kegiatan,
            'penerima'        => $penerima,
            'periodeList'     => $this->periodeModel->getAktif(),
            'rekeningList'    => $this->rekeningModel->getAllWithRelasi(),
            'akunList'        => $this->akunModel
                ->where('is_header', 0)->orderBy('nomor_akun', 'ASC')->findAll(),
        ]);
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $id)
    {
        $jurnal = $this->jurnalModel->find($id);
        if (! $jurnal || $jurnal['jenis_transaksi'] !== 'biaya') {
            return redirect()->to('biaya')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'tanggal'     => 'required|valid_date',
            'periode_id'  => 'required|is_natural_no_zero',
            'rekening_id' => 'required|is_natural_no_zero',
            'uraian'      => 'required|min_length[3]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodeId = (int)$this->request->getPost('periode_id');
        $periode   = $this->periodeModel->find($periodeId);
        if (! $periode || $periode['is_tutup']) {
            return redirect()->back()->withInput()
                ->with('error', 'Periode sudah ditutup atau tidak ditemukan.');
        }

        $rekeningId = (int)$this->request->getPost('rekening_id');
        $rekening   = \Config\Database::connect()
            ->table('rekening_bank rb')
            ->select('rb.*, jd.nama AS nama_dana')
            ->join('jenis_dana jd', 'jd.id = rb.jenis_dana_id', 'left')
            ->where('rb.id', $rekeningId)
            ->get()->getRowArray();

        if (! $rekening) {
            return redirect()->back()->withInput()->with('error', 'Rekening tidak ditemukan.');
        }

        $akunIds    = (array)($this->request->getPost('akun_id')    ?? []);
        $uraianDets = (array)($this->request->getPost('uraian_det') ?? []);
        $jumlahArr  = (array)($this->request->getPost('jumlah')     ?? []);

        $details = [];
        $total   = 0.0;
        $now     = date('Y-m-d H:i:s');

        foreach ($akunIds as $i => $akunId) {
            $jml = (float)str_replace(['.', ','], ['', '.'], $jumlahArr[$i] ?? '0');
            if ((int)$akunId === 0 || $jml <= 0) continue;
            $total += $jml;
            $details[] = [
                'jurnal_id'        => $id,
                'akun_id'          => (int)$akunId,
                'rekening_bank_id' => null,
                'uraian'           => ($uraianDets[$i] ?? '') ?: null,
                'debet'            => $jml,
                'kredit'           => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (empty($details)) {
            return redirect()->back()->withInput()
                ->with('error', 'Minimal 1 baris pengeluaran diperlukan.');
        }

        $details[] = [
            'jurnal_id'        => $id,
            'akun_id'          => (int)$rekening['akun_id'],
            'rekening_bank_id' => $rekeningId,
            'uraian'           => 'Sumber Dana: ' . $rekening['nama'],
            'debet'            => 0,
            'kredit'           => $total,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];

        $namaKegiatan   = trim($this->request->getPost('nama_kegiatan')   ?? '');
        $lokasi         = trim($this->request->getPost('lokasi')          ?? '');
        $tglBerangkat   = $this->request->getPost('tgl_berangkat');
        $tglKembali     = $this->request->getPost('tgl_kembali');
        $uraianKegiatan = trim($this->request->getPost('uraian_kegiatan') ?? '');
        $tglBerangkat   = $tglBerangkat ? date('Y-m-d H:i:s', strtotime($tglBerangkat)) : null;
        $tglKembali     = $tglKembali   ? date('Y-m-d H:i:s', strtotime($tglKembali))   : null;

        $penerimaNames    = (array)($this->request->getPost('penerima_nama')    ?? []);
        $penerimaNominals = (array)($this->request->getPost('penerima_nominal') ?? []);
        $penerimaRows     = [];
        foreach ($penerimaNames as $i => $nama) {
            $nama    = trim($nama);
            $nominal = (float)str_replace(['.', ','], ['', '.'], $penerimaNominals[$i] ?? '0');
            if ($nama === '') continue;
            $penerimaRows[] = [
                'jurnal_id'  => $id,
                'urutan'     => count($penerimaRows) + 1,
                'nama'       => $nama,
                'nominal'    => $nominal,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->jurnalModel->update($id, [
            'tanggal'       => $this->request->getPost('tanggal'),
            'periode_id'    => $periodeId,
            'jenis_dana_id' => (int)$rekening['jenis_dana_id'],
            'uraian'        => $this->request->getPost('uraian'),
            'keterangan'    => trim($this->request->getPost('keterangan') ?? '') ?: null,
            'total_debet'   => $total,
            'total_kredit'  => $total,
            'updated_at'    => $now,
        ]);

        $db->table('jurnal_detail')->where('jurnal_id', $id)->delete();
        $db->table('jurnal_detail')->insertBatch($details);

        $db->table('biaya_kegiatan')->where('jurnal_id', $id)->delete();
        $db->table('biaya_kegiatan')->insert([
            'jurnal_id'       => $id,
            'nama_kegiatan'   => $namaKegiatan   ?: null,
            'lokasi'          => $lokasi         ?: null,
            'tgl_berangkat'   => $tglBerangkat,
            'tgl_kembali'     => $tglKembali,
            'uraian_kegiatan' => $uraianKegiatan ?: null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $db->table('biaya_penerima')->where('jurnal_id', $id)->delete();
        if (! empty($penerimaRows)) {
            $db->table('biaya_penerima')->insertBatch($penerimaRows);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan perubahan. Silakan coba lagi.');
        }

        return redirect()->to('biaya/' . $id)
            ->with('success', "Biaya {$jurnal['nomor_jurnal']} berhasil diperbarui.");
    }

    // ── Detail ────────────────────────────────────────────────
    public function show(int $id)
    {
        $data = $this->jurnalModel->getBiayaWithDetail($id);
        if (empty($data)) {
            return redirect()->to('biaya')->with('error', 'Data tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $kegiatan  = $db->table('biaya_kegiatan')
            ->where('jurnal_id', $id)
            ->get()->getRowArray() ?? [];

        $penerima  = $db->table('biaya_penerima')
            ->where('jurnal_id', $id)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        return view('biaya/show', [
            'pageTitle' => 'Detail Biaya Operasional',
            'header'    => $data['header'],
            'details'   => $data['details'],
            'kegiatan'  => $kegiatan,
            'penerima'  => $penerima,
        ]);
    }

    // ── Cetak PDF ─────────────────────────────────────────────
    public function cetak(int $id)
    {
        $data = $this->jurnalModel->getBiayaWithDetail($id);
        if (empty($data)) {
            return redirect()->to('biaya')->with('error', 'Data tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        $kegiatan = $db->table('biaya_kegiatan')
            ->where('jurnal_id', $id)
            ->get()->getRowArray() ?? [];

        $penerima = $db->table('biaya_penerima')
            ->where('jurnal_id', $id)
            ->orderBy('urutan', 'ASC')
            ->get()->getResultArray();

        return view('biaya/cetak', [
            'header'   => $data['header'],
            'details'  => $data['details'],
            'kegiatan' => $kegiatan,
            'penerima' => $penerima,
        ]);
    }

    // ── Upload Bukti ──────────────────────────────────────────
    public function uploadBukti(int $id)
    {
        $jurnal = $this->jurnalModel->find($id);
        if (! $jurnal || $jurnal['jenis_transaksi'] !== 'biaya') {
            return redirect()->to('biaya')->with('error', 'Data tidak ditemukan.');
        }

        $file = $this->request->getFile('file_bukti');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('biaya/' . $id)
                ->with('error', 'File tidak valid atau tidak ada file yang diunggah.');
        }

        if (strtolower($file->getExtension()) !== 'pdf') {
            return redirect()->to('biaya/' . $id)
                ->with('error', 'Hanya file berformat PDF yang diperbolehkan.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return redirect()->to('biaya/' . $id)
                ->with('error', 'Ukuran file maksimal 5 MB.');
        }

        $uploadDir = FCPATH . 'uploads/biaya/';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $db       = \Config\Database::connect();
        $kegiatan = $db->table('biaya_kegiatan')->where('jurnal_id', $id)->get()->getRowArray();

        // Hapus file lama jika ada
        if ($kegiatan && ($kegiatan['file_bukti'] ?? '')) {
            $oldPath = FCPATH . $kegiatan['file_bukti'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $newName  = 'bukti_' . $id . '_' . time() . '.pdf';
        $file->move($uploadDir, $newName);
        $filePath = 'uploads/biaya/' . $newName;
        $now      = date('Y-m-d H:i:s');

        if ($kegiatan) {
            $db->table('biaya_kegiatan')
                ->where('jurnal_id', $id)
                ->update(['file_bukti' => $filePath, 'updated_at' => $now]);
        } else {
            // Buat record jika belum ada (data lama sebelum migrasi detail kegiatan)
            $db->table('biaya_kegiatan')->insert([
                'jurnal_id'  => $id,
                'file_bukti' => $filePath,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return redirect()->to('biaya/' . $id)
            ->with('success', 'Bukti pengeluaran berhasil diunggah.');
    }

    // ── Hapus Bukti ───────────────────────────────────────────
    public function deleteBukti(int $id)
    {
        $jurnal = $this->jurnalModel->find($id);
        if (! $jurnal || $jurnal['jenis_transaksi'] !== 'biaya') {
            return redirect()->to('biaya')->with('error', 'Data tidak ditemukan.');
        }

        $db       = \Config\Database::connect();
        $kegiatan = $db->table('biaya_kegiatan')->where('jurnal_id', $id)->get()->getRowArray();

        if ($kegiatan && ($kegiatan['file_bukti'] ?? '')) {
            $filePath = FCPATH . $kegiatan['file_bukti'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $db->table('biaya_kegiatan')
                ->where('jurnal_id', $id)
                ->update(['file_bukti' => null, 'updated_at' => date('Y-m-d H:i:s')]);
        }

        return redirect()->to('biaya/' . $id)
            ->with('success', 'Bukti pengeluaran berhasil dihapus.');
    }

    // ── Hapus ─────────────────────────────────────────────────
    public function delete(int $id)
    {
        $jurnal = $this->jurnalModel->find($id);
        if (! $jurnal || $jurnal['jenis_transaksi'] !== 'biaya') {
            return redirect()->to('biaya')->with('error', 'Data tidak ditemukan.');
        }

        $periode = $this->periodeModel->find($jurnal['periode_id']);
        if ($periode && $periode['is_tutup']) {
            return redirect()->to('biaya')
                ->with('error', 'Periode sudah ditutup. Data tidak dapat dihapus.');
        }

        \Config\Database::connect()->table('jurnal')->where('id', $id)->delete();

        return redirect()->to('biaya')
            ->with('success', "Biaya {$jurnal['nomor_jurnal']} berhasil dihapus.");
    }
}
