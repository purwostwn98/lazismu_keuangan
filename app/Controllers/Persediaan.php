<?php

namespace App\Controllers;

use App\Models\PersediaanModel;
use App\Models\JurnalModel;
use App\Models\PeriodeModel;

class Persediaan extends BaseController
{
    protected PersediaanModel $model;
    protected JurnalModel     $jurnalModel;
    protected PeriodeModel    $periodeModel;

    public function __construct()
    {
        $this->model        = new PersediaanModel();
        $this->jurnalModel  = new JurnalModel();
        $this->periodeModel = new PeriodeModel();
    }

    // ----------------------------------------------------------------
    // Daftar stok persediaan
    // ----------------------------------------------------------------
    public function index(): string
    {
        $db = \Config\Database::connect();

        $filter = [
            'q'            => $this->request->getGet('q') ?? '',
            'jenis_dana_id'=> $this->request->getGet('dana') ?? '',
        ];

        $list = $this->model->getDaftar($filter);

        // Ringkasan nilai stok
        $totalNilai = array_sum(array_column($list, 'nilai_stok'));

        return view('persediaan/index', [
            'list'         => $list,
            'filter'       => $filter,
            'totalNilai'   => $totalNilai,
            'jenisDanaList'=> $db->table('jenis_dana')->orderBy('id')->get()->getResultArray(),
        ]);
    }

    // ----------------------------------------------------------------
    // Form tambah item persediaan baru
    // ----------------------------------------------------------------
    public function input(): string
    {
        $db = \Config\Database::connect();

        // Akun persediaan = tipe aset, nomor diawali 113
        $akunPersediaan = $db->table('akun')
            ->where('is_header', 0)
            ->where('tipe', 'aset')
            ->like('nomor_akun', '113', 'after')
            ->orderBy('nomor_akun')
            ->get()->getResultArray();

        return view('persediaan/input', [
            'akunPersediaan'=> $akunPersediaan,
            'jenisDanaList' => $db->table('jenis_dana')->orderBy('id')->get()->getResultArray(),
        ]);
    }

