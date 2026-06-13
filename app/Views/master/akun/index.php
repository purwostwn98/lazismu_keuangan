<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$errors  = session('errors')  ?? [];
$success = session('success') ?? '';
$error   = session('error')   ?? '';

$tipeLabel = [
    'aset'       => 'Aset',
    'liabilitas' => 'Liabilitas',
    'saldo_dana' => 'Saldo Dana',
    'penerimaan' => 'Penerimaan',
    'penyaluran' => 'Penyaluran',
    'biaya'      => 'Biaya',
];
$tipeBadge = [
    'aset'       => 'bg-primary',
    'liabilitas' => 'bg-danger',
    'saldo_dana' => 'bg-warning text-dark',
    'penerimaan' => 'bg-success',
    'penyaluran' => 'bg-info text-dark',
    'biaya'      => 'bg-secondary',
];
$tipeIcon = [
    'aset'       => 'fas fa-coins',
    'liabilitas' => 'fas fa-hand-holding-usd',
    'saldo_dana' => 'fas fa-piggy-bank',
    'penerimaan' => 'fas fa-arrow-down-to-bracket',
    'penyaluran' => 'fas fa-arrow-up-from-bracket',
    'biaya'      => 'fas fa-receipt',
];
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= esc($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= esc($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(232,98,42,.12);color:#E8622A;">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total ?></div>
                    <div class="stat-label">Total Akun</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(255,193,7,.15);color:#ffc107;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $headers ?></div>
                    <div class="stat-label">Akun Grup / Header</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(25,135,84,.12);color:#198754;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $postable ?></div>
                    <div class="stat-label">Akun Postable</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
        <!-- Filter tabs -->
        <div class="d-flex flex-wrap gap-1 align-items-center">
            <button class="btn btn-sm btn-primary filter-btn active" data-filter="all">
                Semua <span class="badge bg-white text-primary ms-1"><?= $total ?></span>
            </button>
            <?php foreach ($tipeLabel as $k => $v): ?>
                <button class="btn btn-sm btn-outline-secondary filter-btn" data-filter="<?= $k ?>">
                    <?= $v ?>
                    <span class="badge bg-secondary ms-1"><?= $byTipe[$k] ?? 0 ?></span>
                </button>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primary btn-sm flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Akun
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-card mb-0" id="tblAkun" style="font-size:.84rem;">
                <thead>
                    <tr>
                        <th style="width:120px;">Nomor Akun</th>
                        <th>Nama Akun</th>
                        <th class="text-center" style="width:110px;">Tipe</th>
                        <th class="text-center" style="width:90px;">Jenis</th>
                        <th class="text-center" style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($akun)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada data akun.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($akun as $a): ?>
                            <tr data-tipe="<?= $a['tipe'] ?>" class="<?= $a['is_header'] ? 'table-light' : '' ?>">
                                <td>
                                    <code style="font-size:.82rem;color:#495057;"><?= esc($a['nomor_akun']) ?></code>
                                </td>
                                <td>
                                    <span style="padding-left:<?= max(0, ($a['level'] - 1)) * 14 ?>px;">
                                        <?php if ($a['is_header']): ?>
                                            <i class="fas fa-folder text-warning me-1" style="font-size:.75rem;"></i>
                                            <strong><?= esc($a['nama_akun']) ?></strong>
                                        <?php else: ?>
                                            <i class="fas fa-minus text-muted me-1" style="font-size:.6rem;vertical-align:middle;"></i>
                                            <?= esc($a['nama_akun']) ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $tipeBadge[$a['tipe']] ?? 'bg-secondary' ?>" style="font-size:.72rem;">
                                        <?= $tipeLabel[$a['tipe']] ?? $a['tipe'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($a['is_header']): ?>
                                        <span class="badge bg-light text-dark border" style="font-size:.72rem;">Grup</span>
                                    <?php else: ?>
                                        <span class="badge" style="font-size:.72rem;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;">Postable</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                        title="Edit"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <a href="<?= base_url('master/akun/delete/' . $a['id']) ?>"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="Hapus"
                                        onclick="return confirm('Hapus akun <?= esc($a['nomor_akun']) ?> — <?= esc(addslashes($a['nama_akun'])) ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================== MODAL TAMBAH ===================== -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('master/akun/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2 text-primary"></i>Tambah Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <?php if (! empty($errors)): ?>
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= esc($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nomor Akun <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_akun" class="form-control font-monospace"
                                placeholder="cth. 11102099"
                                maxlength="10"
                                value="<?= esc(old('nomor_akun')) ?>" required>
                            <div class="form-text">Maks. 10 karakter, unik.</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" name="nama_akun" class="form-control"
                                placeholder="cth. Bank BSI Zakat"
                                value="<?= esc(old('nama_akun')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe <span class="text-danger">*</span></label>
                            <select name="tipe" id="add_tipe" class="form-select" required>
                                <option value="">-- Pilih Tipe --</option>
                                <?php foreach ($tipeLabel as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= old('tipe') === $k ? 'selected' : '' ?>>
                                        <?= $v ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Akun Induk</label>
                            <select name="parent_id" id="add_parent_id" class="form-select">
                                <option value="">(Tanpa Induk — Level 1)</option>
                                <?php foreach ($parentOptions as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        data-tipe="<?= $p['tipe'] ?>"
                                        <?= old('parent_id') == $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['nomor_akun']) ?> — <?= esc($p['nama_akun']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_header" id="add_is_header" value="1"
                                    <?= old('is_header') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="add_is_header">
                                    <strong>Akun Grup / Header</strong>
                                    <small class="text-muted ms-2">(tidak dapat digunakan untuk posting jurnal langsung)</small>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================== MODAL EDIT ===================== -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEdit" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2 text-warning"></i>Edit Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nomor Akun <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_akun" id="edit_nomor_akun"
                                class="form-control font-monospace" maxlength="10" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" name="nama_akun" id="edit_nama_akun" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe <span class="text-danger">*</span></label>
                            <select name="tipe" id="edit_tipe" class="form-select" required>
                                <option value="">-- Pilih Tipe --</option>
                                <?php foreach ($tipeLabel as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Akun Induk</label>
                            <select name="parent_id" id="edit_parent_id" class="form-select">
                                <option value="">(Tanpa Induk — Level 1)</option>
                                <?php foreach ($parentOptions as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-tipe="<?= $p['tipe'] ?>">
                                        <?= esc($p['nomor_akun']) ?> — <?= esc($p['nama_akun']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_header" id="edit_is_header" value="1">
                                <label class="form-check-label" for="edit_is_header">
                                    <strong>Akun Grup / Header</strong>
                                    <small class="text-muted ms-2">(tidak dapat digunakan untuk posting jurnal langsung)</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-save me-1"></i>Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ─── Edit modal ───────────────────────────────────────────────
    function openEdit(data) {
        const form = document.getElementById('formEdit');
        form.action = '<?= base_url('master/akun/update/') ?>' + data.id;

        document.getElementById('edit_nomor_akun').value = data.nomor_akun;
        document.getElementById('edit_nama_akun').value  = data.nama_akun;
        document.getElementById('edit_tipe').value       = data.tipe;
        document.getElementById('edit_parent_id').value  = data.parent_id ?? '';
        document.getElementById('edit_is_header').checked = data.is_header == 1;

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    // ─── Tab filter ───────────────────────────────────────────────
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.filter-btn').forEach(function(b) {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
                b.querySelector('.badge').className = 'badge bg-secondary ms-1';
            });
            this.classList.add('btn-primary', 'active');
            this.classList.remove('btn-outline-secondary');
            this.querySelector('.badge').className = 'badge bg-white text-primary ms-1';

            var filter = this.dataset.filter;
            document.querySelectorAll('#tblAkun tbody tr').forEach(function(row) {
                if (filter === 'all' || row.dataset.tipe === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    <?php if (! empty($errors)): ?>
        window.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('modalTambah')).show();
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>