<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
/* ── Posisi Keuangan table ─────────────────────────── */
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
/* Sticky label column */
.col-label {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    min-width: 220px;
    max-width: 260px;
    white-space: normal;
}
/* Indent levels */
.indent-0 { padding-left: 0 !important; }
.indent-1 { padding-left: 14px !important; }
.indent-2 { padding-left: 28px !important; }

/* Row types */
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
.row-note td {
    color: #6c757d;
    font-size: .72rem;
    font-style: italic;
    background: #fff;
    border: none;
    border-top: 1px dashed #dee2e6;
}
.row-spacer td {
    height: 10px;
    background: #f0f4ff;
    border: none;
}
/* Number cells */
.num-cell {
    text-align: right;
    font-variant-numeric: tabular-nums;
    min-width: 90px;
}
.num-zero { color: #adb5bd; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Laporan Posisi Keuangan</h4>
            <small class="text-muted">Balance Sheet per bulan — LAZISMU UMS</small>
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
            <div class="fw-bold fs-6 mt-1">LAPORAN POSISI KEUANGAN</div>
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
                                Keterangan
                            </th>
                            <?php foreach ($bulanNames as $b => $nama): ?>
                                <th class="num-cell text-center"
                                    style="background:#1a3f6f;color:#fff;border-color:#1a3f6f;">
                                    <?= $nama ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $type    = $row['type'];
                        $label   = $row['label'];
                        $indent  = $row['indent'];
                        $values  = $row['values'] ?? [];
                        $rowCls  = "row-{$type}";
                        $padCls  = "indent-{$indent}";
                        ?>

                        <?php if ($type === 'spacer'): ?>
                            <tr class="row-spacer"><td colspan="13"></td></tr>

                        <?php elseif (in_array($type, ['title', 'section', 'group'])): ?>
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
                                    $fmtCls = $val == 0 ? 'num-cell num-zero' : 'num-cell';
                                ?>
                                    <td class="<?= $fmtCls ?>">
                                        <?= $val == 0 ? '—' : number_format($val, 0, ',', '.') ?>
                                    </td>
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
        Saldo Dana dihitung dari tabel <code>saldo_dana</code> per periode.
        Rekening Bank dihitung dari <code>saldo_awal</code> + kumulatif mutasi jurnal.
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Print: hide sidebar + topbar, show full-width table
window.addEventListener('beforeprint', () => {
    document.querySelectorAll('#sidebar, #main-content > nav').forEach(el => el.style.display = 'none');
});
window.addEventListener('afterprint', () => {
    document.querySelectorAll('#sidebar, #main-content > nav').forEach(el => el.style.display = '');
});
</script>
<?= $this->endSection() ?>