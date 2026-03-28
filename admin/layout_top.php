<?php
/**
 * Mellatron Admin — Layout: shell con sidebar + topbar
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
            --adm-sidebar-w: 286px;
            --adm-sidebar-w-collapsed: 86px;
        }
        body {
            margin: 0;
            background: radial-gradient(circle at 85% -20%, #24283a 0%, var(--adm-bg) 44%);
            color: var(--adm-text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .adm-shell {
            display: block;
            min-height: 100vh;
        }

        .adm-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--adm-sidebar-w);
            z-index: 1035;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #171b27 0%, #121522 100%);
            border-right: 1px solid var(--adm-border);
            transition: width .22s ease, transform .22s ease;
        }

        .adm-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1rem .9rem;
            border-bottom: 1px solid var(--adm-border);
            text-decoration: none;
            color: var(--adm-text);
        }
        .adm-brand-logo {
            height: 38px;
            width: auto;
            filter: drop-shadow(0 3px 10px rgba(245,166,35,.2));
        }
        .adm-brand-logo-icon {
            display: none;
            height: 44px;
        }

        .adm-nav {
            padding: .9rem .65rem;
            overflow: auto;
            flex: 1;
        }
        .adm-section-title {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #646f8d;
            padding: .2rem .75rem .45rem;
            white-space: nowrap;
        }

        .adm-nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            color: var(--adm-muted);
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: .62rem .75rem;
            margin-bottom: .34rem;
            transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .16s ease;
        }
        .adm-nav-link i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        .adm-nav-link:hover {
            color: #f2f5ff;
            background: rgba(255,255,255,.04);
            border-color: rgba(255,255,255,.08);
            transform: translateX(2px);
        }
        .adm-nav-link.active {
            color: #101318;
            background: linear-gradient(135deg, #ffc25b 0%, #f5a623 100%);
            border-color: #ffcb70;
            box-shadow: 0 6px 22px rgba(245,166,35,.28);
            font-weight: 700;
        }

        .adm-link-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .adm-sidebar-footer {
            border-top: 1px solid var(--adm-border);
            padding: .85rem .65rem 1rem;
        }
        .adm-user-meta {
            font-size: .78rem;
            color: #7e88a6;
            padding: 0 .45rem .6rem;
            white-space: nowrap;
        }
        .adm-site-link {
            color: #93a0c4;
        }

        .adm-main {
            margin-left: var(--adm-sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .22s ease;
        }

        .adm-topbar {
            position: sticky;
            top: 0;
            z-index: 1025;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            background: rgba(18, 21, 34, .82);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--adm-border);
        }

        .adm-topbar-left {
            display: flex;
            align-items: center;
            gap: .85rem;
            min-width: 0;
        }

        .adm-burger {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            border: 1px solid #3a4058;
            background: linear-gradient(180deg, #1b2133 0%, #151a29 100%);
            color: #d9e1ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
            transition: border-color .18s ease, color .18s ease, transform .15s ease;
        }
        .adm-burger:hover {
            border-color: #ffbe54;
            color: #ffd28c;
            transform: translateY(-1px);
        }

        .adm-page-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #f3f6ff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .adm-topbar-actions {
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .adm-top-link {
            color: #9ca7c5;
            border: 1px solid #343a52;
            border-radius: 10px;
            padding: .35rem .62rem;
            font-size: .82rem;
            text-decoration: none;
        }
        .adm-top-link:hover {
            color: #ffd393;
            border-color: #f5a623;
        }

        .adm-content {
            flex: 1;
            padding-top: 1.25rem;
            padding-bottom: 1.5rem;
        }

        .adm-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(8, 10, 16, .65);
            backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
            z-index: 1030;
        }

        body.adm-sidebar-collapsed .adm-sidebar {
            width: var(--adm-sidebar-w-collapsed);
        }
        body.adm-sidebar-collapsed .adm-main {
            margin-left: var(--adm-sidebar-w-collapsed);
        }
        body.adm-sidebar-collapsed .adm-brand-logo-full {
            display: none;
        }
        body.adm-sidebar-collapsed .adm-brand-logo-icon {
            display: inline-block;
        }
        body.adm-sidebar-collapsed .adm-section-title,
        body.adm-sidebar-collapsed .adm-link-label,
        body.adm-sidebar-collapsed .adm-user-meta,
        body.adm-sidebar-collapsed .adm-site-link .adm-link-label {
            display: none;
        }
        body.adm-sidebar-collapsed .adm-nav-link,
        body.adm-sidebar-collapsed .adm-site-link {
            justify-content: center;
            padding-left: .5rem;
            padding-right: .5rem;
        }
        body.adm-sidebar-collapsed .adm-brand {
            padding-left: .4rem;
            padding-right: .4rem;
        }

        @media (max-width: 991.98px) {
            .adm-sidebar {
                transform: translateX(-100%);
                width: min(86vw, 320px);
                box-shadow: 10px 0 34px rgba(0,0,0,.45);
            }
            .adm-main {
                margin-left: 0 !important;
            }
            body.adm-sidebar-collapsed .adm-sidebar {
                width: min(86vw, 320px);
            }
            body.adm-sidebar-open-mobile .adm-sidebar {
                transform: translateX(0);
            }
            body.adm-sidebar-open-mobile .adm-backdrop {
                opacity: 1;
                pointer-events: auto;
            }
            .adm-topbar {
                height: 66px;
                padding: 0 .75rem;
            }
            .adm-top-link span {
                display: none;
            }
            .adm-brand-logo-full {
                display: inline-block !important;
                height: 38px;
            }
            .adm-brand-logo-icon {
                display: none !important;
            }
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
        .adm-table tbody td { border-color: var(--adm-border); vertical-align: middle; transition: background-color .15s ease, color .15s ease; }
        .adm-table thead th,
        .adm-table tbody th,
        .adm-table tbody td {
            padding: .62rem .95rem;
        }
        .adm-table tbody tr:hover td {
            background: rgba(245,166,35,.14);
            color: #f6f8ff;
        }
        .adm-table tbody tr:hover .text-muted {
            color: #d5dbea !important;
        }
        .adm-table tbody tr:hover .fw-semibold {
            color: #ffffff;
        }
        .adm-table tbody tr:hover a:not(.btn) {
            color: #ffe1a8 !important;
        }
        .adm-table tbody tr:hover .btn-admin-outline {
            color: var(--adm-gold);
            border-color: #f5a62399;
        }
        .adm-table tbody tr:hover .btn-admin-outline i {
            color: var(--adm-gold);
        }
        .adm-table tbody tr:hover .btn-admin-outline:hover {
            color: #ffffff;
            border-color: var(--adm-gold);
            background: rgba(245,166,35,.28);
        }
        .adm-table tbody tr:hover .btn-admin-outline:hover i {
            color: #ffffff;
        }

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
<div class="adm-shell">
    <aside class="adm-sidebar" id="admSidebar" aria-label="Menú de administración">
        <a class="adm-brand" href="<?= APP_URL ?>/admin/index.php">
            <img class="adm-brand-logo adm-brand-logo-full" src="<?= APP_URL ?>/public/img/logo_amarillo.png" alt="Melate Chocolate">
            <img class="adm-brand-logo adm-brand-logo-icon" src="<?= APP_URL ?>/public/img/logo_amarillo_icon.png" alt="Melate Chocolate">
        </a>

        <nav class="adm-nav">
            <div class="adm-section-title">Panel</div>

            <a class="adm-nav-link <?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/admin/index.php">
                <i class="bi bi-speedometer2"></i>
                <span class="adm-link-label">Dashboard</span>
            </a>
            <a class="adm-nav-link <?= ($active_page ?? '') === 'nuevo' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/admin/nuevo-sorteo.php">
                <i class="bi bi-plus-circle"></i>
                <span class="adm-link-label">Nuevo sorteo</span>
            </a>
            <a class="adm-nav-link <?= ($active_page ?? '') === 'editar' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/admin/editar-sorteo.php">
                <i class="bi bi-pencil-square"></i>
                <span class="adm-link-label">Editar sorteo</span>
            </a>
            <a class="adm-nav-link <?= ($active_page ?? '') === 'importar' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/admin/importar-csv.php">
                <i class="bi bi-file-earmark-arrow-up"></i>
                <span class="adm-link-label">Importar CSV</span>
            </a>
            <a class="adm-nav-link <?= ($active_page ?? '') === 'fuentes' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/admin/fuentes.php">
                <i class="bi bi-link-45deg"></i>
                <span class="adm-link-label">Fuentes y Cron</span>
            </a>

            <div class="adm-section-title mt-2">Blog</div>

            <a class="adm-nav-link <?= ($active_page ?? '') === 'blog' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/admin/blog-posts.php">
                <i class="bi bi-journal-richtext"></i>
                <span class="adm-link-label">Entradas</span>
            </a>
            <a class="adm-nav-link <?= ($active_page ?? '') === 'blog-stats' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/admin/blog-stats.php">
                <i class="bi bi-bar-chart-line"></i>
                <span class="adm-link-label">Stats Blog</span>
            </a>
        </nav>

        <div class="adm-sidebar-footer">
            <div class="adm-user-meta">
                <i class="bi bi-person-circle me-1"></i>
                <?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?>
            </div>
            <a class="adm-nav-link" href="<?= APP_URL ?>/admin/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span class="adm-link-label">Salir</span>
            </a>
            <a class="adm-nav-link adm-site-link" href="<?= APP_URL ?>/index.php" target="_blank">
                <i class="bi bi-eye"></i>
                <span class="adm-link-label">Ver sitio</span>
            </a>
        </div>
    </aside>

    <div class="adm-backdrop" id="admBackdrop"></div>

    <div class="adm-main">
        <header class="adm-topbar">
            <div class="adm-topbar-left">
                <button type="button" class="adm-burger" id="admSidebarToggle" aria-label="Abrir o cerrar menú">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="adm-page-title"><?= htmlspecialchars($page_title ?? 'Admin') ?></h1>
            </div>
            <div class="adm-topbar-actions">
                <a class="adm-top-link" href="<?= APP_URL ?>/admin/blog-posts.php">
                    <i class="bi bi-journal-richtext me-1"></i><span>Blog</span>
                </a>
                <a class="adm-top-link" href="<?= APP_URL ?>/admin/importar-csv.php">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i><span>Importar</span>
                </a>
            </div>
        </header>

        <main class="adm-content container-xl">
