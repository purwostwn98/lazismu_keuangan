<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$biayaList       = $biayaList       ?? [];
$periodeList     = $periodeList     ?? [];
$jenisDanaList   = $jenisDanaList   ?? [];
$totalBiaya      = $totalBiaya      ?? 0;
$jumlahTransaksi = $jumlahTransaksi ?? 0;
$filter          = $filter          ?? [];

function fmtRp(float $v): string { return 'Rp ' . number_format($v, 0, ',', '.'); }
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Biaya Operasional</h4>
            <small class="text-muted">Pencatatan pengeluaran operasional kegiatan LazisMu UMS</small>
        </div>
        <a href="<?= base_url('biaya/input') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Input Biaya
        </a>
    </div>

    <!-- Alert -->
    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="fa fa-check-circle me-1"></i><?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="fa fa-triangle-exclamation me-1"></i><?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 flex-shrink-0" style="background:#FFF0EA;">
                        <i class="fa fa-money-bill-wave fa-lg" style="color:#E8622A;"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Total Biaya</div>
                        <div class="fw-bold fs-6"><?= fmtRp($totalBiaya) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 flex-shrink-0" style="background:#EAF0FF;">
                        <i class="fa fa-receipt fa-lg" style="color:#3b5bdb;"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Jumlah Transaksi</div>
                        <div class="fw-bold fs-6"><?= $jumlahTransaksi ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-12 col-sm-4 col-md-3">
                    <label class="form-label form-label-sm fw-semibold mb-1">Periode</label>
                    <select name="periode" class="form-select form-select-sm">
                        <option value="">Semua Periode</option>
                        <?php foreach ($periodeList as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($filter['periode'] ?? 0) == $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-4 col-md-3">
                    <label class="form-label form-label-sm fw-semibold mb-1">Jenis Dana</label>
                    <select name="dana" class="form-select form-select-sm">
                        <option value="">Semua Dana</option>
                        <?php foreach ($jenisDanaList as $jd): ?>
                            <option value="<?= $jd['id'] ?>" <?= ($filter['dana'] ?? 0) == $jd['id'] ? 'selected' : '' ?>>
                                <?= esc($jd['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-4 col-md-4">
                    <label class="form-label form-label-sm fw-semibold mb-1">Cari</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Nomor jurnal / uraian..."
                           value="<?= esc($filter['q'] ?? '') ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search me-1"></i> Filter
                    </button>
                    <?php if (($filter['periode'] ?? 0) || ($filter['dana'] ?? 0) || ($filter['q'] ?? '')): ?>
                        <a href="<?= base_url('biaya') ?>" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($biayaList)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fa fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Belum ada data biaya operasional.
                    <a href="<?= base_url('biaya/input') ?>" class="d-block mt-2 small">+ Input biaya pertama</a>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" style="font-size:.86rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Tanggal</th>
                            <th>No. Jurnal</th>
                            <th>Uraian Kegiatan</th>
                            <th>Rekening Sumber</th>
                            <th>Dana</th>
                            <th class="text-end">Total (Rp)</th>
                            <th class="text-center">Periode</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($biayaList as $b): ?>
                        <tr>
                            <td class="px-3 text-muted small"><?= date('d M Y', strtotime($b['tanggal'])) ?></td>
                            <td class="font-monospace small text-nowrap"><?= esc($b['nomor_jurnal']) ?></td>
                            <td>
                                <?= esc($b['uraian']) ?>
                                <?php if ($b['nama_kegiatan'] ?? ''): ?>
                                    <div class="small text-muted">
                                        <i class="fa fa-tag fa-xs me-1"></i><?= esc($b['nama_kegiatan']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($b['lokasi'] ?? ''): ?>
                                    <div class="small text-muted">
                                        <i class="fa fa-map-marker-alt fa-xs me-1"></i><?= esc($b['lokasi']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?php if ($b['nama_rekening']): ?>
                                    <?= esc($b['nama_rekening']) ?>
                                    <?php if ($b['nama_bank']): ?><br><span class="text-muted"><?= esc($b['nama_bank']) ?></span><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border small">
                                    <?= esc($b['kode_dana'] ?? $b['nama_dana']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                <?= fmtRp((float)$b['total_debet']) ?>
                            </td>
                            <td class="text-center small">
                                <?php if ($b['is_tutup']): ?>
                                    <span class="badge bg-secondary">Tutup</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('biaya/' . $b['id']) ?>"
                                   class="btn btn-sm btn-outline-primary py-0 px-2" title="Detail">
                                    <i class="fa fa-eye fa-xs"></i>
                                </a>
                                <?php if (! $b['is_tutup']): ?>
                                <a href="<?= base_url('biaya/delete/' . $b['id']) ?>"
                                   class="btn btn-sm btn-outline-danger py-0 px-2 ms-1" title="Hapus"
                                   onclick="return confirm('Hapus biaya <?= esc($b['nomor_jurnal']) ?>?')">
                                    <i class="fa fa-trash fa-xs"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="px-3 fw-semibold text-end">Total:</td>
                            <td class="text-end fw-bold text-danger"><?= fmtRp($totalBiaya) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
