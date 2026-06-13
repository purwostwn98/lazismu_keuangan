<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$errors   = session('errors')     ?? [];
$success  = session('success')    ?? '';
$error    = session('error')      ?? '';
$openModal = session('open_modal') ?? '';

$roleBadge = [
    'admin'     => ['bg' => 'rgba(220,53,69,.12)',   'color' => '#dc3545', 'label' => 'Admin'],
    'bendahara' => ['bg' => 'rgba(13,110,253,.12)',  'color' => '#0d6efd', 'label' => 'Bendahara'],
    'manajer'   => ['bg' => 'rgba(111,66,193,.12)',  'color' => '#6f42c1', 'label' => 'Manajer'],
    'auditor'   => ['bg' => 'rgba(255,193,7,.18)',   'color' => '#856404', 'label' => 'Auditor'],
];
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?= esc($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?= esc($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(13,110,253,.12);color:#0d6efd;">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(25,135,84,.12);color:#198754;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $aktif ?></div>
                    <div class="stat-label">Aktif</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(232,98,42,.12);color:#E8622A;">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $muzaki ?></div>
                    <div class="stat-label">Merangkap Muzaki</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(111,66,193,.12);color:#6f42c1;">
                    <i class="fas fa-hands-holding-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $mustahik ?></div>
                    <div class="stat-label">Merangkap Mustahik</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2 align-items-center">
            <input type="text" id="searchUser" class="form-control form-control-sm"
                placeholder="Cari nama / username / email..."
                style="width:240px;">
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Pengguna
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-card mb-0" id="tblUser" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Nama</th>
                        <th style="width:120px;">Username</th>
                        <th style="width:100px;" class="text-center">Role Sistem</th>
                        <th class="text-center" style="width:80px;">Muzaki</th>
                        <th class="text-center" style="width:80px;">Mustahik</th>
                        <th class="text-center" style="width:70px;">Status</th>
                        <th class="text-center" style="width:90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada pengguna.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $i => $u): ?>
                            <?php $rb = $roleBadge[$u['role']] ?? ['bg' => '#eee', 'color' => '#333', 'label' => $u['role']]; ?>
                            <tr data-search="<?= strtolower(esc($u['nama']) . ' ' . esc($u['username']) . ' ' . esc($u['email'])) ?>"
                                class="<?= $u['is_aktif'] ? '' : 'table-light text-muted' ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($u['nama']) ?></div>
                                    <div class="text-muted" style="font-size:.78rem;"><?= esc($u['email']) ?></div>
                                </td>
                                <td><code style="font-size:.8rem;"><?= esc($u['username']) ?></code></td>
                                <td class="text-center">
                                    <span class="badge" style="background:<?= $rb['bg'] ?>;color:<?= $rb['color'] ?>;font-size:.72rem;">
                                        <?= $rb['label'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($u['is_muzaki']): ?>
                                        <span class="badge" style="background:rgba(232,98,42,.12);color:#E8622A;font-size:.72rem;" title="<?= esc($u['nama_donatur'] ?? '') ?>">
                                            <i class="fas fa-check"></i>
                                            <?php if ($u['nama_donatur']): ?>
                                                <span style="font-size:.68rem;"><?= esc($u['nama_donatur']) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($u['is_mustahik']): ?>
                                        <span class="badge" style="background:rgba(111,66,193,.12);color:#6f42c1;font-size:.72rem;" title="<?= esc($u['nama_penerima'] ?? '') ?>">
                                            <i class="fas fa-check"></i>
                                            <?php if ($u['nama_penerima']): ?>
                                                <span style="font-size:.68rem;"><?= esc($u['nama_penerima']) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($u['is_aktif']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                        title="Edit"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <a href="<?= base_url('pengguna/toggle/' . $u['id']) ?>"
                                        class="btn btn-sm <?= $u['is_aktif'] ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-0 px-2"
                                        title="<?= $u['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>"
                                        onclick="return confirm('<?= $u['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?> pengguna ini?')">
                                        <i class="fas fa-<?= $u['is_aktif'] ? 'ban' : 'check' ?>"></i>
                                    </a>
                                    <a href="<?= base_url('pengguna/delete/' . $u['id']) ?>"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="Hapus"
                                        onclick="return confirm('Hapus pengguna <?= esc(addslashes($u['nama'])) ?>?')">
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
            <form action="<?= base_url('pengguna/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (! empty($errors) && $openModal === 'modalTambah'): ?>
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= esc($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Identitas -->
                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold text-muted mb-2" style="font-size:.78rem;letter-spacing:.05em;text-transform:uppercase;">
                            <i class="fas fa-user me-1"></i>Identitas &amp; Akses
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control"
                                    value="<?= esc(old('nama')) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Role Sistem <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="admin"     <?= old('role') === 'admin'     ? 'selected' : '' ?>>Admin</option>
                                    <option value="bendahara" <?= old('role', 'bendahara') === 'bendahara' ? 'selected' : '' ?>>Bendahara</option>
                                    <option value="manajer"   <?= old('role') === 'manajer'   ? 'selected' : '' ?>>Manajer</option>
                                    <option value="auditor"   <?= old('role') === 'auditor'   ? 'selected' : '' ?>>Auditor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control font-monospace"
                                    placeholder="huruf, angka, underscore, strip"
                                    value="<?= esc(old('username')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= esc(old('email')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>
                    </div>

                    <!-- Peran Tambahan -->
                    <div class="border rounded p-3">
                        <div class="fw-semibold text-muted mb-2" style="font-size:.78rem;letter-spacing:.05em;text-transform:uppercase;">
                            <i class="fas fa-id-card me-1"></i>Peran Tambahan (Merangkap)
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_muzaki" id="add_is_muzaki"
                                        value="1" onchange="toggleSelect('add_donatur_wrap', this.checked)"
                                        <?= old('is_muzaki') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="add_is_muzaki">
                                        Muzaki / Donatur
                                    </label>
                                </div>
                                <div id="add_donatur_wrap" style="display:<?= old('is_muzaki') ? 'block' : 'none' ?>;">
                                    <label class="form-label text-muted" style="font-size:.82rem;">Tautkan ke data donatur</label>
                                    <select name="donatur_id" class="form-select form-select-sm">
                                        <option value="">(Pilih Donatur)</option>
                                        <?php foreach ($donatur as $d): ?>
                                            <option value="<?= $d['id'] ?>"
                                                <?= old('donatur_id') == $d['id'] ? 'selected' : '' ?>>
                                                <?= esc($d['nama']) ?> — <?= esc($d['kode']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_mustahik" id="add_is_mustahik"
                                        value="1" onchange="toggleSelect('add_penerima_wrap', this.checked)"
                                        <?= old('is_mustahik') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="add_is_mustahik">
                                        Mustahik / Penerima
                                    </label>
                                </div>
                                <div id="add_penerima_wrap" style="display:<?= old('is_mustahik') ? 'block' : 'none' ?>;">
                                    <label class="form-label text-muted" style="font-size:.82rem;">Tautkan ke data penerima manfaat</label>
                                    <select name="penerima_manfaat_id" class="form-select form-select-sm">
                                        <option value="">(Pilih Penerima)</option>
                                        <?php foreach ($penerima as $p): ?>
                                            <option value="<?= $p['id'] ?>"
                                                <?= old('penerima_manfaat_id') == $p['id'] ? 'selected' : '' ?>>
                                                <?= esc($p['nama']) ?> — <?= esc($p['kode']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
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
                    <h5 class="modal-title"><i class="fas fa-pen me-2 text-warning"></i>Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (! empty($errors) && str_starts_with($openModal, 'edit_')): ?>
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= esc($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Identitas -->
                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold text-muted mb-2" style="font-size:.78rem;letter-spacing:.05em;text-transform:uppercase;">
                            <i class="fas fa-user me-1"></i>Identitas &amp; Akses
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="edit_nama" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Role Sistem <span class="text-danger">*</span></label>
                                <select name="role" id="edit_role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="bendahara">Bendahara</option>
                                    <option value="manajer">Manajer</option>
                                    <option value="auditor">Auditor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Kosongkan jika tidak diubah">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_aktif" id="edit_is_aktif" value="1">
                                    <label class="form-check-label" for="edit_is_aktif">Pengguna Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Peran Tambahan -->
                    <div class="border rounded p-3">
                        <div class="fw-semibold text-muted mb-2" style="font-size:.78rem;letter-spacing:.05em;text-transform:uppercase;">
                            <i class="fas fa-id-card me-1"></i>Peran Tambahan (Merangkap)
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_muzaki" id="edit_is_muzaki"
                                        value="1" onchange="toggleSelect('edit_donatur_wrap', this.checked)">
                                    <label class="form-check-label fw-semibold" for="edit_is_muzaki">
                                        Muzaki / Donatur
                                    </label>
                                </div>
                                <div id="edit_donatur_wrap">
                                    <label class="form-label text-muted" style="font-size:.82rem;">Tautkan ke data donatur</label>
                                    <select name="donatur_id" id="edit_donatur_id" class="form-select form-select-sm">
                                        <option value="">(Pilih Donatur)</option>
                                        <?php foreach ($donatur as $d): ?>
                                            <option value="<?= $d['id'] ?>"><?= esc($d['nama']) ?> — <?= esc($d['kode']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_mustahik" id="edit_is_mustahik"
                                        value="1" onchange="toggleSelect('edit_penerima_wrap', this.checked)">
                                    <label class="form-check-label fw-semibold" for="edit_is_mustahik">
                                        Mustahik / Penerima
                                    </label>
                                </div>
                                <div id="edit_penerima_wrap">
                                    <label class="form-label text-muted" style="font-size:.82rem;">Tautkan ke data penerima manfaat</label>
                                    <select name="penerima_manfaat_id" id="edit_penerima_manfaat_id" class="form-select form-select-sm">
                                        <option value="">(Pilih Penerima)</option>
                                        <?php foreach ($penerima as $p): ?>
                                            <option value="<?= $p['id'] ?>"><?= esc($p['nama']) ?> — <?= esc($p['kode']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
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
function toggleSelect(wrapperId, show) {
    document.getElementById(wrapperId).style.display = show ? 'block' : 'none';
}

function openEdit(data) {
    const form = document.getElementById('formEdit');
    form.action = '<?= base_url('pengguna/update/') ?>' + data.id;

    document.getElementById('edit_nama').value     = data.nama;
    document.getElementById('edit_role').value     = data.role;
    document.getElementById('edit_email').value    = data.email;
    document.getElementById('edit_is_aktif').checked = data.is_aktif == 1;

    const isMuzaki   = data.is_muzaki   == 1;
    const isMustahik = data.is_mustahik == 1;

    document.getElementById('edit_is_muzaki').checked   = isMuzaki;
    document.getElementById('edit_is_mustahik').checked = isMustahik;

    document.getElementById('edit_donatur_wrap').style.display  = isMuzaki   ? 'block' : 'none';
    document.getElementById('edit_penerima_wrap').style.display = isMustahik ? 'block' : 'none';

    document.getElementById('edit_donatur_id').value          = data.donatur_id          ?? '';
    document.getElementById('edit_penerima_manfaat_id').value = data.penerima_manfaat_id ?? '';

    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

// Search
document.getElementById('searchUser').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#tblUser tbody tr').forEach(function(row) {
        row.style.display = q === '' || (row.dataset.search || '').includes(q) ? '' : 'none';
    });
});

<?php if ($openModal === 'modalTambah'): ?>
    window.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('modalTambah')).show();
    });
<?php elseif (str_starts_with($openModal, 'edit_')): ?>
    window.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    });
<?php endif; ?>
</script>

<?= $this->endSection() ?>
