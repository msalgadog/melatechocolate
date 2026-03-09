<?php
/**
 * Mellatron - Historial completo de sorteos
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/MelateRepository.php';
require_once __DIR__ . '/includes/helpers.php';

$pagina_actual = 'historial';
$page_title    = 'Historial de Resultados';

$repo = new MelateRepository();

// Juego activo
$_juegoInput = $_GET['juego'] ?? 'melate';
$juego = in_array($_juegoInput, ['melate','revancha','revanchita']) ? $_juegoInput : 'melate';

// Paginación
$porPagina = 25;
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));

// Búsqueda por concurso
$buscarConcurso = !empty($_GET['concurso']) ? (int)$_GET['concurso'] : null;
$resultadoBusqueda = null;
if ($buscarConcurso) {
    $resultadoBusqueda = $repo->buscarConcurso($buscarConcurso);
}

// Datos del juego seleccionado
switch ($juego) {
    case 'revancha':
        $total      = $repo->totalRevancha();
        $sorteos    = $repo->historialRevancha($pagina, $porPagina);
        $tipoNombre = 'Revancha';
        $campos     = ['r1','r2','r3','r4','r5','r6'];
        break;
    case 'revanchita':
        $total      = $repo->totalRevanchita();
        $sorteos    = $repo->historialRevanchita($pagina, $porPagina);
        $tipoNombre = 'Revanchita';
        $campos     = ['f1','f2','f3','f4','f5','f6'];
        break;
    default:
        $total      = $repo->totalMelate();
        $sorteos    = $repo->historialMelate($pagina, $porPagina);
        $tipoNombre = 'Melate';
        $campos     = ['r1','r2','r3','r4','r5','r6'];
        $juego      = 'melate';
}

$totalPaginas = (int)ceil($total / $porPagina);
$baseUrl      = "historial.php?juego={$juego}";

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <h2 class="fw-bold mb-1" style="color:var(--ml-verde-oscuro)">
        <i class="bi bi-clock-history"></i> Historial — <?= $tipoNombre ?>
    </h2>
    <p class="text-muted mb-3">
        <?= number_format($total) ?> sorteos registrados en el histórico.
    </p>

    <!-- Selector de juego -->
    <ul class="nav nav-pills-verde nav-pills mb-3 flex-wrap">
        <li class="nav-item">
            <a class="nav-link <?= $juego === 'melate' ? 'active' : '' ?>"
               href="?juego=melate">🟢 Melate</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $juego === 'revancha' ? 'active' : '' ?>"
               href="?juego=revancha">🔵 Revancha</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $juego === 'revanchita' ? 'active' : '' ?>"
               href="?juego=revanchita">🟣 Revanchita</a>
        </li>
    </ul>

    <!-- Buscador por concurso -->
    <form method="GET" class="mb-4 d-flex gap-2 flex-wrap">
        <input type="hidden" name="juego" value="<?= $juego ?>">
        <input type="number" name="concurso" class="form-control"
               placeholder="Buscar concurso #..." style="max-width:200px"
               value="<?= htmlspecialchars($buscarConcurso ?? '') ?>">
        <button type="submit" class="btn btn-success">
            <i class="bi bi-search"></i> Buscar
        </button>
        <?php if ($buscarConcurso): ?>
            <a href="?juego=<?= $juego ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x"></i> Limpiar
            </a>
        <?php endif; ?>
    </form>

    <!-- Resultado de búsqueda -->
    <?php if ($buscarConcurso && $resultadoBusqueda): ?>
        <div class="stat-card mb-4">
            <h5>🔍 Resultados para el Concurso #<?= $buscarConcurso ?></h5>
            <div class="row g-3">

                <?php if ($resultadoBusqueda['melate']): ?>
                    <div class="col-md-4">
                        <div class="card card-mellatron">
                            <div class="card-header header-melate">
                                🟢 Melate — Concurso #<?= $resultadoBusqueda['melate']['concurso'] ?>
                            </div>
                            <div class="card-body py-2">
                                <?= renderBolassMelate($resultadoBusqueda['melate'], 'bola-sm') ?>
                                <p class="text-center small text-muted mb-0">
                                    <?= formatFecha($resultadoBusqueda['melate']['fecha']) ?><br>
                                    <span class="badge-bolsa"><?= formatBolsa((int)$resultadoBusqueda['melate']['bolsa']) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($resultadoBusqueda['revancha']): ?>
                    <div class="col-md-4">
                        <div class="card card-mellatron">
                            <div class="card-header header-revancha">
                                🔵 Revancha — Concurso #<?= $resultadoBusqueda['revancha']['concurso'] ?>
                            </div>
                            <div class="card-body py-2">
                                <?= renderBolasRevancha($resultadoBusqueda['revancha'], 'bola-sm') ?>
                                <p class="text-center small text-muted mb-0">
                                    <?= formatFecha($resultadoBusqueda['revancha']['fecha']) ?><br>
                                    <span class="badge-bolsa"><?= formatBolsa((int)$resultadoBusqueda['revancha']['bolsa']) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($resultadoBusqueda['revanchita']): ?>
                    <div class="col-md-4">
                        <div class="card card-mellatron">
                            <div class="card-header header-revanchita">
                                🟣 Revanchita — Concurso #<?= $resultadoBusqueda['revanchita']['concurso'] ?>
                            </div>
                            <div class="card-body py-2">
                                <?= renderBolasRevanchita($resultadoBusqueda['revanchita'], 'bola-sm') ?>
                                <p class="text-center small text-muted mb-0">
                                    <?= formatFecha($resultadoBusqueda['revanchita']['fecha']) ?><br>
                                    <span class="badge-bolsa"><?= formatBolsa((int)$resultadoBusqueda['revanchita']['bolsa']) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$resultadoBusqueda['melate'] && !$resultadoBusqueda['revancha'] && !$resultadoBusqueda['revanchita']): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            No se encontró el concurso #<?= $buscarConcurso ?>.
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    <?php endif; ?>

    <!-- Publicidad -->
    <div class="ad-slot mb-4">📢 Espacio publicitario</div>

    <!-- Tabla historial -->
    <div class="stat-card">
        <div class="table-responsive">
            <table class="table tabla-historial table-hover table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th style="min-width:90px">Concurso</th>
                        <?php if ($juego === 'melate'): ?>
                            <th class="text-center">N1</th>
                            <th class="text-center">N2</th>
                            <th class="text-center">N3</th>
                            <th class="text-center">N4</th>
                            <th class="text-center">N5</th>
                            <th class="text-center">N6</th>
                            <th class="text-center">Adicional</th>
                        <?php elseif ($juego === 'revancha'): ?>
                            <th class="text-center">N1</th>
                            <th class="text-center">N2</th>
                            <th class="text-center">N3</th>
                            <th class="text-center">N4</th>
                            <th class="text-center">N5</th>
                            <th class="text-center">N6</th>
                        <?php else: ?>
                            <th class="text-center">F1</th>
                            <th class="text-center">F2</th>
                            <th class="text-center">F3</th>
                            <th class="text-center">F4</th>
                            <th class="text-center">F5</th>
                            <th class="text-center">F6</th>
                        <?php endif; ?>
                        <th>Bolsa</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sorteos as $row): ?>
                        <?= renderFilaHistorial($row, $juego) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Leyenda -->
        <div class="d-flex align-items-center gap-2 mt-2 mb-1 small text-muted">
            <span style="display:inline-block;width:14px;height:14px;background:rgba(240,192,64,.35);border-left:3px solid #f0c040;border-radius:2px;flex-shrink:0"></span>
            Sorteo con <strong>Primer Premio</strong> ganado (el pozo se reinició en el siguiente sorteo)
        </div>

        <!-- Paginación -->
        <div class="mt-3">
            <div class="text-center text-muted small mb-2">
                Página <?= $pagina ?> de <?= $totalPaginas ?>
                (<?= number_format($total) ?> registros)
            </div>
            <?= renderPaginacion($pagina, $totalPaginas, $baseUrl) ?>
        </div>
    </div>

    <!-- Publicidad -->
    <div class="ad-slot mt-4">📢 Espacio publicitario</div>

</div><!-- /container -->

<?php include __DIR__ . '/includes/footer.php'; ?>
