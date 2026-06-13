<header id="topbar">
    <!-- Toggle sidebar -->
    <button id="sidebarToggle" class="sidebar-toggle" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Page title + breadcrumb -->
    <div class="breadcrumb-topbar">
        <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
        <?php if (!empty($breadcrumb)): ?>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('dashboard') ?>" class="text-muted">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <?php foreach ($breadcrumb as $label => $url): ?>
                    <?php if ($url): ?>
                        <li class="breadcrumb-item"><a href="<?= $url ?>" class="text-muted"><?= $label ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?= $label ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Action buttons -->
    <div class="topbar-actions">
        <!-- Notifications -->
        <div class="dropdown">
            <button class="topbar-icon-btn" data-bs-toggle="dropdown" title="Notifikasi">
                <i class="fas fa-bell"></i>
                <span class="topbar-badge"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" style="width:300px;">
                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-semibold" style="font-size:.85rem;">Notifikasi</span>
                    <a href="#" class="text-primary" style="font-size:.75rem;">Tandai Semua Dibaca</a>
                </div>
                <div class="p-3 text-center text-muted" style="font-size:.8rem;">
                    <i class="fas fa-bell-slash d-block mb-1" style="font-size:1.5rem;opacity:.3;"></i>
                    Tidak ada notifikasi
                </div>
            </div>
        </div>

        <!-- Periode aktif -->
        <span class="badge rounded-pill ms-1"
              style="background:rgba(232,98,42,.12);color:#E8622A;font-size:.72rem;padding:6px 10px;font-weight:600;">
            <i class="fas fa-calendar-alt me-1"></i>
            <?php
                $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                echo $bulan[date('n')] . ' ' . date('Y');
            ?>
        </span>

        <!-- User dropdown -->
        <div class="dropdown">
            <div class="topbar-user" data-bs-toggle="dropdown">
                <div class="topbar-avatar">A</div>
                <span class="topbar-user-name d-none d-md-inline">Admin</span>
                <i class="fas fa-chevron-down ms-1" style="font-size:.65rem;color:#A0AEBA;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:180px;">
                <li>
                    <div class="px-3 py-2 border-bottom">
                        <div class="fw-semibold" style="font-size:.82rem;">Admin</div>
                        <div class="text-muted" style="font-size:.72rem;">Administrator</div>
                    </div>
                </li>
                <li><a class="dropdown-item" href="<?= base_url('profil') ?>"><i class="fas fa-user me-2 text-muted"></i>Profil</a></li>
                <li><a class="dropdown-item" href="<?= base_url('sandi') ?>"><i class="fas fa-key me-2 text-muted"></i>Ganti Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
            </ul>
        </div>
    </div>
</header>
