<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? "Lazismu UMS") ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo/logo.png') ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

    <style>
        :root {
            --brand: #E8622A;
            --brand-dk: #C4491A;
            --brand-lt: #FFF0EA;
            --dark: #1B2537;
            --dark2: #2D3A4E;
            --text: #3D4C5E;
            --muted: #7A8FA6;
            --border: #E8ECF1;
            --bg: #F7F9FC;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            overflow-x: hidden;
        }

        /* ── Navbar ── */
        #navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 18px 0;
            transition: all .35s ease;
        }

        #navbar.scrolled {
            background: rgba(255, 255, 255, .97);
            backdrop-filter: blur(12px);
            padding: 12px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, .08);
        }

        #navbar .navbar-brand img {
            height: 38px;
        }

        #navbar .nav-link {
            color: rgba(255, 255, 255, .9);
            font-weight: 500;
            font-size: .88rem;
            padding: 6px 14px;
            border-radius: 6px;
            transition: all .2s;
        }

        #navbar.scrolled .nav-link {
            color: var(--text);
        }

        #navbar .nav-link:hover {
            color: var(--brand) !important;
        }

        #navbar .btn-login {
            background: var(--brand);
            color: #fff !important;
            font-weight: 600;
            padding: 8px 22px;
            border-radius: 50px;
        }

        #navbar.scrolled .btn-login {
            background: var(--brand);
        }

        #navbar .btn-login:hover {
            background: var(--brand-dk) !important;
        }

        #navbar .navbar-toggler {
            border: none;
        }

        #navbar .navbar-toggler-icon {
            filter: invert(1);
        }

        #navbar.scrolled .navbar-toggler-icon {
            filter: none;
        }

        /* ── Hero ── */
        #hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #1B2537 0%, #2D4A6E 50%, #1B3A5C 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        #hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .2;
        }

        .hero-blob-1 {
            width: 500px;
            height: 500px;
            background: var(--brand);
            top: -100px;
            right: -100px;
        }

        .hero-blob-2 {
            width: 350px;
            height: 350px;
            background: #3B82F6;
            bottom: -50px;
            left: -80px;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(232, 98, 42, .2);
            border: 1px solid rgba(232, 98, 42, .4);
            color: #FFB494;
            font-size: .78rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 50px;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.6rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 20px;
        }

        .hero-title span {
            color: var(--brand);
        }

        .hero-sub {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, .7);
            line-height: 1.7;
            margin-bottom: 36px;
            max-width: 520px;
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background: var(--brand);
            color: #fff;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 50px;
            font-size: .95rem;
            border: none;
            transition: all .25s;
            box-shadow: 0 8px 24px rgba(232, 98, 42, .4);
        }

        .btn-hero-primary:hover {
            background: var(--brand-dk);
            transform: translateY(-2px);
            color: #fff;
        }

        .btn-hero-outline {
            background: rgba(255, 255, 255, .1);
            color: #fff;
            font-weight: 600;
            padding: 14px 32px;
            border-radius: 50px;
            font-size: .95rem;
            border: 1px solid rgba(255, 255, 255, .3);
            transition: all .25s;
            backdrop-filter: blur(8px);
        }

        .btn-hero-outline:hover {
            background: rgba(255, 255, 255, .2);
            transform: translateY(-2px);
            color: #fff;
        }

        /* Hero stats */
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: rgba(255, 255, 255, .1);
            border-radius: 16px;
            overflow: hidden;
            margin-top: 48px;
        }

        .hero-stat {
            background: rgba(255, 255, 255, .05);
            padding: 20px 24px;
            backdrop-filter: blur(8px);
        }

        .hero-stat .stat-val {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
        }

        .hero-stat .stat-lbl {
            font-size: .73rem;
            color: rgba(255, 255, 255, .55);
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* Hero image side */
        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-card-float {
            background: rgba(255, 255, 255, .08);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 20px;
            padding: 28px 32px;
            color: #fff;
            width: 100%;
            max-width: 380px;
        }

        .hero-card-float .fc-label {
            font-size: .72rem;
            color: rgba(255, 255, 255, .5);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 6px;
        }

        .hero-card-float .fc-amount {
            font-size: 2rem;
            font-weight: 800;
        }

        .fc-badge-row {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .fc-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: .72rem;
            font-weight: 600;
        }

        .fc-badge.green {
            background: rgba(34, 197, 94, .2);
            color: #4ADE80;
        }

        .fc-badge.blue {
            background: rgba(59, 130, 246, .2);
            color: #60A5FA;
        }

        .fc-badge.orange {
            background: rgba(232, 98, 42, .25);
            color: #FFB494;
        }

        .fc-divider {
            border-color: rgba(255, 255, 255, .1);
            margin: 18px 0;
        }

        .fc-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .fc-row .fc-name {
            font-size: .82rem;
            color: rgba(255, 255, 255, .7);
        }

        .fc-row .fc-val {
            font-size: .88rem;
            font-weight: 700;
        }

        /* ── Section common ── */
        .section-label {
            font-size: .72rem;
            font-weight: 700;
            color: var(--brand);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 14px;
        }

        .section-sub {
            font-size: .95rem;
            color: var(--muted);
            line-height: 1.7;
            max-width: 560px;
        }

        /* ── Program Cards ── */
        .program-card {
            background: #fff;
            border-radius: 20px;
            padding: 32px 28px;
            border: 1.5px solid var(--border);
            transition: all .3s;
            height: 100%;
        }

        .program-card:hover {
            border-color: var(--brand);
            transform: translateY(-6px);
            box-shadow: 0 20px 48px rgba(232, 98, 42, .12);
        }

        .prog-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }

        .prog-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .prog-desc {
            font-size: .85rem;
            color: var(--muted);
            line-height: 1.65;
        }

        .prog-nishab {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            font-size: .8rem;
            color: var(--text);
            display: flex;
            justify-content: space-between;
        }

        .prog-nishab .lbl {
            color: var(--muted);
        }

        /* ── Kalkulator ── */
        #kalkulator {
            background: var(--bg);
        }

        .calc-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 32px rgba(0, 0, 0, .06);
        }

        .calc-input-group label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .calc-input-group .form-control {
            border-radius: 10px;
            border: 1.5px solid var(--border);
            padding: 10px 14px;
            font-size: .9rem;
        }

        .calc-input-group .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(232, 98, 42, .12);
        }

        .calc-result {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dk) 100%);
            border-radius: 20px;
            padding: 32px;
            color: #fff;
            position: sticky;
            top: 90px;
        }

        .calc-result .cr-label {
            font-size: .72rem;
            opacity: .75;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .calc-result .cr-amount {
            font-size: 2rem;
            font-weight: 800;
            margin: 4px 0 20px;
        }

        .calc-result .cr-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .15);
            font-size: .85rem;
        }

        .calc-result .cr-row:last-child {
            border-bottom: none;
        }

        .calc-result .cr-row .cr-lbl {
            opacity: .75;
        }

        .calc-result .cr-badge {
            padding: 3px 10px;
            border-radius: 50px;
            font-size: .7rem;
            font-weight: 700;
            background: rgba(255, 255, 255, .2);
        }

        /* ── Steps ── */
        .step-number {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--brand-lt);
            color: var(--brand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .step-connector {
            width: 2px;
            background: var(--border);
            flex: 1;
            min-height: 40px;
            margin: 8px auto;
        }

        /* ── Testimonial / CTA ── */
        #cta {
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark2) 100%);
        }

        /* ── Footer ── */
        footer {
            background: #111827;
        }

        footer .footer-brand {
            font-size: 1.1rem;
            font-weight: 800;
            color: #fff;
        }

        footer .footer-link {
            color: rgba(255, 255, 255, .5);
            font-size: .83rem;
            transition: color .2s;
            display: block;
            margin-bottom: 8px;
        }

        footer .footer-link:hover {
            color: var(--brand);
        }

        footer .footer-label {
            font-size: .72rem;
            font-weight: 700;
            color: rgba(255, 255, 255, .4);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 14px;
        }

        footer .footer-divider {
            border-color: rgba(255, 255, 255, .08);
        }

        footer .footer-bottom {
            font-size: .75rem;
            color: rgba(255, 255, 255, .35);
        }

        .social-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            color: rgba(255, 255, 255, .6);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            transition: all .2s;
        }

        .social-btn:hover {
            background: var(--brand);
            color: #fff;
        }

        /* ── Divider wave ── */
        .wave-divider {
            line-height: 0;
        }

        .wave-divider svg {
            display: block;
            width: 100%;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .hero-stats {
                grid-template-columns: repeat(3, 1fr);
            }

            .hero-stat {
                padding: 14px 12px;
            }

            .hero-stat .stat-val {
                font-size: 1.1rem;
            }

            .calc-card {
                padding: 24px 20px;
            }
        }

        @media (max-width: 576px) {
            .hero-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- ═══════════════════════════════ NAVBAR ═══════════════════════════════ -->
    <nav id="navbar">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <a href="<?= base_url('/') ?>" class="navbar-brand text-decoration-none">
                    <img src="<?= base_url('assets/img/logo/favicon.png') ?>" alt="Logo LAZISMU"
                        style="height:50px;filter:brightness(0) invert(1);" id="navLogo">
                </a>

                <!-- Desktop Nav -->
                <div class="d-none d-md-flex align-items-center gap-1">
                    <a href="#program" class="nav-link">Program</a>
                    <a href="#kalkulator" class="nav-link">Kalkulator Zakat</a>
                    <a href="#cara-donasi" class="nav-link">Cara Berdonasi</a>
                    <a href="<?= base_url('login') ?>" class="nav-link btn-login ms-3">
                        <i class="fas fa-sign-in-alt me-1"></i>Masuk Sistem
                    </a>
                </div>

                <!-- Mobile toggle -->
                <button class="d-md-none btn" data-bs-toggle="collapse" data-bs-target="#mobileMenu"
                    style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;padding:7px 12px;border-radius:8px;">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div class="collapse mt-3" id="mobileMenu">
                <div class="d-flex flex-column gap-1 pb-3" style="background:rgba(255,255,255,.05);backdrop-filter:blur(12px);border-radius:12px;padding:16px;">
                    <a href="#program" class="nav-link">Program</a>
                    <a href="#kalkulator" class="nav-link">Kalkulator Zakat</a>
                    <a href="#cara-donasi" class="nav-link">Cara Berdonasi</a>
                    <a href="<?= base_url('login') ?>" class="nav-link btn-login text-center mt-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Masuk Sistem
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ═══════════════════════════════ HERO ═══════════════════════════════ -->
    <section id="hero">
        <div class="hero-blob hero-blob-1"></div>
        <div class="hero-blob hero-blob-2"></div>

        <div class="container py-5">
            <div class="row justify-content-center" style="padding-top:80px;padding-bottom:60px;">
                <div class="col-lg-8 text-center hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-mosque" style="font-size:.7rem;"></i>
                        LAZISMU Universitas Muhammadiyah Surakarta
                    </div>
                    <h1 class="hero-title">
                        Bersama Wujudkan <span>Kebaikan</span> yang Berkelanjutan
                    </h1>
                    <p class="hero-sub mx-auto">
                        Platform pengelolaan Zakat, Infak &amp; Sedekah LAZISMU UMS. Transparan, terpercaya, dan memberikan dampak nyata bagi mustahik di lingkungan UMS.
                    </p>
                    <div class="hero-actions justify-content-center">
                        <a href="#kalkulator" class="btn-hero-primary">
                            <i class="fas fa-calculator me-2"></i>Hitung Zakat Saya
                        </a>
                        <a href="#program" class="btn-hero-outline">
                            Lihat Program <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Wave -->
    <div class="wave-divider" style="background:var(--dark);margin-top:-1px;">
        <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 L0,30 Q360,0 720,30 Q1080,60 1440,30 L1440,60 Z" fill="#F7F9FC" />
        </svg>
    </div>

    <!-- ═══════════════════════════════ PROGRAM ═══════════════════════════════ -->
    <section id="program" class="py-5" style="background:var(--bg);">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-label">Program Unggulan</div>
                <h2 class="section-title">Saluran Kebaikan Anda</h2>
                <p class="section-sub mx-auto">
                    LAZISMU UMS mengelola berbagai jenis dana sesuai prinsip syariah PSAK 109 untuk memastikan amanah terjaga dengan baik.
                </p>
            </div>

            <div class="row g-4">
                <!-- Zakat -->
                <div class="col-sm-6 col-lg-3">
                    <div class="program-card">
                        <div class="prog-icon" style="background:#FFF0EA;color:#E8622A;">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="prog-title">Zakat Maal</div>
                        <p class="prog-desc">Zakat harta wajib bagi Muslim yang hartanya telah mencapai nishab dan haul. Disalurkan kepada 8 asnaf yang berhak.</p>
                        <div class="prog-nishab">
                            <span class="lbl">Nishab (85g emas)</span>
                            <span class="fw-semibold" style="color:var(--brand);">2,5%</span>
                        </div>
                    </div>
                </div>

                <!-- Infak Terikat -->
                <div class="col-sm-6 col-lg-3">
                    <div class="program-card">
                        <div class="prog-icon" style="background:#EFF6FF;color:#3B82F6;">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="prog-title">Infak Terikat</div>
                        <p class="prog-desc">Infak yang diberikan muzaki dengan peruntukan khusus, seperti beasiswa, pemberdayaan, atau program kemanusiaan tertentu.</p>
                        <div class="prog-nishab">
                            <span class="lbl">Pemanfaatan</span>
                            <span class="fw-semibold" style="color:#3B82F6;">Sesuai Niat</span>
                        </div>
                    </div>
                </div>

                <!-- Infak Sedekah -->
                <div class="col-sm-6 col-lg-3">
                    <div class="program-card">
                        <div class="prog-icon" style="background:#F0FDF4;color:#22C55E;">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="prog-title">Infak &amp; Sedekah</div>
                        <p class="prog-desc">Infak tidak terikat yang disalurkan secara fleksibel sesuai prioritas dan kebutuhan mustahik yang paling mendesak.</p>
                        <div class="prog-nishab">
                            <span class="lbl">Nominal</span>
                            <span class="fw-semibold" style="color:#22C55E;">Bebas</span>
                        </div>
                    </div>
                </div>

                <!-- Amil -->
                <div class="col-sm-6 col-lg-3">
                    <div class="program-card">
                        <div class="prog-icon" style="background:#FAF5FF;color:#8B5CF6;">
                            <i class="fas fa-users-gear"></i>
                        </div>
                        <div class="prog-title">Dana Amil</div>
                        <p class="prog-desc">Operasional dan hak amil sebesar 12,5% dari dana zakat untuk memastikan pengelolaan yang profesional dan amanah.</p>
                        <div class="prog-nishab">
                            <span class="lbl">Porsi Amil</span>
                            <span class="fw-semibold" style="color:#8B5CF6;">12,5%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════ KALKULATOR ═══════════════════════════════ -->
    <section id="kalkulator" class="py-5">
        <div class="container py-4">
            <div class="row g-5 align-items-start">
                <!-- Form -->
                <div class="col-lg-7">
                    <div class="section-label">Kalkulator Zakat</div>
                    <h2 class="section-title">Hitung Kewajiban Zakat Anda</h2>
                    <p class="section-sub mb-4">Masukkan penghasilan dan pengeluaran bulanan Anda. Nishab dihitung berdasarkan harga emas 85g saat ini ≈ Rp 8.000.000/bulan.</p>

                    <div class="calc-card">
                        <div class="row g-3">
                            <div class="col-md-6 calc-input-group">
                                <label>Penghasilan / Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" style="font-size:.8rem;border-radius:10px 0 0 10px;">Rp</span>
                                    <input type="text" id="c_gaji" class="form-control calc-num" placeholder="5.000.000"
                                        style="border-radius:0 10px 10px 0;">
                                </div>
                            </div>
                            <div class="col-md-6 calc-input-group">
                                <label>Tunjangan &amp; Bonus</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" style="font-size:.8rem;border-radius:10px 0 0 10px;">Rp</span>
                                    <input type="text" id="c_tunjangan" class="form-control calc-num" placeholder="0"
                                        style="border-radius:0 10px 10px 0;">
                                </div>
                            </div>
                            <div class="col-md-6 calc-input-group">
                                <label>Penghasilan Lain-lain</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" style="font-size:.8rem;border-radius:10px 0 0 10px;">Rp</span>
                                    <input type="text" id="c_lain" class="form-control calc-num" placeholder="0"
                                        style="border-radius:0 10px 10px 0;">
                                </div>
                            </div>
                            <div class="col-md-6 calc-input-group">
                                <label>Pengeluaran Pokok / Kebutuhan Dasar</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light" style="font-size:.8rem;border-radius:10px 0 0 10px;">Rp</span>
                                    <input type="text" id="c_kebutuhan" class="form-control calc-num" placeholder="0"
                                        style="border-radius:0 10px 10px 0;">
                                </div>
                            </div>

                            <div class="col-12">
                                <div style="background:var(--bg);border-radius:12px;padding:16px 20px;font-size:.83rem;color:var(--muted);line-height:1.6;">
                                    <i class="fas fa-info-circle me-1" style="color:var(--brand);"></i>
                                    Zakat profesi dihitung dari <strong>penghasilan bersih</strong> (total penghasilan dikurangi kebutuhan pokok). Jika penghasilan bersih ≥ nishab, wajib zakat <strong>2,5%</strong>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Result -->
                <div class="col-lg-5">
                    <div class="calc-result">
                        <div class="cr-label">Zakat Yang Wajib Dibayar</div>
                        <div class="cr-amount" id="cr_zakat">Rp 0</div>

                        <div class="cr-row">
                            <span class="cr-lbl">Total Penghasilan</span>
                            <span id="cr_penghasilan" class="fw-semibold">Rp 0</span>
                        </div>
                        <div class="cr-row">
                            <span class="cr-lbl">Pengeluaran Pokok</span>
                            <span id="cr_pengeluaran" class="fw-semibold">Rp 0</span>
                        </div>
                        <div class="cr-row">
                            <span class="cr-lbl">Penghasilan Bersih</span>
                            <span id="cr_bersih" class="fw-semibold">Rp 0</span>
                        </div>
                        <div class="cr-row">
                            <span class="cr-lbl">Nishab (85g emas)</span>
                            <span class="fw-semibold">Rp 8.000.000</span>
                        </div>
                        <div class="cr-row">
                            <span class="cr-lbl">Status</span>
                            <span id="cr_status" class="cr-badge">Belum Wajib</span>
                        </div>

                        <a href="<?= base_url('login') ?>" class="btn w-100 mt-4 fw-bold"
                            style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:10px;padding:12px;">
                            <i class="fas fa-paper-plane me-2"></i>Bayar Zakat via Sistem
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════ CARA DONASI ═══════════════════════════════ -->
    <section id="cara-donasi" style="background:var(--bg);" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-label">Cara Berdonasi</div>
                <h2 class="section-title">Mudah &amp; Transparan</h2>
                <p class="section-sub mx-auto">Tiga langkah sederhana untuk menyalurkan kebaikan Anda melalui LAZISMU UMS.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <?php
                $steps = [
                    [
                        'icon' => 'fa-calculator',
                        'color' => '#E8622A',
                        'bg' => '#FFF0EA',
                        'no' => '01',
                        'title' => 'Hitung Kewajiban',
                        'desc' => 'Gunakan kalkulator zakat di atas untuk mengetahui jumlah zakat, infak, atau sedekah yang perlu Anda salurkan.'
                    ],
                    [
                        'icon' => 'fa-building-columns',
                        'color' => '#3B82F6',
                        'bg' => '#EFF6FF',
                        'no' => '02',
                        'title' => 'Transfer ke Rekening',
                        'desc' => 'Kirimkan donasi ke rekening LAZISMU UMS. Tersedia di Bank Jateng Syariah atau BMT Amanah Ummah.'
                    ],
                    [
                        'icon' => 'fa-file-invoice',
                        'color' => '#22C55E',
                        'bg' => '#F0FDF4',
                        'no' => '03',
                        'title' => 'Konfirmasi &amp; Laporan',
                        'desc' => 'Hubungi amil untuk konfirmasi. Laporan penyaluran dapat diakses secara transparan melalui sistem ini.'
                    ],
                ];
                foreach ($steps as $i => $s):
                ?>
                    <div class="col-md-4">
                        <div class="text-center px-3">
                            <div style="width:80px;height:80px;border-radius:24px;background:<?= $s['bg'] ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem;color:<?= $s['color'] ?>;">
                                <i class="fas <?= $s['icon'] ?>"></i>
                            </div>
                            <div style="font-size:.7rem;font-weight:700;color:var(--muted);letter-spacing:.1em;margin-bottom:8px;">LANGKAH <?= $s['no'] ?></div>
                            <h5 style="font-weight:700;color:var(--dark);margin-bottom:10px;"><?= $s['title'] ?></h5>
                            <p style="font-size:.87rem;color:var(--muted);line-height:1.65;"><?= $s['desc'] ?></p>
                        </div>
                        <?php if ($i < count($steps) - 1): ?>
                            <div class="d-none d-md-block" style="position:absolute;"></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Rekening info -->
            <div class="row g-3 mt-4 justify-content-center">
                <div class="col-md-5">
                    <div class="d-flex align-items-center gap-3 p-4 bg-white rounded-3 border" style="border-color:var(--border)!important;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#EFF6FF;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#3B82F6;font-size:1.2rem;">
                            <i class="fas fa-building-columns"></i>
                        </div>
                        <div>
                            <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">Bank Jateng Syariah</div>
                            <div style="font-weight:700;font-size:1rem;color:var(--dark);">LAZISMU UMS</div>
                            <div style="font-size:.85rem;color:var(--text);">a.n. LAZISMU Universitas Muhammadiyah Surakarta</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="d-flex align-items-center gap-3 p-4 bg-white rounded-3 border" style="border-color:var(--border)!important;">
                        <div style="width:48px;height:48px;border-radius:14px;background:#FFF0EA;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--brand);font-size:1.2rem;">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <div>
                            <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">BMT Amanah Ummah</div>
                            <div style="font-weight:700;font-size:1rem;color:var(--dark);">LAZISMU UMS</div>
                            <div style="font-size:.85rem;color:var(--text);">a.n. LAZISMU Universitas Muhammadiyah Surakarta</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════ CTA ═══════════════════════════════ -->
    <section id="cta" class="py-5">
        <div class="container py-4 text-center">
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px;">
                Untuk Amil &amp; Pengelola
            </div>
            <h2 style="font-size:clamp(1.6rem,3vw,2.4rem);font-weight:800;color:#fff;margin-bottom:14px;">
                Kelola Dana ZIS Secara Profesional
            </h2>
            <p style="font-size:.95rem;color:rgba(255,255,255,.6);max-width:520px;margin:0 auto 32px;line-height:1.7;">
                Sistem informasi keuangan LAZISMU UMS berbasis PSAK 109. Laporan otomatis, jurnal terintegrasi, dan transparansi penuh.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= base_url('login') ?>" class="btn-hero-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Sistem
                </a>
                <a href="#program" class="btn-hero-outline">
                    Pelajari Lebih Lanjut
                </a>
            </div>

            <!-- Feature list -->
            <div class="row g-3 mt-5 justify-content-center">
                <?php
                $feats = [
                    ['fas fa-chart-line',    'Laporan Keuangan PSAK 109'],
                    ['fas fa-book-open',     'Jurnal Otomatis Terintegrasi'],
                    ['fas fa-shield-halved', 'Multi-level Akses Pengguna'],
                    ['fas fa-file-pdf',      'Export Laporan Siap Cetak'],
                ];
                foreach ($feats as $f):
                ?>
                    <div class="col-6 col-md-3">
                        <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:20px 16px;">
                            <i class="fas <?= $f[0] ?>" style="font-size:1.4rem;color:var(--brand);margin-bottom:10px;display:block;"></i>
                            <div style="font-size:.82rem;color:rgba(255,255,255,.75);font-weight:500;line-height:1.4;"><?= $f[1] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════ FOOTER ═══════════════════════════════ -->
    <footer class="py-5">
        <div class="container">
            <div class="row g-4 pb-4">
                <div class="col-lg-4">
                    <div class="mb-16" style="margin-bottom:14px;">
                        <img src="<?= base_url('assets/img/logo/favicon.png') ?>" alt="Logo" style="height:48px;filter:brightness(0) invert(1);">
                    </div>
                    <p style="font-size:.82rem;color:rgba(255,255,255,.4);line-height:1.7;margin-bottom:20px;">
                        Lembaga Amil Zakat Infaq dan Shadaqah Muhammadiyah Universitas Muhammadiyah Surakarta. Mengelola dana ZIS secara transparan dan profesional.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="social-btn"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>

                <div class="col-6 col-lg-2 offset-lg-1">
                    <div class="footer-label">Navigasi</div>
                    <a href="#program" class="footer-link">Program ZIS</a>
                    <a href="#kalkulator" class="footer-link">Kalkulator Zakat</a>
                    <a href="#cara-donasi" class="footer-link">Cara Berdonasi</a>
                    <a href="<?= base_url('login') ?>" class="footer-link">Masuk Sistem</a>
                </div>

                <div class="col-6 col-lg-2">
                    <div class="footer-label">Program</div>
                    <a href="#" class="footer-link">Zakat Maal</a>
                    <a href="#" class="footer-link">Zakat Profesi</a>
                    <a href="#" class="footer-link">Infak &amp; Sedekah</a>
                    <a href="#" class="footer-link">Beasiswa</a>
                </div>

                <div class="col-lg-3">
                    <div class="footer-label">Kontak</div>
                    <div style="font-size:.82rem;color:rgba(255,255,255,.4);line-height:2;">
                        <div><i class="fas fa-location-dot me-2" style="color:var(--brand);width:16px;"></i>Universitas Muhammadiyah Surakarta</div>
                        <div><i class="fas fa-map-marker-alt me-2" style="color:var(--brand);width:16px;"></i>Jl. A. Yani No.157, Surakarta</div>
                        <div><i class="fas fa-envelope me-2" style="color:var(--brand);width:16px;"></i>lazismu@ums.ac.id</div>
                        <div><i class="fas fa-phone me-2" style="color:var(--brand);width:16px;"></i>(0271) 717417</div>
                    </div>
                </div>
            </div>

            <hr class="footer-divider">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="footer-bottom">&copy; <?= date('Y') ?> LAZISMU Universitas Muhammadiyah Surakarta. All rights reserved.</div>
                <div class="footer-bottom">Dibuat dengan <i class="fas fa-heart" style="color:var(--brand);"></i> untuk umat</div>
            </div>
        </div>
    </footer>
    <?php
    $total_penerimaan = $totalPenerimaan ?? 0;
    $total_penyaluran = $totalPenyaluran ?? 0;
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Navbar scroll effect ──────────────────────────────────
        const navbar = document.getElementById('navbar');
        const navLogo = document.getElementById('navLogo');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 60) {
                navbar.classList.add('scrolled');
                navLogo.style.filter = 'none';
            } else {
                navbar.classList.remove('scrolled');
                navLogo.style.filter = 'brightness(0) invert(1)';
            }
        });

        // ── Counter animation ─────────────────────────────────────
        function animateCounter(el, target, prefix, suffix, duration) {
            const start = performance.now();
            const update = (time) => {
                const elapsed = time - start;
                const progress = Math.min(elapsed / duration, 1);
                const ease = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(ease * target);
                el.textContent = prefix + formatShort(current) + suffix;
                if (progress < 1) requestAnimationFrame(update);
            };
            requestAnimationFrame(update);
        }

        function formatShort(n) {
            if (n >= 1e9) return (n / 1e9).toFixed(1).replace('.0', '') + ' M';
            if (n >= 1e6) return (n / 1e6).toFixed(0) + ' Jt';
            if (n >= 1e3) return (n / 1e3).toFixed(0) + ' Rb';
            return n.toLocaleString('id');
        }

        const penEl = document.getElementById('ctr-penerimaan');
        const pslEl = document.getElementById('ctr-penyaluran');

        const pen = <?= (int)$total_penerimaan ?>;
        const psl = <?= (int)$total_penyaluran ?>;

        const obs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    animateCounter(penEl, pen, 'Rp ', '', 1800);
                    animateCounter(pslEl, psl, 'Rp ', '', 1800);
                    obs.disconnect();
                }
            });
        }, {
            threshold: 0.3
        });
        obs.observe(penEl);

        // ── Kalkulator Zakat ──────────────────────────────────────
        const NISHAB = 8000000;
        const TARIF = 0.025;
        const formatter = new Intl.NumberFormat('id-ID');

        function parseNum(id) {
            return parseInt((document.getElementById(id).value || '0').replace(/\D/g, ''), 10) || 0;
        }

        function formatRp(n) {
            return 'Rp ' + formatter.format(Math.round(n));
        }

        function hitungZakat() {
            const gaji = parseNum('c_gaji');
            const tunjangan = parseNum('c_tunjangan');
            const lain = parseNum('c_lain');
            const kebutuhan = parseNum('c_kebutuhan');

            const penghasilan = gaji + tunjangan + lain;
            const bersih = Math.max(0, penghasilan - kebutuhan);
            const zakat = bersih >= NISHAB ? bersih * TARIF : 0;
            const wajib = bersih >= NISHAB;

            document.getElementById('cr_penghasilan').textContent = formatRp(penghasilan);
            document.getElementById('cr_pengeluaran').textContent = formatRp(kebutuhan);
            document.getElementById('cr_bersih').textContent = formatRp(bersih);
            document.getElementById('cr_zakat').textContent = formatRp(zakat);

            const badge = document.getElementById('cr_status');
            if (wajib) {
                badge.textContent = 'Wajib Zakat';
                badge.style.background = 'rgba(74,222,128,.25)';
                badge.style.color = '#4ADE80';
            } else {
                badge.textContent = bersih > 0 ? 'Belum Nishab' : 'Belum Wajib';
                badge.style.background = 'rgba(255,255,255,.2)';
                badge.style.color = 'rgba(255,255,255,.8)';
            }
        }

        // Format input as thousand separator
        document.querySelectorAll('.calc-num').forEach(input => {
            input.addEventListener('input', function() {
                const raw = this.value.replace(/\D/g, '');
                this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
                hitungZakat();
            });
        });
    </script>
</body>

</html>

<?php
function shortNum(float $n): string
{
    if ($n >= 1_000_000_000) return number_format($n / 1_000_000_000, 1) . ' M';
    if ($n >= 1_000_000)     return number_format($n / 1_000_000, 0)     . ' Jt';
    if ($n >= 1_000)         return number_format($n / 1_000, 0)         . ' Rb';
    return number_format($n, 0, ',', '.');
}
?>