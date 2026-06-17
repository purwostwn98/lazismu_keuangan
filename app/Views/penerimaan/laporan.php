<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .laporan-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-laporan {
        min-width: 1000px;
        font-size: .78rem;
        border-collapse: collapse;
        width: 100%;
    }

    .table-laporan th,
    .table-laporan td {
        padding: .3rem .5rem;
        vertical-align: middle;
        white-space: nowrap;
        border: 1px solid #dee2e6;
    }

    .col-label {
        position: sticky;
        left: 0;
        z-index: 2;
        background: #fff;
        min-width: 240px;
        max-width: 280px;
        white-space: normal;
    }

    .row-section td {
        background: #e8f0fb !important;
        font-weight: 700;
        color: #1a3f6f;
        border-top: 2px solid #c0d0ee;
        border-bottom: 1px solid #c0d0ee;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .row-data td { background: #fff; }
    .row-data:hover td { background: #f5f7ff !important; }

    .row-subsection td {
        background: #f0f5ff !important;
        font-weight: 600;
        color: #2c4a8a;
        border-top: 1px solid #c8d8f5;
        font-style: italic;
        padding-left: 1.2rem !important;
    }

    .row-subtotal td {
        background: #fff !important;
        font-weight: 700;
        border-top: 1px solid #6c757d;
        border-bottom: 3px double #495057;
    }

    .row-dana-total td {
        background: #dce8fc !important;
        font-weight: 700;
        font-size: .8rem;
        text-transform: uppercase;
        border-top: 2px solid #5b82c8;
        border-bottom: 2px solid #5b82c8;
        color: #1a3f6f;
    }

    .row-total td {
        background: #f0f4ff !important;
        font-weight: 700;
        font-size: .82rem;
        text-transform: uppercase;
        border-top: 2px solid #1a3f6f;
        border-bottom: 3px double #1a3f6f;
        color: #0d2b5e;
    }

    .row-spacer td {
        height: 8px;
        background: #f8f9fc;
        border: none;
    }

    .num-cell {
        text-align: right;
        font-variant-numeric: tabular-nums;
        min-width: 80px;
    }

    .num-zero { color: #ced4da; }
    .total-cell { text-align: right; font-variant-numeric: tabular-nums; min-width: 100px; }

    @media print {
        #sidebar, #main-content > nav { display: none !important; }
        .no-print { display: none !important; }
        .laporan-wrapper { overflow: visible; }
        .col-label { position: static; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$tahunList  = $tahunList  ?? [];
$tahun      = $tahun      ?? date('Y');
$bulanNames = $bulanNames ?? [];
$rows       = $rows       ?? [];
$grandTotal = $grandTotal ?? array_fill(1, 12, 0.0);
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Laporan Penghimpunan ZIS</h4>
            <small class="text-muted">Rekapitulasi penerimaan ZIS per jenis dan per bulan</small>
        </div>
        <div class="d-flex gap-2 align-items-center no-print">
            <form method="get" class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0 text-nowrap small fw-semibold">Tahun:</label>
                <select name="tahun" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    <?php foreach ($tahunList as $y): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-print me-1"></i> Cetak
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-3 no-print">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3 text-center">
                    <div class="text-muted small mb-1">Total Penghimpunan <?= $tahun ?></div>
                    <div class="fw-bold fs-6 text-primary">Rp <?= number_format($totalAll ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3 text-center">
                    <div class="text-muted small mb-1">Total Zakat</div>
                    <div class="fw-bold fs-6 text-warning">Rp <?= number_format($totalZakat ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3 text-center">
                    <div class="text-muted small mb-1">Total Infak / Sedekah</div>
                    <div class="fw-bold fs-6 text-info">Rp <?= number_format($totalInfak ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Heading (for print) -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 text-center">
            <div class="fw-bold">LAZISMU UNIVERSITAS MUHAMMADIYAH SURAKARTA</div>
            <div class="fw-bold fs-6 mt-1">LAPORAN PENGHIMPUNAN ZIS</div>
            <div class="text-muted small mt-1">Tahun <?= $tahun ?> &nbsp;·&nbsp; (Dalam Satuan Rupiah)</div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="laporan-wrapper">
                <table class="table-laporan mb-0">
                    <thead>
                        <tr>
                            <th class="col-label" style="background:#1a3f6f;color:#fff;border-color:#1a3f6f;z-index:3;">
                                Jenis ZIS
                            </th>
                            <?php foreach ($bulanNames as $b => $nama): ?>
                                <th class="num-cell text-center" style="background:#1a3f6f;color:#fff;border-color:#1a3f6f;">
                                    <?= $nama ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="total-cell text-center" style="background:#0d2b5e;color:#fff;border-color:#0d2b5e;">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row):
                            $type  = $row['type'];
                            $label = $row['label'];
                            $vals  = $row['vals'] ?? [];
                            $rowTotal = array_sum($vals);
                            $cols = count($bulanNames) + 2;
                        ?>

                            <?php if ($type === 'spacer'): ?>
                                <tr class="row-spacer"><td colspan="<?= $cols ?>"></td></tr>

                            <?php elseif ($type === 'section'): ?>
                                <tr class="row-section">
                                    <td class="col-label"><?= esc($label) ?></td>
                                    <?php foreach ($bulanNames as $b => $nm): ?>
                                        <td class="num-cell"></td>
                                    <?php endforeach; ?>
                                    <td class="total-cell"></td>
                                </tr>

                            <?php elseif ($type === 'subsection'): ?>
                                <tr class="row-subsection">
                                    <td class="col-label ps-4"><?= esc($label) ?></td>
                                    <?php foreach ($bulanNames as $b => $nm): ?>
                                        <td class="num-cell"></td>
                                    <?php endforeach; ?>
                                    <td class="total-cell"></td>
                                </tr>

                            <?php elseif ($type === 'data'): ?>
                                <tr class="row-data">
                                    <td class="col-label ps-5"><?= esc($label) ?></td>
                                    <?php foreach ($bulanNames as $b => $nm):
                                        $v = $vals[$b] ?? 0;
                                    ?>
                                        <td class="num-cell <?= $v == 0 ? 'num-zero' : '' ?>">
                                            <?= $v != 0 ? number_format($v, 0, ',', '.') : '—' ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="total-cell <?= $rowTotal == 0 ? 'num-zero' : 'fw-semibold' ?>">
                                        <?= $rowTotal != 0 ? number_format($rowTotal, 0, ',', '.') : '—' ?>
                                    </td>
                                </tr>

                            <?php elseif ($type === 'subtotal'): ?>
                                <tr class="row-subtotal">
                                    <td class="col-label ps-4"><?= esc($label) ?></td>
                                    <?php foreach ($bulanNames as $b => $nm):
                                        $v = $vals[$b] ?? 0;
                                    ?>
                                        <td class="num-cell <?= $v == 0 ? 'num-zero' : '' ?>">
                                            <?= $v != 0 ? number_format($v, 0, ',', '.') : '—' ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="total-cell"><?= number_format($rowTotal, 0, ',', '.') ?></td>
                                </tr>

                            <?php elseif ($type === 'dana_total'): ?>
                                <tr class="row-dana-total">
                                    <td class="col-label ps-2"><?= esc($label) ?></td>
                                    <?php foreach ($bulanNames as $b => $nm):
                                        $v = $vals[$b] ?? 0;
                                    ?>
                                        <td class="num-cell"><?= number_format($v, 0, ',', '.') ?></td>
                                    <?php endforeach; ?>
                                    <td class="total-cell"><?= number_format($rowTotal, 0, ',', '.') ?></td>
                                </tr>

                            <?php elseif ($type === 'total'): ?>
                                <tr class="row-total">
                                    <td class="col-label"><?= esc($label) ?></td>
                                    <?php foreach ($bulanNames as $b => $nm):
                                        $v = $vals[$b] ?? 0;
                                    ?>
                                        <td class="num-cell"><?= number_format($v, 0, ',', '.') ?></td>
                                    <?php endforeach; ?>
                                    <td class="total-cell"><?= number_format($rowTotal, 0, ',', '.') ?></td>
                                </tr>

                            <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>