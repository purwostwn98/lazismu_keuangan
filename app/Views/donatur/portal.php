<?= $this->extend('layouts/donatur') ?>

<?= $this->section('styles') ?>
<style>
    .stat-card .card-body {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.1;
        color: #1a2c4e;
    }

    .stat-label {
        font-size: .72rem;
        color: #6c757d;
        margin-top: 2px;
    }

    .table-riwayat {
        font-size: .83rem;
    }

    .table-riwayat thead th {
        background: #f4f6fb;
        font-weight: 600;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #495057;
        padding: .5rem .75rem;
        white-space: nowrap;
    }

    .table-riwayat td {
        vertical-align: middle;
        padding: .5rem .75rem;
    }

    .badge-dana {
        font-size: .68rem;
        border-radius: 4px;
        padding: .2rem .45rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$bulanNames = [
    1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
    7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des',
];

$donatur          = $donatur          ?? null;
$tahun            = $tahun            ?? date('Y');
$tahunList        = $tahunList        ?? [$tahun];
$statsAll         = $statsAll         ?? null;
$statsTahun       = $statsTahun       ?? null;
$trendBulanan     = $trendBulanan     ?? array_fill(1, 12, 0.0);
$ringkasanDana    = $ringkasanDana    ?? [];
$breakdownBulanan = $breakdownBulanan ?? [];
$riwayat          = $riwayat          ?? [];
$jenisZisLabels   = $jenisZisLabels   ?? [];
?>

<!-- Greeting -->
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">
            Assalamu'alaikum, <?= esc($donatur->nama ?? session()->get('user_nama')) ?> 👋
        </h5>
        <div class="text-muted" style="font-size:.82rem;">
            <?php if ($donatur): ?>
                <span class="me-2"><i class="fas fa-id-card me-1"></i><?= esc($donatur->kode ?? '') ?></span>
                <?php if ($donatur->kategori_parent ?? ''): ?>
                    <span><i class="fas fa-tag me-1"></i><?= esc($donatur->kategori_parent) ?> › <?= esc($donatur->kategori_nama) ?></span>
                <?php elseif ($donatur->kategori_nama ?? ''): ?>
                    <span><i class="fas fa-tag me-1"></i><?= esc($donatur->kategori_nama) ?></span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <form method="get" class="d-flex gap-2 align-items-center">
        <label class="mb-0 text-muted small">Tampilkan tahun:</label>
        <select name="tahun" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <?php foreach ($tahunList as $y): ?>
                <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-portal stat-card h-100">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(232,98,42,.12);color:#E8622A;">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.1rem;">
                        Rp <?= number_format((float)($statsAll->total_donasi ?? 0), 0, ',', '.') ?>
                    </div>
                    <div class="stat-label">Total Donasi (Semua Waktu)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-portal stat-card h-100">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(25,135,84,.12);color:#198754;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.1rem;">
                        Rp <?= number_format((float)($statsTahun->total_donasi ?? 0), 0, ',', '.') ?>
                    </div>
                    <div class="stat-label">Total Donasi <?= $tahun ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-portal stat-card h-100">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(13,110,253,.12);color:#0d6efd;">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($statsTahun->jumlah_trx ?? 0) ?></div>
                    <div class="stat-label">Transaksi <?= $tahun ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-portal stat-card h-100">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(111,66,193,.12);color:#6f42c1;">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <div class="stat-value"><?= (int)($statsAll->jumlah_trx ?? 0) ?></div>
                    <div class="stat-label">Total Semua Transaksi</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart + Ringkasan Bulanan -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card card-portal h-100">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    Donasi Bulanan — <?= $tahun ?>
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartTrend" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-portal h-100">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-table text-warning me-2"></i>
                    Rekap Per Bulan
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.8rem;">
                    <tbody>
                        <?php foreach ($bulanNames as $b => $nm):
                            $val = $trendBulanan[$b] ?? 0;
                        ?>
                        <tr class="<?= $val > 0 ? '' : 'text-muted' ?>">
                            <td class="ps-3 py-1"><?= $nm ?></td>
                            <td class="text-end pe-3 py-1 <?= $val > 0 ? 'fw-semibold' : '' ?>">
                                <?= $val > 0 ? 'Rp ' . number_format($val, 0, ',', '.') : '—' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid #dee2e6;">
                            <td class="ps-3 py-1 fw-bold">Total</td>
                            <td class="text-end pe-3 py-1 fw-bold text-primary">
                                Rp <?= number_format(array_sum($trendBulanan), 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ringkasan per Jenis Dana -->
<?php if (! empty($ringkasanDana)): ?>
<?php
$danaColors = [
    'ZAKAT'     => ['bg' => 'rgba(232,98,42,.12)',   'fg' => '#E8622A', 'icon' => 'fa-hand-holding-heart'],
    'INFAK_T'   => ['bg' => 'rgba(13,110,253,.12)',  'fg' => '#0d6efd', 'icon' => 'fa-donate'],
    'INFAK_TT'  => ['bg' => 'rgba(25,135,84,.12)',   'fg' => '#198754', 'icon' => 'fa-seedling'],
    'AMIL'      => ['bg' => 'rgba(111,66,193,.12)',  'fg' => '#6f42c1', 'icon' => 'fa-users'],
    'WAKAF'     => ['bg' => 'rgba(13,202,240,.12)',  'fg' => '#0dcaf0', 'icon' => 'fa-mosque'],
    'CSR'       => ['bg' => 'rgba(253,126,20,.12)',  'fg' => '#fd7e14', 'icon' => 'fa-building'],
    'KAS_KECIL' => ['bg' => 'rgba(108,117,125,.12)', 'fg' => '#6c757d', 'icon' => 'fa-coins'],
];
$defaultColor = ['bg' => 'rgba(108,117,125,.12)', 'fg' => '#6c757d', 'icon' => 'fa-circle-dot'];
$colSize = count($ringkasanDana) <= 4 ? 'col-6 col-md-3' : 'col-6 col-md-4';
?>
<div class="card card-portal mb-4">
    <div class="card-header bg-white border-bottom">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-layer-group text-primary me-2"></i>
            Ringkasan Donasi per Jenis Dana — <?= $tahun ?>
        </h6>
    </div>
    <div class="card-body">
        <!-- Cards -->
        <div class="row g-3 mb-4">
            <?php foreach ($ringkasanDana as $d):
                $clr = $danaColors[$d['kode']] ?? $defaultColor;
            ?>
                <div class="<?= $colSize ?>">
                    <div class="rounded-3 p-3 h-100 d-flex align-items-center gap-3"
                         style="background:<?= $clr['bg'] ?>;border:1px solid <?= $clr['fg'] ?>22;">
                        <div style="width:40px;height:40px;border-radius:10px;background:<?= $clr['bg'] ?>;
                                    color:<?= $clr['fg'] ?>;display:flex;align-items:center;
                                    justify-content:center;font-size:1.1rem;flex-shrink:0;
                                    border:1px solid <?= $clr['fg'] ?>44;">
                            <i class="fas <?= $clr['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1rem;color:#1a2c4e;line-height:1.1;">
                                Rp <?= number_format((float)$d['total'], 0, ',', '.') ?>
                            </div>
                            <div class="mt-1" style="font-size:.72rem;color:#6c757d;">
                                <?= esc($d['nama']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabel breakdown bulanan -->
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:.78rem;">
                <thead>
                    <tr>
                        <th style="width:50px;background:#f4f6fb;">Bulan</th>
                        <?php foreach ($ringkasanDana as $d):
                            $clr = $danaColors[$d['kode']] ?? $defaultColor;
                        ?>
                            <th class="text-end" style="background:#f4f6fb;color:<?= $clr['fg'] ?>;">
                                <?= esc($d['nama']) ?>
                            </th>
                        <?php endforeach; ?>
                        <th class="text-end" style="background:#f4f6fb;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $colTotals = array_fill(0, count($ringkasanDana), 0.0);
                    foreach ($bulanNames as $b => $nm):
                        $rowTotal = 0.0;
                        $vals = [];
                        foreach ($ringkasanDana as $idx => $d) {
                            $v = $breakdownBulanan[$d['kode']][$b] ?? 0.0;
                            $vals[] = $v;
                            $rowTotal += $v;
                            $colTotals[$idx] += $v;
                        }
                        $hasData = $rowTotal > 0;
                    ?>
                        <tr class="<?= $hasData ? '' : 'text-muted' ?>">
                            <td class="fw-semibold"><?= $nm ?></td>
                            <?php foreach ($vals as $idx => $v):
                                $clr = $danaColors[$ringkasanDana[$idx]['kode']] ?? $defaultColor;
                            ?>
                                <td class="text-end <?= $v > 0 ? 'fw-semibold' : '' ?>"
                                    style="<?= $v > 0 ? 'color:'.$clr['fg'].';' : '' ?>">
                                    <?= $v > 0 ? number_format($v, 0, ',', '.') : '—' ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-end fw-bold">
                                <?= $rowTotal > 0 ? number_format($rowTotal, 0, ',', '.') : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid #dee2e6;background:#f8f9fa;">
                        <td class="fw-bold">Total</td>
                        <?php foreach ($colTotals as $idx => $ct):
                            $clr = $danaColors[$ringkasanDana[$idx]['kode']] ?? $defaultColor;
                        ?>
                            <td class="text-end fw-bold" style="color:<?= $clr['fg'] ?>;">
                                <?= number_format($ct, 0, ',', '.') ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-end fw-bold text-primary">
                            Rp <?= number_format(array_sum($colTotals), 0, ',', '.') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Riwayat Donasi -->
<div class="card card-portal">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">
            <i class="fas fa-clock-rotate-left text-info me-2"></i>
            Riwayat Donasi <?= $tahun ?>
        </h6>
        <span class="badge bg-light text-secondary border" style="font-size:.72rem;">
            <?= count($riwayat) ?> transaksi
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($riwayat)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                Belum ada donasi di tahun <?= $tahun ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-riwayat mb-0">
                    <thead>
                        <tr>
                            <th style="width:110px;">Tanggal</th>
                            <th>No. Jurnal</th>
                            <th>Jenis ZIS</th>
                            <th>Dana</th>
                            <th>Keterangan</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $r): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                                <td>
                                    <code style="font-size:.75rem;color:#495057;"><?= esc($r['nomor_jurnal']) ?></code>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?= esc($jenisZisLabels[$r['jenis_zis']] ?? $r['jenis_zis']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-dana bg-light text-secondary border">
                                        <?= esc($r['nama_dana']) ?>
                                    </span>
                                </td>
                                <td class="text-truncate" style="max-width:180px;" title="<?= esc($r['uraian']) ?>">
                                    <?= esc($r['uraian']) ?>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid #dee2e6;background:#f8f9fa;">
                            <td colspan="5" class="ps-3 fw-bold text-end">Total <?= $tahun ?></td>
                            <td class="text-end fw-bold text-success pe-3">
                                Rp <?= number_format(array_sum(array_column($riwayat, 'jumlah')), 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function() {
    const bulanLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    const trendData   = <?= json_encode(array_values($trendBulanan)) ?>;

    const ctx = document.getElementById('chartTrend');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Donasi',
                data: trendData,
                backgroundColor: 'rgba(232,98,42,.75)',
                borderColor: '#E8622A',
                borderWidth: 1,
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => {
                            if (v >= 1_000_000) return (v / 1_000_000).toFixed(1) + ' Jt';
                            if (v >= 1_000) return (v / 1_000).toFixed(0) + ' Rb';
                            return v;
                        },
                        font: { size: 11 },
                    },
                    grid: { color: '#f0f0f0' },
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            },
        },
    });
})();
</script>
<?= $this->endSection() ?>
