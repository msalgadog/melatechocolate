<?php
/**
 * Mellatron - Header incluido en todas las páginas
 * Requiere que $pagina_actual esté definida en la página padre
 */
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/database.php';
}
$currentPage = $pagina_actual ?? '';
$titulo = isset($page_title) ? $page_title . ' | ' . APP_NAME : APP_NAME . ' — Estadísticas del Melate';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($page_desc ?? 'Melate el Chocolate — Estadísticas, predicciones y resultados históricos del Melate, Revancha y Revanchita de la Lotería Nacional de México.') ?>">
    <meta name="keywords" content="melate resultados, estadísticas melate, predicción melate, números calientes melate, revancha revanchita, melate histórico">
    <meta property="og:title"       content="<?= htmlspecialchars($titulo) ?>">
    <meta property="og:description" content="Estadísticas y predicciones del Melate mexicano">
    <meta property="og:type"        content="website">
    <title><?= htmlspecialchars($titulo) ?></title>

    <!-- Bootstrap 5 CDN -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">

    <!-- Google AdSense (reemplaza con tu ID) -->
    <!-- <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXXXX" crossorigin="anonymous"></script> -->
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-mellatron sticky-top">
    <div class="container-fluid container-xl">

        <a class="navbar-brand" href="<?= APP_URL ?>/index.php">
            <i class="bi bi-award-fill"></i> Melate el Chocolate
        </a>

        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMain"
            aria-controls="navMain" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'inicio' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/index.php">
                        <i class="bi bi-house-door-fill"></i> Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'estadisticas' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/estadisticas.php">
                        <i class="bi bi-bar-chart-fill"></i> Estadísticas
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'predicciones' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/predicciones.php">
                        <i class="bi bi-stars"></i> Predicciones
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'historial' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/historial.php">
                        <i class="bi bi-clock-history"></i> Historial
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'reglas' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/reglas.php">
                        <i class="bi bi-book-fill"></i> Reglas
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>
<!-- ===== /NAVBAR ===== -->
