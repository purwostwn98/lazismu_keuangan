<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$list          = $list          ?? [];
$filter        = $filter        ?? [];
$totalNilai    = $totalNilai    ?? 0;
$jenisDanaList = $jenisDanaList ?? [];

function fmtRp(float $v): string { return 'Rp ' . number_format($v, 0, ',', '.'); }
function fmtQty(float $v, string $sat): string { return number_format($v, 3, ',', '.') . ' ' . $sat; }
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Persediaan Barang</h4>
            <small class="text-muted">Stok persediaan natura — LAZISMU UMS</small>
        </div>
        <a href="<?= base_url('persediaan/input') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Tambah Barang
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

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label small mb-1">Cari Barang</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Kode / nama barang…" value="<?= esc($filter['q'] ?? '') ?>">
                </div>
                <div class="col-sm-3">
                    <label class="form-label small mb-1">Jenis Dana</label>
                    <select name="dana" class="form-select form-select-sm">
                        <option value="">— Semua Dana —</option>
                        <?php foreach ($jenisDanaList as $jd): ?>
                            <option value="<?= $jd['id'] ?>" <?= ($filter['jenis_dana_id'] ?? '') == $jd['id'] ? 'selected' : '' ?>>
                                <?= esc($jd['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="fa fa-search me-1"></i> Filter
                    </button>
                    <a href="<?= base_url('persediaan') ?>" class="btn btn-sm btn-outline-secondary flex-fill">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Jenis Barang</div>
                <div class="fw-bold fs-5"><?= count($list) ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Nilai Stok Total</div>
                <div class="fw-bold fs-6 text-success"><?= fmtRp($totalNilai) ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Stok Kritis (= 0)</div>
                <?php $kritis = count(array_filter($list, fn($r) => ($r['stok_akhir'] ?? 0) <= 0)); ?>
                <div class="fw-bold fs-5 <?= $kritis > 0 ? 'text-danger' : 'text-muted' ?>"><?= $kritis ?></div>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0" style="font-size:.83rem;">
                    <thead style="background:#1a3f6f;color:#fff;">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th>Dana</th>
                            <th>Akun</th>
                            <th class="text-end">Masuk</th>
                            <th class="text-end">Keluar</th>
                            <th class="text-end">Stok Akhir</th>
                            <th class="text-end">Nilai/Satuan</th>
                            <th class="text-end">Nilai Stok</th>
                            <th class="text-center" style="width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fa fa-boxes-stacked fa-2x mb-2 d-block"></i>
                                Belum ada data persediaan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($list as $row):
                            $stok = (float) ($row['stok_akhir'] ?? 0);
                        ?>
                            <tr>
                                <td class="font-monospace small"><?= esc($row['kode_barang']) ?></td>
                                <td class="fw-semibold"><?= esc($row['nama_barang']) ?></td>
                                <td><?= esc($row['satuan']) ?></td>
                                <td>
                                    <?php if ($row['kode_dana']): ?>
                                        <span class="badge bg-light text-dark border small"><?= esc($row['kode_dana']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= esc($row['nomor_akun']) ?></td>
                                <td class="text-end"><?= number_format($row['stok_masuk'], 3, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($row['stok_keluar'], 3, ',', '.') ?></td>
                                <td class="text-end fw-semibold <?= $stok <= 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= number_format($stok, 3, ',', '.') ?>
                                </td>
                                <td class="text-end"><?= number_format($row['nilai_per_satuan'], 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($row['nilai_stok'] ?? 0, 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('persediaan/' . $row['id']) ?>"
                                       class="btn btn-sm btn-outline-primary py-0 px-1" title="Kartu Stok">
                                        <i class="fa fa-eye fa-xs"></i>
                                    </a>
                                    <a href="<?= base_url('persediaan/delete/' . $row['id']) ?>"
                                       class="btn btn-sm btn-outline-danger py-0 px-1"
                                       onclick="return confirm('Hapus barang <?= esc($row['nama_barang']) ?>?')"
                                       title="Hapus">
                                        <i class="fa fa-trash fa-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Total -->
                        <tr class="table-light fw-bold">
                            <td colspan="9" class="text-end">Total Nilai Stok</td>
                            <td class="text-end"><?= number_format($totalNilai, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-2 px-1">
        <i class="fa fa-info-circle me-1"></i>
        Klik ikon <i class="fa fa-eye"></i> untuk melihat kartu stok dan mencatat mutasi masuk/keluar.
    </div>
</div>
<?= $this->endSection() ?>
