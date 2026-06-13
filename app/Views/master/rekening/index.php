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
    <div class="col-6 col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(232,98,42,.12);color:#E8622A;">
                    <i class="fas fa-university"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total ?? 0 ?></div>
                    <div class="stat-label">Total Rekening</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(25,135,84,.12);color:#198754;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $aktif ?? 0 ?></div>
                    <div class="stat-label">Aktif</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <div class="stat-icon" style="background:rgba(108,117,125,.12);color:#6c757d;">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $nonAktif ?? 0 ?></div>
                    <div class="stat-label">Non-Aktif</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-university me-2 text-primary"></i>Daftar Rekening Bank</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>Tambah Rekening
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-card mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Nama Rekening</th>
                        <th>Nomor Rekening</th>
                        <th>Bank</th>
                        <th>Jenis Dana</th>
                        <th>Akun</th>
                        <th class="text-end">Saldo Awal</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rekening)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>Belum ada data rekening bank.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekening as $i => $r): ?>
                            <tr class="<?= $r['is_aktif'] ? '' : 'table-light text-muted' ?>">
                                <td><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= esc($r['nama']) ?></td>
                                <td>
                                    <?php if ($r['nomor_rekening']): ?>
                                        <code style="font-size:.82rem;color:#495057;"><?= esc($r['nomor_rekening']) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($r['bank']) ?></td>
                                <td>
                                    <span class="badge badge-soft-blue" style="font-size:.75rem;">
                                        <?= esc($r['nama_dana']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size:.78rem;"><?= esc($r['nomor_akun']) ?></span>
                                    <span> <?= esc($r['nama_akun']) ?></span>
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp <?= number_format($r['saldo_awal'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['is_aktif']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                        title="Edit"
                                        onclick="openEdit(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <a href="<?= base_url('master/rekening/toggle/' . $r['id']) ?>"
                                        class="btn btn-sm <?= $r['is_aktif'] ? 'btn-outline-secondary' : 'btn-outline-success' ?> py-0 px-2"
                                        title="<?= $r['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>"
                                        onclick="return confirm('<?= $r['is_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?> rekening ini?')">
                                        <i class="fas fa-<?= $r['is_aktif'] ? 'ban' : 'check' ?>"></i>
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
            <form action="<?= base_url('master/rekening/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2 text-primary"></i>Tambah Rekening Bank</h5>
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control"
                                placeholder="cth. Kas Zakat BSI"
                                value="<?= esc(old('nama')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" class="form-control"
                                placeholder="cth. 7188888888"
                                value="<?= esc(old('nomor_rekening')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" name="bank" class="form-control"
                                placeholder="cth. BSI / BCA / Mandiri"
                                value="<?= esc(old('bank')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <?php
                            $jenisDana = $jenisDana ?? [];
                            ?>
                            <label class="form-label fw-semibold">Jenis Dana <span class="text-danger">*</span></label>
                            <select name="jenis_dana_id" id="tambah_jenis_dana_id" class="form-select" required>
                                <option value="">-- Pilih Jenis Dana --</option>
                                <?php foreach ($jenisDana as $jd): ?>
                                    <option value="<?= $jd['id'] ?>" <?= old('jenis_dana_id') == $jd['id'] ? 'selected' : '' ?>>
                                        <?= esc($jd['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <?php
                            $akunKas = $akunKas ?? [];
                            ?>
                            <label class="form-label fw-semibold">Akun Kas <span class="text-danger">*</span></label>
                            <select name="akun_id" id="tambah_akun_id" class="form-select" required>
                                <option value="">-- Pilih Akun --</option>
                                <?php foreach ($akunKas as $ak): ?>
                                    <option value="<?= $ak['id'] ?>" <?= old('akun_id') == $ak['id'] ? 'selected' : '' ?>>
                                        <?= esc($ak['nomor_akun']) ?> — <?= esc($ak['nama_akun']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Akun yang terhubung untuk pencatatan jurnal otomatis.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Saldo Awal (Rp)</label>
                            <input type="text" name="saldo_awal" class="form-control text-end rupiah-input"
                                placeholder="0"
                                value="<?= old('saldo_awal') ? number_format((float)old('saldo_awal'), 0, ',', '.') : '' ?>">
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
                    <h5 class="modal-title"><i class="fas fa-pen me-2 text-warning"></i>Edit Rekening Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" id="edit_nomor_rekening" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" name="bank" id="edit_bank" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Dana <span class="text-danger">*</span></label>
                            <select name="jenis_dana_id" id="edit_jenis_dana_id" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($jenisDana as $jd): ?>
                                    <option value="<?= $jd['id'] ?>"><?= esc($jd['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Akun Kas <span class="text-danger">*</span></label>
                            <select name="akun_id" id="edit_akun_id" class="form-select" required>
                                <option value="">-- Pilih Akun --</option>
                                <?php foreach ($akunKas as $ak): ?>
                                    <option value="<?= $ak['id'] ?>">
                                        <?= esc($ak['nomor_akun']) ?> — <?= esc($ak['nama_akun']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Saldo Awal (Rp)</label>
                            <input type="text" name="saldo_awal" id="edit_saldo_awal" class="form-control text-end rupiah-input">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_aktif" id="edit_is_aktif" value="1">
                                <label class="form-check-label" for="edit_is_aktif">Rekening Aktif</label>
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
    const tsTambahJenisDana = new TomSelect('#tambah_jenis_dana_id', { maxOptions: 20,  allowEmptyOption: true });
    const tsTambahAkun      = new TomSelect('#tambah_akun_id',       { maxOptions: 500, allowEmptyOption: true });
    const tsEditJenisDana   = new TomSelect('#edit_jenis_dana_id',   { maxOptions: 20,  allowEmptyOption: true });
    const tsEditAkun        = new TomSelect('#edit_akun_id',         { maxOptions: 500, allowEmptyOption: true });

    function openEdit(data) {
        const form = document.getElementById('formEdit');
        form.action = '<?= base_url('master/rekening/update/') ?>' + data.id;

        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_nomor_rekening').value = data.nomor_rekening ?? '';
        document.getElementById('edit_bank').value = data.bank;
        tsEditJenisDana.setValue(String(data.jenis_dana_id), true);
        tsEditAkun.setValue(String(data.akun_id), true);
        document.getElementById('edit_is_aktif').checked = data.is_aktif == 1;

        // Format saldo awal ke Rupiah
        const saldo = parseFloat(data.saldo_awal) || 0;
        document.getElementById('edit_saldo_awal').value = saldo > 0 ?
            saldo.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }) :
            '';

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    // Format rupiah on input
    document.querySelectorAll('.rupiah-input').forEach(function(el) {
        el.addEventListener('blur', function() {
            const raw = this.value.replace(/\./g, '').replace(',', '.');
            const num = parseFloat(raw);
            if (!isNaN(num) && num > 0) {
                this.value = num.toLocaleString('id-ID', {
                    maximumFractionDigits: 0
                });
            }
        });
        el.addEventListener('focus', function() {
            this.value = this.value.replace(/\./g, '');
        });
    });

    // Strip format sebelum submit (tambah & edit)
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            form.querySelectorAll('.rupiah-input').forEach(function(el) {
                el.value = el.value.replace(/\./g, '').replace(',', '.');
            });
        });
    });

    <?php if (! empty($errors)): ?>
        // Auto-open modal tambah jika ada error validasi
        window.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('modalTambah')).show();
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>