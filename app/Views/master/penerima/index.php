<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$errors  = session('errors')  ?? [];
$success = session('success') ?? '';
$error   = session('error')   ?? '';

$asnafLabel = [
    'fakir'        => 'Fakir',
    'miskin'       => 'Miskin',
    'amil'         => 'Amil',
    'muallaf'      => 'Muallaf',
    'riqab'        => 'Riqab',
    'gharimin'     => 'Gharimin',
    'fisabilillah' => 'Fisabilillah',
    'ibnu_sabil'   => 'Ibnu Sabil',
];
$asnafColor = [
    'fakir'        => '#0d6efd',
    'miskin'       => '#198754',
    'amil'         => '#E8622A',
    'muallaf'      => '#6f42c1',
    'riqab'        => '#0dcaf0',
    'gharimin'     => '#ffc107',
    'fisabilillah' => '#20c997',
    'ibnu_sabil'   => '#fd7e14',
];
$asnafBg = [
    'fakir'        => '#e3f0ff',
    'miskin'       => '#e8f5e9',
    'amil'         => '#fef0e8',
    'muallaf'      => '#f3e8ff',
    'riqab'        => '#e0f9ff',
    'gharimin'     => '#fff8e1',
    'fisabilillah' => '#e0f7f0',
    'ibnu_sabil'   => '#fff3e0',
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
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(232,98,42,.12);color:#E8622A;">
                    <i class="fas fa-hands-holding-child"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total ?? 0 ?></div>
                    <div class="stat-label">Total Penerima</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:#e3f0ff;color:#0d6efd;">
                    <i class="fas fa-user-minus"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= ($byAsnaf['fakir'] ?? 0) + ($byAsnaf['miskin'] ?? 0) ?></div>
                    <div class="stat-label">Fakir + Miskin</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:#e8f5e9;color:#198754;">
                    <i class="fas fa-star-and-crescent"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= ($byAsnaf['fisabilillah'] ?? 0) + ($byAsnaf['ibnu_sabil'] ?? 0) ?></div>
                    <div class="stat-label">Fisabilillah + Ibnu Sabil</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:#fff8e1;color:#ffc107;">
                    <i class="fas fa-circle-question"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $tanpaAsnaf ?? 0 ?></div>
                    <div class="stat-label">Tanpa Asnaf</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <!-- Filter asnaf -->
            <select id="filterAsnaf" class="form-select form-select-sm" style="width:auto;min-width:170px;">
                <option value="">Semua Asnaf (<?= $total ?? 0 ?>)</option>
                <?php foreach ($asnafLabel as $k => $v): ?>
                    <option value="<?= $k ?>"><?= $v ?> (<?= $byAsnaf[$k] ?? 0 ?>)</option>
                <?php endforeach; ?>
                <option value="_kosong">— Tanpa Asnaf (<?= $tanpaAsnaf ?? 0 ?>)</option>
            </select>
            <!-- Search -->
            <input type="text" id="searchPenerima" class="form-control form-control-sm"
                placeholder="Cari nama / kode..."
                style="width:200px;">
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Penerima
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-card mb-0" id="tblPenerima" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="width:110px;">Kode</th>
                        <th>Nama</th>
                        <th class="text-center" style="width:90px;">Tipe</th>
                        <th class="text-center" style="width:130px;">Asnaf</th>
                        <th style="width:130px;">No. HP</th>
                        <th style="width:170px;">Email</th>
                        <th>Alamat</th>
                        <th class="text-center" style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($penerima)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada data penerima manfaat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($penerima as $i => $p): ?>
                            <tr data-asnaf="<?= $p['asnaf'] ?? '_kosong' ?>"
                                data-search="<?= strtolower(esc($p['nama']) . ' ' . esc($p['kode']) . ' ' . esc($p['email'] ?? '')) ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <code style="font-size:.8rem;color:#495057;"><?= esc($p['kode']) ?></code>
                                </td>
                                <td class="fw-semibold"><?= esc($p['nama']) ?></td>
                                <td class="text-center">
                                    <?php if (($p['tipe'] ?? 'individu') === 'lembaga'): ?>
                                        <span class="badge" style="font-size:.72rem;background:#e3f0ff;color:#0d6efd;border:1px solid #0d6efd33;">
                                            <i class="fas fa-building me-1"></i>Lembaga
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="font-size:.72rem;background:#e8f5e9;color:#198754;border:1px solid #19875433;">
                                            <i class="fas fa-user me-1"></i>Individu
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($p['asnaf']): ?>
                                        <span class="badge" style="font-size:.72rem;
                                            background:<?= $asnafBg[$p['asnaf']] ?? '#e9ecef' ?>;
                                            color:<?= $asnafColor[$p['asnaf']] ?? '#6c757d' ?>;
                                            border:1px solid <?= $asnafColor[$p['asnaf']] ?? '#6c757d' ?>33;">
                                            <?= $asnafLabel[$p['asnaf']] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.78rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['no_hp']): ?>
                                        <span style="font-size:.82rem;"><?= esc($p['no_hp']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['email'])): ?>
                                        <span style="font-size:.8rem;"><?= esc($p['email']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['alamat']): ?>
                                        <span style="font-size:.8rem;" title="<?= esc($p['alamat']) ?>">
                                            <?= esc(mb_strimwidth($p['alamat'], 0, 50, '…')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                        title="Edit"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <a href="<?= base_url('master/penerima/delete/' . $p['id']) ?>"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="Hapus"
                                        onclick="return confirm('Hapus penerima <?= esc(addslashes($p['nama'])) ?>?')">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('master/penerima/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2 text-primary"></i>Tambah Penerima Manfaat</h5>
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
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tipe Penerima <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipe_individu" value="individu"
                                        <?= old('tipe', 'individu') === 'individu' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tipe_individu">
                                        <i class="fas fa-user me-1 text-success"></i>Individu
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipe_lembaga" value="lembaga"
                                        <?= old('tipe') === 'lembaga' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tipe_lembaga">
                                        <i class="fas fa-building me-1 text-primary"></i>Lembaga
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control"
                                placeholder="Nama lengkap penerima manfaat / nama lembaga"
                                value="<?= esc(old('nama')) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Golongan Asnaf</label>
                            <select name="asnaf" class="form-select">
                                <option value="">(Tidak / Belum Ditentukan)</option>
                                <?php foreach ($asnafLabel as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= old('asnaf') === $k ? 'selected' : '' ?>>
                                        <?= $v ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Golongan 8 asnaf penerima zakat menurut syariah.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                placeholder="email@contoh.com (opsional, harus unik)"
                                value="<?= esc(old('email')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">No. HP / WA</label>
                            <input type="text" name="no_hp" class="form-control"
                                placeholder="cth. 081234567890"
                                value="<?= esc(old('no_hp')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3"
                                placeholder="Alamat lengkap (opsional)"><?= esc(old('alamat')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Kode Penerima</label>
                            <input type="text" name="kode" class="form-control font-monospace"
                                placeholder="Kosongkan untuk auto-generate (PM00001)"
                                value="<?= esc(old('kode')) ?>">
                            <div class="form-text">Dapat diisi dengan <strong>NIK</strong> (untuk individu) atau <strong>Nomor Lembaga</strong> (untuk lembaga). Kosongkan untuk auto-generate.</div>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEdit" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2 text-warning"></i>Edit Penerima Manfaat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tipe Penerima <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="edit_tipe_individu" value="individu">
                                    <label class="form-check-label" for="edit_tipe_individu">
                                        <i class="fas fa-user me-1 text-success"></i>Individu
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe" id="edit_tipe_lembaga" value="lembaga">
                                    <label class="form-check-label" for="edit_tipe_lembaga">
                                        <i class="fas fa-building me-1 text-primary"></i>Lembaga
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Golongan Asnaf</label>
                            <select name="asnaf" id="edit_asnaf" class="form-select">
                                <option value="">(Tidak / Belum Ditentukan)</option>
                                <?php foreach ($asnafLabel as $k => $v): ?>
                                    <option value="<?= $k ?>"><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control"
                                placeholder="email@contoh.com (opsional, harus unik)">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">No. HP / WA</label>
                            <input type="text" name="no_hp" id="edit_no_hp" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Kode Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="kode" id="edit_kode"
                                class="form-control font-monospace" required>
                            <div class="form-text">Dapat diisi dengan <strong>NIK</strong> (individu) atau <strong>Nomor Lembaga</strong> (lembaga).</div>
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

<!-- Asnaf info tooltip card -->
<div class="mt-3">
    <small class="text-muted">
        <i class="fas fa-info-circle me-1"></i>
        <strong>8 Asnaf Zakat:</strong>
        Fakir · Miskin · Amil · Muallaf · Riqab (hamba sahaya) · Gharimin (berhutang) · Fisabilillah (di jalan Allah) · Ibnu Sabil (musafir)
    </small>
</div>

<script>
    // ─── Edit modal ───────────────────────────────────────────────
    function openEdit(data) {
        const form = document.getElementById('formEdit');
        form.action = '<?= base_url('master/penerima/update/') ?>' + data.id;

        const tipe = data.tipe ?? 'individu';
        document.getElementById('edit_tipe_individu').checked = tipe === 'individu';
        document.getElementById('edit_tipe_lembaga').checked = tipe === 'lembaga';

        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_asnaf').value = data.asnaf ?? '';
        document.getElementById('edit_email').value = data.email ?? '';
        document.getElementById('edit_no_hp').value = data.no_hp ?? '';
        document.getElementById('edit_alamat').value = data.alamat ?? '';
        document.getElementById('edit_kode').value = data.kode;

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    // ─── Filter & Search ─────────────────────────────────────────
    function applyFilters() {
        var asnaf = document.getElementById('filterAsnaf').value;
        var search = document.getElementById('searchPenerima').value.toLowerCase().trim();

        document.querySelectorAll('#tblPenerima tbody tr').forEach(function(row) {
            var matchAsnaf = asnaf === '' || row.dataset.asnaf === asnaf;
            var matchSearch = search === '' || (row.dataset.search || '').includes(search);
            row.style.display = (matchAsnaf && matchSearch) ? '' : 'none';
        });
    }

    document.getElementById('filterAsnaf').addEventListener('change', applyFilters);
    document.getElementById('searchPenerima').addEventListener('input', applyFilters);

    <?php if (! empty($errors)): ?>
        window.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('modalTambah')).show();
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>