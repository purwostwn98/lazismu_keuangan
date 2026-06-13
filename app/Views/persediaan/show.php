<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.mutasi-masuk  { border-left: 3px solid #198754; }
.mutasi-keluar { border-left: 3px solid #dc3545; }
.num-right     { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$item          = $item          ?? [];
$mutasi        = $mutasi        ?? [];
$akunMasuk     = $akunMasuk     ?? [];
$akunKeluar    = $akunKeluar    ?? [];
$rekeningList  = $rekeningList  ?? [];
$periodeList   = $periodeList   ?? [];
$periodeAktif  = $periodeAktif  ?? null;
$subJenisLabel = $subJenisLabel ?? [];

$stokAkhir  = (float) ($item['stok_masuk'] ?? 0) - (float) ($item['stok_keluar'] ?? 0);
$nilaiStok  = $stokAkhir * (float) ($item['nilai_per_satuan'] ?? 0);

function fmtRp(float $v): string  { return 'Rp ' . number_format($v, 0, ',', '.'); }
function fmtQty(float $v): string { return number_format($v, 3, ',', '.'); }
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><?= esc($item['nama_barang']) ?></h4>
            <small class="text-muted">
                <span class="font-monospace"><?= esc($item['kode_barang']) ?></span> &nbsp;|&nbsp;
                <?= esc($item['nomor_akun']) ?> <?= esc($item['nama_akun']) ?>
                <?php if ($item['kode_dana']): ?>
                    &nbsp;|&nbsp; <span class="badge bg-light text-dark border"><?= esc($item['kode_dana']) ?></span>
                <?php endif; ?>
            </small>
        </div>
        <a href="<?= base_url('persediaan') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="fa fa-check-circle me-1"></i><?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="fa fa-exclamation-circle me-1"></i><?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 small">
            <ul class="mb-0 ps-3">
                <?php foreach (session('errors') as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <!-- Info stok -->
        <div class="col-md-8">
            <div class="row g-2">
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="text-muted small">Total Masuk</div>
                        <div class="fw-bold text-success"><?= fmtQty($item['stok_masuk']) ?> <?= esc($item['satuan']) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="text-muted small">Total Keluar</div>
                        <div class="fw-bold text-danger"><?= fmtQty($item['stok_keluar']) ?> <?= esc($item['satuan']) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="text-muted small">Stok Akhir</div>
                        <div class="fw-bold <?= $stokAkhir <= 0 ? 'text-danger' : 'text-primary' ?> fs-5">
                            <?= fmtQty($stokAkhir) ?> <?= esc($item['satuan']) ?>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="text-muted small">Nilai/Satuan (HPP)</div>
                        <div class="fw-semibold"><?= fmtRp($item['nilai_per_satuan']) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="text-muted small">Nilai Stok</div>
                        <div class="fw-semibold text-success"><?= fmtRp($nilaiStok) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-2">
                        <div class="text-muted small">Mutasi</div>
                        <div class="fw-bold"><?= count($mutasi) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Mutasi -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-2 fw-semibold" style="background:#1a3f6f;color:#fff;">
                    <i class="fa fa-exchange-alt me-1"></i> Catat Mutasi
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('persediaan/mutasi-store/' . $item['id']) ?>" id="formMutasi">
                    <?= csrf_field() ?>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Jenis Mutasi <span class="text-danger">*</span></label>
                            <select name="sub_jenis" class="form-select form-select-sm" id="subJenis" required>
                                <option value="">— Pilih Jenis —</option>
                                <optgroup label="Masuk">
                                    <option value="penerimaan_natura">Penerimaan Natura / Barang</option>
                                    <option value="pembelian">Pembelian / Pengadaan</option>
                                </optgroup>
                                <optgroup label="Keluar">
                                    <option value="penyaluran">Penyaluran ke Mustahiq</option>
                                    <option value="pemakaian">Pemakaian Operasional</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Akun lawan (untuk non-pembelian) -->
                        <div class="mb-2" id="wrapAkunLawan">
                            <label class="form-label small fw-semibold">Akun Lawan <span class="text-danger">*</span></label>
                            <select name="akun_lawan_id" class="form-select form-select-sm" id="akunLawan">
                                <option value="">— Pilih Akun —</option>
                            </select>
                        </div>

                        <!-- Rekening (khusus pembelian) -->
                        <div class="mb-2 d-none" id="wrapRekening">
                            <label class="form-label small fw-semibold">Rekening Sumber Dana <span class="text-danger">*</span></label>
                            <select name="rekening_id" class="form-select form-select-sm">
                                <option value="">— Pilih Rekening —</option>
                                <?php foreach ($rekeningList as $r): ?>
                                    <option value="<?= $r['id'] ?>">
                                        <?= esc($r['nama']) ?> (<?= esc($r['nama_dana']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control form-control-sm"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <select name="periode_id" class="form-select form-select-sm" required>
                                <option value="">— Pilih —</option>
                                <?php foreach ($periodeList as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        <?= ($periodeAktif && $periodeAktif['id'] == $p['id']) ? 'selected' : '' ?>>
                                        <?= esc($p['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Kuantitas <span class="text-danger">*</span></label>
                                <input type="number" name="kuantitas" id="kuantitas"
                                       class="form-control form-control-sm text-end"
                                       min="0.001" step="0.001" placeholder="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Nilai/Satuan (Rp) <span class="text-danger">*</span></label>
                                <input type="text" name="nilai_satuan" id="nilaiSatuan"
                                       class="form-control form-control-sm text-end"
                                       placeholder="<?= number_format($item['nilai_per_satuan'], 0, ',', '.') ?>"
                                       value="<?= number_format($item['nilai_per_satuan'], 0, ',', '.') ?>" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Total Nilai</label>
                            <div class="form-control form-control-sm text-end bg-light fw-bold" id="totalNilai">Rp 0</div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Uraian <span class="text-danger">*</span></label>
                            <input type="text" name="uraian" class="form-control form-control-sm"
                                   placeholder="Keterangan singkat mutasi" maxlength="255" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control form-control-sm" rows="1"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-sm">
                            <i class="fa fa-save me-1"></i> Simpan Mutasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Stok / Riwayat Mutasi -->
    <div class="card border-0 shadow-sm">
        <div class="card-header py-2 fw-semibold" style="background:#495057;color:#fff;">
            <i class="fa fa-table me-1"></i> Kartu Stok — <?= esc($item['nama_barang']) ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th>No. Mutasi</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Uraian</th>
                            <th>Akun Lawan</th>
                            <th class="num-right">Masuk</th>
                            <th class="num-right">Keluar</th>
                            <th class="num-right">Saldo (<?= esc($item['satuan']) ?>)</th>
                            <th class="num-right">Nilai (Rp)</th>
                            <th class="text-center">Jurnal</th>
                            <th class="text-center" style="width:60px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($mutasi)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada mutasi tercatat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $saldo = 0;
                        foreach ($mutasi as $m):
                            if ($m['jenis'] === 'masuk') {
                                $saldo += (float) $m['kuantitas'];
                            } else {
                                $saldo -= (float) $m['kuantitas'];
                            }
                        ?>
                            <tr class="mutasi-<?= $m['jenis'] ?>">
                                <td class="font-monospace small"><?= esc($m['nomor_mutasi']) ?></td>
                                <td class="text-nowrap"><?= date('d/m/Y', strtotime($m['tanggal'])) ?></td>
                                <td>
                                    <span class="badge <?= $m['jenis'] === 'masuk' ? 'bg-success' : 'bg-danger' ?>" style="font-size:.68rem;">
                                        <?= $subJenisLabel[$m['sub_jenis']] ?? $m['sub_jenis'] ?>
                                    </span>
                                </td>
                                <td><?= esc($m['uraian']) ?></td>
                                <td class="small text-muted"><?= esc($m['nama_akun_lawan'] ?? '—') ?></td>
                                <td class="num-right text-success">
                                    <?= $m['jenis'] === 'masuk' ? fmtQty($m['kuantitas']) : '—' ?>
                                </td>
                                <td class="num-right text-danger">
                                    <?= $m['jenis'] === 'keluar' ? fmtQty($m['kuantitas']) : '—' ?>
                                </td>
                                <td class="num-right fw-semibold"><?= fmtQty($saldo) ?></td>
                                <td class="num-right"><?= number_format($m['total_nilai'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if ($m['nomor_jurnal']): ?>
                                        <span class="font-monospace small text-muted"><?= esc($m['nomor_jurnal']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('persediaan/mutasi-delete/' . $m['id']) ?>"
                                       class="btn btn-sm btn-outline-danger py-0 px-1"
                                       onclick="return confirm('Batalkan mutasi ini?')"
                                       title="Hapus">
                                        <i class="fa fa-trash fa-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const akunMasuk  = <?= json_encode(array_values($akunMasuk)) ?>;
const akunKeluar = <?= json_encode(array_values($akunKeluar)) ?>;

const subJenis = document.getElementById('subJenis');
const selAkun  = document.getElementById('akunLawan');
const wrapAkun = document.getElementById('wrapAkunLawan');
const wrapRek  = document.getElementById('wrapRekening');

subJenis.addEventListener('change', function () {
    const val = this.value;
    selAkun.innerHTML = '<option value="">— Pilih Akun —</option>';

    if (val === 'pembelian') {
        wrapAkun.classList.add('d-none');
        wrapRek.classList.remove('d-none');
        selAkun.removeAttribute('required');
    } else {
        wrapAkun.classList.remove('d-none');
        wrapRek.classList.add('d-none');
        selAkun.setAttribute('required', 'required');

        const list = (val === 'penerimaan_natura') ? akunMasuk : akunKeluar;
        list.forEach(function (a) {
            const opt = document.createElement('option');
            opt.value = a.id;
            opt.text  = a.nomor_akun + ' — ' + a.nama_akun + ' (' + a.tipe + ')';
            selAkun.appendChild(opt);
        });
    }
    hitungTotal();
});

function hitungTotal() {
    const qty   = parseFloat(document.getElementById('kuantitas').value) || 0;
    const nilai = parseFloat(document.getElementById('nilaiSatuan').value.replace(/\./g, '').replace(',', '.')) || 0;
    const total = qty * nilai;
    document.getElementById('totalNilai').textContent =
        'Rp ' + total.toLocaleString('id-ID', {maximumFractionDigits: 0});
}

document.getElementById('kuantitas').addEventListener('input', hitungTotal);
document.getElementById('nilaiSatuan').addEventListener('input', hitungTotal);
</script>
<?= $this->endSection() ?>
