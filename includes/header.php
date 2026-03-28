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
$description = (string)($page_desc ?? 'Melate el Chocolate — Estadísticas, predicciones y resultados históricos del Melate, Revancha y Revanchita de la Lotería Nacional de México.');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$appUrl = (string)(APP_URL ?? '');

if (preg_match('#^https?://#i', $appUrl) === 1) {
    $siteBaseUrl = rtrim($appUrl, '/');
} else {
    $siteBaseUrl = $scheme . '://' . $host . rtrim($appUrl, '/');
}

$requestPath = (string)($_SERVER['REQUEST_URI'] ?? '/');
$requestPath = (string)parse_url($requestPath, PHP_URL_PATH);
if ($requestPath === '') {
    $requestPath = '/';
}

$canonicalUrl = (string)($page_canonical ?? ($siteBaseUrl . $requestPath));
$robots = (string)($page_robots ?? 'index,follow,max-image-preview:large');
$ogType = (string)($page_og_type ?? 'website');
$ogTitle = (string)($page_og_title ?? $titulo);
$ogDescription = (string)($page_og_desc ?? $description);
$ogImage = (string)($page_og_image ?? ($siteBaseUrl . '/public/img/logo.png'));
$twitterCard = (string)($page_twitter_card ?? 'summary_large_image');
$seoJsonLd = $seo_json_ld ?? null;
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <meta name="keywords" content="melate resultados, estadísticas melate, predicción melate, números calientes melate, revancha revanchita, melate histórico">
    <meta name="robots" content="<?= htmlspecialchars($robots) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

    <meta property="og:title" content="<?= htmlspecialchars($ogTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars(APP_NAME) ?>">
    <meta property="og:locale" content="es_MX">

    <meta name="twitter:card" content="<?= htmlspecialchars($twitterCard) ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">

    <title><?= htmlspecialchars($titulo) ?></title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>/public/img/favicon.png">
    <link rel="shortcut icon" type="image/png" href="<?= APP_URL ?>/public/img/favicon.png">

    <?php if (!empty($seoJsonLd)): ?>
    <script type="application/ld+json"><?= json_encode($seoJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>

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

    <?php if (!empty($katex_enabled)): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.21/dist/katex.min.css" crossorigin="anonymous">
    <?php endif; ?>

    <?php if (!empty($adsense_script)): ?>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4972818705870113"
         crossorigin="anonymous"></script>
    <?php endif; ?>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LLFJ5EE3R5"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-LLFJ5EE3R5');
    </script>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-mellatron sticky-top">
    <div class="container-fluid container-xl">

        <a class="navbar-brand" href="<?= APP_URL ?>/index.php">
            <img src="<?= APP_URL ?>/public/img/logo_amarillo.png" alt="Melate el Chocolate" class="navbar-brand-logo">
            <span class="visually-hidden">Melate el Chocolate</span>
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
                       href="<?= APP_URL ?>/">
                        <i class="bi bi-house-door-fill"></i> Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                       href="https://www.buymeacoffee.com/msalgadogonza"
                       target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-cup-hot-fill"></i> Buy me a coffee
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'blog' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/blog">
                        <i class="bi bi-journal-text"></i> Blog
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'estadisticas' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/estadisticas">
                        <i class="bi bi-bar-chart-fill"></i> Estadísticas
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'predicciones' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/predicciones">
                        <i class="bi bi-stars"></i> Predicciones
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'historial' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/historial">
                        <i class="bi bi-clock-history"></i> Historial
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'reglas' ? 'active' : '' ?>"
                       href="<?= APP_URL ?>/reglas">
                        <i class="bi bi-book-fill"></i> Reglas
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>
<!-- ===== /NAVBAR ===== -->
