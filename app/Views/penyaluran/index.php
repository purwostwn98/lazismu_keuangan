<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success d-flex gap-2 align-items-center py-2 mb-4">
        <i class="fas fa-circle-check"></i>
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fs-2 fw-bold text-primary"><?= count($penyaluran ?? []) ?></div>
            <div class="text-muted" style="font-size:.78rem;">Total Transaksi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div class="fw-bold text-danger" style="font-size:1.1rem;">
                Rp <?= number_format($totalJumlah ?? 0, 0, ',', '.') ?>
            </div>
            <div class="text-muted" style="font-size:.78rem;">Total Penyaluran</div>
        </div>
    </div>
    <div class="col-12 col-md-6 d-flex align-items-center justify-content-md-end">
        <a href="<?= base_url('penyaluran/input') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Input Penyaluran
        </a>
    </div>
</div>

<!-- Filter -->
<?php $tahunList ??= [];
$periodeList ??= [];
$filter ??= [];
$jenisDana ??= []; ?>
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="get" class="row g-2 align-items-end">

            <!-- Tahun -->
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Tahun</label>
                <select name="tahun" id="filterTahun" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($tahunList as $t): ?>
                        <option value="<?= $t ?>" <?= (int)($filter['tahun'] ?? 0) === $t ? 'selected' : '' ?>>
                            <?= $t ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Periode (bulan, cascade dari tahun) -->
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Periode</label>
                <select name="periode" id="filterPeriode" class="form-select form-select-sm">
                    <option value="">Semua Periode</option>
                    <?php foreach ($periodeList as $p): ?>
                        <option value="<?= $p['id'] ?>"
                            data-tahun="<?= $p['tahun'] ?>"
                            <?= (int)($filter['periode_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>
                            style="display:none;">
                            <?= esc($p['nama']) ?><?= $p['is_tutup'] ? ' [Tutup]' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Jenis Dana -->
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Jenis Dana</label>
                <select name="dana" class="form-select form-select-sm">
                    <option value="">Semua Dana</option>
                    <?php foreach ($jenisDana as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($filter['jenis_dana_id'] == $d['id']) ? 'selected' : '' ?>>
                            <?= esc($d['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Cari -->
            <div class="col-12 col-md">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Cari</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control"
                        placeholder="Nomor jurnal atau uraian..."
                        value="<?= esc($filter['q'] ?? '') ?>">
                </div>
            </div>

            <!-- Tombol -->
            <div class="col-auto d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="<?= base_url('penyaluran') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        var filterTahun = document.getElementById('filterTahun');
        var filterPeriode = document.getElementById('filterPeriode');
        var opts = filterPeriode.querySelectorAll('option[data-tahun]');

        function syncPeriode() {
            var tahun = filterTahun.value;
            opts.forEach(function(opt) {
                var show = tahun === '' || opt.dataset.tahun === tahun;
                opt.style.display = show ? '' : 'none';
                if (!show && opt.selected) {
                    opt.selected = false;
                    filterPeriode.value = '';
                }
            });
        }

        syncPeriode();
        filterTahun.addEventListener('change', function() {
            filterPeriode.value = '';
            syncPeriode();
        });
    })();
</script>

<!-- Tabel -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-card mb-0">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>No. Jurnal</th>
                    <th>Tanggal</th>
                    <th>Uraian</th>
                    <th>Jenis Dana</th>
                    <th>Penerima</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-center" style="width:80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($penyaluran)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-inbox d-block mb-2" style="font-size:2rem;opacity:.25;"></i>
                            Belum ada data penyaluran.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($penyaluran as $i => $row): ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td>
                                <span class="badge badge-soft-orange fw-normal" style="font-size:.78rem; letter-spacing:.3px;">
                                    <?= esc($row['nomor_jurnal']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td style="max-width:220px;">
                                <div class="fw-semibold" style="font-size:.83rem;">
                                    <?= esc($row['uraian']) ?>
                                </div>
                                <?php if ($row['keterangan']): ?>
                                    <div class="text-muted" style="font-size:.75rem;">
                                        <?= esc(mb_strimwidth($row['keterangan'], 0, 50, '...')) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-soft-blue">
                                    <?= esc($row['nama_dana']) ?>
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:.82rem;">
                                <?= esc($row['nama_penerima'] ?? '—') ?>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                Rp <?= number_format($row['total_debet'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('penyaluran/' . $row['id']) ?>"
                                    class="btn btn-sm btn-outline-primary px-2 py-1"
                                    title="Detail">
                                    <i class="fas fa-eye" style="font-size:.75rem;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($penyaluran)): ?>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="6" class="text-end">Total</th>
                        <th class="text-end text-danger">
                            Rp <?= number_format($totalJumlah ?? 0, 0, ',', '.') ?>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?= $this->endSection() ?>