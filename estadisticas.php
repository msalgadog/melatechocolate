<?php
/**
 * Mellatron - Estadísticas avanzadas
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/MelateRepository.php';
require_once __DIR__ . '/src/StatsCalculator.php';
require_once __DIR__ . '/includes/helpers.php';

$pagina_actual = 'estadisticas';
$page_title    = 'Estadísticas';

$repo = new MelateRepository();

// --- Selección activa de juego ---
$_juegoInput = $_GET['juego'] ?? 'melate';
$juego = in_array($_juegoInput, ['melate','revancha','revanchita']) ? $_juegoInput : 'melate';

// --- Datos según juego ---
switch ($juego) {
    case 'revancha':
        $frecuencia = $repo->frecuenciaRevancha();
        $retardo    = $repo->retardoRevancha();
        $sorteos    = $repo->ultimosSorteosRevancha(50);
        $campos     = ['r1','r2','r3','r4','r5','r6'];
        $tipoNombre = 'Revancha';
        $tipoBola   = 'revancha';
        break;
    case 'revanchita':
        $frecuencia = $repo->frecuenciaRevanchita();
        $retardo    = $repo->retardoRevanchita();
        $sorteos    = $repo->ultimosSorteosRevanchita(50);
        $campos     = ['f1','f2','f3','f4','f5','f6'];
        $tipoNombre = 'Revanchita';
        $tipoBola   = 'revanchita';
        break;
    default: // melate
        $frecuencia = $repo->frecuenciaMelate();
        $retardo    = $repo->retardoMelate();
        $sorteos    = $repo->ultimosSorteosMelate(50);
        $campos     = ['r1','r2','r3','r4','r5','r6'];
        $tipoNombre = 'Melate';
        $tipoBola   = 'melate';
}

$calientes = StatsCalculator::numerosCalientes($frecuencia, 10);
$frios     = StatsCalculator::numerosFrios($frecuencia, 10);

$parImpar  = StatsCalculator::distribucionParImpar($sorteos, $campos);
$altoBajo  = StatsCalculator::distribucionAltoBajo($sorteos, $campos);
$pctConsec = StatsCalculator::porcentajeConsecutivos($sorteos, $campos);

// Pares frecuentes solo Melate
$pares = ($juego === 'melate') ? $repo->paresMasFrecuentesMelate(15) : [];

// Suma distribución
$distSum = ($juego === 'melate') ? $repo->distribucionSumaMelate() : null;

// Nivel de calor por número para el heatmap
$maxFreq = max($frecuencia);
$minFreq = min($frecuencia);

// JSON para JS
$freqJson    = json_encode(array_values($frecuencia));
$labelsJson  = json_encode(array_keys($frecuencia));

arsort($retardo);
$top10Retardo = array_slice($retardo, 0, 10, true);

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <h2 class="fw-bold mb-1" style="color:var(--ml-verde-oscuro)">
        <i class="bi bi-bar-chart-fill"></i> Estadísticas — <?= $tipoNombre ?>
    </h2>
    <p class="text-muted mb-3">Basado en <?= count($sorteos) ?> últimos sorteos del histórico completo.</p>

    <!-- Selector de juego -->
    <ul class="nav nav-pills-verde nav-pills mb-4 flex-wrap">
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

    <!-- Publicidad -->
    <div class="ad-slot mb-4">📢 Espacio publicitario</div>

    <!-- ========================
         HEATMAP de todos los números
         ======================== -->
    <div class="stat-card mb-4">
        <h5><i class="bi bi-grid-3x3-gap-fill me-1"></i> Mapa de Calor — Frecuencia histórica (1-56)</h5>
        <p class="small text-muted">Rojo = más frecuente / Azul = menos frecuente</p>
        <div class="heatmap-grid" id="heatmap-grid">
            <?php foreach ($frecuencia as $num => $freq):
                $rango  = $maxFreq - $minFreq;
                $pct    = $rango > 0 ? ($freq - $minFreq) / $rango : 0;
                $nivel  = round($pct * 9);
            ?>
                <div class="heatmap-cell heat-<?= $nivel ?>"
                     data-bs-toggle="tooltip"
                     title="Número <?= $num ?>: <?= $freq ?> veces">
                    <?= $num ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4 mb-4">

        <!-- Números calientes -->
        <div class="col-md-6">
            <div class="stat-card h-100">
                <h5>🔥 Top 10 Números Calientes</h5>
                <?php foreach ($calientes as $num => $freq): ?>
                    <div class="freq-bar-container">
                        <div class="bola bola-caliente bola-sm"><?= $num ?></div>
                        <div class="freq-bar"
                             style="width:<?= round($freq / $maxFreq * 100) ?>%"></div>
                        <span class="freq-count"><?= $freq ?> veces</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Números fríos -->
        <div class="col-md-6">
            <div class="stat-card h-100">
                <h5>❄️ Top 10 Números Fríos</h5>
                <?php foreach ($frios as $num => $freq): ?>
                    <div class="freq-bar-container">
                        <div class="bola bola-frio bola-sm"><?= $num ?></div>
                        <div class="freq-bar"
                             style="width:<?= round($freq / $maxFreq * 100) ?>%;background:linear-gradient(90deg,#42a5f5,#0d47a1)"></div>
                        <span class="freq-count"><?= $freq ?> veces</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Gráfica de barras frecuencia -->
    <div class="stat-card mb-4">
        <h5><i class="bi bi-bar-chart me-1"></i> Frecuencia histórica de todos los números (1-56)</h5>
        <div style="position:relative;height:280px">
            <canvas id="chart-frecuencia"></canvas>
        </div>
    </div>

    <div class="row g-4 mb-4">

        <!-- Par / Impar -->
        <div class="col-md-4">
            <div class="stat-card h-100 text-center">
                <h5>Par / Impar</h5>
                <div style="height:200px;position:relative">
                    <canvas id="chart-par-impar"></canvas>
                </div>
                <p class="mt-2 mb-0 small text-muted">
                    Par: <?= $parImpar['pct_par'] ?>% &nbsp;|&nbsp; Impar: <?= $parImpar['pct_imp'] ?>%
                </p>
            </div>
        </div>

        <!-- Alto / Bajo -->
        <div class="col-md-4">
            <div class="stat-card h-100 text-center">
                <h5>Alto (29-56) / Bajo (1-28)</h5>
                <div style="height:200px;position:relative">
                    <canvas id="chart-alto-bajo"></canvas>
                </div>
                <p class="mt-2 mb-0 small text-muted">
                    Bajos: <?= $altoBajo['pct_bajo'] ?>% &nbsp;|&nbsp; Altos: <?= $altoBajo['pct_alto'] ?>%
                </p>
            </div>
        </div>

        <!-- Consecutivos -->
        <div class="col-md-4">
            <div class="stat-card h-100 text-center d-flex flex-column justify-content-center">
                <h5>Números Consecutivos</h5>
                <div class="fs-1 fw-bold" style="color:var(--ml-verde-claro)">
                    <?= $pctConsec ?>%
                </div>
                <p class="text-muted small mb-0">
                    De los últimos <?= count($sorteos) ?> sorteos contienen<br>al menos 2 números seguidos.
                </p>
            </div>
        </div>

    </div>

    <!-- Números más atrasados -->
    <div class="stat-card mb-4">
        <h5><i class="bi bi-hourglass-split me-1"></i> Top 10 Números más Atrasados</h5>
        <p class="small text-muted mb-3">Cantidad de sorteos transcurridos desde su última aparición.</p>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($top10Retardo as $num => $sorteosSin): ?>
                <div class="text-center">
                    <div class="bola bola-caliente bola-lg mx-auto"><?= $num ?></div>
                    <small class="d-block mt-1 text-muted"><?= $sorteosSin ?> sorteos</small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Pares más frecuentes (solo Melate) -->
    <?php if ($juego === 'melate' && !empty($pares)): ?>
    <div class="stat-card mb-4">
        <h5><i class="bi bi-people-fill me-1"></i> Top 15 Pares de Números más Frecuentes</h5>
        <p class="small text-muted mb-3">Pares de números que han salido juntos más veces en la historia del Melate.</p>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-success">
                    <tr><th>#</th><th>Par</th><th>Veces juntos</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pares as $i => $par): ?>
                        <tr>
                            <td><strong><?= $i + 1 ?></strong></td>
                            <td>
                                <?= renderBola((int)$par['a'], 'melate', 'bola-sm') ?>
                                <?= renderBola((int)$par['b'], 'melate', 'bola-sm') ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="height:12px;width:<?= round($par['veces'] / $pares[0]['veces'] * 200) ?>px;
                                         background:var(--ml-verde-claro);border-radius:3px;min-width:4px"></div>
                                    <span><?= $par['veces'] ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Distribución de sumas -->
    <?php if ($distSum): ?>
    <div class="stat-card mb-4">
        <h5><i class="bi bi-calculator me-1"></i> Distribución de la Suma de los 6 Números</h5>
        <div class="row text-center mb-3">
            <div class="col-4">
                <div class="fw-bold fs-4" style="color:var(--ml-verde-claro)"><?= $distSum['promedio'] ?></div>
                <small class="text-muted">Promedio</small>
            </div>
            <div class="col-4">
                <div class="fw-bold fs-4" style="color:var(--ml-azul)"><?= $distSum['min'] ?></div>
                <small class="text-muted">Mínima suma</small>
            </div>
            <div class="col-4">
                <div class="fw-bold fs-4" style="color:var(--ml-rojo)"><?= $distSum['max'] ?></div>
                <small class="text-muted">Máxima suma</small>
            </div>
        </div>
        <div style="position:relative;height:200px">
            <canvas id="chart-sumas"></canvas>
        </div>
        <p class="small text-muted mt-2 mb-0">
            💡 El 80% de los sorteos tiene una suma entre <?= round($distSum['promedio'] * 0.75) ?> y <?= round($distSum['promedio'] * 1.25) ?>.
            Considera jugar combinaciones dentro de ese rango.
        </p>
    </div>
    <?php endif; ?>

    <!-- Publicidad -->
    <div class="ad-slot">📢 Espacio publicitario</div>

    <div class="text-center mt-4">
        <a href="predicciones.php" class="btn btn-success btn-lg">
            <i class="bi bi-stars"></i> Ver Predicciones y Herramientas
        </a>
    </div>

</div><!-- /container -->

<?php
// Calcular rangos de suma para chart
$rangosSuma = [];
if ($distSum) {
    foreach ($distSum['valores'] as $s) {
        $ini = (int)(floor(($s - 1) / 30) * 30 + 1);
        $fin = $ini + 29;
        $key = "$ini-$fin";
        $rangosSuma[$key] = ($rangosSuma[$key] ?? 0) + 1;
    }
    ksort($rangosSuma);
}

// Pre-computar todos los valores PHP que irán en el JS
$_jsPares   = (int)($parImpar['pares']   ?? 0);
$_jsImpares = (int)($parImpar['impares'] ?? 0);
$_jsBajos   = (int)($altoBajo['bajos']   ?? 0);
$_jsAltos   = (int)($altoBajo['altos']   ?? 0);
$_sumChart  = '';
if ($distSum && !empty($rangosSuma)) {
    $_sumChart = 'new Chart(document.getElementById(\'chart-sumas\').getContext(\'2d\'), {'
        . 'type: \'bar\','
        . 'data: {'
        . '  labels: ' . json_encode(array_keys($rangosSuma)) . ','
        . '  datasets: [{ label: \'Sorteos\','
        . '    data: ' . json_encode(array_values($rangosSuma)) . ','
        . '    backgroundColor: \'#27ae60\', borderRadius: 4 }]'
        . '},'
        . 'options: { responsive: true, maintainAspectRatio: false,'
        . '  plugins: { legend: { display: false } },'
        . '  scales: { y: { beginAtZero: true } } }'
        . '});';
}

ob_start();
?>
<script>
// ---- Gráfica Frecuencia ----
const ctxFreq = document.getElementById('chart-frecuencia').getContext('2d');
const freqData   = <?= $freqJson ?>;
const labelsData = <?= $labelsJson ?>;
const maxF = Math.max(...freqData);
new Chart(ctxFreq, {
    type: 'bar',
    data: {
        labels: labelsData,
        datasets: [{
            label: 'Frecuencia',
            data: freqData,
            backgroundColor: freqData.map(v =>
                v >= maxF * 0.7 ? '#e53935' :
                v <= maxF * 0.3 ? '#1976d2' : '#27ae60'
            ),
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { font: { size: 10 } } },
            y: { beginAtZero: true }
        }
    }
});

// ---- Doughnut Par/Impar ----
new Chart(document.getElementById('chart-par-impar').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Par', 'Impar'],
        datasets: [{ data: [<?= $_jsPares ?>, <?= $_jsImpares ?>],
            backgroundColor: ['#27ae60','#1976d2'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } } }
});

// ---- Doughnut Alto/Bajo ----
new Chart(document.getElementById('chart-alto-bajo').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Bajos (1-28)', 'Altos (29-56)'],
        datasets: [{ data: [<?= $_jsBajos ?>, <?= $_jsAltos ?>],
            backgroundColor: ['#66bb6a','#ef5350'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } } }
});

<?= $_sumChart ?>
</script>
<?php
$page_scripts = ob_get_clean();

include __DIR__ . '/includes/footer.php';
