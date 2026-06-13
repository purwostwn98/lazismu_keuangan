<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3 px-2">
            <div class="fs-2 fw-bold text-primary"><?= count($programs) ?></div>
            <div class="text-muted" style="font-size:.78rem;">Total Program</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3 px-2">
            <div class="fs-2 fw-bold text-success"><?= $totalAktif ?></div>
            <div class="text-muted" style="font-size:.78rem;">Program Aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3 px-2">
            <div class="fs-2 fw-bold text-secondary"><?= $totalNonAktif ?></div>
            <div class="text-muted" style="font-size:.78rem;">Tidak Aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3 px-2">
            <div class="fs-2 fw-bold" style="color:var(--brand-primary);"><?= count($grouped) ?></div>
            <div class="text-muted" style="font-size:.78rem;">Kategori</div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="get" action="" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Cari Program</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control"
                           placeholder="Nama program atau deskripsi..."
                           value="<?= esc($search ?? '') ?>">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold mb-1" style="font-size:.78rem;">Kategori</label>
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id_kategori_program'] ?>"
                            <?= ($filterKategori == $k['id_kategori_program']) ? 'selected' : '' ?>>
                            <?= esc($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <?php if ($search || $filterKategori): ?>
                    <a href="<?= base_url('program') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Program List -->
<?php if (empty($programs)): ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-folder-open d-block mb-3" style="font-size:3rem;opacity:.25;"></i>
            <p class="mb-0">Tidak ada program ditemukan.</p>
        </div>
    </div>

<?php else: ?>

    <!-- View Toggle -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted" style="font-size:.82rem;">
            Menampilkan <strong><?= count($programs) ?></strong> program
            dalam <strong><?= count($grouped) ?></strong> kategori
        </span>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-primary active" id="btnGrid" title="Grid">
                <i class="fas fa-th-large"></i>
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnList" title="List">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <!-- GRID VIEW -->
    <div id="viewGrid">
        <?php foreach ($grouped as $namaKategori => $items): ?>
            <div class="mb-4">
                <!-- Kategori Header -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:4px;height:22px;background:var(--brand-primary);border-radius:2px;"></div>
                    <h6 class="mb-0 fw-bold" style="color:var(--brand-secondary);">
                        <?= esc($namaKategori) ?>
                    </h6>
                    <span class="badge badge-soft-orange ms-1"><?= count($items) ?> program</span>
                </div>

                <div class="row g-3">
                    <?php foreach ($items as $p): ?>
                        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <div class="card h-100 program-card <?= $p['status_program'] != 1 ? 'opacity-60' : '' ?>">
                                <div class="card-body d-flex flex-column gap-2 p-3">
                                    <!-- Icon & Status -->
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="program-icon">
                                            <i class="fas fa-hand-holding-heart"></i>
                                        </div>
                                        <?php if ($p['status_program'] == 1): ?>
                                            <span class="badge badge-soft-green">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Non-Aktif</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Nama -->
                                    <div class="fw-semibold" style="font-size:.85rem;color:var(--brand-secondary);line-height:1.3;">
                                        <?= esc($p['nama_program']) ?>
                                    </div>

                                    <!-- Deskripsi -->
                                    <?php if (!empty($p['deskripsi_program'])): ?>
                                        <div class="text-muted" style="font-size:.76rem;line-height:1.4;flex:1;">
                                            <?= esc(mb_strimwidth($p['deskripsi_program'], 0, 80, '...')) ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="flex:1;"></div>
                                    <?php endif; ?>

                                    <!-- Footer -->
                                    <div class="d-flex justify-content-between align-items-center pt-2"
                                         style="border-top:1px solid #f0f2f5;">
                                        <span class="badge badge-soft-blue" style="font-size:.68rem;">
                                            <i class="fas fa-tag me-1"></i><?= esc($namaKategori) ?>
                                        </span>
                                        <?php if ($p['jenis_formulir']): ?>
                                            <span class="text-muted" style="font-size:.7rem;">
                                                <i class="fas fa-file-alt me-1"></i>F<?= $p['jenis_formulir'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- LIST VIEW (hidden by default) -->
    <div id="viewList" style="display:none;">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-card mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Nama Program</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jenis Formulir</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $i => $p): ?>
                            <tr>
                                <td class="text-muted"><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= esc($p['nama_program']) ?></td>
                                <td>
                                    <span class="badge badge-soft-orange">
                                        <?= esc($p['nama_kategori'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:.8rem;max-width:250px;">
                                    <?= esc(mb_strimwidth($p['deskripsi_program'] ?? '', 0, 60, '...')) ?: '-' ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['jenis_formulir'] ? 'Formulir ' . $p['jenis_formulir'] : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($p['status_program'] == 1): ?>
                                        <span class="badge badge-soft-green">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.program-card {
    border: 1px solid #eef0f3 !important;
    transition: transform .15s, box-shadow .15s;
}
.program-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,.1) !important;
}
.program-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(232,98,42,.1);
    color: var(--brand-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
}
.opacity-60 { opacity: .6; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const btnGrid = document.getElementById('btnGrid');
const btnList = document.getElementById('btnList');
const viewGrid = document.getElementById('viewGrid');
const viewList = document.getElementById('viewList');

btnGrid.addEventListener('click', () => {
    viewGrid.style.display = '';
    viewList.style.display = 'none';
    btnGrid.classList.replace('btn-outline-primary', 'btn-primary');
    btnList.classList.replace('btn-primary', 'btn-outline-primary');
    localStorage.setItem('programView', 'grid');
});

btnList.addEventListener('click', () => {
    viewGrid.style.display = 'none';
    viewList.style.display = '';
    btnList.classList.replace('btn-outline-primary', 'btn-primary');
    btnGrid.classList.replace('btn-primary', 'btn-outline-primary');
    localStorage.setItem('programView', 'list');
});

// Restore last view preference
if (localStorage.getItem('programView') === 'list') {
    btnList.click();
}
</script>
<?= $this->endSection() ?>
