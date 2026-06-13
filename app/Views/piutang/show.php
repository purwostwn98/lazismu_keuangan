<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$piutang     = $piutang     ?? [];
$cicilan     = $cicilan     ?? [];
$periodeList = $periodeList ?? [];
$periodeAktif= $periodeAktif?? null;
$rekeningList= $rekeningList?? [];
$jenisLabels = $jenisLabels ?? [];

function fmtRp(float $v): string { return 'Rp ' . number_format($v, 0, ',', '.'); }

$statusBadge = [
    'aktif'      => ['label' => 'Aktif',      'class' => 'bg-warning text-dark'],
    'lunas'      => ['label' => 'Lunas',      'class' => 'bg-success'],
    'hapus_buku' => ['label' => 'Hapus Buku', 'class' => 'bg-secondary'],
];
$sb          = $statusBadge[$piutang['status']] ?? ['label' => $piutang['status'], 'class' => 'bg-secondary'];
$persen      = $piutang['jumlah_pokok'] > 0
    ? round(($piutang['jumlah_terbayar'] / $piutang['jumlah_pokok']) * 100, 1)
    : 0;
$overdue     = $piutang['status'] === 'aktif'
    && $piutang['tanggal_jatuh_tempo']
    && $piutang['tanggal_jatuh_tempo'] < date('Y-m-d');
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold"><?= esc($piutang['nomor_piutang']) ?></h4>
            <small class="text-muted"><?= esc($jenisLabels[$piutang['jenis']] ?? $piutang['jenis']) ?></small>
        </div>
        <div class="d-flex gap-2">
            <span class="badge <?= $sb['class'] ?> align-self-center"><?= $sb['label'] ?></span>
            <a href="<?= base_url('piutang') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="fa fa-check-circle me-1"></i><?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 small">
            <ul class="mb-0 ps-3">
                <?php foreach (session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($overdue): ?>
        <div class="alert alert-danger py-2 small">
            <i class="fa fa-exclamation-triangle me-1"></i>
            Piutang ini sudah <strong>jatuh tempo</strong> sejak
            <?= date('d/m/Y', strtotime($piutang['tanggal_jatuh_tempo'])) ?>.
        </div>
    <?php endif; ?>

    <div class="row g-3">

        <!-- Detail piutang -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-2 fw-semibold" style="background:#1a3f6f;color:#fff;">
                    <i class="fa fa-info-circle me-1"></i> Informasi Piutang
                </div>
                <div class="card-body" style="font-size:.85rem;">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:45%">Penerima</td>
                            <td class="fw-semibold"><?= esc($piutang['nama_penerima'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Asnaf</td>
                            <td><?= esc($piutang['asnaf'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. HP</td>
                            <td><?= esc($piutang['no_hp'] ?? '—') ?></td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-1"></td></tr>
                        <tr>
                            <td class="text-muted">Jenis Dana</td>
                            <td><span class="badge bg-light text-dark border"><?= esc($piutang['kode_dana'] ?? '') ?></span>
                                <?= esc($piutang['nama_dana'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Pinjam</td>
                            <td><?= date('d/m/Y', strtotime($piutang['tanggal_pinjam'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jatuh Tempo</td>
                            <td class="<?= $overdue ? 'text-danger fw-bold' : '' ?>">
                                <?= $piutang['tanggal_jatuh_tempo'] ? date('d/m/Y', strtotime($piutang['tanggal_jatuh_tempo'])) : '—' ?>
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr class="my-1"></td></tr>
                        <tr>
                            <td class="text-muted">Jumlah Pokok</td>
                            <td class="fw-bold"><?= fmtRp((float)$piutang['jumlah_pokok']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sudah Dibayar</td>
                            <td class="text-success fw-bold"><?= fmtRp((float)$piutang['jumlah_terbayar']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sisa Piutang</td>
                            <td class="text-danger fw-bold"><?= fmtRp((float)$piutang['sisa_piutang']) ?></td>
                        </tr>
                    </table>

                    <!-- Progress bar -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Kemajuan Pelunasan</span>
                            <span><?= $persen ?>%</span>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-success" style="width:<?= $persen ?>%"></div>
                        </div>
                    </div>

                    <?php if ($piutang['keterangan']): ?>
                        <div class="mt-3 small text-muted">
                            <i class="fa fa-note-sticky me-1"></i><?= esc($piutang['keterangan']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($piutang['status'] === 'aktif'): ?>
                        <div class="mt-3 d-flex gap-2">
                            <a href="<?= base_url('piutang/hapus-buku/' . $piutang['id']) ?>"
                               class="btn btn-sm btn-outline-secondary"
                               onclick="return confirm('Hapus buku piutang ini? Status akan menjadi hapus_buku (write-off).')">
                                <i class="fa fa-ban me-1"></i> Hapus Buku
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Riwayat cicilan & form bayar -->
        <div class="col-lg-7">

            <!-- Riwayat cicilan -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header py-2 fw-semibold" style="background:#1a3f6f;color:#fff;">
                    <i class="fa fa-history me-1"></i> Riwayat Pembayaran
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-end">Jumlah (Rp)</th>
                                <th>No. Jurnal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($cicilan)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Belum ada pembayaran.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $totalCicilan = 0; ?>
                            <?php foreach ($cicilan as $c): ?>
                                <?php $totalCicilan += (float)$c['jumlah']; ?>
                                <tr>
                                    <td class="text-nowrap"><?= date('d/m/Y', strtotime($c['tanggal'])) ?></td>
                                    <td class="text-end text-success fw-semibold">
                                        <?= number_format($c['jumlah'], 0, ',', '.') ?>
                                    </td>
                                    <td class="font-monospace small"><?= esc($c['nomor_jurnal'] ?? '—') ?></td>
                                    <td><?= esc($c['keterangan'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-bold">
                                <td>Total Dibayar</td>
                                <td class="text-end text-success"><?= number_format($totalCicilan, 0, ',', '.') ?></td>
                                <td colspan="2"></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form bayar cicilan -->
            <?php if ($piutang['status'] === 'aktif'): ?>
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-header py-2 fw-semibold text-success">
                    <i class="fa fa-money-bill-wave me-1"></i> Input Pembayaran Cicilan
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('piutang/bayar/' . $piutang['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Tanggal Bayar <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control form-control-sm"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                            <input type="text" name="jumlah" class="form-control form-control-sm text-end"
                                   placeholder="Maks: <?= number_format($piutang['sisa_piutang'], 0, ',', '.') ?>"
                                   autocomplete="off" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Rekening Penerimaan <span class="text-danger">*</span></label>
                            <select name="rekening_id" class="form-select form-select-sm" required>
                                <option value="">— Pilih Rekening —</option>
                                <?php foreach ($rekeningList as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= esc($r['nama']) ?> (<?= esc($r['nama_dana']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold">Periode <span class="text-danger">*</span></label>
                            <select name="periode_id" class="form-select form-select-sm" required>
                                <option value="">— Pilih Periode —</option>
                                <?php foreach ($periodeList as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        <?= ($periodeAktif['id'] ?? 0) == $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control form-control-sm"
                                   placeholder="Opsional — cicilan ke-…, dll" maxlength="255">
                        </div>
                        <div class="col-12 mt-1">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa fa-save me-1"></i> Simpan Pembayaran
                            </button>
                            <span class="text-muted small ms-2">
                                Jurnal Debit Kas / Kredit Piutang dibuat otomatis.
                            </span>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?= $this->endSection() ?>
