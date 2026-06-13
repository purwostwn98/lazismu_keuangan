<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<h5 class="auth-title">Masuk ke Sistem</h5>
<p class="auth-subtitle">Masukkan kredensial Anda untuk melanjutkan</p>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.82rem;">
        <i class="fas fa-circle-exclamation"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label fw-semibold" style="font-size:.82rem;">Username</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="fas fa-user text-muted"></i>
            </span>
            <input type="text" name="username" class="form-control border-start-0"
                   placeholder="Masukkan username" value="<?= old('username') ?>" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold" style="font-size:.82rem;">Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="fas fa-lock text-muted"></i>
            </span>
            <input type="password" name="password" id="passwordInput"
                   class="form-control border-start-0 border-end-0"
                   placeholder="Masukkan password" required>
            <button type="button" class="input-group-text bg-light border-start-0"
                    onclick="togglePassword()" title="Tampilkan/sembunyikan">
                <i class="fas fa-eye text-muted" id="eyeIcon"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
        <i class="fas fa-sign-in-alt me-2"></i>Masuk
    </button>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
<?= $this->endSection() ?>
