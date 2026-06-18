<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .card-stat .card-body {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.1;
        color: #1a2c4e;
    }

    .stat-label {
        font-size: .75rem;
        color: #6c757d;
        margin-top: 2px;
    }

    .chart-card {
        height: 100%;
    }

    .chart-card .card-body {
        padding: 1rem;
    }

    .table-dashboard {
        font-size: .82rem;
    }

    .table-dashboard thead th {
        background: #f4f6fb;
        font-weight: 600;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #495057;
        white-space: nowrap;
        padding: .5rem .75rem;
    }

    .table-dashboard td {
        vertical-align: middle;
        padding: .45rem .75rem;
    }

    .badge-individu {
        background: #e3f0ff;
        color: #0d6efd;
        font-size: .7rem;
    }

    .badge-lembaga {
        background: #f3e8ff;
        color: #6f42c1;
        font-size: .7rem;
    }

    .rank-badge {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        font-weight: 700;
    }

    .rank-1 { background: #ffd700; color: #7a5a00; }
    .rank-2 { background: #c0c0c0; color: #444; }
    .rank-3 { background: #cd7f32; color: #fff; }
    .rank-other { background: #e9ecef; color: #495057; }

    .progress-bar-custom {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
    }

    .progress-bar-custom .fill {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #E8622A, #f5a86a);
        transition: width .3s ease;
    }

    @media print {
        .no-print { display: none !important; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$bulanNames = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
    5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
    9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
];

$tahun          = $tahun          ?? date('Y');
$tahunList      = $tahunList      ?? [$tahun];
$totalAktif     = $totalAktif     ?? 0;
$donaturBerdonasi = $donaturBerdonasi ?? 0;
$totalDonasi    = $totalDonasi    ?? 0;
$rataRata       = $rataRata       ?? 0;
$trendBulanan   = $trendBulanan   ?? array_fill(1, 12, 0.0);
$kategoriRows   = $kategoriRows   ?? [];
$topDonatur     = $topDonatur     ?? [];
$terbaru        = $terbaru        ?? [];

$maxTrend = max($trendBulanan) ?: 1;
$totalKategori = array_sum(array_column($kategoriRows, 'total')) ?: 1;
?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Dashboard Donatur</h4>
            <small class="text-muted">Statistik & analitik donatur/muzakki tahun <?= $tahun ?></small>
        </div>
        <div class="d-flex gap-2 align-items-center no-print flex-wrap">
            <form method="get" class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0 text-nowrap small fw-semibold">Tahun:</label>
                <select name="tahun" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    <?php foreach ($tahunList as $y): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="<?= base_url('master/donatur') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-users me-1"></i> Daftar Donatur
            </a>
        </div>
    </div>

    <!-- ── Stat Cards ──────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm card-stat h-100">
                <div class="card-body">
                    <div class="stat-icon" style="background:rgba(232,98,42,.12);color:#E8622A;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($totalAktif) ?></div>
                        <div class="stat-label">Donatur Aktif Terdaftar</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm card-stat h-100">
                <div class="card-body">
                    <div class="stat-icon" style="background:rgba(13,110,253,.12);color:#0d6efd;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($donaturBerdonasi) ?></div>
                        <div class="stat-label">Donatur Berdonasi <?= $tahun ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm card-stat h-100">
                <div class="card-body">
                    <div class="stat-icon" style="background:rgba(25,135,84,.12);color:#198754;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size:1.1rem;">
                            Rp <?= number_format($totalDonasi, 0, ',', '.') ?>
                        </div>
                        <div class="stat-label">Total Donasi <?= $tahun ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm card-stat h-100">
                <div class="card-body">
                    <div class="stat-icon" style="background:rgba(111,66,193,.12);color:#6f42c1;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size:1.05rem;">
                            Rp <?= number_format($rataRata, 0, ',', '.') ?>
                        </div>
                        <div class="stat-label">Rata-rata per Donatur</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Charts Row ──────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <!-- Trend Bulanan -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm chart-card h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Trend Donasi Bulanan — <?= $tahun ?>
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="chartTrend" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribusi Kategori -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm chart-card h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-chart-pie text-warning me-2"></i>
                        Distribusi per Kategori
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($kategoriRows)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            Belum ada data <?= $tahun ?>
                        </div>
                    <?php else: ?>
                        <canvas id="chartKategori" height="160"></canvas>
                        <!-- Legend manual -->
                        <div class="mt-3" style="font-size:.75rem;">
                            <?php
                            $palette = ['#E8622A','#0d6efd','#198754','#6f42c1','#fd7e14','#0dcaf0','#6c757d'];
                            foreach ($kategoriRows as $i => $kr):
                                $pct = round(($kr['total'] / $totalKategori) * 100, 1);
                                $clr = $palette[$i % count($palette)];
                            ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center gap-1">
                                        <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:<?= $clr ?>;"></span>
                                        <span class="text-truncate" style="max-width:130px;" title="<?= esc($kr['parent_nama']) ?>">
                                            <?= esc($kr['parent_nama']) ?>
                                        </span>
                                    </div>
                                    <span class="text-muted"><?= $pct ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Tables Row ──────────────────────────────────── -->
    <div class="row g-3">

        <!-- Top Donatur -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        Top 10 Donatur Terbesar — <?= $tahun ?>
                    </h6>
                    <?php if ($donaturBerdonasi > 10): ?>
                        <small class="text-muted">dari <?= $donaturBerdonasi ?> donatur aktif</small>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($topDonatur)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            Belum ada data donasi tahun <?= $tahun ?>
                        </div>
                    <?php else: ?>
                        <?php
                        $maxDonasi = (float)$topDonatur[0]['total_donasi'] ?: 1;
                        ?>
                        <table class="table table-hover table-dashboard mb-0">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>Donatur</th>
                                    <th class="text-center" style="width:70px;">Jenis</th>
                                    <th class="text-end" style="width:120px;">Total Donasi</th>
                                    <th style="width:80px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topDonatur as $i => $d): ?>
                                    <?php
                                    $rankClass = match($i) {
                                        0 => 'rank-1',
                                        1 => 'rank-2',
                                        2 => 'rank-3',
                                        default => 'rank-other',
                                    };
                                    $pct = round(((float)$d['total_donasi'] / $maxDonasi) * 100);
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="rank-badge <?= $rankClass ?>"><?= $i + 1 ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width:180px;" title="<?= esc($d['nama']) ?>">
                                                <?= esc($d['nama']) ?>
                                            </div>
                                            <div class="text-muted" style="font-size:.72rem;">
                                                <?= esc($d['kode']) ?>
                                                &nbsp;·&nbsp;
                                                <?= esc($d['kategori']) ?>
                                                &nbsp;·&nbsp;
                                                <?= $d['jumlah_transaksi'] ?>x transaksi
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($d['jenis'] === 'individu'): ?>
                                                <span class="badge badge-individu">
                                                    <i class="fas fa-user me-1"></i>Individu
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-lembaga">
                                                    <i class="fas fa-building me-1"></i>Lembaga
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-semibold">
                                            Rp <?= number_format((float)$d['total_donasi'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <div class="progress-bar-custom">
                                                <div class="fill" style="width:<?= $pct ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Donasi Terbaru -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-clock-rotate-left text-info me-2"></i>
                        Donasi Terbaru — <?= $tahun ?>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($terbaru)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                            Belum ada data donasi tahun <?= $tahun ?>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover table-dashboard mb-0">
                            <thead>
                                <tr>
                                    <th>Donatur</th>
                                    <th class="text-end" style="width:110px;">Jumlah</th>
                                    <th style="width:80px;">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($terbaru as $t): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width:160px;" title="<?= esc($t['nama']) ?>">
                                                <?= esc($t['nama']) ?>
                                            </div>
                                            <div class="text-muted text-truncate" style="font-size:.72rem;max-width:160px;" title="<?= esc($t['uraian']) ?>">
                                                <?= esc($t['kategori']) ?>
                                                <?php if ($t['uraian']): ?>
                                                    &nbsp;·&nbsp;<?= esc($t['uraian']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-end fw-semibold">
                                            Rp <?= number_format((float)$t['jumlah'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-muted" style="font-size:.75rem;white-space:nowrap;">
                                            <?= date('d M', strtotime($t['tanggal'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <?php if ($donaturBerdonasi > 10): ?>
                    <div class="card-footer bg-white border-top-0 text-center no-print">
                        <a href="<?= base_url('penerimaan?tahun=' . $tahun) ?>" class="text-muted small">
                            Lihat semua penerimaan →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /.row tables -->

</div><!-- /.container-fluid -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function() {
    const bulanLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

    // ── Trend Bar Chart ──────────────────────────────────────────
    const trendData = <?= json_encode(array_values($trendBulanan)) ?>;

    const ctxTrend = document.getElementById('chartTrend');
    if (ctxTrend) {
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: bulanLabels,
                datasets: [{
                    label: 'Total Donasi',
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
                                if (v >= 1_000)     return (v / 1_000).toFixed(0) + ' Rb';
                                return v;
                            },
                            font: { size: 11 },
                        },
                        grid: { color: '#f0f0f0' },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } },
                    },
                },
            },
        });
    }

    // ── Kategori Doughnut ────────────────────────────────────────
    <?php if (!empty($kategoriRows)): ?>
    const palette = ['#E8622A','#0d6efd','#198754','#6f42c1','#fd7e14','#0dcaf0','#6c757d'];
    const katLabels = <?= json_encode(array_column($kategoriRows, 'parent_nama')) ?>;
    const katData   = <?= json_encode(array_map(fn($r) => (float)$r['total'], $kategoriRows)) ?>;
    const katColors = katLabels.map((_, i) => palette[i % palette.length]);

    const ctxKat = document.getElementById('chartKategori');
    if (ctxKat) {
        new Chart(ctxKat, {
            type: 'doughnut',
            data: {
                labels: katLabels,
                datasets: [{
                    data: katData,
                    backgroundColor: katColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' Rp ' + ctx.parsed.toLocaleString('id-ID'),
                        },
                    },
                },
                cutout: '60%',
            },
        });
    }
    <?php endif; ?>
})();
</script>
<?= $this->endSection() ?>
