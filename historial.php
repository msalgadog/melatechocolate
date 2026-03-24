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
$baseUrl      = APP_URL . "/historial/juego/{$juego}";

$analisisGanadorPorConcurso = [];
$analysisModalIds = [];
if ($juego === 'melate') {
    foreach ($sorteos as $row) {
        if (empty($row['ganador'])) {
            continue;
        }

        $concurso = (int)$row['concurso'];
        $nums = [
            (int)$row['r1'], (int)$row['r2'], (int)$row['r3'],
            (int)$row['r4'], (int)$row['r5'], (int)$row['r6'],
        ];
        sort($nums);

        $pares = 0;
        foreach ($nums as $n) {
            if ($n % 2 === 0) {
                $pares++;
            }
        }
        $impares = 6 - $pares;
        $suma = array_sum($nums);

        $consecutivos = [];
        for ($i = 0; $i < count($nums) - 1; $i++) {
            if (($nums[$i + 1] - $nums[$i]) === 1) {
                $consecutivos[] = $nums[$i] . '-' . $nums[$i + 1];
            }
        }

        $analisis = $repo->analizarCombinacion($nums);
        $modalId = 'analisis-ganador-' . $concurso;
        $analysisModalIds[$concurso] = $modalId;
        $analisisGanadorPorConcurso[$concurso] = [
            'modal_id' => $modalId,
            'row' => $row,
            'nums' => $nums,
            'suma' => $suma,
            'pares' => $pares,
            'impares' => $impares,
            'consecutivos' => $consecutivos,
            'analisis' => $analisis,
        ];
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4 historial-page">

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
               href="<?= APP_URL ?>/historial/juego/melate">🟢 Melate</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $juego === 'revancha' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/historial/juego/revancha">🔵 Revancha</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $juego === 'revanchita' ? 'active' : '' ?>"
               href="<?= APP_URL ?>/historial/juego/revanchita">🟣 Revanchita</a>
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
            <a href="<?= APP_URL ?>/historial/juego/<?= $juego ?>" class="btn btn-outline-secondary">
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

    <!---->

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
                        <?= renderFilaHistorial($row, $juego, ['analysisModalIds' => $analysisModalIds]) ?>
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

    <?php if ($juego === 'melate' && !empty($analisisGanadorPorConcurso)): ?>
        <?php foreach ($analisisGanadorPorConcurso as $concurso => $data):
            $rowA = $data['row'];
            $ana = $data['analisis'];
            $topPair = $ana['pares'][0] ?? null;
            $consecTxt = !empty($data['consecutivos']) ? implode(', ', $data['consecutivos']) : 'Sin consecutivos';
        ?>
        <div class="modal fade" id="<?= htmlspecialchars($data['modal_id']) ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            🏆 Análisis de combinación ganadora — Concurso #<?= (int)$concurso ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2 small text-muted">
                            Fecha: <?= formatFecha((string)$rowA['fecha']) ?> · Bolsa: <?= formatBolsa((int)$rowA['bolsa']) ?>
                        </div>
                        <div class="bola-container mb-3">
                            <?php foreach ($data['nums'] as $n): ?>
                                <?= renderBola((int)$n, 'melate', 'bola-sm') ?>
                            <?php endforeach; ?>
                            <span class="sep-adicional">+</span>
                            <?= renderBola((int)$rowA['r7'], 'adicional', 'bola-sm') ?>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3"><div class="stat-card py-2 px-3 mb-0"><div class="small text-muted">Suma</div><div class="fw-bold"><?= (int)$data['suma'] ?></div></div></div>
                            <div class="col-6 col-md-3"><div class="stat-card py-2 px-3 mb-0"><div class="small text-muted">Par / Impar</div><div class="fw-bold"><?= (int)$data['pares'] ?> / <?= (int)$data['impares'] ?></div></div></div>
                            <div class="col-6 col-md-3"><div class="stat-card py-2 px-3 mb-0"><div class="small text-muted">Pureza</div><div class="fw-bold"><?= (int)$ana['pureza'] ?>/100</div></div></div>
                            <div class="col-6 col-md-3"><div class="stat-card py-2 px-3 mb-0"><div class="small text-muted">Pares vírgenes</div><div class="fw-bold"><?= (int)$ana['virgin_pairs'] ?>/15</div></div></div>
                        </div>

                        <div class="mb-2"><strong>Consecutivos:</strong> <?= htmlspecialchars($consecTxt) ?></div>
                        <div class="mb-2"><strong>Frecuencia promedio de pares:</strong> <?= htmlspecialchars((string)$ana['avg_pair_freq']) ?></div>
                        <?php if ($topPair): ?>
                            <div class="mb-2">
                                <strong>Par más frecuente de la combinación:</strong>
                                <?= (int)$topPair['a'] ?>-<?= (int)$topPair['b'] ?> (<?= (int)$topPair['veces'] ?> veces)
                            </div>
                        <?php endif; ?>
                        <div class="mb-0">
                            <strong>Histórico similar:</strong>
                            <?= count($ana['similares']) ?> sorteos con 4-5 coincidencias,
                            <?= count($ana['exactos']) ?> coincidencias exactas.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!---->

</div><!-- /container -->

<?php include __DIR__ . '/includes/footer.php'; ?>
