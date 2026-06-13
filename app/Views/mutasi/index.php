<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><?= esc($pageTitle) ?></h4>
            <small class="text-muted">Daftar mutasi / transfer antar rekening bank</small>
        </div>
        <a href="<?= base_url('mutasi/input') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Input Mutasi
        </a>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="fa fa-check-circle me-1"></i> <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="fa fa-triangle-exclamation me-1"></i> <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Tanggal</th>
                            <th>No. Jurnal</th>
                            <th>Rekening Asal</th>
                            <th>Rekening Tujuan</th>
                            <th>Uraian</th>
                            <th class="text-end">Jumlah</th>
                            <th>Periode</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fa fa-right-left fa-2x d-block mb-2 opacity-25"></i>
                                    Belum ada data mutasi
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($daftar as $r): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $no++ ?></td>
                                    <td class="text-nowrap">
                                        <?= $r['tanggal'] ? date('d/m/Y', strtotime($r['tanggal'])) : '—' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-dark border border-info-subtle fw-normal font-monospace">
                                            <?= esc($r['nomor_jurnal']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-danger">
                                            <i class="fa fa-arrow-up fa-xs me-1"></i>
                                        </span>
                                        <?= esc($r['rekening_asal'] ?? '—') ?>
                                    </td>
                                    <td>
                                        <span class="text-success">
                                            <i class="fa fa-arrow-down fa-xs me-1"></i>
                                        </span>
                                        <?= esc($r['rekening_tujuan'] ?? '—') ?>
                                    </td>
                                    <td class="text-muted"><?= esc($r['uraian']) ?></td>
                                    <td class="text-end fw-semibold text-nowrap">
                                        Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">
                                            <?= esc($r['nama_periode'] ?? '—') ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if (! $r['is_tutup']): ?>
                                            <a href="<?= base_url('mutasi/delete/' . $r['id']) ?>"
                                               class="btn btn-outline-danger btn-sm"
                                               title="Hapus"
                                               onclick="return confirm('Hapus mutasi ini?\nJurnal terkait juga akan dihapus.')">
                                                <i class="fa fa-trash fa-xs"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small" title="Periode sudah ditutup">
                                                <i class="fa fa-lock fa-xs"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="6" class="ps-3 text-end text-muted small">
                                    Total <?= count($daftar) ?> transaksi
                                </td>
                                <td class="text-end">
                                    Rp <?= number_format(array_sum(array_column($daftar, 'jumlah')), 0, ',', '.') ?>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>