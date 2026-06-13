<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .laporan-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-laporan {
        min-width: 1200px;
        font-size: .78rem;
        border-collapse: collapse;
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
        min-width: 280px;
        max-width: 320px;
        white-space: normal;
    }

    .indent-0 { padding-left: 2px  !important; }
    .indent-1 { padding-left: 16px !important; }
    .indent-2 { padding-left: 30px !important; }

    .row-title td {
        background: #1a3f6f !important;
        color: #fff;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        border-color: #1a3f6f;
    }

    .row-section td {
        background: #e8f0fb !important;
        font-weight: 700;
        color: #1a3f6f;
        border-top: 2px solid #c0d0ee;
        border-bottom: 1px solid #c0d0ee;
    }

    .row-group td {
        background: #f8f9fa !important;
        font-style: italic;
        color: #495057;
    }

    .row-data td { background: #fff; }
    .row-data:hover td { background: #f5f7ff !important; }

    .row-subtotal td {
        background: #fff !important;
        font-weight: 700;
        border-top: 1px solid #6c757d;
        border-bottom: 3px double #495057;
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
        height: 10px;
        background: #f0f4ff;
        border: none;
    }

    .num-cell {
        text-align: right;
        font-variant-numeric: tabular-nums;
        min-width: 90px;
    }

    .num-zero { color: #adb5bd; }
    .num-neg  { color: #dc3545; }

    @media print {
        #sidebar, #main-content > nav { display: none !important; }
        .laporan-wrapper { overflow: visible; }
        .col-label { position: static; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$availableYears = $availableYears ?? [];
$tahun          = $tahun ?? date('Y');
$bulanNames     = $bulanNames ?? [];
$rows           = $rows ?? [];
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Laporan Arus Kas</h4>
            <small class="text-muted">Aktivitas Operasi, Investasi &amp; Pendanaan — LAZISMU UMS</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form method="get" class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0 text-nowrap small">Tahun:</label>
                <select name="tahun" class="form-select form-select-sm" style="width:auto"
                        onchange="this.form.submit()">
                    <?php foreach ($availableYears as $y): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-print me-1"></i> Cetak
            </button>
        </div>
    </div>

    <!-- Report heading -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3 text-center">
            <div class="fw-bold">LAZISMU UNIVERSITAS MUHAMMADIYAH SURAKARTA</div>
            <div class="fw-bold fs-6 mt-1">LAPORAN ARUS KAS</div>
            <div class="text-muted small mt-1">Tahun <?= $tahun ?> &nbsp;·&nbsp; (Dalam Satuan Rupiah)</div>
        </div>
    </div>

    <!-- Report table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="laporan-wrapper">
                <table class="table-laporan mb-0">
                    <thead>
                        <tr>
                            <th class="col-label" style="background:#1a3f6f;color:#fff;border-color:#1a3f6f;z-index:3;">
                                Uraian
                            </th>
                            <?php foreach ($bulanNames as $nama): ?>
                                <th class="num-cell text-center"
                                    style="background:#1a3f6f;color:#fff;border-color:#1a3f6f;">
                                    <?= $nama ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row):
                        $type   = $row['type'];
                        $label  = $row['label'];
                        $indent = $row['indent'];
                        $values = $row['values'] ?? [];
                        $rowCls = "row-{$type}";
                        $padCls = "indent-{$indent}";
                    ?>

                        <?php if ($type === 'spacer'): ?>
                            <tr class="row-spacer"><td colspan="13"></td></tr>

                        <?php elseif (in_array($type, ['title','section','group'])): ?>
                            <tr class="<?= $rowCls ?>">
                                <td class="col-label <?= $padCls ?>"><?= esc($label) ?></td>
                                <?php for ($b = 1; $b <= 12; $b++): ?>
                                    <td class="num-cell"></td>
                                <?php endfor; ?>
                            </tr>

                        <?php else: ?>
                            <tr class="<?= $rowCls ?>">
                                <td class="col-label <?= $padCls ?>"><?= esc($label) ?></td>
                                <?php for ($b = 1; $b <= 12; $b++):
                                    $val = (float)($values[$b] ?? 0);
                                    if ($val == 0) {
                                        $cls = 'num-cell num-zero'; $txt = '—';
                                    } elseif ($val < 0) {
                                        $cls = 'num-cell num-neg';
                                        $txt = '(' . number_format(abs($val), 0, ',', '.') . ')';
                                    } else {
                                        $cls = 'num-cell';
                                        $txt = number_format($val, 0, ',', '.');
                                    }
                                ?>
                                    <td class="<?= $cls ?>"><?= $txt ?></td>
                                <?php endfor; ?>
                            </tr>

                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-muted small mt-2 px-1">
        <i class="fa fa-info-circle me-1"></i>
        Angka dalam kurung <span class="text-danger fw-semibold">(xxx)</span> menunjukkan defisit/penurunan.
        Saldo Kas mencakup semua rekening Kas dan Bank (tidak termasuk investasi SIMKA).
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.addEventListener('beforeprint', () => {
    document.querySelectorAll('#sidebar, #main-content > nav').forEach(el => el.style.display = 'none');
});
window.addEventListener('afterprint', () => {
    document.querySelectorAll('#sidebar, #main-content > nav').forEach(el => el.style.display = '');
});
</script>
<?= $this->endSection() ?>
