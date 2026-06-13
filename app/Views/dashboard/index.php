<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Helper: format rupiah singkat (1.2 Jt, 500 Rb, dst)
function fmtRp(float $n): string
{
    if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 1, ',', '.') . ' M';
    if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000,     1, ',', '.') . ' Jt';
    if ($n >= 1_000)         return 'Rp ' . number_format($n / 1_000,         0, ',', '.') . ' Rb';
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function fmtFull(float $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}
$tipeBadge = [
    'penerimaan'  => ['bg-success bg-opacity-10 text-success',   'Terima'],
    'penyaluran'  => ['bg-danger  bg-opacity-10 text-danger',    'Salur'],
    'biaya'       => ['bg-warning bg-opacity-10 text-warning',   'Biaya'],
    'transfer'    => ['bg-info    bg-opacity-10 text-info',      'Transfer'],
    'jurnal_umum' => ['bg-secondary bg-opacity-10 text-secondary', 'Jurnal'],
];
?>

<!-- ── Info strip: periode & donatur ─────────────────────── -->
<div class="d-flex gap-3 flex-wrap mb-4 align-items-center">
    <?php if ($periodeAktif): ?>
        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2">
            <i class="fa fa-calendar-check me-1"></i> Periode aktif: <?= esc($periodeAktif) ?>
        </span>
    <?php else: ?>
        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning-subtle px-3 py-2">
            <i class="fa fa-triangle-exclamation me-1"></i> Tidak ada periode aktif bulan ini
        </span>
    <?php endif; ?>
    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2">
        <i class="fa fa-users me-1"></i> <?= $jumlahDonaturAktif ?> donatur aktif
    </span>

    <!-- Filter Periode -->
    <?php if (!empty($periodeList)): ?>
        <form method="get" action="<?= base_url('dashboard') ?>" class="ms-auto d-flex align-items-center gap-2">
            <label class="text-muted small mb-0 text-nowrap">
                <i class="fa fa-filter me-1"></i>Tampilkan periode:
            </label>
            <select name="periode_id" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <?php foreach ($periodeList as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (string)$p['id'] === (string)($selectedPeriodeId ?? '') ? 'selected' : '' ?>>
                        <?= esc($p['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php else: ?>
        <span class="ms-auto text-muted small">
            <i class="fa fa-circle-info me-1"></i>
            Data per <?= date('d M Y') ?>
        </span>
    <?php endif; ?>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card-orange">
            <div class="stat-icon"><i class="fas fa-hand-holding-dollar"></i></div>
            <div class="stat-value" title="<?= fmtFull($totalZakat) ?>"><?= fmtRp($totalZakat) ?></div>
            <div class="stat-label">Penerimaan Zakat</div>
            <span class="stat-change"><i class="fas fa-calendar me-1"></i><?= esc($namaBulanTahun) ?></span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card-teal">
            <div class="stat-icon"><i class="fas fa-seedling"></i></div>
            <div class="stat-value" title="<?= fmtFull($totalInfak) ?>"><?= fmtRp($totalInfak) ?></div>
            <div class="stat-label">Penerimaan Infak/Sedekah</div>
            <span class="stat-change"><i class="fas fa-calendar me-1"></i><?= esc($namaBulanTahun) ?></span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card-blue">
            <div class="stat-icon"><i class="fas fa-hands-holding-circle"></i></div>
            <div class="stat-value" title="<?= fmtFull($totalPenyaluran) ?>"><?= fmtRp($totalPenyaluran) ?></div>
            <div class="stat-label">Penyaluran &amp; Biaya</div>
            <span class="stat-change"><i class="fas fa-calendar me-1"></i><?= esc($namaBulanTahun) ?></span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card-purple">
            <div class="stat-icon"><i class="fas fa-piggy-bank"></i></div>
            <div class="stat-value" title="<?= fmtFull($totalSaldoDana) ?>"><?= fmtRp($totalSaldoDana) ?></div>
            <div class="stat-label">Saldo Dana (Total)</div>
            <span class="stat-change"><i class="fas fa-database me-1"></i>Kumulatif</span>
        </div>
    </div>

</div>

<!-- ── Saldo per Dana & Transaksi Terakhir ─────────────────── -->
<div class="row g-3 mb-4">

    <!-- Saldo per Jenis Dana -->
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-wallet me-2 text-primary"></i>Saldo per Jenis Dana</span>
                <span class="badge badge-soft-orange"><?= $tahun ?></span>
            </div>
            <div class="card-body p-0">
                <table class="table table-card mb-0">
                    <thead>
                        <tr>
                            <th>Dana</th>
                            <th class="text-end">Masuk</th>
                            <th class="text-end">Keluar</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalPen = $totalPsl = $totalSld = 0;
                        $badgeMap = [
                            'ZAKAT'    => 'badge-soft-orange',
                            'INFAK_T'  => 'badge-soft-green',
                            'INFAK_TT' => 'badge-soft-green',
                            'AMIL'     => 'badge-soft-blue',
                            'CSR'      => 'bg-secondary text-white',
                        ];
                        foreach ($saldoPerDana as $sd):
                            $totalPen += $sd['penerimaan'];
                            $totalPsl += $sd['penyaluran'];
                            $totalSld += $sd['saldo'];
                            $bc = $badgeMap[$sd['kode']] ?? 'bg-secondary text-white';
                        ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $bc ?> me-1" style="font-size:.65rem">
                                        <?= esc($sd['kode']) ?>
                                    </span>
                                    <span class="small"><?= esc($sd['nama']) ?></span>
                                </td>
                                <td class="text-end text-success fw-semibold small">
                                    <?= $sd['penerimaan'] > 0 ? fmtRp($sd['penerimaan']) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-end text-danger small">
                                    <?= $sd['penyaluran'] > 0 ? fmtRp($sd['penyaluran']) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-end fw-bold small <?= $sd['saldo'] < 0 ? 'text-danger' : '' ?>">
                                    <?= fmtRp($sd['saldo']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th class="small">Total <?= $tahun ?></th>
                            <th class="text-end text-success small"><?= fmtRp($totalPen) ?></th>
                            <th class="text-end text-danger small"><?= fmtRp($totalPsl) ?></th>
                            <th class="text-end text-primary small"><?= fmtRp($totalSld) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Transaksi Terakhir -->
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Transaksi Terakhir</span>
                <a href="<?= base_url('penerimaan') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($transaksiTerakhir)): ?>
                    <div class="text-center text-muted py-5" style="font-size:.82rem;">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.8rem;opacity:.3;"></i>
                        Belum ada transaksi
                    </div>
                <?php else: ?>
                    <table class="table table-card mb-0">
                        <thead>
                            <tr>
                                <th>Tgl</th>
                                <th>No. Jurnal</th>
                                <th>Uraian</th>
                                <th class="text-end">Jumlah</th>
                                <th>Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transaksiTerakhir as $t):
                                [$bc, $bl] = $tipeBadge[$t['jenis_transaksi']] ?? ['bg-secondary bg-opacity-10 text-secondary', $t['jenis_transaksi']];
                            ?>
                                <tr>
                                    <td class="text-nowrap small text-muted">
                                        <?= $t['tanggal'] ? date('d/m', strtotime($t['tanggal'])) : '—' ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="font-monospace" style="font-size:.72rem;">
                                            <?= esc($t['nomor_jurnal']) ?>
                                        </span>
                                    </td>
                                    <td class="small">
                                        <div><?= esc(mb_strimwidth($t['uraian'], 0, 35, '…')) ?></div>
                                        <div class="text-muted" style="font-size:.7rem;"><?= esc($t['nama_dana']) ?></div>
                                    </td>
                                    <td class="text-end small fw-semibold text-nowrap">
                                        <?= fmtRp((float)$t['total_debet']) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $bc ?> fw-normal" style="font-size:.68rem;">
                                            <?= $bl ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── Donatur Terbaru & Aksi Cepat ──────────────────────── -->
<div class="row g-3">

    <!-- Donatur / Muzakki Terbaru -->
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users me-2 text-primary"></i>Donatur / Muzakki Terbaru</span>
                <a href="<?= base_url('master/donatur') ?>" class="btn btn-sm btn-outline-primary">Kelola</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($donaturTerbaru)): ?>
                    <div class="text-center text-muted py-5" style="font-size:.82rem;">
                        <i class="fas fa-user-plus d-block mb-2" style="font-size:1.8rem;opacity:.3;"></i>
                        Belum ada donatur
                    </div>
                <?php else: ?>
                    <table class="table table-card mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th class="text-end">Total Donasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donaturTerbaru as $d): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold small"><?= esc($d['nama']) ?></div>
                                        <div class="text-muted" style="font-size:.72rem;">
                                            <?= esc($d['kode_donatur']) ?>
                                        </div>
                                    </td>
                                    <td class="small text-muted"><?= esc($d['kategori']) ?></td>
                                    <td class="text-end fw-semibold small text-nowrap">
                                        <?= fmtFull((float)$d['total_donasi']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2 text-primary"></i>Aksi Cepat
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?= base_url('penerimaan/input') ?>"
                            class="btn btn-primary w-100 d-flex align-items-center gap-2 justify-content-center">
                            <i class="fas fa-plus-circle"></i> Input Penerimaan
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('penyaluran/input') ?>"
                            class="btn btn-outline-primary w-100 d-flex align-items-center gap-2 justify-content-center">
                            <i class="fas fa-share-nodes"></i> Input Penyaluran
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('mutasi/input') ?>"
                            class="btn btn-outline-secondary w-100 d-flex align-items-center gap-2 justify-content-center">
                            <i class="fas fa-right-left"></i> Mutasi Rekening
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('laporan/posisi-keuangan') ?>"
                            class="btn btn-outline-secondary w-100 d-flex align-items-center gap-2 justify-content-center">
                            <i class="fas fa-file-invoice"></i> Posisi Keuangan
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('laporan/perubahan-dana') ?>"
                            class="btn btn-outline-secondary w-100 d-flex align-items-center gap-2 justify-content-center">
                            <i class="fas fa-chart-bar"></i> Perubahan Dana
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('laporan/arus-kas') ?>"
                            class="btn btn-outline-secondary w-100 d-flex align-items-center gap-2 justify-content-center">
                            <i class="fas fa-water"></i> Arus Kas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>