    // ----------------------------------------------------------------
    // Simpan item persediaan baru
    // ----------------------------------------------------------------
    public function store()
    {
        $rules = [
            'nama_barang'   => 'required|max_length[150]',
            'satuan'        => 'required|max_length[20]',
            'akun_id'       => 'required|is_natural_no_zero',
            'jenis_dana_id' => 'permit_empty|is_natural_no_zero',
            'nilai_per_satuan' => 'required|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kode = $this->model->generateKode();

        $this->model->insert([
            'kode_barang'      => $kode,
            'nama_barang'      => $this->request->getPost('nama_barang'),
            'satuan'           => $this->request->getPost('satuan'),
            'akun_id'          => (int) $this->request->getPost('akun_id'),
            'jenis_dana_id'    => $this->request->getPost('jenis_dana_id') ?: null,
            'nilai_per_satuan' => (float) str_replace(',', '', $this->request->getPost('nilai_per_satuan')),
            'keterangan'       => $this->request->getPost('keterangan'),
        ]);

        return redirect()->to(base_url('persediaan'))
            ->with('success', "Barang {$kode} berhasil ditambahkan.");
    }

    // ----------------------------------------------------------------
    // Kartu stok — detail item + riwayat mutasi
    // ----------------------------------------------------------------
    public function show(int $id)
    {
        $data = $this->model->getDetail($id);
        if (empty($data)) {
            return redirect()->to(base_url('persediaan'))->with('error', 'Data tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Akun lawan berdasarkan sub_jenis:
        $akunMasuk = $db->table('akun')
            ->where('is_header', 0)
            ->whereIn('tipe', ['penerimaan', 'aset'])
            ->orderBy('tipe', 'DESC')->orderBy('nomor_akun')
            ->get()->getResultArray();

        $akunKeluar = $db->table('akun')
            ->where('is_header', 0)
            ->whereIn('tipe', ['penyaluran', 'beban'])
            ->orderBy('tipe', 'DESC')->orderBy('nomor_akun')
            ->get()->getResultArray();

        $rekeningList = $db->table('rekening_bank r')
            ->select('r.id, r.nama, r.bank, r.akun_id, jd.nama AS nama_dana')
            ->join('jenis_dana jd', 'jd.id = r.jenis_dana_id')
            ->where('r.is_aktif', 1)
            ->orderBy('r.nama')
            ->get()->getResultArray();

        $periodeList  = $this->periodeModel->orderBy('tahun DESC, bulan DESC')->findAll();
        $periodeAktif = $this->periodeModel->where('is_tutup', 0)->orderBy('tahun,bulan')->first();

        return view('persediaan/show', [
            'item'          => $data['item'],
            'mutasi'        => $data['mutasi'],
            'akunMasuk'     => $akunMasuk,
            'akunKeluar'    => $akunKeluar,
            'rekeningList'  => $rekeningList,
            'periodeList'   => $periodeList,
            'periodeAktif'  => $periodeAktif,
            'subJenisLabel' => PersediaanModel::$subJenisLabel,
        ]);
    }

    // ----------------------------------------------------------------
    // Simpan mutasi (masuk / keluar)
    // ----------------------------------------------------------------
    public function mutasiStore(int $id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(base_url('persediaan'))->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'sub_jenis'     => 'required|in_list[penerimaan_natura,pembelian,penyaluran,pemakaian]',
            'tanggal'       => 'required|valid_date',
            'periode_id'    => 'required|is_natural_no_zero',
            'kuantitas'     => 'required|decimal',
            'nilai_satuan'  => 'required|decimal',
            'uraian'        => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $subJenis    = $this->request->getPost('sub_jenis');
        $kuantitas   = (float) $this->request->getPost('kuantitas');
        $nilaiSatuan = (float) str_replace(',', '', $this->request->getPost('nilai_satuan'));
        $totalNilai  = $kuantitas * $nilaiSatuan;
        $periodeId   = (int) $this->request->getPost('periode_id');
        $tanggal     = $this->request->getPost('tanggal');
        $uraian      = $this->request->getPost('uraian');
        $keterangan  = $this->request->getPost('keterangan');

        $jenis = in_array($subJenis, ['penerimaan_natura', 'pembelian']) ? 'masuk' : 'keluar';

        // Validasi stok saat keluar
        if ($jenis === 'keluar') {
            $stokAkhir = (float) $item['stok_masuk'] - (float) $item['stok_keluar'];
            if ($kuantitas > $stokAkhir) {
                return redirect()->back()->withInput()
                    ->with('error', "Stok tidak cukup. Stok tersedia: {$stokAkhir} {$item['satuan']}.");
            }
        }

        // Periksa periode terkunci
        $periode = $this->periodeModel->find($periodeId);
        if ($periode && $periode['is_tutup']) {
            return redirect()->back()->withInput()->with('error', 'Periode sudah ditutup, tidak dapat menambah mutasi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Tentukan akun lawan & jurnal
        $akunLawanId   = null;
        $jurnalJenis   = PersediaanModel::$jurnalJenis[$subJenis];
        $nomorMutasi   = $this->model->generateNomorMutasi($jenis);

        if ($subJenis === 'pembelian') {
            // Kredit rekening_bank → ambil akun_id dari rekening
            $rekeningId  = (int) $this->request->getPost('rekening_id');
            $rek         = $db->table('rekening_bank')->where('id', $rekeningId)->get()->getRowArray();
            $akunLawanId = $rek ? (int) $rek['akun_id'] : null;

            if (! $akunLawanId) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Rekening tidak valid.');
            }
        } else {
            $akunLawanId = (int) $this->request->getPost('akun_lawan_id');
            $rekeningId  = null;
        }

        // Ambil jenis_dana dari item persediaan
        $jenisDanaId = (int) $item['jenis_dana_id'];
        if (! $jenisDanaId) {
            // Fallback: ambil dari akun persediaan parent chain — tidak ada, gunakan AMIL
            $jd = $db->table('jenis_dana')->where('kode', 'AMIL')->get()->getRowArray();
            $jenisDanaId = $jd ? (int) $jd['id'] : 1;
        }

        // Buat jurnal
        $nomorJurnal = $this->jurnalModel->generateNomor($jurnalJenis);
        $this->jurnalModel->insert([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal'        => $tanggal,
            'periode_id'     => $periodeId,
            'jenis_dana_id'  => $jenisDanaId,
            'jenis_transaksi'=> $jurnalJenis,
            'uraian'         => $uraian,
            'keterangan'     => $keterangan,
            'total_debet'    => $totalNilai,
            'total_kredit'   => $totalNilai,
        ]);
        $jurnalId = $this->jurnalModel->getInsertID();

        // Jurnal detail
        $now = date('Y-m-d H:i:s');
        if ($jenis === 'masuk') {
            // D: Persediaan, K: Akun Lawan
            $details = [
                ['jurnal_id' => $jurnalId, 'akun_id' => (int) $item['akun_id'], 'debet' => $totalNilai, 'kredit' => 0, 'uraian' => $uraian, 'created_at' => $now, 'updated_at' => $now],
                ['jurnal_id' => $jurnalId, 'akun_id' => $akunLawanId,           'debet' => 0, 'kredit' => $totalNilai, 'uraian' => $uraian, 'created_at' => $now, 'updated_at' => $now],
            ];
        } else {
            // D: Akun Lawan (penyaluran/biaya), K: Persediaan
            $details = [
                ['jurnal_id' => $jurnalId, 'akun_id' => $akunLawanId,           'debet' => $totalNilai, 'kredit' => 0, 'uraian' => $uraian, 'created_at' => $now, 'updated_at' => $now],
                ['jurnal_id' => $jurnalId, 'akun_id' => (int) $item['akun_id'], 'debet' => 0, 'kredit' => $totalNilai, 'uraian' => $uraian, 'created_at' => $now, 'updated_at' => $now],
            ];
        }
        $db->table('jurnal_detail')->insertBatch($details);

        // Simpan mutasi
        $db->table('persediaan_mutasi')->insert([
            'nomor_mutasi'  => $nomorMutasi,
            'persediaan_id' => $id,
            'periode_id'    => $periodeId,
            'tanggal'       => $tanggal,
            'jenis'         => $jenis,
            'sub_jenis'     => $subJenis,
            'kuantitas'     => $kuantitas,
            'nilai_satuan'  => $nilaiSatuan,
            'total_nilai'   => $totalNilai,
            'akun_lawan_id' => $akunLawanId,
            'rekening_id'   => $rekeningId,
            'jurnal_id'     => $jurnalId,
            'uraian'        => $uraian,
            'keterangan'    => $keterangan,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Update stok & nilai rata-rata tertimbang di master persediaan
        if ($jenis === 'masuk') {
            $stokLama      = (float) $item['stok_masuk'] - (float) $item['stok_keluar'];
            $nilaiLama     = $stokLama * (float) $item['nilai_per_satuan'];
            $stokBaru      = $stokLama + $kuantitas;
            $nilaiRataRata = $stokBaru > 0 ? ($nilaiLama + $totalNilai) / $stokBaru : $nilaiSatuan;

            $db->table('persediaan')->where('id', $id)->update([
                'stok_masuk'       => (float) $item['stok_masuk'] + $kuantitas,
                'nilai_per_satuan' => $nilaiRataRata,
                'updated_at'       => $now,
            ]);
        } else {
            $db->table('persediaan')->where('id', $id)->update([
                'stok_keluar' => (float) $item['stok_keluar'] + $kuantitas,
                'updated_at'  => $now,
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan mutasi persediaan.');
        }

        return redirect()->to(base_url("persediaan/{$id}"))
            ->with('success', "Mutasi {$nomorMutasi} berhasil disimpan. Jurnal: {$nomorJurnal}.");
    }

    // ----------------------------------------------------------------
    // Hapus mutasi (reverse)
    // ----------------------------------------------------------------
    public function mutasiDelete(int $mutasiId)
    {
        $db     = \Config\Database::connect();
        $mutasi = $db->table('persediaan_mutasi')->where('id', $mutasiId)->get()->getRowArray();

        if (! $mutasi) {
            return redirect()->to(base_url('persediaan'))->with('error', 'Mutasi tidak ditemukan.');
        }

        // Cek periode terkunci
        $periode = $this->periodeModel->find($mutasi['periode_id']);
        if ($periode && $periode['is_tutup']) {
            return redirect()->to(base_url("persediaan/{$mutasi['persediaan_id']}"))
                ->with('error', 'Periode sudah ditutup, mutasi tidak dapat dihapus.');
        }

        $item = $this->model->find($mutasi['persediaan_id']);
        if (! $item) {
            return redirect()->to(base_url('persediaan'))->with('error', 'Data persediaan tidak ditemukan.');
        }

        // Validasi stok saat batal masuk
        if ($mutasi['jenis'] === 'masuk') {
            $stokAkhir = (float) $item['stok_masuk'] - (float) $item['stok_keluar'];
            if ((float) $mutasi['kuantitas'] > $stokAkhir) {
                return redirect()->to(base_url("persediaan/{$mutasi['persediaan_id']}"))
                    ->with('error', 'Tidak dapat menghapus — stok keluar sudah melampaui stok yang akan dibatalkan.');
            }
        }

        $jurnalId    = $mutasi['jurnal_id'];
        $persediaanId= $mutasi['persediaan_id'];
        $now         = date('Y-m-d H:i:s');

        $db->transStart();

        // Hapus mutasi & jurnal
        $db->table('persediaan_mutasi')->where('id', $mutasiId)->delete();
        if ($jurnalId) {
            $db->table('jurnal_detail')->where('jurnal_id', $jurnalId)->delete();
            $db->table('jurnal')->where('id', $jurnalId)->delete();
        }

        // Revert stok
        if ($mutasi['jenis'] === 'masuk') {
            $db->table('persediaan')->where('id', $persediaanId)->update([
                'stok_masuk' => max(0, (float) $item['stok_masuk'] - (float) $mutasi['kuantitas']),
                'updated_at' => $now,
            ]);
        } else {
            $db->table('persediaan')->where('id', $persediaanId)->update([
                'stok_keluar' => max(0, (float) $item['stok_keluar'] - (float) $mutasi['kuantitas']),
                'updated_at'  => $now,
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to(base_url("persediaan/{$persediaanId}"))
                ->with('error', 'Gagal menghapus mutasi.');
        }

        return redirect()->to(base_url("persediaan/{$persediaanId}"))
            ->with('success', 'Mutasi berhasil dihapus dan jurnal dibatalkan.');
    }

    // ----------------------------------------------------------------
    // Hapus item persediaan (hanya jika belum ada mutasi)
    // ----------------------------------------------------------------
    public function delete(int $id)
    {
        $db  = \Config\Database::connect();
        $ada = $db->table('persediaan_mutasi')->where('persediaan_id', $id)->countAllResults();

        if ($ada > 0) {
            return redirect()->to(base_url('persediaan'))
                ->with('error', 'Barang tidak dapat dihapus karena sudah memiliki riwayat mutasi.');
        }

        $this->model->delete($id);
        return redirect()->to(base_url('persediaan'))->with('success', 'Barang berhasil dihapus.');
    }
}
