<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .voucher-header {
        background: linear-gradient(135deg, #1a3f6f 0%, #2d6abf 100%);
        color: #fff;
    }

    .row-debet  { border-left: 3px solid #dc3545; }
    .row-kredit { border-left: 3px solid #0d6efd; }

    .num-right {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .bukti-box {
        border: 1.5px dashed #198754;
        border-radius: 8px;
        padding: 14px 16px;
        background: #f6fff9;
    }
    .bukti-box.empty {
        border-color: #adb5bd;
        background: #f8f9fa;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$header   = $header   ?? [];
$details  = $details  ?? [];
$kegiatan = $kegiatan ?? [];

$debetRows  = array_values(array_filter($details, fn($d) => (float)$d['debet']  > 0));
$kreditRow  = array_values(array_filter($details, fn($d) => (float)$d['kredit'] > 0));
$totalDebet = array_sum(array_column($debetRows, 'debet'));
$fileBukti  = $kegiatan['file_bukti'] ?? '';

function fmtRp(float $v): string
{
    return 'Rp ' . number_format($v, 0, ',', '.');
}

function fmtDt(?string $v): string
{
    if (!$v || $v === '0000-00-00 00:00:00') return '—';
    return date('d M Y, H:i', strtotime($v));
}

$hasKegiatan = ! empty($kegiatan) && (
    ($kegiatan['nama_kegiatan'] ?? '') ||
    ($kegiatan['lokasi'] ?? '') ||
    ($kegiatan['tgl_berangkat'] ?? '') ||
    ($kegiatan['tgl_kembali'] ?? '') ||
    ($kegiatan['uraian_kegiatan'] ?? '')
);
?>

<div class="container-fluid">

    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Detail Biaya Operasional</h4>
            <small class="text-muted font-monospace"><?= esc($header['nomor_jurnal'] ?? '') ?></small>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <!-- Cetak PDF -->
            <a href="<?= base_url('biaya/cetak/' . ($header['id'] ?? 0)) ?>"
               target="_blank" class="btn btn-sm btn-danger">
                <i class="fa fa-file-pdf me-1"></i> Cetak PDF
            </a>

            <!-- Bukti: lihat / ganti / unggah -->
            <?php if ($fileBukti): ?>
                <a href="<?= base_url($fileBukti) ?>" target="_blank"
                   class="btn btn-sm btn-success">
                    <i class="fa fa-paperclip me-1"></i> Lihat Bukti
                </a>
                <button type="button" class="btn btn-sm btn-outline-success"
                        data-bs-toggle="modal" data-bs-target="#modalUploadBukti"
                        title="Ganti file bukti">
                    <i class="fa fa-arrow-up-from-bracket me-1"></i> Ganti
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-sm btn-outline-success"
                        data-bs-toggle="modal" data-bs-target="#modalUploadBukti">
                    <i class="fa fa-upload me-1"></i> Unggah Bukti
                </button>
            <?php endif; ?>

            <?php if (! ($header['is_tutup'] ?? true)): ?>
                <a href="<?= base_url('biaya/delete/' . $header['id']) ?>"
                    class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Hapus biaya <?= esc($header['nomor_jurnal']) ?>? Data tidak dapat dikembalikan.')">
                    <i class="fa fa-trash me-1"></i> Hapus
                </a>
            <?php endif; ?>
            <a href="<?= base_url('biaya') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
            <i class="fa fa-triangle-exclamation me-1"></i><?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
            <i class="fa fa-check-circle me-1"></i><?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3">

        <!-- ── Kolom kiri: voucher + detail kegiatan ── -->
        <div class="col-12 col-lg-8">

            <!-- Voucher card -->
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="voucher-header px-4 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="small opacity-75 mb-1">Biaya Operasional</div>
                            <div class="fw-bold fs-6"><?= esc($header['uraian'] ?? '') ?></div>
                            <?php if ($header['keterangan'] ?? ''): ?>
                                <div class="small opacity-75 mt-1"><?= esc($header['keterangan']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-auto text-end">
                            <div class="small opacity-75">Total Biaya</div>
                            <div class="fw-bold fs-5"><?= fmtRp($totalDebet) ?></div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="px-3 py-2 bg-light border-bottom">
                        <small class="fw-semibold text-uppercase text-muted">
                            <i class="fa fa-arrow-right fa-xs me-1 text-danger"></i> Pengeluaran (Debet)
                        </small>
                    </div>
                    <table class="table table-sm table-hover mb-0" style="font-size:.86rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">#</th>
                                <th>Akun</th>
                                <th>Keterangan</th>
                                <th class="text-end pe-3">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debetRows as $i => $d): ?>
                                <tr class="row-debet">
                                    <td class="px-3 text-muted small"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($d['nama_akun'] ?? '') ?></div>
                                        <small class="text-muted font-monospace"><?= esc($d['nomor_akun'] ?? '') ?></small>
                                    </td>
                                    <td class="text-muted small"><?= esc($d['uraian'] ?? '—') ?></td>
                                    <td class="text-end pe-3 fw-semibold text-danger num-right">
                                        <?= fmtRp((float)$d['debet']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="px-3 text-end fw-semibold">Total Pengeluaran:</td>
                                <td class="text-end pe-3 fw-bold text-danger num-right">
                                    <?= fmtRp($totalDebet) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if (! empty($kreditRow)): $kr = $kreditRow[0]; ?>
                        <div class="px-3 py-2 bg-light border-top border-bottom mt-2">
                            <small class="fw-semibold text-uppercase text-muted">
                                <i class="fa fa-arrow-left fa-xs me-1 text-primary"></i> Sumber Dana (Kredit)
                            </small>
                        </div>
                        <div class="px-3 py-3 row-kredit ms-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold"><?= esc($kr['nama_rekening'] ?? $kr['nama_akun'] ?? '—') ?></div>
                                    <?php if ($kr['nama_bank'] ?? ''): ?>
                                        <small class="text-muted"><?= esc($kr['nama_bank']) ?></small>
                                    <?php endif; ?>
                                    <div class="small text-muted font-monospace mt-1"><?= esc($kr['nomor_akun'] ?? '') ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary num-right fs-6"><?= fmtRp((float)$kr['kredit']) ?></div>
                                    <small class="text-muted">Total Kredit</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detail kegiatan -->
            <?php if ($hasKegiatan): ?>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent border-bottom py-2">
                    <small class="fw-semibold text-uppercase text-muted">
                        <i class="fa fa-map-location-dot me-1 text-success"></i> Detail Kegiatan
                    </small>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3 small">

                        <?php if ($kegiatan['nama_kegiatan'] ?? ''): ?>
                        <div class="col-12 col-md-6">
                            <div class="text-muted mb-1">Program / Nama Kegiatan</div>
                            <div class="fw-semibold"><?= esc($kegiatan['nama_kegiatan']) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if ($kegiatan['lokasi'] ?? ''): ?>
                        <div class="col-12 col-md-6">
                            <div class="text-muted mb-1"><i class="fa fa-map-marker-alt fa-xs me-1"></i>Lokasi</div>
                            <div class="fw-semibold"><?= esc($kegiatan['lokasi']) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (($kegiatan['tgl_berangkat'] ?? '') || ($kegiatan['tgl_kembali'] ?? '')): ?>
                        <div class="col-12">
                            <div class="text-muted mb-1"><i class="fa fa-clock fa-xs me-1"></i>Waktu Pelaksanaan</div>
                            <div>
                                <?php if ($kegiatan['tgl_berangkat'] ?? ''): ?>
                                    <span class="badge bg-light text-dark border me-1">
                                        Berangkat: <?= fmtDt($kegiatan['tgl_berangkat']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($kegiatan['tgl_kembali'] ?? ''): ?>
                                    <span class="badge bg-light text-dark border">
                                        Kembali: <?= fmtDt($kegiatan['tgl_kembali']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($kegiatan['uraian_kegiatan'] ?? ''): ?>
                        <div class="col-12">
                            <div class="text-muted mb-1">Deskripsi Kegiatan</div>
                            <div class="p-3 rounded bg-light" style="white-space: pre-wrap; font-size: .85rem; line-height: 1.6;">
                                <?= esc($kegiatan['uraian_kegiatan']) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Kolom kanan: sidebar ── -->
        <div class="col-12 col-lg-4">

            <!-- Info transaksi -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom py-2">
                    <small class="fw-semibold text-uppercase text-muted">Informasi Transaksi</small>
                </div>
                <div class="card-body py-2">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">No. Jurnal</dt>
                        <dd class="col-7 font-monospace fw-semibold"><?= esc($header['nomor_jurnal'] ?? '') ?></dd>

                        <dt class="col-5 text-muted fw-normal">Tanggal</dt>
                        <dd class="col-7"><?= date('d M Y', strtotime($header['tanggal'] ?? 'now')) ?></dd>

                        <dt class="col-5 text-muted fw-normal">Periode</dt>
                        <dd class="col-7">
                            <?= esc($header['nama_periode'] ?? '') ?>
                            <?php if ($header['is_tutup'] ?? false): ?>
                                <span class="badge bg-secondary ms-1">Tutup</span>
                            <?php else: ?>
                                <span class="badge bg-success ms-1">Aktif</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Jenis Dana</dt>
                        <dd class="col-7">
                            <span class="badge bg-light text-dark border">
                                <?= esc($header['kode_dana'] ?? $header['nama_dana'] ?? '—') ?>
                            </span>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Total</dt>
                        <dd class="col-7 fw-bold text-danger"><?= fmtRp($totalDebet) ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Bukti lampiran -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom py-2 d-flex justify-content-between align-items-center">
                    <small class="fw-semibold text-uppercase text-muted">
                        <i class="fa fa-paperclip me-1"></i> Bukti Lampiran
                    </small>
                    <?php if ($fileBukti): ?>
                        <a href="<?= base_url('biaya/delete-bukti/' . $header['id']) ?>"
                           class="btn btn-outline-danger btn-sm py-0 px-2"
                           style="font-size:.72rem;"
                           onclick="return confirm('Hapus file bukti ini?')">
                            <i class="fa fa-trash fa-xs"></i> Hapus
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body py-3">
                    <?php if ($fileBukti): ?>
                        <div class="bukti-box">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa fa-file-pdf fa-lg text-danger"></i>
                                <div class="small">
                                    <div class="fw-semibold text-dark">File Bukti (PDF)</div>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        <?= esc(basename($fileBukti)) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url($fileBukti) ?>" target="_blank"
                                   class="btn btn-success btn-sm flex-fill">
                                    <i class="fa fa-eye me-1"></i> Buka / Unduh
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalUploadBukti"
                                        title="Ganti file">
                                    <i class="fa fa-rotate me-1"></i> Ganti
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bukti-box empty text-center py-2">
                            <i class="fa fa-file-circle-plus fa-2x text-muted mb-2 d-block opacity-50"></i>
                            <div class="small text-muted mb-3">Belum ada file bukti diunggah.</div>
                            <button type="button" class="btn btn-outline-success btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#modalUploadBukti">
                                <i class="fa fa-upload me-1"></i> Unggah Bukti
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ringkasan jurnal -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-2">
                    <small class="fw-semibold text-uppercase text-muted">Ringkasan Jurnal</small>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Akun</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end pe-3">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debetRows as $d): ?>
                                <tr>
                                    <td class="px-3 small"><?= esc($d['nomor_akun']) ?> <?= esc($d['nama_akun']) ?></td>
                                    <td class="text-end text-success small num-right"><?= fmtRp((float)$d['debet']) ?></td>
                                    <td class="text-end pe-3 text-muted small">—</td>
                                </tr>
                            <?php endforeach; ?>
                            <?php foreach ($kreditRow as $k): ?>
                                <tr>
                                    <td class="px-3 small ps-4 fst-italic"><?= esc($k['nomor_akun']) ?> <?= esc($k['nama_akun']) ?></td>
                                    <td class="text-end text-muted small">—</td>
                                    <td class="text-end pe-3 text-danger small num-right"><?= fmtRp((float)$k['kredit']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="px-3 fw-semibold small">Total</td>
                                <td class="text-end fw-semibold small num-right"><?= fmtRp($totalDebet) ?></td>
                                <td class="text-end pe-3 fw-semibold small num-right"><?= fmtRp($totalDebet) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ── Modal Upload Bukti ── -->
<div class="modal fade" id="modalUploadBukti" tabindex="-1" aria-labelledby="modalUploadBuktiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post"
                  action="<?= base_url('biaya/upload-bukti/' . ($header['id'] ?? 0)) ?>"
                  enctype="multipart/form-data"
                  id="formUploadBukti">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-bold" id="modalUploadBuktiLabel">
                        <i class="fa fa-upload me-1 text-success"></i>
                        <?= $fileBukti ? 'Ganti File Bukti' : 'Unggah Bukti Pengeluaran' ?>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($fileBukti): ?>
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="fa fa-triangle-exclamation me-1"></i>
                        File bukti yang sudah ada akan digantikan.
                    </div>
                    <?php endif; ?>
                    <label class="form-label fw-semibold">
                        Pilih File <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="file_bukti" id="inputFileBukti"
                           class="form-control" accept=".pdf" required>
                    <div class="form-text mt-1">
                        <i class="fa fa-circle-info fa-xs me-1"></i>
                        Hanya file <strong>PDF</strong>. Ukuran maksimal <strong>5 MB</strong>.
                    </div>
                    <!-- Preview nama file -->
                    <div id="filePreview" class="mt-2 d-none">
                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded small">
                            <i class="fa fa-file-pdf text-danger"></i>
                            <span id="filePreviewName" class="text-truncate"></span>
                            <span id="filePreviewSize" class="text-muted ms-auto text-nowrap"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4" id="btnUpload" disabled>
                        <i class="fa fa-upload me-1"></i> Unggah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const input       = document.getElementById('inputFileBukti');
    const preview     = document.getElementById('filePreview');
    const previewName = document.getElementById('filePreviewName');
    const previewSize = document.getElementById('filePreviewSize');
    const btnUpload   = document.getElementById('btnUpload');
    const MAX_SIZE    = 5 * 1024 * 1024; // 5 MB

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) {
            preview.classList.add('d-none');
            btnUpload.disabled = true;
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'pdf') {
            this.value = '';
            preview.classList.add('d-none');
            btnUpload.disabled = true;
            alert('Hanya file PDF yang diperbolehkan.');
            return;
        }

        if (file.size > MAX_SIZE) {
            this.value = '';
            preview.classList.add('d-none');
            btnUpload.disabled = true;
            alert('Ukuran file melebihi batas maksimal 5 MB.');
            return;
        }

        const kb = file.size / 1024;
        const sizeLabel = kb >= 1024
            ? (kb / 1024).toFixed(1) + ' MB'
            : Math.round(kb) + ' KB';

        previewName.textContent = file.name;
        previewSize.textContent = sizeLabel;
        preview.classList.remove('d-none');
        btnUpload.disabled = false;
    });

    // Reset modal when closed
    document.getElementById('modalUploadBukti').addEventListener('hidden.bs.modal', function () {
        input.value = '';
        preview.classList.add('d-none');
        btnUpload.disabled = true;
    });
})();
</script>
<?= $this->endSection() ?>
