<?php
/**
 * Mellatron - Laboratorio Estadístico del Melate (Apache ECharts)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/MelateRepository.php';
require_once __DIR__ . '/src/StatsCalculator.php';
require_once __DIR__ . '/includes/helpers.php';

$pagina_actual = 'estadisticas';
$page_title    = 'Laboratorio Estadístico';

$repo = new MelateRepository();

// --- Selección activa de juego ---
$_juegoInput = $_GET['juego'] ?? 'melate';
$juego = in_array($_juegoInput, ['melate','revancha','revanchita'], true) ? $_juegoInput : 'melate';

// --- Datos para pestaña Resumen ---
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
        break;
}
$pagina_actual  = 'estadisticas';
$page_title     = "Laboratorio Estadístico — $tipoNombre";
$page_desc      = "Explora el Laboratorio Estadístico del Melate ($tipoNombre): Radiografía de números 1-56, Matriz de parejas 56x56, Mapa interactivo de relaciones (Grafo), Tendencias en ventanas móviles y ADN de combinación.";
$page_keywords  = "estadísticas melate, laboratorio estadistico melate, radiografia numero melate, matriz parejas 56x56, grafo relaciones melate, tendencias melate, adn combinacion melate, echarts melate, $tipoNombre historico";

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$appUrl = (string)(APP_URL ?? '');
$siteBaseUrl = preg_match('#^https?://#i', $appUrl) === 1
    ? rtrim($appUrl, '/')
    : $scheme . '://' . $host . rtrim($appUrl, '/');

$page_canonical = $siteBaseUrl . '/estadisticas/juego/' . $juego;
$page_og_title  = "Laboratorio Estadístico del Melate ($tipoNombre) — Análisis Interactivo ECharts";
$page_og_desc   = "Explora datos históricos del Melate con 6 herramientas interactivas ECharts: Radiografía por número 1-56, matriz de parejas, grafo de relaciones, tendencias y ADN de combinación.";

$seo_json_ld = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebApplication',
            '@id' => $siteBaseUrl . '/estadisticas#webapp',
            'name' => 'Laboratorio Estadístico del Melate',
            'applicationCategory' => 'DataAnalyticsApplication',
            'operatingSystem' => 'All',
            'description' => $page_desc,
            'browserRequirements' => 'Requires JavaScript. Requires HTML5.',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'MXN'
            ]
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Herramientas del Laboratorio Estadístico',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Resumen y Frecuencias Históricas (1-56)'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Radiografía de un Número (Expediente e Historial de Retardo)'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Análisis de Tendencias en Ventanas Móviles'],
                ['@type' => 'ListItem', 'position' => 4, 'name' => 'Matriz de Parejas 56x56 (Heatmap Interactivo)'],
                ['@type' => 'ListItem', 'position' => 5, 'name' => 'Mapa Interactivo de Relaciones (Grafo de Red)'],
                ['@type' => 'ListItem', 'position' => 6, 'name' => 'ADN Estadístico de Combinaciones y Similaridad']
            ]
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => $siteBaseUrl . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Estadísticas', 'item' => $siteBaseUrl . '/estadisticas'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $tipoNombre, 'item' => $page_canonical]
            ]
        ]
    ]
];

$calientes = StatsCalculator::numerosCalientes($frecuencia, 10);
$frios     = StatsCalculator::numerosFrios($frecuencia, 10);
$parImpar  = StatsCalculator::distribucionParImpar($sorteos, $campos);
$altoBajo  = StatsCalculator::distribucionAltoBajo($sorteos, $campos);
$pctConsec = StatsCalculator::porcentajeConsecutivos($sorteos, $campos);
$pares     = ($juego === 'melate') ? $repo->paresMasFrecuentesMelate(15) : [];
$distSum   = ($juego === 'melate') ? $repo->distribucionSumaMelate() : null;

$maxFreq = max($frecuencia);
$minFreq = min($frecuencia);

arsort($retardo);
$top10Retardo = array_slice($retardo, 0, 10, true);

// Calcular rangos de suma para ECharts
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

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <!-- Encabezado del Laboratorio -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h2 class="fw-bold mb-1" style="color:var(--ml-verde-oscuro)">
                <i class="bi bi-cpu-fill text-warning"></i> Laboratorio Estadístico — <?= $tipoNombre ?>
            </h2>
            <p class="text-muted mb-0">Exploración visual e interactiva con datos históricos del histórico completo.</p>
        </div>

        <!-- Selector de Juego -->
        <ul class="nav nav-pills-verde nav-pills mb-0 flex-wrap">
            <li class="nav-item">
                <a class="nav-link <?= $juego === 'melate' ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/estadisticas/juego/melate">🟢 Melate</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $juego === 'revancha' ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/estadisticas/juego/revancha">🔵 Revancha</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $juego === 'revanchita' ? 'active' : '' ?>"
                   href="<?= APP_URL ?>/estadisticas/juego/revanchita">🟣 Revanchita</a>
            </li>
        </ul>
    </div>

    <!-- Barra de Pestañas del Laboratorio -->
    <div class="stats-tab-nav">
        <button class="stats-tab-link active" data-tab="resumen">
            <i class="bi bi-grid-fill"></i> Resumen
        </button>
        <button class="stats-tab-link" data-tab="numero">
            <i class="bi bi-search"></i> Radiografía de un número
        </button>
        <button class="stats-tab-link" data-tab="tendencias">
            <i class="bi bi-graph-up-arrow"></i> Tendencias
        </button>
        <button class="stats-tab-link" data-tab="matriz">
            <i class="bi bi-grid-3x3-gap"></i> Matriz de parejas
        </button>
        <button class="stats-tab-link" data-tab="relaciones">
            <i class="bi bi-diagram-3"></i> Mapa de relaciones
        </button>
        <button class="stats-tab-link" data-tab="combinacion">
            <i class="bi bi-fingerprint"></i> Analizar combinación
        </button>
    </div>

    <!-- ============================================================
         PESTAÑA 1: RESUMEN
         ============================================================ -->
    <div class="stats-tab-pane active" id="tab-pane-resumen">

        <!-- Heatmap simple histórico -->
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
                            <div class="freq-bar freq-bar-hot" style="width:<?= round($freq / $maxFreq * 100) ?>%"></div>
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
                            <div class="freq-bar freq-bar-cold" style="width:<?= round($freq / $maxFreq * 100) ?>%"></div>
                            <span class="freq-count"><?= $freq ?> veces</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Gráfica ECharts Frecuencia -->
        <div class="stat-card mb-4">
            <h5><i class="bi bi-bar-chart-fill me-1 text-success"></i> Frecuencia Histórica de Todos los Números (1-56)</h5>
            <div id="echart-frecuencia" style="width:100%;height:320px;"></div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Par / Impar ECharts -->
            <div class="col-md-4">
                <div class="stat-card h-100 text-center">
                    <h5>Par / Impar</h5>
                    <div id="echart-par-impar" style="width:100%;height:220px;"></div>
                    <p class="mt-2 mb-0 small text-muted">
                        Par: <?= $parImpar['pct_par'] ?>% &nbsp;|&nbsp; Impar: <?= $parImpar['pct_imp'] ?>%
                    </p>
                </div>
            </div>

            <!-- Alto / Bajo ECharts -->
            <div class="col-md-4">
                <div class="stat-card h-100 text-center">
                    <h5>Alto (29-56) / Bajo (1-28)</h5>
                    <div id="echart-alto-bajo" style="width:100%;height:220px;"></div>
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
                        De los últimos <?= count($sorteos) ?> sorteos contienen al menos 2 números seguidos.
                    </p>
                </div>
            </div>
        </div>

        <!-- Top 10 Atrasados -->
        <div class="stat-card mb-4">
            <h5><i class="bi bi-hourglass-split me-1 text-warning"></i> Top 10 Números más Atrasados</h5>
            <p class="small text-muted mb-3">Sorteos transcurridos desde su última aparición.</p>
            <div class="d-flex flex-wrap gap-3">
                <?php foreach ($top10Retardo as $num => $sorteosSin): ?>
                    <div class="text-center">
                        <div class="bola bola-caliente bola-lg mx-auto"><?= $num ?></div>
                        <small class="d-block mt-1 text-muted"><?= $sorteosSin ?> sorteos</small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pares más frecuentes (Melate) -->
        <?php if ($juego === 'melate' && !empty($pares)): ?>
        <div class="stat-card mb-4">
            <h5><i class="bi bi-people-fill me-1"></i> Top 15 Pares más Frecuentes</h5>
            <p class="small text-muted mb-3">Pares de números que han salido juntos más veces en la historia.</p>
            <div class="pair-grid">
                <?php foreach ($pares as $i => $par): ?>
                    <div class="pair-card">
                        <div class="pair-card-head">
                            <span class="pair-rank">#<?= $i + 1 ?></span>
                            <span class="pair-count"><?= (int)$par['veces'] ?> veces</span>
                        </div>
                        <div class="pair-balls">
                            <?= renderBola((int)$par['a'], 'melate', 'bola-sm') ?>
                            <?= renderBola((int)$par['b'], 'melate', 'bola-sm') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Distribución de Sumas ECharts -->
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
            <div id="echart-sumas" style="width:100%;height:250px;"></div>
        </div>
        <?php endif; ?>

    </div>

    <!-- ============================================================
         PESTAÑA 2: RADIOGRAFÍA DE UN NÚMERO
         ============================================================ -->
    <div class="stats-tab-pane" id="tab-pane-numero" style="display:none;">
        <div class="stat-card mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h5 class="mb-1"><i class="bi bi-search text-success me-1"></i> Expediente Estadístico de Número</h5>
                    <p class="small text-muted mb-0">Selecciona cualquier número (1-56) para consultar su historial detallado.</p>
                </div>
                <div style="min-width:160px">
                    <select id="num-selector-select" class="form-select form-select-sm fw-bold"></select>
                </div>
            </div>

            <div class="mb-4">
                <div id="num-selector-grid" class="num-selector-grid"></div>
            </div>

            <!-- Loader / Contenido -->
            <div id="num-profile-loading" class="text-center py-4">
                <div class="spinner-border text-success" role="status"></div>
                <p class="text-muted small mt-2">Cargando expediente del número...</p>
            </div>

            <div id="num-profile-content" style="display:none;">
                <!-- Tarjetas KPI -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">Apariciones totales</small>
                            <span class="fs-4 fw-bold text-success val-total-apariciones">-</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">Porcentaje histórico</small>
                            <span class="fs-4 fw-bold text-primary val-pct-aparicion">-</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">Retardo actual</small>
                            <span class="fs-4 fw-bold text-danger val-retardo-actual">-</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">Retardo promedio / Máx</small>
                            <span class="fs-6 fw-bold text-dark d-block val-retardo-promedio">-</span>
                            <small class="text-muted">Máx: <span class="val-retardo-maximo">-</span></small>
                        </div>
                    </div>
                </div>

                <!-- Ventanas de Frecuencia Reciente -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Últimos 20</small>
                            <div class="fw-bold val-freq-20">-</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Últimos 50</small>
                            <div class="fw-bold val-freq-50">-</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Últimos 100</small>
                            <div class="fw-bold val-freq-100">-</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Últimos 200</small>
                            <div class="fw-bold val-freq-200">-</div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica ECharts Línea de Tiempo -->
                <div class="stat-card mb-4">
                    <div id="echart-number-timeline" style="width:100%;height:320px;"></div>
                </div>

                <!-- Compañeros más Frecuentes -->
                <div class="stat-card">
                    <h6 class="fw-bold mb-2"><i class="bi bi-people me-1 text-success"></i> Números Compañeros más Frecuentes</h6>
                    <div id="num-companions-list" class="d-flex flex-wrap gap-2 pt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         PESTAÑA 3: TENDENCIAS
         ============================================================ -->
    <div class="stats-tab-pane" id="tab-pane-tendencias" style="display:none;">
        <div class="stat-card mb-4">
            <h5><i class="bi bi-graph-up-arrow text-success me-1"></i> Análisis de Tendencias Históricas</h5>
            <p class="small text-muted mb-3">Selecciona hasta 6 números para comparar la evolución de su frecuencia en ventanas móviles.</p>

            <div class="mb-3">
                <label class="small text-muted fw-bold mb-1">Seleccionar números a comparar:</label>
                <div id="trends-selector-grid" class="num-selector-grid"></div>
            </div>

            <!-- Disclaimer explícito -->
            <div class="alert alert-info small py-2 mb-4">
                <i class="bi bi-info-circle-fill me-1"></i>
                <b>Aviso importante:</b> Esta herramienta analiza exclusivamente el comportamiento histórico de frecuencia en sorteos pasados. Un incremento o decremento histórico no predice el resultado del siguiente sorteo.
            </div>

            <div id="trends-loading" class="text-center py-4">
                <div class="spinner-border text-success" role="status"></div>
                <p class="text-muted small mt-2">Calculando tendencias móviles...</p>
            </div>

            <div id="trends-content" style="display:none;">
                <!-- Gráfica de Líneas ECharts -->
                <div class="stat-card mb-4">
                    <div id="echart-trends-line" style="width:100%;height:360px;"></div>
                </div>

                <!-- Tabla de Resumen Ventanas -->
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Últimos 20 Sorteos</th>
                                <th>Últimos 50 Sorteos</th>
                                <th>Últimos 100 Sorteos</th>
                                <th>Últimos 200 Sorteos</th>
                            </tr>
                        </thead>
                        <tbody id="trends-summary-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         PESTAÑA 4: MATRIZ DE PAREJAS 56x56
         ============================================================ -->
    <div class="stats-tab-pane" id="tab-pane-matriz" style="display:none;">
        <div class="stat-card mb-4">
            <h5><i class="bi bi-grid-3x3-gap text-success me-1"></i> Matriz de Coincidencias de Parejas (56 × 56)</h5>
            <p class="small text-muted mb-3">Mapa de calor interactivo que representa la cantidad de sorteos en los que dos números salieron juntos.</p>

            <div id="matrix-loading" class="text-center py-4">
                <div class="spinner-border text-success" role="status"></div>
                <p class="text-muted small mt-2">Generando matriz de parejas (1,540 combinaciones)...</p>
            </div>

            <div id="matrix-content" style="display:none;">
                <div id="echart-pairs-heatmap" style="width:100%;height:600px;"></div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         PESTAÑA 5: MAPA INTERACTIVO DE RELACIONES (GRAFO)
         ============================================================ -->
    <div class="stats-tab-pane" id="tab-pane-relaciones" style="display:none;">
        <div class="stat-card mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h5 class="mb-1"><i class="bi bi-diagram-3 text-success me-1"></i> Red Histórica de Relaciones entre Números</h5>
                    <p class="small text-muted mb-0">Nodos = Números (tamaño por frecuencia) | Conexiones = Coincidencias en sorteos.</p>
                </div>

                <!-- Slider Filtro de Umbral -->
                <div class="d-flex align-items-center gap-2">
                    <label for="relations-threshold-slider" class="small text-muted mb-0">Filtrar coincidencia mín:</label>
                    <input type="range" class="form-range" id="relations-threshold-slider" min="1" max="60" value="15" style="width:140px">
                    <span id="relations-threshold-val" class="badge bg-success">15</span>
                </div>
            </div>

            <div id="relations-loading" class="text-center py-4">
                <div class="spinner-border text-success" role="status"></div>
                <p class="text-muted small mt-2">Construyendo mapa de relaciones de red...</p>
            </div>

            <div id="relations-content" style="display:none;">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div id="echart-relations-graph" style="width:100%;height:520px;border:1px solid #e0e0e0;border-radius:8px;background:#fafafa"></div>
                    </div>
                    <div class="col-lg-4" id="relations-node-details">
                        <div class="p-3 bg-light rounded border text-center text-muted">
                            <i class="bi bi-hand-index-thumb fs-2 d-block mb-2 text-success"></i>
                            Haz clic en cualquier número dentro del mapa interactivo para desplegar sus relaciones principales.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         PESTAÑA 6: ANALIZAR COMBINACIÓN (ADN ESTADÍSTICO)
         ============================================================ -->
    <div class="stats-tab-pane" id="tab-pane-combinacion" style="display:none;">
        <div class="stat-card mb-4">
            <h5><i class="bi bi-fingerprint text-success me-1"></i> ADN Estadístico de Combinaciones</h5>
            <p class="small text-muted mb-3">Introduce 6 números para evaluar su perfil estadístico frente al histórico completo.</p>

            <div class="row g-2 justify-content-center mb-3" id="comb-inputs-container"></div>

            <div class="d-flex justify-content-center gap-2 mb-4">
                <button id="comb-btn-analyze" class="btn btn-success fw-bold">
                    <i class="bi bi-cpu me-1"></i> Analizar Combinación
                </button>
                <button id="comb-btn-random" class="btn btn-outline-secondary">
                    <i class="bi bi-shuffle me-1"></i> Generar Aleatoria
                </button>
            </div>

            <div id="comb-loading" class="text-center py-4" style="display:none;">
                <div class="spinner-border text-success" role="status"></div>
                <p class="text-muted small mt-2">Analizando ADN estadístico de la combinación...</p>
            </div>

            <div id="comb-content" style="display:none;">
                <div class="text-center mb-4">
                    <div id="comb-balls-display" class="d-flex justify-content-center gap-2 flex-wrap mb-2"></div>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Radar ECharts -->
                    <div class="col-md-6">
                        <div class="stat-card h-100">
                            <div id="echart-comb-radar" style="width:100%;height:320px;"></div>
                        </div>
                    </div>

                    <!-- Dossier Indicadores -->
                    <div class="col-md-6">
                        <div class="stat-card h-100">
                            <h6 class="fw-bold mb-3">Indicadores Históricos</h6>
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Suma total de números:</span>
                                    <strong class="val-comb-suma">-</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Percentil histórico de suma:</span>
                                    <strong class="val-comb-percentil-suma text-success">-</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Proporción Par / Impar:</span>
                                    <strong class="val-comb-par-impar">-</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Proporción Bajo (1-28) / Alto (29-56):</span>
                                    <strong class="val-comb-alto-bajo">-</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Números consecutivos:</span>
                                    <strong class="val-comb-consecutivos">-</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Distancia promedio entre números:</span>
                                    <strong class="val-comb-dist-promedio">-</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Dispersión (Desviación estándar):</span>
                                    <strong class="val-comb-dispersion">-</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between bg-light">
                                    <span>Índice de Pureza de Combinación:</span>
                                    <strong class="val-comb-pureza text-primary fs-6">-</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Sorteos Históricos Similares -->
                <div class="stat-card" id="comb-similar-draws"></div>
            </div>
        </div>
    </div>

    <!-- Enlace a predicciones -->
    <div class="text-center mt-4">
        <a href="<?= APP_URL ?>/predicciones" class="btn btn-success btn-lg shadow-sm">
            <i class="bi bi-stars"></i> Ver Predicciones y Herramientas
        </a>
    </div>

</div><!-- /container -->

<?php
// Pre-computar dataset inicial para ECharts Resumen
$overviewPayload = [
    'freqData'    => array_values($frecuencia),
    'labelsData'  => array_keys($frecuencia),
    'pares'       => (int)($parImpar['pares'] ?? 0),
    'impares'     => (int)($parImpar['impares'] ?? 0),
    'bajos'       => (int)($altoBajo['bajos'] ?? 0),
    'altos'       => (int)($altoBajo['altos'] ?? 0),
    'sumasLabels' => array_keys($rangosSuma),
    'sumasValues' => array_values($rangosSuma),
];

ob_start();
?>
<script>
window.APP_URL = <?= json_encode(APP_URL) ?>;
window.overviewData = <?= json_encode($overviewPayload) ?>;
</script>

<script src="<?= APP_URL ?>/public/js/statistics/overview-charts.js"></script>
<script src="<?= APP_URL ?>/public/js/statistics/number-profile.js"></script>
<script src="<?= APP_URL ?>/public/js/statistics/trends-chart.js"></script>
<script src="<?= APP_URL ?>/public/js/statistics/pairs-matrix.js"></script>
<script src="<?= APP_URL ?>/public/js/statistics/relations-graph.js"></script>
<script src="<?= APP_URL ?>/public/js/statistics/combination-profile.js"></script>
<script src="<?= APP_URL ?>/public/js/statistics/stats-tab-manager.js"></script>
<?php
$page_scripts = ob_get_clean();

include __DIR__ . '/includes/footer.php';
