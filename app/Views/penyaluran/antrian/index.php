<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$antrian      = $antrian      ?? [];
$statusFilter = $statusFilter ?? '';
$counts       = $counts       ?? ['pending' => 0, 'verified' => 0, 'rejected' => 0];
?>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        <i class="fas fa-circle-check me-1"></i> <?= session('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
        <i class="fas fa-triangle-exclamation me-1"></i> <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <a href="?status=pending" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?= $statusFilter === 'pending' ? 'border-warning border' : '' ?>">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-2 fs-5 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1 text-warning"><?= $counts['pending'] ?></div>
                        <div class="text-muted small">Menunggu Verifikasi</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="?status=verified" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?= $statusFilter === 'verified' ? 'border-success border' : '' ?>">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 p-2 fs-5 text-success">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1 text-success"><?= $counts['verified'] ?></div>
                        <div class="text-muted small">Terverifikasi</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="?status=rejected" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 <?= $statusFilter === 'rejected' ? 'border-danger border' : '' ?>">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-2 fs-5 text-danger">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold lh-1 text-danger"><?= $counts['rejected'] ?></div>
                        <div class="text-muted small">Ditolak</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filter & Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-list-check text-primary"></i>
            <span class="fw-semibold small">Daftar Antrian</span>
            <?php if ($statusFilter): ?>
                <a href="<?= base_url('penyaluran/antrian') ?>" class="btn btn-outline-secondary btn-sm py-0 px-2">
                    <i class="fas fa-times"></i> Reset
                </a>
            <?php endif; ?>
        </div>
        <span class="badge bg-secondary"><?= count($antrian) ?> data</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Diterima</th>
                        <th>Sumber / Ref</th>
                        <th>Tanggal</th>
                        <th>Program / Penerima</th>
                        <th>Jenis Dana</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($antrian)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-inbox d-block mb-2" style="font-size:2rem;opacity:.25;"></i>
                                Tidak ada data antrian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($antrian as $i => $row): ?>
                            <tr>
                                <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                                <td class="text-muted text-nowrap" style="font-size:.75rem;">
                                    <?= $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '—' ?>
                                </td>
                                <td>
                                    <?php if ($row['sumber']): ?>
                                        <div class="fw-semibold"><?= esc($row['sumber']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($row['ref_eksternal']): ?>
                                        <div class="text-muted font-monospace" style="font-size:.72rem;">
                                            <?= esc($row['ref_eksternal']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                                </td>
                                <td style="max-width:200px;">
                                    <?php if ($row['program_nama']): ?>
                                        <div class="fw-semibold text-truncate"><?= esc($row['program_nama']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($row['nama_penerima']): ?>
                                        <div class="text-muted text-truncate" style="font-size:.78rem;">
                                            <i class="fas fa-user fa-xs me-1"></i><?= esc($row['nama_penerima']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!$row['program_nama'] && !$row['nama_penerima']): ?>
                                        <span class="text-muted fst-italic">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['nama_dana']): ?>
                                        <span class="badge badge-soft-blue"><?= esc($row['nama_dana']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-nowrap">
                                    Rp <?= number_format($row['jumlah'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    [$bg, $txt] = match($row['status']) {
                                        'pending'  => ['bg-warning bg-opacity-15 text-warning', 'Pending'],
                                        'verified' => ['bg-success bg-opacity-15 text-success', 'Verified'],
                                        'rejected' => ['bg-danger  bg-opacity-15 text-danger',  'Ditolak'],
                                        default    => ['bg-secondary bg-opacity-15 text-secondary', $row['status']],
                                    };
                                    ?>
                                    <span class="badge rounded-pill fw-normal <?= $bg ?>" style="font-size:.72rem;">
                                        <?= $txt ?>
                                    </span>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="<?= base_url('penyaluran/antrian/' . $row['id']) ?>"
                                           class="btn btn-sm btn-outline-primary px-2 py-1"
                                           title="<?= $row['status'] === 'pending' ? 'Verifikasi' : 'Detail' ?>">
                                            <?= $row['status'] === 'pending'
                                                ? '<i class="fas fa-check-circle" style="font-size:.75rem;"></i>'
                                                : '<i class="fas fa-eye" style="font-size:.75rem;"></i>' ?>
                                        </a>
                                        <?php if ($row['status'] === 'rejected'): ?>
                                            <form action="<?= base_url('penyaluran/antrian/hapus/' . $row['id']) ?>"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Hapus antrian #<?= $row['id'] ?>? Tindakan ini tidak dapat dibatalkan.')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Hapus">
                                                    <i class="fas fa-trash" style="font-size:.75rem;"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
