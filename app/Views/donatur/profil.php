<?= $this->extend('layouts/donatur') ?>

<?= $this->section('content') ?>
<?php
$donatur = $donatur ?? (object) [];
$success = $success ?? '';
$error   = $error   ?? '';
?>

<div class="mb-3">
    <h5 class="fw-bold mb-0">Profil Saya</h5>
    <small class="text-muted">Kelola informasi dan keamanan akun Anda</small>
</div>

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

<div class="row g-3">

    <!-- Info Donatur -->
    <div class="col-lg-6">
        <div class="card card-portal h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-id-card text-primary me-2"></i>
                    Informasi Donatur
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size:.85rem;">
                    <tr>
                        <td class="text-muted" style="width:130px;">Kode</td>
                        <td><code><?= esc($donatur->kode ?? '—') ?></code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama</td>
                        <td class="fw-semibold"><?= esc($donatur->nama ?? '—') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis</td>
                        <td>
                            <?php if (($donatur->jenis ?? '') === 'individu'): ?>
                                <span class="badge" style="background:#e3f0ff;color:#0d6efd;font-size:.72rem;">
                                    <i class="fas fa-user me-1"></i>Individu
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background:#f3e8ff;color:#6f42c1;font-size:.72rem;">
                                    <i class="fas fa-building me-1"></i>Lembaga
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori</td>
                        <td>
                            <?php if ($donatur->kategori_parent ?? ''): ?>
                                <?= esc($donatur->kategori_parent) ?> › <?= esc($donatur->kategori_nama) ?>
                            <?php else: ?>
                                <?= esc($donatur->kategori_nama ?? '—') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($donatur->nip ?? ''): ?>
                        <tr>
                            <td class="text-muted">NIP</td>
                            <td><?= esc($donatur->nip) ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Kontak -->
    <div class="col-lg-6">
        <div class="card card-portal h-100">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-pen text-warning me-2"></i>
                    Edit Kontak
                </h6>
            </div>
            <div class="card-body">
                <form action="<?= base_url('donatur/profil/update') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control form-control-sm"
                            value="<?= esc($donatur->no_hp ?? '') ?>"
                            placeholder="cth. 081234567890">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm"
                            value="<?= esc($donatur->email ?? '') ?>"
                            placeholder="cth. nama@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <textarea name="alamat" class="form-control form-control-sm" rows="2"
                            placeholder="Alamat lengkap"><?= esc($donatur->alamat ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ganti Password -->
    <div class="col-lg-6">
        <div class="card card-portal">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-key text-danger me-2"></i>
                    Ganti Password
                </h6>
            </div>
            <div class="card-body">
                <form action="<?= base_url('donatur/ganti-password') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control form-control-sm"
                            placeholder="Masukkan password lama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control form-control-sm"
                            placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Konfirmasi Password Baru</label>
                        <input type="password" name="password_konfirm" class="form-control form-control-sm"
                            placeholder="Ulangi password baru" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-key me-1"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Akun -->
    <div class="col-lg-6">
        <div class="card card-portal">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-user-circle text-info me-2"></i>
                    Info Akun Login
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size:.85rem;">
                    <tr>
                        <td class="text-muted" style="width:130px;">Username</td>
                        <td><code><?= esc(session()->get('user_username') ?? '—') ?></code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email Akun</td>
                        <td><?= esc(session()->get('user_email') ?? '—') ?></td>
                    </tr>
                </table>
                <p class="text-muted mt-3 mb-0" style="font-size:.75rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    Username tidak dapat diubah sendiri. Hubungi admin jika perlu perubahan username atau email akun.
                </p>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>