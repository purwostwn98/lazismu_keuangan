<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Login' ?> — Si-LazisMu UMS</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        body { background: #F0F2F5; }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #E8622A 0%, #C4491A 100%);
            padding: 36px 32px 28px;
            text-align: center;
            color: #fff;
        }
        .auth-header img { width: 60px; height: 60px; margin-bottom: 12px; filter: brightness(0) invert(1); }
        .auth-header .app-name { font-size: 1.4rem; font-weight: 700; letter-spacing: .3px; }
        .auth-header .app-sub  { font-size: .8rem; opacity: .85; margin-top: 2px; }
        .auth-body { padding: 32px; }
        .auth-title { font-size: 1rem; font-weight: 600; color: #3D4C5E; margin-bottom: 4px; }
        .auth-subtitle { font-size: .8rem; color: #A0AEBA; margin-bottom: 24px; }
        .auth-footer-text {
            text-align: center;
            font-size: .72rem;
            color: #A0AEBA;
            padding: 16px 32px;
            border-top: 1px solid #EEF0F3;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="<?= base_url('assets/img/logo/logo.png') ?>" alt="Logo">
            <div class="app-name">Si-LazisMu UMS</div>
            <div class="app-sub">Sistem Informasi Keuangan LAZISMU</div>
        </div>
        <div class="auth-body">
            <?= $this->renderSection('content') ?>
        </div>
        <div class="auth-footer-text">
            &copy; <?= date('Y') ?> LAZISMU Universitas Muhammadiyah Surakarta
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
