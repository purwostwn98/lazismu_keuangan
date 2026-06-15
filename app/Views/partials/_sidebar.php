<?php
$uri         = service('uri');
$segment     = $uri->getSegment(1) ?? 'dashboard';
$sub1        = $uri->getSegment(2) ?? '';
$currentPath = $sub1 !== '' ? "{$segment}/{$sub1}" : $segment;

function isActive(string $seg, string $match): string
{
    return $seg === $match ? ' active' : '';
}
function isOpen(string $seg, array $matches): string
{
    return in_array($seg, $matches) ? ' show' : '';
}
?>

<nav id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <img src="<?= base_url('assets/img/logo/logo.png') ?>" alt="Si-LazisMu UMS" class="brand-logo">
        <div class="brand-text">
            <div class="brand-name">Si-LazisMu</div>
            <div class="brand-sub">UMS · Keuangan</div>
        </div>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-nav nav flex-column">

        <!-- Dashboard -->
        <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link<?= isActive($segment, 'dashboard') ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>

        <!-- PENGHIMPUNAN -->
        <li class="sidebar-section-label">Penghimpunan</li>

        <li class="nav-item">
            <a href="#menu-penerimaan" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'penerimaan') ?>"
                aria-expanded="<?= in_array($segment, ['penerimaan']) ? 'true' : 'false' ?>">
                <i class="fas fa-hand-holding-dollar"></i> Penerimaan ZIS
            </a>
            <div class="collapse<?= isOpen($segment, ['penerimaan']) ?>" id="menu-penerimaan">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('penerimaan/input') ?>" class="nav-link<?= isActive($currentPath, 'penerimaan/input') ?>">
                            Input Penerimaan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('penerimaan') ?>" class="nav-link<?= isActive($currentPath, 'penerimaan') ?>">
                            Daftar Penerimaan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('penerimaan/laporan') ?>" class="nav-link<?= isActive($currentPath, 'penerimaan/laporan') ?>">
                            Laporan Penghimpunan
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- PENYALURAN -->
        <li class="sidebar-section-label">Penyaluran</li>

        <li class="nav-item">
            <a href="#menu-penyaluran" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'penyaluran') ?>"
                aria-expanded="<?= in_array($segment, ['penyaluran']) ? 'true' : 'false' ?>">
                <i class="fas fa-hands-holding-circle"></i> Penyaluran Dana
            </a>
            <div class="collapse<?= isOpen($segment, ['penyaluran']) ?>" id="menu-penyaluran">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('penyaluran/input') ?>" class="nav-link<?= isActive($currentPath, 'penyaluran/input') ?>">
                            Input Penyaluran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('penyaluran/daftar') ?>" class="nav-link<?= isActive($currentPath, 'penyaluran/daftar') ?>">
                            Daftar Penyaluran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('penyaluran/program') ?>" class="nav-link<?= isActive($currentPath, 'penyaluran/program') ?>">
                            Program Penyaluran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('penyaluran/antrian') ?>" class="nav-link<?= isActive($currentPath, 'penyaluran/antrian') ?>">
                            Antrian Penyaluran
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- OPERASIONAL -->
        <li class="sidebar-section-label">Operasional</li>

        <li class="nav-item">
            <a href="#menu-biaya" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'biaya') ?>"
                aria-expanded="<?= in_array($segment, ['biaya']) ? 'true' : 'false' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Biaya Operasional
            </a>
            <div class="collapse<?= isOpen($segment, ['biaya']) ?>" id="menu-biaya">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('biaya/input') ?>" class="nav-link<?= isActive($currentPath, 'biaya/input') ?>">
                            Input Biaya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('biaya') ?>" class="nav-link<?= isActive($currentPath, 'biaya') ?>">
                            Daftar Biaya
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- AKUNTANSI -->
        <li class="sidebar-section-label">Akuntansi</li>

        <li class="nav-item">
            <a href="#menu-jurnal" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'jurnal') ?>"
                aria-expanded="<?= in_array($segment, ['jurnal']) ? 'true' : 'false' ?>">
                <i class="fas fa-book-open"></i> Jurnal
            </a>
            <div class="collapse<?= isOpen($segment, ['jurnal']) ?>" id="menu-jurnal">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('jurnal/input') ?>" class="nav-link<?= isActive($currentPath, 'jurnal/input') ?>">
                            Input Jurnal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('jurnal') ?>" class="nav-link">
                            Buku Jurnal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('buku-besar') ?>" class="nav-link<?= isActive($segment, 'buku-besar') ?>">
                            Buku Besar
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a href="#menu-piutang" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'piutang') ?>"
                aria-expanded="<?= in_array($segment, ['piutang']) ? 'true' : 'false' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Piutang
            </a>
            <div class="collapse<?= isOpen($segment, ['piutang']) ?>" id="menu-piutang">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('piutang/input') ?>" class="nav-link<?= isActive($currentPath, 'piutang/input') ?>">
                            Input Piutang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('piutang') ?>" class="nav-link<?= isActive($currentPath, 'piutang') ?>">
                            Daftar Piutang
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a href="#menu-persediaan" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'persediaan') ?>"
                aria-expanded="<?= in_array($segment, ['persediaan']) ? 'true' : 'false' ?>">
                <i class="fas fa-boxes-stacked"></i> Persediaan
            </a>
            <div class="collapse<?= isOpen($segment, ['persediaan']) ?>" id="menu-persediaan">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('persediaan/input') ?>" class="nav-link<?= isActive($currentPath, 'persediaan/input') ?>">
                            Tambah Barang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('persediaan') ?>" class="nav-link<?= isActive($currentPath, 'persediaan') ?>">
                            Daftar &amp; Stok
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- MUTASI -->
        <li class="nav-item">
            <a href="#menu-mutasi" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'mutasi') ?>"
                aria-expanded="<?= in_array($segment, ['mutasi']) ? 'true' : 'false' ?>">
                <i class="fas fa-right-left"></i> Mutasi Rekening
            </a>
            <div class="collapse<?= isOpen($segment, ['mutasi']) ?>" id="menu-mutasi">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('mutasi/input') ?>" class="nav-link<?= isActive($currentPath, 'mutasi/input') ?>">
                            Input Mutasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('mutasi') ?>" class="nav-link<?= isActive($currentPath, 'mutasi') ?>">
                            Daftar Mutasi
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- LAPORAN -->
        <li class="sidebar-section-label">Laporan Keuangan</li>

        <li class="nav-item">
            <a href="#menu-laporan" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'laporan') ?>"
                aria-expanded="<?= in_array($segment, ['laporan']) ? 'true' : 'false' ?>">
                <i class="fas fa-chart-bar"></i> Laporan Keuangan
            </a>
            <div class="collapse<?= isOpen($segment, ['laporan']) ?>" id="menu-laporan">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('laporan/posisi-keuangan') ?>" class="nav-link<?= isActive($currentPath, 'laporan/posisi-keuangan') ?>">
                            Posisi Keuangan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('laporan/perubahan-dana') ?>" class="nav-link<?= isActive($currentPath, 'laporan/perubahan-dana') ?>">
                            Perubahan Dana
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('laporan/arus-kas') ?>" class="nav-link<?= isActive($currentPath, 'laporan/arus-kas') ?>">
                            Arus Kas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('laporan/rab') ?>" class="nav-link<?= isActive($currentPath, 'laporan/rab') ?>">
                            RAB (Realisasi)
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- MASTER DATA -->
        <li class="sidebar-section-label">Master Data</li>

        <li class="nav-item">
            <a href="#menu-master" data-bs-toggle="collapse" class="nav-link<?= isActive($segment, 'master') ?>"
                aria-expanded="<?= $segment === 'master' ? 'true' : 'false' ?>">
                <i class="fas fa-database"></i> Master Data
            </a>
            <div class="collapse<?= isOpen($segment, ['master']) ?>" id="menu-master">
                <ul class="sidebar-submenu nav flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('master/akun') ?>" class="nav-link<?= isActive($currentPath, 'master/akun') ?>">
                            Bagan Akun (CoA)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('master/donatur') ?>" class="nav-link<?= isActive($currentPath, 'master/donatur') ?>">
                            Donatur / Muzakki
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('master/penerima') ?>" class="nav-link<?= isActive($currentPath, 'master/penerima') ?>">
                            Penerima Manfaat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('master/rekening') ?>" class="nav-link<?= isActive($currentPath, 'master/rekening') ?>">
                            Rekening Bank
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('master/aset') ?>" class="nav-link<?= isActive($currentPath, 'master/aset') ?>">
                            Aset Tetap
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('master/periode') ?>" class="nav-link<?= isActive($currentPath, 'master/periode') ?>">
                            Periode
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('master/saldo-awal') ?>" class="nav-link<?= isActive($currentPath, 'master/saldo-awal') ?>">
                            Saldo Dana Awal
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- PENGATURAN -->
        <li class="sidebar-section-label">Sistem</li>

        <li class="nav-item">
            <a href="<?= base_url('pengguna') ?>" class="nav-link<?= isActive($segment, 'pengguna') ?>">
                <i class="fas fa-users-cog"></i> Pengguna
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('profil') ?>" class="nav-link<?= isActive($segment, 'profil') ?>">
                <i class="fas fa-user-circle"></i> Profil Lembaga
            </a>
        </li>

    </ul>

    <!-- User info at bottom -->
    <div class="sidebar-footer">
        <div class="user-info" data-bs-toggle="dropdown" id="sidebarUserDrop">
            <div class="avatar">A</div>
            <div>
                <div class="user-name">Admin</div>
                <div class="user-role">Administrator</div>
            </div>
            <i class="fas fa-ellipsis-v ms-auto" style="color:rgba(255,255,255,.3);font-size:.7rem;"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-dark mb-2" aria-labelledby="sidebarUserDrop" style="width:220px;">
            <li><a class="dropdown-item" href="<?= base_url('profil') ?>"><i class="fas fa-user me-2"></i> Profil Saya</a></li>
            <li><a class="dropdown-item" href="<?= base_url('sandi') ?>"><i class="fas fa-key me-2"></i> Ganti Password</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
        </ul>
    </div>
</nav>