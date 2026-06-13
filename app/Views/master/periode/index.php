<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><?= esc($pageTitle) ?></h4>
            <small class="text-muted">Kelola periode akuntansi bulanan</small>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa fa-plus me-1"></i> Tambah Periode
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 fs-5 text-primary">
                            <i class="fa fa-calendar-days"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1"><?= $total ?></div>
                            <div class="text-muted small">Total Periode</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-2 fs-5 text-success">
                            <i class="fa fa-lock-open"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1"><?= $aktif ?></div>
                            <div class="text-muted small">Periode Aktif</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-danger bg-opacity-10 p-2 fs-5 text-danger">
                            <i class="fa fa-lock"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1"><?= $tutup ?></div>
                            <div class="text-muted small">Periode Ditutup</div>
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
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fa fa-triangle-exclamation me-1"></i>
            <ul class="mb-0 ps-3">
                <?php foreach (session('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="btn-group btn-group-sm" id="filterStatus" role="group">
            <button type="button" class="btn btn-outline-secondary active" data-status="semua">
                Semua <span class="badge bg-secondary ms-1"><?= $total ?></span>
            </button>
            <button type="button" class="btn btn-outline-success" data-status="aktif">
                Aktif <span class="badge bg-success ms-1"><?= $aktif ?></span>
            </button>
            <button type="button" class="btn btn-outline-danger" data-status="tutup">
                Ditutup <span class="badge bg-danger ms-1"><?= $tutup ?></span>
            </button>
        </div>

        <?php if (count($tahunList) > 1): ?>
        <select id="filterTahun" class="form-select form-select-sm" style="width:auto;">
            <option value="">Semua Tahun</option>
            <?php foreach ($tahunList as $t): ?>
                <option value="<?= $t ?>"><?= $t ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Nama Periode</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="periodeTableBody">
                        <?php if (empty($periode)): ?>
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fa fa-calendar-days fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada data periode
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php $no = 1; foreach ($periode as $p): ?>
                        <tr data-status="<?= $p['is_tutup'] ? 'tutup' : 'aktif' ?>" data-tahun="<?= $p['tahun'] ?>">
                            <td class="ps-3 text-muted small"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= esc($p['nama']) ?></td>
                            <td><?= esc($bulanNames[(int)$p['bulan']]) ?></td>
                            <td><?= esc($p['tahun']) ?></td>
                            <td>
                                <?php if ($p['is_tutup']): ?>
                                    <span class="badge rounded-pill" style="background:#f8d7da;color:#842029;border:1px solid #f5c2c7;">
                                        <i class="fa fa-lock fa-xs me-1"></i>Ditutup
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill" style="background:#d1e7dd;color:#0a3622;border:1px solid #a3cfbb;">
                                        <i class="fa fa-lock-open fa-xs me-1"></i>Aktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex gap-1 justify-content-end">
                                    <!-- Edit nama -->
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        title="Ubah nama"
                                        onclick="openEdit(<?= $p['id'] ?>, '<?= esc($p['nama'], 'js') ?>', '<?= esc($bulanNames[(int)$p['bulan']]) ?>', <?= $p['tahun'] ?>)">
                                        <i class="fa fa-pen fa-xs"></i>
                                    </button>

                                    <!-- Tutup / Buka -->
                                    <?php if ($p['is_tutup']): ?>
                                        <a href="<?= base_url('master/periode/tutup/' . $p['id']) ?>"
                                           class="btn btn-outline-success btn-sm"
                                           title="Buka kembali"
                                           onclick="return confirm('Buka kembali periode <?= esc($p['nama'], 'js') ?>?')">
                                            <i class="fa fa-lock-open fa-xs"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('master/periode/tutup/' . $p['id']) ?>"
                                           class="btn btn-outline-warning btn-sm"
                                           title="Tutup periode"
                                           onclick="return confirm('Tutup periode <?= esc($p['nama'], 'js') ?>?\n\nSetelah ditutup, transaksi pada periode ini tidak dapat diubah.')">
                                            <i class="fa fa-lock fa-xs"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Hapus -->
                                    <a href="<?= base_url('master/periode/delete/' . $p['id']) ?>"
                                       class="btn btn-outline-danger btn-sm"
                                       title="Hapus"
                                       onclick="return confirm('Hapus periode <?= esc($p['nama'], 'js') ?>?')">
                                        <i class="fa fa-trash fa-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="noResultRow" class="d-none">
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="fa fa-filter me-1"></i> Tidak ada data untuk filter ini
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="alert alert-info border-0 mt-3 py-2 small">
        <i class="fa fa-circle-info me-1"></i>
        <strong>Catatan:</strong> Periode yang <strong>ditutup</strong> akan mengunci seluruh transaksi (jurnal, penerimaan, penyaluran) pada bulan tersebut agar tidak dapat diubah.
    </div>

</div><!-- /container -->


<!-- ===================== MODAL ADD ===================== -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold"><i class="fa fa-plus me-1"></i> Tambah Periode</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('master/periode/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">Bulan <span class="text-danger">*</span></label>
                        <select name="bulan" id="addBulan" class="form-select form-select-sm" required onchange="autoNama()">
                            <option value="">— Pilih Bulan —</option>
                            <?php foreach ($bulanNames as $num => $nama): ?>
                                <option value="<?= $num ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" id="addTahun" class="form-control form-control-sm"
                               value="<?= date('Y') ?>" min="2000" max="2099" required oninput="autoNama()">
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm fw-semibold">Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="addNama" class="form-control form-control-sm"
                               maxlength="30" required placeholder="cth: Januari 2026">
                        <div class="form-text">Terisi otomatis, dapat diubah.</div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ===================== MODAL EDIT ===================== -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold"><i class="fa fa-pen me-1"></i> Edit Nama Periode</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label form-label-sm fw-semibold text-muted">Periode</label>
                        <div id="editPeriodeInfo" class="form-control form-control-sm bg-light text-muted"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm fw-semibold">Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="editNama" class="form-control form-control-sm"
                               maxlength="30" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// --- Auto-generate nama in Add modal ---
const BULAN_NAMES = <?= json_encode(array_values(array_merge([''], array_values($bulanNames)))) ?>;

function autoNama() {
    const b = parseInt(document.getElementById('addBulan').value);
    const t = document.getElementById('addTahun').value.trim();
    if (b && t.length === 4) {
        document.getElementById('addNama').value = BULAN_NAMES[b] + ' ' + t;
    }
}

// --- Open edit modal ---
function openEdit(id, nama, bulanNama, tahun) {
    document.getElementById('editForm').action = '<?= base_url('master/periode/update') ?>/' + id;
    document.getElementById('editPeriodeInfo').textContent = bulanNama + ' ' + tahun;
    document.getElementById('editNama').value = nama;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// --- Client-side filtering ---
let currentStatus = 'semua';
let currentTahun  = '';

function applyFilters() {
    const rows = document.querySelectorAll('#periodeTableBody tr[data-status]');
    const noResult = document.getElementById('noResultRow');
    let visible = 0;

    rows.forEach(row => {
        const statusMatch = currentStatus === 'semua' || row.dataset.status === currentStatus;
        const tahunMatch  = !currentTahun || row.dataset.tahun === currentTahun;
        const show = statusMatch && tahunMatch;
        row.classList.toggle('d-none', !show);
        if (show) visible++;
    });

    if (noResult) noResult.classList.toggle('d-none', visible > 0);
}

// Status tabs
document.getElementById('filterStatus').addEventListener('click', e => {
    const btn = e.target.closest('[data-status]');
    if (!btn) return;
    document.querySelectorAll('#filterStatus [data-status]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentStatus = btn.dataset.status;
    applyFilters();
});

// Year filter
const filterTahunEl = document.getElementById('filterTahun');
if (filterTahunEl) {
    filterTahunEl.addEventListener('change', e => {
        currentTahun = e.target.value;
        applyFilters();
    });
}

// Re-open add modal if there were validation errors (from flash)
<?php if (session('errors') || old('bulan')): ?>
    document.addEventListener('DOMContentLoaded', () => {
        new bootstrap.Modal(document.getElementById('addModal')).show();
    });
<?php endif; ?>
</script>
<?= $this->endSection() ?>