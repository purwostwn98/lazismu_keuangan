<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$errors  = session('errors')  ?? [];
$success = session('success') ?? '';
$error   = session('error')   ?? '';
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
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(232,98,42,.12);color:#E8622A;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total ?></div>
                    <div class="stat-label">Total Donatur</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(13,110,253,.12);color:#0d6efd;">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $individu ?></div>
                    <div class="stat-label">Individu</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(111,66,193,.12);color:#6f42c1;">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $lembaga ?></div>
                    <div class="stat-label">Lembaga</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(25,135,84,.12);color:#198754;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $aktif ?></div>
                    <div class="stat-label">Aktif</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <!-- Filter jenis -->
            <div class="btn-group btn-group-sm">
                <button class="btn btn-primary filter-btn active" data-filter="all">
                    Semua <span class="badge bg-white text-primary ms-1"><?= $total ?></span>
                </button>
                <button class="btn btn-outline-secondary filter-btn" data-filter="individu">
                    Individu <span class="badge bg-secondary ms-1"><?= $individu ?></span>
                </button>
                <button class="btn btn-outline-secondary filter-btn" data-filter="lembaga">
                    Lembaga <span class="badge bg-secondary ms-1"><?= $lembaga ?></span>
                </button>
            </div>
            <!-- Search -->
            <input type="text" id="searchDonatur" class="form-control form-control-sm"
                placeholder="Cari nama / kode / NIP..."
                style="width:220px;">
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Donatur
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-card mb-0" id="tblDonatur" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="width:110px;">Kode</th>
                        <th>Nama</th>
                        <th class="text-center" style="width:90px;">Jenis</th>
                        <th>Kategori</th>
                        <th>Kontak</th>
                        <th class="text-center" style="width:80px;">Status</th>
                        <th class="text-center" style="width:90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($donatur)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada data donatur.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($donatur as $i => $d): ?>
                            <tr data-jenis="<?= $d['jenis'] ?>"
                                data-search="<?= strtolower(esc($d['nama']) . ' ' . esc($d['kode']) . ' ' . esc($d['nip'] ?? '')) ?>"
                                class="<?= $d['is_aktif'] ? '' : 'table-light text-muted' ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <code style="font-size:.8rem;color:#495057;"><?= esc($d['kode']) ?></code>
                                </td>
                                <td class="fw-semibold"><?= esc($d['nama']) ?></td>
                                <td class="text-center">
                                    <?php if ($d['jenis'] === 'individu'): ?>
                                        <span class="badge" style="background:#e3f0ff;color:#0d6efd;font-size:.72rem;">
                                            <i class="fas fa-user me-1"></i>Individu
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#f3e8ff;color:#6f42c1;font-size:.72rem;">
                                            <i class="fas fa-building me-1"></i>Lembaga
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($d['nama_kategori']): ?>
                                        <span class="text-muted" style="font-size:.82rem;"><?= esc($d['nama_kategori']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:.82rem;">
                                    <?php if ($d['nip']): ?>
                                        <div><i class="fas fa-id-badge text-muted me-1" style="font-size:.7rem;"></i><?= esc($d['nip']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($d['no_hp']): ?>
                                        <div><i class="fas fa-phone text-muted me-1" style="font-size:.7rem;"></i><?= esc($d['no_hp']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($d['email']): ?>
                                        <div><i class="fas fa-envelope text-muted me-1" style="font-size:.7rem;"></i><?= esc($d['email']) ?></div>
                                    <?php endif; ?>
                                    <?php if (! $d['nip'] && ! $d['no_hp'] && ! $d['email']): ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($d['is_aktif']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                        title="Edit"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <a href="<?= base_url('master/donatur/toggle/' . $d['id']) ?>"
                                        class="btn btn-sm <?= $d['is_aktif'] ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-0 px-2"
                                        title="<?= $d['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>"
                                        onclick="return confirm('<?= $d['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?> donatur ini?')">
                                        <i class="fas fa-<?= $d['is_aktif'] ? 'ban' : 'check' ?>"></i>
                                    </a>
                                    <a href="<?= base_url('master/donatur/delete/' . $d['id']) ?>"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="Hapus"
                                        onclick="return confirm('Hapus donatur <?= esc(addslashes($d['nama'])) ?>?')">
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
            <form action="<?= base_url('master/donatur/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2 text-primary"></i>Tambah Donatur / Muzakki</h5>
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
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control"
                                placeholder="Nama lengkap donatur / muzakki"
                                value="<?= esc(old('nama')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" id="add_jenis" class="form-select" required>
                                <option value="individu" <?= old('jenis', 'individu') === 'individu' ? 'selected' : '' ?>>Individu</option>
                                <option value="lembaga" <?= old('jenis') === 'lembaga' ? 'selected' : '' ?>>Lembaga</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="kategori_id" class="form-select">
                                <option value="">(Tanpa Kategori)</option>
                                <?php foreach ($kategori as $kat): ?>
                                    <optgroup label="<?= esc($kat['nama']) ?>">
                                        <?php foreach ($kat['children'] as $sub): ?>
                                            <option value="<?= $sub['id'] ?>"
                                                <?= old('kategori_id') == $sub['id'] ? 'selected' : '' ?>>
                                                <?= esc($sub['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6" id="add_nip_wrap">
                            <label class="form-label fw-semibold">NIP</label>
                            <input type="text" name="nip" class="form-control"
                                placeholder="NIP Dosen / Karyawan UMS"
                                value="<?= esc(old('nip')) ?>">
                            <div class="form-text">Jika diisi, NIP akan digunakan sebagai Kode Donatur.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. HP / WA</label>
                            <input type="text" name="no_hp" class="form-control"
                                placeholder="cth. 081234567890"
                                value="<?= esc(old('no_hp')) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                placeholder="cth. nama@ums.ac.id"
                                value="<?= esc(old('email')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"
                                placeholder="Alamat lengkap (opsional)"><?= esc(old('alamat')) ?></textarea>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Kode Donatur</label>
                            <input type="text" name="kode" class="form-control font-monospace"
                                placeholder="Kosongkan untuk auto-generate"
                                value="<?= esc(old('kode')) ?>">
                            <div class="form-text">Otomatis: <code>DON00001</code>, atau gunakan NIP.</div>
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
                    <h5 class="modal-title"><i class="fas fa-pen me-2 text-warning"></i>Edit Donatur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" id="edit_jenis" class="form-select" required>
                                <option value="individu">Individu</option>
                                <option value="lembaga">Lembaga</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="kategori_id" id="edit_kategori_id" class="form-select">
                                <option value="">(Tanpa Kategori)</option>
                                <?php foreach ($kategori as $kat): ?>
                                    <optgroup label="<?= esc($kat['nama']) ?>">
                                        <?php foreach ($kat['children'] as $sub): ?>
                                            <option value="<?= $sub['id'] ?>"><?= esc($sub['nama']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIP</label>
                            <input type="text" name="nip" id="edit_nip" class="form-control"
                                placeholder="NIP Dosen / Karyawan UMS">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. HP / WA</label>
                            <input type="text" name="no_hp" id="edit_no_hp" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Kode Donatur <span class="text-danger">*</span></label>
                            <input type="text" name="kode" id="edit_kode" class="form-control font-monospace" required>
                        </div>
                        <div class="col-md-7 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_aktif" id="edit_is_aktif" value="1">
                                <label class="form-check-label" for="edit_is_aktif">Donatur Aktif</label>
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
        form.action = '<?= base_url('master/donatur/update/') ?>' + data.id;

        document.getElementById('edit_nama').value        = data.nama;
        document.getElementById('edit_jenis').value       = data.jenis;
        document.getElementById('edit_kategori_id').value = data.kategori_id ?? '';
        document.getElementById('edit_nip').value         = data.nip ?? '';
        document.getElementById('edit_no_hp').value       = data.no_hp ?? '';
        document.getElementById('edit_email').value       = data.email ?? '';
        document.getElementById('edit_alamat').value      = data.alamat ?? '';
        document.getElementById('edit_kode').value        = data.kode;
        document.getElementById('edit_is_aktif').checked  = data.is_aktif == 1;

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    // ─── Filter jenis (tab group) ─────────────────────────────────
    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(function(b) {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
                var badge = b.querySelector('.badge');
                if (badge) badge.className = 'badge bg-secondary ms-1';
            });
            this.classList.add('btn-primary', 'active');
            this.classList.remove('btn-outline-secondary');
            var badge = this.querySelector('.badge');
            if (badge) badge.className = 'badge bg-white text-primary ms-1';

            var filter = this.dataset.filter;
            applyFilters(filter, document.getElementById('searchDonatur').value);
        });
    });

    // ─── Search ───────────────────────────────────────────────────
    document.getElementById('searchDonatur').addEventListener('input', function() {
        var activeFilter = (document.querySelector('.filter-btn.active') || {}).dataset?.filter || 'all';
        applyFilters(activeFilter, this.value);
    });

    function applyFilters(jenis, search) {
        var q = search.toLowerCase().trim();
        document.querySelectorAll('#tblDonatur tbody tr').forEach(function(row) {
            var matchJenis  = jenis === 'all' || row.dataset.jenis === jenis;
            var matchSearch = q === '' || (row.dataset.search || '').includes(q);
            row.style.display = (matchJenis && matchSearch) ? '' : 'none';
        });
    }

    <?php if (! empty($errors)): ?>
        window.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('modalTambah')).show();
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>