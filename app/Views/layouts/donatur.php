<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Portal Donatur') ?> — Si-LazisMu UMS</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo/logo.png') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: #F0F2F5;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .portal-navbar {
            background: linear-gradient(135deg, #E8622A 0%, #C4491A 100%);
            padding: .6rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .portal-navbar .brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            text-decoration: none;
        }

        .portal-navbar .brand img {
            width: 32px;
            height: 32px;
            filter: brightness(0) invert(1);
        }

        .portal-navbar .brand-text {
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            line-height: 1.1;
        }

        .portal-navbar .brand-sub {
            color: rgba(255,255,255,.75);
            font-size: .68rem;
            font-weight: 400;
        }

        .portal-nav-links {
            display: flex;
            align-items: center;
            gap: .25rem;
        }

        .portal-nav-links a {
            color: rgba(255,255,255,.85);
            text-decoration: none;
            font-size: .82rem;
            padding: .35rem .7rem;
            border-radius: 6px;
            transition: background .15s;
        }

        .portal-nav-links a:hover,
        .portal-nav-links a.active {
            background: rgba(255,255,255,.18);
            color: #fff;
        }

        .portal-nav-links a i {
            margin-right: .3rem;
        }

        .portal-user {
            display: flex;
            align-items: center;
            gap: .6rem;
            color: #fff;
        }

        .portal-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem;
        }

        .portal-user-name {
            font-size: .82rem;
            font-weight: 600;
        }

        .portal-user-sub {
            font-size: .68rem;
            opacity: .75;
        }

        .logout-btn {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            font-size: .75rem;
            padding: .3rem .65rem;
            border-radius: 6px;
            text-decoration: none;
            transition: background .15s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,.25);
            color: #fff;
        }

        .portal-content {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.5rem 1rem 3rem;
        }

        .card-portal {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
        }

        @media (max-width: 576px) {
            .portal-nav-links { display: none; }
            .portal-navbar { gap: .5rem; }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body>

    <!-- Navbar -->
    <nav class="portal-navbar">
        <a href="<?= base_url('donatur/portal') ?>" class="brand">
            <img src="<?= base_url('assets/img/logo/logo.png') ?>" alt="Logo">
            <div>
                <div class="brand-text">Si-LazisMu</div>
                <div class="brand-sub">Portal Donatur</div>
            </div>
        </a>

        <div class="portal-nav-links">
            <?php
            $seg = service('uri')->getSegment(2) ?? 'portal';
            ?>
            <a href="<?= base_url('donatur/portal') ?>" class="<?= $seg === 'portal' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Beranda
            </a>
            <a href="<?= base_url('donatur/profil') ?>" class="<?= $seg === 'profil' ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i> Profil Saya
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="portal-user d-none d-sm-flex">
                <div class="portal-avatar">
                    <?= strtoupper(mb_substr(session()->get('user_nama') ?? 'D', 0, 1)) ?>
                </div>
                <div>
                    <div class="portal-user-name"><?= esc(session()->get('user_nama') ?? '') ?></div>
                    <div class="portal-user-sub">Donatur / Muzakki</div>
                </div>
            </div>
            <a href="<?= base_url('logout') ?>" class="logout-btn">
                <i class="fas fa-sign-out-alt me-1"></i>Keluar
            </a>
        </div>
    </nav>

    <!-- Content -->
    <div class="portal-content">
        <?= $this->renderSection('content') ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>
