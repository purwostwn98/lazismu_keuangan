<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><?= esc($pageTitle ?? 'Penerimaan ZIS') ?></h4>
            <small class="text-muted">Daftar seluruh transaksi penerimaan ZIS</small>
        </div>
        <a href="<?= base_url('penerimaan/input') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> Input Penerimaan
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 fs-5 text-primary">
                            <i class="fa fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold lh-1"><?= count($daftar ?? []) ?></div>
                            <div class="text-muted small">Total Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-2 fs-5 text-success">
                            <i class="fa fa-coins"></i>
                        </div>
                        <div>
                            <div class="fw-bold lh-1 small"><?= 'Rp ' . number_format($totalJumlah ?? 0, 0, ',', '.') ?></div>
                            <div class="text-muted small">Total Penerimaan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-2 fs-5 text-warning">
                            <i class="fa fa-star-and-crescent"></i>
                        </div>
                        <div>
                            <div class="fw-bold lh-1 small"><?= 'Rp ' . number_format($totalZakat ?? 0, 0, ',', '.') ?></div>
                            <div class="text-muted small">Total Zakat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-info bg-opacity-10 p-2 fs-5 text-info">
                            <i class="fa fa-heart"></i>
                        </div>
                        <div>
                            <div class="fw-bold lh-1 small"><?= 'Rp ' . number_format($totalInfak ?? 0, 0, ',', '.') ?></div>
                            <div class="text-muted small">Total Infak</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="fa fa-check-circle me-1"></i> <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fa fa-triangle-exclamation me-1"></i> <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Form -->
    <?php
    $filter     = $filter     ?? [];
    $tahunList  = $tahunList  ?? [];
    $periodeList= $periodeList?? [];
    $groups     = $groups     ?? [];
    $labels     = $labels     ?? [];
    // Encode periodeList ke JSON untuk cascading JS
    $periodeJson = json_encode(array_map(fn($p) => [
        'id'      => $p['id'],
        'nama'    => $p['nama'] . ($p['is_tutup'] ? ' [Tutup]' : ''),
        'tahun'   => $p['tahun'],
    ], $periodeList));
    ?>
    <form method="get" action="" class="card border-0 shadow-sm mb-3" id="filterForm">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">

                <!-- Filter Tahun -->
                <div class="col-6 col-md-2">
                    <select name="tahun" id="filterTahun" class="form-select form-select-sm">
                        <option value="">— Semua Tahun —</option>
                        <?php foreach ($tahunList as $t): ?>
                            <option value="<?= $t ?>" <?= (int)$filter['tahun'] === $t ? 'selected' : '' ?>>
                                <?= $t ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Periode (bulan, cascade dari tahun) -->
                <div class="col-6 col-md-2">
                    <select name="periode" id="filterPeriode" class="form-select form-select-sm">
                        <option value="">— Semua Periode —</option>
                        <?php foreach ($periodeList as $p): ?>
                            <option value="<?= $p['id'] ?>"
                                data-tahun="<?= $p['tahun'] ?>"
                                <?= (int)$filter['periode'] === (int)$p['id'] ? 'selected' : '' ?>
                                style="display:none;">
                                <?= esc($p['nama']) ?><?= $p['is_tutup'] ? ' [Tutup]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Jenis ZIS -->
                <div class="col-12 col-md-2">
                    <select name="group" class="form-select form-select-sm">
                        <option value="">— Semua Jenis ZIS —</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= esc($g) ?>" <?= $filter['group'] === $g ? 'selected' : '' ?>>
                                <?= esc($g) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="col-12 col-md">
                    <input type="text" name="q" class="form-control form-control-sm"
                        placeholder="Cari donatur / nomor jurnal / uraian..."
                        value="<?= esc($filter['q']) ?>">
                </div>

                <!-- Tombol -->
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-search me-1"></i> Filter
                    </button>
                    <a href="<?= base_url('penerimaan') ?>" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function () {
        var filterTahun  = document.getElementById('filterTahun');
        var filterPeriode = document.getElementById('filterPeriode');
        var opts = filterPeriode.querySelectorAll('option[data-tahun]');

        function syncPeriode() {
            var tahun = filterTahun.value;
            var anyVisible = false;
            opts.forEach(function (opt) {
                var show = tahun === '' || opt.dataset.tahun === tahun;
                opt.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
                // Reset pilihan jika opsi tidak relevan
                if (!show && opt.selected) {
                    opt.selected = false;
                    filterPeriode.value = '';
                }
            });
            filterPeriode.disabled = !anyVisible && tahun !== '';
        }

        syncPeriode(); // jalankan saat load (restore state dari GET)
        filterTahun.addEventListener('change', function () {
            filterPeriode.value = ''; // reset pilihan periode saat tahun berubah
            syncPeriode();
        });
    })();
    </script>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Tanggal</th>
                            <th>No. Jurnal</th>
                            <th>Donatur</th>
                            <th>Kategori</th>
                            <th>Jenis ZIS</th>
                            <th>Rekening</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fa fa-hand-holding-dollar fa-2x mb-2 d-block opacity-25"></i>
                                    Belum ada data penerimaan
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($daftar as $r): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $no++ ?></td>
                                    <td class="text-nowrap">
                                        <?= $r['tanggal'] ? date('d/m/Y', strtotime($r['tanggal'])) : '—' ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-primary bg-opacity-10 text-white border border-primary-subtle fw-normal font-monospace">
                                            <?= esc($r['nomor_jurnal'] ?? '—') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($r['nama_donatur']): ?>
                                            <div class="fw-semibold"><?= esc($r['nama_donatur']) ?></div>
                                            <div class="text-muted" style="font-size:.78rem;"><?= esc($r['kode_donatur']) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Anonim</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($r['nama_kategori'] ?? '—') ?></td>
                                    <td>
                                        <?php
                                        $jz = $r['jenis_zis'];
                                        $label = $labels[$jz] ?? $jz;
                                        $isZakat = str_starts_with($jz, 'zakat');
                                        $isInfak = str_starts_with($jz, 'infak');
                                        [$bg, $tc, $bc] = $isZakat
                                            ? ['#fff3cd', '#664d03', '#ffe69c']
                                            : ($isInfak ? ['#cff4fc', '#055160', '#9eeaf9'] : ['#e2e3e5', '#41464b', '#d3d6d8']);
                                        ?>
                                        <span class="badge rounded-pill fw-normal" style="background:<?= $bg ?>;color:<?= $tc ?>;border:1px solid <?= $bc ?>;">
                                            <?= esc($label) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= esc($r['nama_rekening'] ?? '—') ?></td>
                                    <td class="text-end fw-semibold text-nowrap">
                                        Rp <?= number_format((float)$r['jumlah'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if (! $r['is_tutup']): ?>
                                            <a href="<?= base_url('penerimaan/delete/' . $r['id']) ?>"
                                                class="btn btn-outline-danger btn-sm"
                                                title="Hapus"
                                                onclick="return confirm('Hapus penerimaan ini?\n\nJurnal terkait juga akan dihapus.')">
                                                <i class="fa fa-trash fa-xs"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small" title="Periode sudah ditutup"><i class="fa fa-lock fa-xs"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Subtotal row -->
                            <tr class="table-light fw-bold">
                                <td colspan="7" class="ps-3 text-end text-muted small">Total <?= count($daftar) ?> transaksi</td>
                                <td class="text-end">Rp <?= number_format($totalJumlah ?? 0, 0, ',', '.') ?></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>