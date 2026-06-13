<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$daftar      = $daftar      ?? [];
$filter      = $filter      ?? [];
$jenisLabels = $jenisLabels ?? [];
$totalPokok  = $totalPokok  ?? 0;
$totalTerbayar = $totalTerbayar ?? 0;
$totalSisa   = $totalSisa   ?? 0;
$countAktif  = $countAktif  ?? 0;

function fmtRp(float $v): string { return 'Rp ' . number_format($v, 0, ',', '.'); }

$statusBadge = [
    'aktif'      => ['label' => 'Aktif',      'class' => 'bg-warning text-dark'],
    'lunas'      => ['label' => 'Lunas',      'class' => 'bg-success'],
    'hapus_buku' => ['label' => 'Hapus Buku', 'class' => 'bg-secondary'],
];
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Daftar Piutang</h4>
            <small class="text-muted">Qardul Hasan, Penyaluran Bertahap, Talangan Amil</small>
        </div>
        <a href="<?= base_url('piutang/input') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Tambah Piutang
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

    <!-- Summary cards -->
    <div class="row g-3 mb-3">
        <div class="col-sm-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Piutang Aktif</div>
                <div class="fw-bold fs-4 text-warning"><?= $countAktif ?></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Total Pokok</div>
                <div class="fw-bold"><?= fmtRp($totalPokok) ?></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Sudah Dibayar</div>
                <div class="fw-bold text-success"><?= fmtRp($totalTerbayar) ?></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Sisa Outstanding</div>
                <div class="fw-bold text-danger"><?= fmtRp($totalSisa) ?></div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">— Semua Status —</option>
                        <?php foreach ($statusBadge as $k => $s): ?>
                            <option value="<?= $k ?>" <?= ($filter['status'] ?? '') === $k ? 'selected' : '' ?>>
                                <?= $s['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small mb-1">Jenis</label>
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="">— Semua Jenis —</option>
                        <?php foreach ($jenisLabels as $k => $l): ?>
                            <option value="<?= $k ?>" <?= ($filter['jenis'] ?? '') === $k ? 'selected' : '' ?>>
                                <?= $l ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label small mb-1">Cari nama / nomor</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           value="<?= esc($filter['q'] ?? '') ?>" placeholder="Nama penerima atau No. PIU…">
                </div>
                <div class="col-sm-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="fa fa-search me-1"></i>Filter
                    </button>
                    <a href="<?= base_url('piutang') ?>" class="btn btn-sm btn-outline-secondary flex-fill">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                    <thead style="background:#1a3f6f;color:#fff;">
                        <tr>
                            <th>No. Piutang</th>
                            <th>Penerima</th>
                            <th>Jenis</th>
                            <th>Dana</th>
                            <th class="text-end">Pokok (Rp)</th>
                            <th class="text-end">Terbayar (Rp)</th>
                            <th class="text-end">Sisa (Rp)</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th class="text-center" style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($daftar)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                Belum ada data piutang.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar as $p):
                            $sb = $statusBadge[$p['status']] ?? ['label' => $p['status'], 'class' => 'bg-secondary'];
                            $overdue = $p['status'] === 'aktif'
                                && $p['tanggal_jatuh_tempo']
                                && $p['tanggal_jatuh_tempo'] < date('Y-m-d');
                        ?>
                            <tr class="<?= $overdue ? 'table-danger' : '' ?>">
                                <td class="fw-semibold text-nowrap">
                                    <a href="<?= base_url('piutang/' . $p['id']) ?>" class="text-decoration-none">
                                        <?= esc($p['nomor_piutang']) ?>
                                    </a>
                                </td>
                                <td><?= esc($p['nama_penerima'] ?? '—') ?></td>
                                <td class="text-nowrap"><?= esc($jenisLabels[$p['jenis']] ?? $p['jenis']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($p['kode_dana'] ?? '') ?></span></td>
                                <td class="text-end"><?= number_format($p['jumlah_pokok'], 0, ',', '.') ?></td>
                                <td class="text-end text-success"><?= number_format($p['jumlah_terbayar'], 0, ',', '.') ?></td>
                                <td class="text-end <?= (float)$p['sisa_piutang'] > 0 ? 'text-danger fw-semibold' : '' ?>">
                                    <?= number_format($p['sisa_piutang'], 0, ',', '.') ?>
                                </td>
                                <td class="text-nowrap <?= $overdue ? 'text-danger fw-semibold' : '' ?>">
                                    <?= $p['tanggal_jatuh_tempo'] ? date('d/m/Y', strtotime($p['tanggal_jatuh_tempo'])) : '—' ?>
                                    <?= $overdue ? '<i class="fa fa-exclamation-triangle ms-1" title="Jatuh tempo terlewat"></i>' : '' ?>
                                </td>
                                <td><span class="badge <?= $sb['class'] ?>"><?= $sb['label'] ?></span></td>
                                <td class="text-center">
                                    <a href="<?= base_url('piutang/' . $p['id']) ?>"
                                       class="btn btn-outline-primary btn-sm py-0 px-1" title="Detail">
                                        <i class="fa fa-eye fa-xs"></i>
                                    </a>
                                    <?php if ($p['status'] === 'aktif'): ?>
                                        <a href="<?= base_url('piutang/delete/' . $p['id']) ?>"
                                           class="btn btn-outline-danger btn-sm py-0 px-1"
                                           onclick="return confirm('Hapus piutang <?= esc($p['nomor_piutang'], 'js') ?>?')"
                                           title="Hapus">
                                            <i class="fa fa-trash fa-xs"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Total -->
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">Total</td>
                            <td class="text-end"><?= number_format($totalPokok, 0, ',', '.') ?></td>
                            <td class="text-end text-success"><?= number_format($totalTerbayar, 0, ',', '.') ?></td>
                            <td class="text-end text-danger"><?= number_format($totalSisa, 0, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-2 px-1">
        <i class="fa fa-info-circle me-1"></i>
        Baris merah = piutang jatuh tempo terlewat.
        Sisa piutang aktif masuk ke <strong>Laporan Posisi Keuangan</strong> sebagai Aset Lancar &gt; Piutang.
    </div>
</div>
<?= $this->endSection() ?>
