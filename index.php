<?php
/**
 * Mellatron - Página de inicio
 * Muestra los últimos resultados de Melate, Revancha y Revanchita
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/MelateRepository.php';
require_once __DIR__ . '/src/StatsCalculator.php';
require_once __DIR__ . '/src/ContentRepository.php';
require_once __DIR__ . '/includes/helpers.php';

$pagina_actual  = 'inicio';
$page_title     = 'Últimos Resultados';
$adsense_script = true;

// Datos
$repo       = new MelateRepository();
$melate     = $repo->ultimoMelate();
$revancha   = $repo->ultimoRevancha();
$revanchita = $repo->ultimoRevanchita();

// Frecuencias para clasificar números del último sorteo
$freqMelate     = $repo->frecuenciaMelate();
$freqRevancha   = $repo->frecuenciaRevancha();
$freqRevanchita = $repo->frecuenciaRevanchita();

// Blog
$contentRepo = new ContentRepository();
$contentRepo->ensureSeedPosts();
$blogPosts = $contentRepo->latestPosts(5);

include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<div class="hero-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="mb-1">
                    <img class="logo-chocolate" src="<?= APP_URL ?>/public/img/logo.png" alt="Melate el Chocolate" style="height: 120px;">
                </h1>
                <p class="mb-0 fs-5">Estadísticas, predicciones y resultados del Melate Mexicano</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <?php if ($melate): ?>
                    <div class="small text-white-50">Bolsa actual Melate</div>
                    <div class="bolsa-value" data-count-to="<?= (int)$melate['bolsa'] ?>">
                        <?= formatBolsa((int)$melate['bolsa']) ?>
                    </div>
                    <div class="small text-white-75">Concurso #<?= $melate['concurso'] ?> &mdash; <?= formatFecha($melate['fecha']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== CONTENIDO ===== -->
<div class="container pb-4">

    <!---->

    <!-- ---- Últimos resultados ---- -->
    <h2 class="mb-3 fw-bold" style="color:var(--ml-verde-oscuro)">
        <i class="bi bi-trophy-fill"></i> Últimos Resultados
    </h2>

    <div class="row g-4 mb-4">

        <!-- Melate -->
        <div class="col-md-12">
            <div class="card card-mellatron">
                <div class="card-header header-melate d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>
                        <i class="bi bi-circle-fill me-1"></i> Melate — Concurso #<?= $melate['concurso'] ?? '—' ?>
                        <?php if (!empty($melate['ganador'])): ?>
                            <span class="badge bg-warning text-dark ms-1">🏆 1er Premio</span>
                        <?php endif; ?>
                    </span>
                    <?php if ($melate): ?>
                        <span class="small fw-normal"><?= formatFecha($melate['fecha']) ?></span>
                        <span class="badge-bolsa"><?= formatBolsa((int)$melate['bolsa']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body text-center py-3">
                    <?php if ($melate): ?>
                        <?php
                        // Clasificar cada número
                        $nums   = [(int)$melate['r1'],(int)$melate['r2'],(int)$melate['r3'],
                                   (int)$melate['r4'],(int)$melate['r5'],(int)$melate['r6']];
                        $clases = StatsCalculator::clasificarNumeros($nums, $freqMelate);
                        ?>
                        <div class="bola-container justify-content-center">
                            <?php foreach ($nums as $n):
                                $nivel = $clases[$n]['nivel']; ?>
                                <div class="bola bola-melate bola-lg" data-bs-toggle="tooltip"
                                     title="<?= $clases[$n]['frecuencia'] ?> veces histórico<?= $nivel==='caliente' ? ' 🔥' : ($nivel==='frio' ? ' ❄️' : '') ?>">
                                    <?= $n ?>
                                </div>
                            <?php endforeach; ?>
                            <span class="sep-adicional">+</span>
                            <div class="bola bola-adicional bola-lg"
                                 data-bs-toggle="tooltip" title="Número adicional">
                                <?= (int)$melate['r7'] ?>
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-0">
                            🔥 Caliente &nbsp;|&nbsp; ❄️ Frío &nbsp;|&nbsp; (+) Adicional
                        </p>
                    <?php else: ?>
                        <p class="text-muted">Sin datos disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Revancha + Revanchita -->
        <div class="col-md-6">
            <div class="card card-mellatron h-100">
                <div class="card-header header-revancha d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <span>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Revancha #<?= $revancha['concurso'] ?? '—' ?>
                        <?php if (!empty($revancha['ganador'])): ?>
                            <span class="badge bg-warning text-dark ms-1">🏆 1er Premio</span>
                        <?php endif; ?>
                    </span>
                    <?php if ($revancha): ?>
                        <span class="badge-bolsa"><?= formatBolsa((int)$revancha['bolsa']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body py-3">
                    <?php if ($revancha): ?>
                        <?= renderBolasRevancha($revancha, '', true) ?>
                        <p class="text-center text-muted small mb-0"><?= formatFecha($revancha['fecha']) ?></p>
                    <?php else: ?>
                        <p class="text-muted text-center">Sin datos</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-mellatron h-100">
                <div class="card-header header-revanchita d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <span>
                        <i class="bi bi-arrow-repeat me-1"></i> Revanchita #<?= $revanchita['concurso'] ?? '—' ?>
                        <?php if (!empty($revanchita['ganador'])): ?>
                            <span class="badge bg-warning text-dark ms-1">🏆 1er Premio</span>
                        <?php endif; ?>
                    </span>
                    <?php if ($revanchita): ?>
                        <span class="badge-bolsa"><?= formatBolsa((int)$revanchita['bolsa']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body py-3">
                    <?php if ($revanchita): ?>
                        <?= renderBolasRevanchita($revanchita, '', true) ?>
                        <p class="text-center text-muted small mb-0"><?= formatFecha($revanchita['fecha']) ?></p>
                    <?php else: ?>
                        <p class="text-muted text-center">Sin datos</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /row resultados -->

    <!-- ---- Blog de análisis ---- -->
    <h2 class="mb-3 fw-bold" style="color:var(--ml-verde-oscuro)">
        <i class="bi bi-journal-richtext"></i> Blog de análisis
    </h2>
    <?php
    $blogColors = ['blog-img-1','blog-img-2','blog-img-3','blog-img-4','blog-img-5'];
    $blogIcons  = ['bi-journal-richtext','bi-bar-chart-line','bi-trophy','bi-stars','bi-dice-6'];
    ?>
    <div class="row g-4 mb-4">
    <?php foreach ($blogPosts as $bi => $bpost):
        $bImgClass = $blogColors[$bi % 5];
        $bIcon     = $blogIcons[$bi % 5];
    ?>
    <div class="col-12 col-sm-6 col-lg-4">
        <article class="blog-card">
            <a class="blog-card-thumb <?= $bImgClass ?>"
               href="<?= APP_URL ?>/blog-articulo.php?slug=<?= urlencode($bpost['slug']) ?>">
                <?php if (!empty($bpost['image_url'])): ?>
                    <img src="<?= htmlspecialchars($bpost['image_url']) ?>"
                         alt="<?= htmlspecialchars($bpost['title']) ?>">
                <?php else: ?>
                    <i class="bi <?= $bIcon ?> blog-card-icon"></i>
                <?php endif; ?>
            </a>
            <div class="blog-card-body">
                <p class="blog-card-meta">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?= !empty($bpost['published_at']) ? date('d M Y', strtotime($bpost['published_at'])) : '' ?>
                </p>
                <h2 class="blog-card-title">
                    <a href="<?= APP_URL ?>/blog-articulo.php?slug=<?= urlencode($bpost['slug']) ?>">
                        <?= htmlspecialchars($bpost['title']) ?>
                    </a>
                </h2>
                <p class="blog-card-excerpt"><?= htmlspecialchars($bpost['excerpt']) ?></p>
                <a class="blog-card-readmore"
                   href="<?= APP_URL ?>/blog-articulo.php?slug=<?= urlencode($bpost['slug']) ?>">
                    Leer art&iacute;culo completo <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </article>
    </div>
    <?php endforeach; ?>
    </div><!-- /row blog -->
    <div class="text-end mb-4">
        <a href="<?= APP_URL ?>/blog.php" class="btn btn-success btn-sm">
            Ver todos los artículos <i class="bi bi-journal-text ms-1"></i>
        </a>
    </div>

    <!-- ---- Accesos rápidos ---- -->
    <h2 class="mb-3 fw-bold" style="color:var(--ml-verde-oscuro)">
        <i class="bi bi-grid-fill"></i> ¿Qué quieres hacer?
    </h2>
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <a href="estadisticas.php" class="card card-mellatron text-decoration-none text-center p-3 d-block">
                <div class="fs-2 mb-2">📊</div>
                <strong class="text-success">Estadísticas</strong>
                <p class="small text-muted mb-0">Números calientes, fríos, frecuencias</p>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="predicciones.php" class="card card-mellatron text-decoration-none text-center p-3 d-block">
                <div class="fs-2 mb-2">🔮</div>
                <strong class="text-success">Predicciones</strong>
                <p class="small text-muted mb-0">Zodiacal, numerología, sugeridos</p>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="historial.php" class="card card-mellatron text-decoration-none text-center p-3 d-block">
                <div class="fs-2 mb-2">📅</div>
                <strong class="text-success">Historial</strong>
                <p class="small text-muted mb-0">Todos los resultados históricos</p>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="reglas.php" class="card card-mellatron text-decoration-none text-center p-3 d-block">
                <div class="fs-2 mb-2">📖</div>
                <strong class="text-success">Reglas</strong>
                <p class="small text-muted mb-0">Cómo jugar y ganar premios</p>
            </a>
        </div>

    </div>

    <!---->

</div><!-- /container -->

<?php include __DIR__ . '/includes/footer.php'; ?>
