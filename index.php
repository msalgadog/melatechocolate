<?php
/**
 * Mellatron - Página de inicio
 * Muestra los últimos resultados de Melate, Revancha y Revanchita
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/MelateRepository.php';
require_once __DIR__ . '/src/StatsCalculator.php';
require_once __DIR__ . '/includes/helpers.php';

$pagina_actual = 'inicio';
$page_title    = 'Últimos Resultados';

// Datos
$repo       = new MelateRepository();
$melate     = $repo->ultimoMelate();
$revancha   = $repo->ultimoRevancha();
$revanchita = $repo->ultimoRevanchita();

// Frecuencias para clasificar números del último sorteo
$freqMelate     = $repo->frecuenciaMelate();
$freqRevancha   = $repo->frecuenciaRevancha();
$freqRevanchita = $repo->frecuenciaRevanchita();

// Últimos 10 sorteos para mini-tendencia
$ultimos10 = $repo->historialMelate(1, 10);

include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<div class="hero-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="mb-1">
                    <i class="bi bi-award-fill"></i> Melate el Chocolate
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

    <!-- Publicidad (slot horizontal) -->
    <div class="ad-slot text-center mb-4">
        <!-- Google AdSense: banner 728x90 o responsive -->
        📢 Espacio publicitario
    </div>

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

    <!-- ---- Mini tabla de últimos 10 Melate ---- -->
    <div class="stat-card mb-4">
        <h5><i class="bi bi-clock-history me-1"></i> Últimos 10 sorteos Melate</h5>
        <div class="table-responsive">
            <table class="table tabla-historial table-hover table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Concurso</th>
                        <th colspan="6" class="text-center">Números</th>
                        <th class="text-center">Adicional</th>
                        <th>Bolsa</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimos10 as $row): ?>
                        <?= renderFilaHistorial($row, 'melate') ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mb-1 small text-muted">
            <span style="display:inline-block;width:14px;height:14px;background:rgba(240,192,64,.35);border-left:3px solid #f0c040;border-radius:2px;flex-shrink:0"></span>
            Sorteo con <strong>Primer Premio</strong> ganado
        </div>
        <div class="text-end mt-1">
            <a href="historial.php" class="btn btn-sm btn-outline-success">
                Ver historial completo <i class="bi bi-arrow-right"></i>
            </a>
        </div>
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

    <!-- ---- Datos curiosos ---- -->
    <?php
    $totalSorteos = $repo->totalMelate();
    $distSum      = $repo->distribucionSumaMelate();
    $retardo      = $repo->retardoMelate();
    arsort($retardo);
    $masAtrasado  = array_key_first($retardo);
    ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="fs-1 fw-bold" style="color:var(--ml-verde-claro)"><?= number_format($totalSorteos) ?></div>
                <p class="mb-0 text-muted small">Sorteos en el histórico</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="fs-1 fw-bold" style="color:var(--ml-dorado)"><?= $distSum['promedio'] ?></div>
                <p class="mb-0 text-muted small">Suma promedio de los 6 números</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="bola bola-caliente bola-lg mx-auto"><?= $masAtrasado ?></div>
                <p class="mb-0 text-muted small mt-2">Número más atrasado (<?= $retardo[$masAtrasado] ?> sorteos sin salir)</p>
            </div>
        </div>
    </div>

    <!-- Publicidad 2 -->
    <div class="ad-slot text-center">
        <!-- Google AdSense: banner rectangular o leaderboard -->
        📢 Espacio publicitario
    </div>

</div><!-- /container -->

<?php include __DIR__ . '/includes/footer.php'; ?>
