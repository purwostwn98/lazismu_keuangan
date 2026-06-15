<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .badge-penerimaan {
        background: #198754;
        color: #fff;
    }

    .badge-penyaluran {
        background: #0d6efd;
        color: #fff;
    }

    .badge-biaya {
        background: #fd7e14;
        color: #fff;
    }

    .badge-transfer {
        background: #6f42c1;
        color: #fff;
    }

    .badge-jurnal_umum {
        background: #6c757d;
        color: #fff;
    }

    .badge-koreksi {
        background: #dc3545;
        color: #fff;
    }

    .badge-piutang {
        background: #20c997;
        color: #fff;
    }

    .jurnal-header {
        cursor: pointer;
        user-select: none;
        transition: background .15s;
    }

    .jurnal-header:hover td {
        background: #f0f4ff !important;
    }

    .jurnal-detail-row td {
        background: #f8f9fa;
        font-size: .8rem;
        color: #495057;
        border-top: none;
    }

    .num-right {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .num-zero {
        color: #adb5bd;
    }

    .toggle-icon {
        transition: transform .2s;
        display: inline-block;
    }

    .collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$filter       = $filter       ?? [];
$jurnalList   = $jurnalList   ?? [];
$periodeList  = $periodeList  ?? [];
$jenisDanaList = $jenisDanaList ?? [];
$totalDebet   = $totalDebet   ?? 0;
$totalKredit  = $totalKredit  ?? 0;

$tipeBadge = [
    'penerimaan'  => ['label' => 'Penerimaan',  'class' => 'badge-penerimaan'],
    'penyaluran'  => ['label' => 'Penyaluran',  'class' => 'badge-penyaluran'],
    'biaya'       => ['label' => 'Biaya',        'class' => 'badge-biaya'],
    'transfer'    => ['label' => 'Transfer',     'class' => 'badge-transfer'],
    'jurnal_umum' => ['label' => 'Jurnal Umum',  'class' => 'badge-jurnal_umum'],
    'koreksi'     => ['label' => 'Koreksi',      'class' => 'bg-danger'],
    'piutang'     => ['label' => 'Piutang',      'class' => 'badge-piutang'],
];
function fmtRp(float $v): string
{
    return number_format($v, 0, ',', '.');
}
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Buku Jurnal</h4>
            <small class="text-muted">Seluruh transaksi jurnal — LAZISMU UMS</small>
        </div>
        <a href="<?= base_url('jurnal/input') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Tambah Jurnal
        </a>
    </div>

    <!-- Alert -->
    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="fa fa-check-circle me-1"></i><?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fa fa-exclamation-circle me-1"></i><?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label small mb-1">Periode</label>
                    <select name="periode" class="form-select form-select-sm">
                        <option value="">— Semua Periode —</option>
                        <?php foreach ($periodeList as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $filter['periode'] == $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small mb-1">Jenis Transaksi</label>
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="">— Semua —</option>
                        <?php foreach ($tipeBadge as $k => $t): ?>
                            <option value="<?= $k ?>" <?= $filter['jenis'] === $k ? 'selected' : '' ?>>
                                <?= $t['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-2">
                    <label class="form-label small mb-1">Jenis Dana</label>
                    <select name="dana" class="form-select form-select-sm">
                        <option value="">— Semua Dana —</option>
                        <?php foreach ($jenisDanaList as $jd): ?>
                            <option value="<?= $jd['id'] ?>" <?= $filter['dana'] == $jd['id'] ? 'selected' : '' ?>>
                                <?= esc($jd['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label small mb-1">Cari</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                        placeholder="No. jurnal / uraian…" value="<?= esc($filter['q'] ?? '') ?>">
                </div>
                <div class="col-sm-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="fa fa-search me-1"></i> Filter
                    </button>
                    <a href="<?= base_url('jurnal') ?>" class="btn btn-sm btn-outline-secondary flex-fill">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Jumlah Jurnal</div>
                <div class="fw-bold fs-5"><?= count($jurnalList) ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Total Debet</div>
                <div class="fw-bold fs-6 text-success">Rp <?= fmtRp($totalDebet) ?></div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="text-muted small">Total Kredit</div>
                <div class="fw-bold fs-6 text-primary">Rp <?= fmtRp($totalKredit) ?></div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                    <thead style="background:#1a3f6f;color:#fff;">
                        <tr>
                            <th style="width:30px;"></th>
                            <th>No. Jurnal</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Dana</th>
                            <th>Uraian</th>
                            <th class="num-right">Debet (Rp)</th>
                            <th class="num-right">Kredit (Rp)</th>
                            <th style="width:80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jurnalList)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                    Tidak ada data jurnal.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jurnalList as $idx => $j):
                                $badge     = $tipeBadge[$j['jenis_transaksi']] ?? ['label' => $j['jenis_transaksi'], 'class' => 'badge-secondary'];
                                $canDelete = in_array($j['jenis_transaksi'], ['biaya', 'jurnal_umum', 'koreksi']) && !$j['is_tutup'];
                                $rowId     = 'det-' . $j['id'];
                            ?>
                                <!-- Header row (clickable) -->
                                <tr class="jurnal-header" data-bs-toggle="collapse"
                                    data-bs-target="#<?= $rowId ?>" aria-expanded="false">
                                    <td class="text-center text-muted">
                                        <span class="toggle-icon">▼</span>
                                    </td>
                                    <td class="fw-semibold text-nowrap"><?= esc($j['nomor_jurnal']) ?></td>
                                    <td class="text-nowrap"><?= date('d/m/Y', strtotime($j['tanggal'])) ?></td>
                                    <td>
                                        <span class="badge <?= $badge['class'] ?>" style="font-size:.7rem;">
                                            <?= $badge['label'] ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border" style="font-size:.7rem;"><?= esc($j['kode_dana']) ?></span></td>
                                    <td><?= esc($j['uraian']) ?></td>
                                    <td class="num-right"><?= fmtRp($j['total_debet']) ?></td>
                                    <td class="num-right"><?= fmtRp($j['total_kredit']) ?></td>
                                    <td class="text-center">
                                        <?php if ($canDelete): ?>
                                            <a href="<?= base_url('jurnal/delete/' . $j['id']) ?>"
                                                class="btn btn-outline-danger btn-sm py-0 px-1"
                                                onclick="return confirm('Hapus jurnal <?= esc($j['nomor_jurnal']) ?>?')"
                                                title="Hapus">
                                                <i class="fa fa-trash fa-xs"></i>
                                            </a>
                                        <?php elseif ($j['is_tutup']): ?>
                                            <i class="fa fa-lock text-secondary" title="Periode terkunci"></i>
                                        <?php else: ?>
                                            <i class="fa fa-shield-halved text-secondary" title="Dikelola modul lain"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Detail rows (collapsible) -->
                                <tr class="collapse" id="<?= $rowId ?>">
                                    <td colspan="9" class="p-0">
                                        <table class="table table-sm mb-0" style="font-size:.78rem;border-top:2px solid #dee2e6;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:30px;"></th>
                                                    <th>Nomor Akun</th>
                                                    <th>Nama Akun</th>
                                                    <th>Rekening</th>
                                                    <th>Uraian</th>
                                                    <th class="num-right">Debet (Rp)</th>
                                                    <th class="num-right">Kredit (Rp)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($j['details'])): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-muted text-center py-2">
                                                            Tidak ada detail jurnal.
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($j['details'] as $di => $det): ?>
                                                        <tr class="jurnal-detail-row">
                                                            <td class="text-center text-muted"><?= $di + 1 ?></td>
                                                            <td class="font-monospace"><?= esc($det['nomor_akun']) ?></td>
                                                            <td><?= esc($det['nama_akun']) ?></td>
                                                            <td><?= esc($det['nama_rekening'] ?? '—') ?></td>
                                                            <td><?= esc($det['uraian_det'] ?? '—') ?></td>
                                                            <td class="num-right <?= $det['debet'] == 0 ? 'num-zero' : '' ?>">
                                                                <?= $det['debet'] > 0 ? fmtRp($det['debet']) : '—' ?>
                                                            </td>
                                                            <td class="num-right <?= $det['kredit'] == 0 ? 'num-zero' : '' ?>">
                                                                <?= $det['kredit'] > 0 ? fmtRp($det['kredit']) : '—' ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        <?php if ($j['keterangan']): ?>
                                            <div class="px-3 py-1 text-muted small border-top bg-light">
                                                <i class="fa fa-note-sticky me-1"></i><?= esc($j['keterangan']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                            <!-- Total row -->
                            <tr class="table-light fw-bold">
                                <td colspan="6" class="text-end">Total</td>
                                <td class="num-right"><?= fmtRp($totalDebet) ?></td>
                                <td class="num-right"><?= fmtRp($totalKredit) ?></td>
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
        Klik baris jurnal untuk melihat detail debet/kredit.
        Jurnal dari modul Penerimaan, Penyaluran, dan Mutasi tidak dapat dihapus dari sini.
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Rotate toggle icon when collapse opens/closes
    document.querySelectorAll('.jurnal-header').forEach(function(row) {
        var targetId = row.getAttribute('data-bs-target');
        var detail = document.querySelector(targetId);
        var icon = row.querySelector('.toggle-icon');
        if (!detail || !icon) return;

        detail.addEventListener('show.bs.collapse', function() {
            icon.style.transform = 'rotate(0deg)';
        });
        detail.addEventListener('hide.bs.collapse', function() {
            icon.style.transform = 'rotate(-90deg)';
        });

        // Start collapsed
        icon.style.transform = 'rotate(-90deg)';
    });
</script>
<?= $this->endSection() ?>