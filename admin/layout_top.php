<?php
/**
 * Mellatron Admin — Layout: head y navbar
 * Variables esperadas: $page_title (string), $active_page (string)
 */
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --adm-bg:      #0f1117;
            --adm-surface: #1a1d27;
            --adm-border:  #2d3145;
            --adm-text:    #e8eaf0;
            --adm-muted:   #9aa3b5;
            --adm-gold:    #f5a623;
            --adm-green:   #2ecc71;
            --adm-red:     #e74c3c;
        }
        body { background: var(--adm-bg); color: var(--adm-text); min-height: 100vh; }

        /* Navbar */
        .adm-navbar {
            background: var(--adm-surface);
            border-bottom: 1px solid var(--adm-border);
        }
        .adm-navbar .navbar-brand {
            color: var(--adm-gold) !important;
            font-weight: 700;
        }
        .adm-navbar .nav-link { color: var(--adm-muted); }
        .adm-navbar .nav-link:hover,
        .adm-navbar .nav-link.active { color: var(--adm-gold); }
        .adm-navbar .nav-link.active::after {
            content: '';
            display: block;
            height: 2px;
            background: var(--adm-gold);
            border-radius: 2px;
        }

        /* Cards */
        .adm-card {
            background: var(--adm-surface);
            border: 1px solid var(--adm-border);
            border-radius: 12px;
            padding: 1.5rem;
        }
        .adm-card h6 { color: var(--adm-muted); font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .25rem; }
        .adm-card .stat-value { font-size: 2rem; font-weight: 700; color: var(--adm-text); }
        .adm-card .stat-label { color: var(--adm-muted); font-size: .85rem; }

        /* Form controls oscuros */
        .form-control, .form-select {
            background: var(--adm-bg);
            border-color: var(--adm-border);
            color: var(--adm-text);
        }
        .form-control:focus, .form-select:focus {
            background: var(--adm-bg);
            border-color: var(--adm-gold);
            color: var(--adm-text);
            box-shadow: 0 0 0 .2rem rgba(245,166,35,.2);
        }
        .form-control::placeholder { color: #555a6e; }
        .form-label { color: var(--adm-muted); font-size: .85rem; font-weight: 600; }
        .input-group-text { background: #16192600; border-color: var(--adm-border); color: var(--adm-muted); }

        /* Botones */
        .btn-admin-primary { background: var(--adm-gold); color: #000; font-weight: 700; border: none; }
        .btn-admin-primary:hover { background: #e09515; color: #000; }
        .btn-admin-outline { border-color: var(--adm-border); color: var(--adm-muted); }
        .btn-admin-outline:hover { border-color: var(--adm-gold); color: var(--adm-gold); }

        /* Tabla */
        .adm-table { color: var(--adm-text); }
        .adm-table thead th { color: var(--adm-muted); border-color: var(--adm-border); font-size: .78rem; text-transform: uppercase; background: transparent; }
        .adm-table tbody td { border-color: var(--adm-border); vertical-align: middle; }
        .adm-table tbody tr:hover td { background: rgba(245,166,35,.05); }

        /* Badges de juego */
        .badge-melate     { background: #3498db22; color: #5dade2; border: 1px solid #5dade244; }
        .badge-revancha   { background: #2ecc7122; color: #58d68d; border: 1px solid #58d68d44; }
        .badge-revanchita { background: #9b59b622; color: #bb8fce; border: 1px solid #bb8fce44; }

        /* Nav tabs oscuros */
        .nav-tabs { border-color: var(--adm-border); }
        .nav-tabs .nav-link { color: var(--adm-muted); border-color: transparent; }
        .nav-tabs .nav-link:hover { color: var(--adm-gold); border-color: var(--adm-border); }
        .nav-tabs .nav-link.active {
            background: var(--adm-surface);
            border-color: var(--adm-border) var(--adm-border) var(--adm-surface);
            color: var(--adm-gold);
        }

        /* Número de bola mini */
        .num-bola {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 50%;
            font-size: .75rem; font-weight: 700;
        }
        .num-melate     { background: #1a4a7a; color: #5dade2; }
        .num-revancha   { background: #145a3a; color: #58d68d; }
        .num-revanchita { background: #3d1a6a; color: #bb8fce; }
        .num-adicional  { background: #6a3d1a; color: #f0a040; }

        /* Alert flash */
        .flash-success { background: rgba(46,204,113,.15); border-color: #2ecc7144; color: #58d68d; }
        .flash-error   { background: rgba(231,76,60,.15);  border-color: #e74c3c44; color: #ec7063; }
    </style>
</head>
<body>

<!-- ===== NAVBAR ADMIN ===== -->
<nav class="adm-navbar navbar navbar-expand-lg">
    <div class="container-fluid container-xl">
        <a class="navbar-brand" href="<?= APP_URL ?>/admin/index.php">
            <i class="bi bi-shield-fill-check me-1"></i> MEC Admin
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#adminNav"
                style="border-color:var(--adm-border)">
            <i class="bi bi-list text-light"></i>
        </button>

        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/admin/index.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_page ?? '') === 'nuevo' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/admin/nuevo-sorteo.php">
                        <i class="bi bi-plus-circle me-1"></i>Nuevo sorteo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_page ?? '') === 'editar' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/admin/editar-sorteo.php">
                        <i class="bi bi-pencil-square me-1"></i>Editar sorteo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_page ?? '') === 'importar' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/admin/importar-csv.php">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i>Importar CSV
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item d-flex align-items-center me-3">
                    <span class="small text-muted">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/admin/logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Salir
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="<?= APP_URL ?>/index.php" target="_blank">
                        <i class="bi bi-eye me-1"></i>Ver sitio
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- ===== /NAVBAR ADMIN ===== -->
<div class="container-xl py-4">
