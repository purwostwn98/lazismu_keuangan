<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> — Si-LazisMu UMS</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo/logo.png') ?>">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <!-- Tom Select -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <style>
        .ts-wrapper.form-select,
        .ts-wrapper.form-select-sm { padding: 0; border: none; }
        .ts-wrapper .ts-control {
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            min-height: 36px;
            font-size: .875rem;
            padding: .375rem .75rem;
            box-shadow: none;
            background: #fff;
        }
        .ts-wrapper.form-select-sm .ts-control {
            min-height: 30px;
            padding: .2rem .5rem;
            font-size: .82rem;
            border-radius: .25rem;
        }
        .ts-wrapper.focus .ts-control,
        .ts-wrapper .ts-control:focus-within {
            border-color: #f5896a;
            box-shadow: 0 0 0 .2rem rgba(232,98,42,.18);
        }
        .ts-dropdown { font-size: .84rem; border-color: #dee2e6; }
        .ts-dropdown .ts-dropdown-content .option { padding: 6px 12px; }
        .ts-dropdown .ts-dropdown-content .option.selected,
        .ts-dropdown .ts-dropdown-content .option:hover { background: #FFF0EA; color: #C4491A; }
        .ts-dropdown .ts-dropdown-content .option.active { background: #E8622A; color: #fff; }
        .ts-wrapper .ts-control .item { color: #3d4c5e; }
        .ts-wrapper .clear-button { color: #999; }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body>

    <!-- Sidebar overlay (mobile) -->
    <div id="sidebar-overlay"></div>

    <div id="app-wrapper">

        <!-- Sidebar -->
        <?= $this->include('partials/_sidebar') ?>

        <!-- Main content -->
        <div id="main-content">

            <!-- Topbar -->
            <?= $this->include('partials/_navbar') ?>

            <!-- Page content -->
            <main class="page-content">
                <?= $this->renderSection('content') ?>
            </main>

            <!-- Footer -->
            <?= $this->include('partials/_footer') ?>

        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Tom Select -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <!-- App JS -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>