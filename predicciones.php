<?php
/**
 * Mellatron - Predicciones y herramientas de suerte
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/MelateRepository.php';
require_once __DIR__ . '/src/StatsCalculator.php';
require_once __DIR__ . '/src/ZodiacHelper.php';
require_once __DIR__ . '/includes/helpers.php';

$pagina_actual = 'predicciones';
$page_title    = 'Predicciones y Herramientas';

$repo = new MelateRepository();
$freqMelate  = $repo->frecuenciaMelate();
$retardoMelate = $repo->retardoMelate();

// --- Procesar formularios ---

// 1) Numerología por fecha de nacimiento
$resultNumerologia = null;
if (!empty($_POST['fecha_nac'])) {
    $fechaNac = strip_tags($_POST['fecha_nac']);
    $resultado = StatsCalculator::numerosDeLaSuerte($fechaNac);
    $signo     = ZodiacHelper::getSigno($fechaNac);
    $resultNumerologia = [
        'numero'  => $resultado['numero_personal'],
        'numeros' => $resultado['numeros'],
        'signo'   => $signo,
        'datos_signo' => $signo ? ZodiacHelper::getDatosSigno($signo) : null,
    ];
}

// 2) Zodiac directo
$zodiacResult = null;
if (!empty($_POST['signo_zodiac'])) {
    $signo = $_POST['signo_zodiac'];
    $datos = ZodiacHelper::getDatosSigno($signo);
    if ($datos) {
        $zodiacResult = [
            'signo'  => $signo,
            'datos'  => $datos,
            'combo'  => ZodiacHelper::combinacionPersonalizada(
                $signo, rand(1, 9)
            ),
        ];
    }
}

// 3) Generador por múltiplos
$multiplosResult = null;
$multiplosError  = null;
if (!empty($_POST['multiplos_check'])) {
    $mDigito = (int)($_POST['m_digito'] ?? 0);
    $mJuego  = in_array($_POST['m_juego'] ?? '', ['melate','revancha','revanchita'])
               ? $_POST['m_juego'] : 'melate';
    $mMaxN   = $mJuego === 'revanchita' ? 39 : 56;

    if ($mDigito < 1 || $mDigito > 9) {
        $multiplosError = 'Debes ingresar un dígito entre 1 y 9.';
    } else {
        // Todos los múltiplos exactos del dígito en el rango del juego
        $mTodos = [];
        for ($n = $mDigito; $n <= $mMaxN; $n += $mDigito) {
            $mTodos[] = $n;
        }
        $mTotal = count($mTodos);

        if ($mTotal < 6) {
            $multiplosError = "El dígito {$mDigito} solo produce {$mTotal} múltiplo(s) en el rango 1–{$mMaxN}.
                               Se necesitan al menos 6. Prueba con un dígito menor (1–" .
                               ($mJuego === 'revanchita' ? '6' : '8') . ").";
        } else {
            // Selección inteligente: reparte los múltiplos en 6 zonas y elige
            // uno al azar por zona → combinación siempre balanceada (baja/media/alta)
            $mSeed = (int)($_POST['m_seed'] ?? time());
            mt_srand($mSeed);

            $mSeleccion = [];
            if ($mTotal === 6) {
                $mSeleccion = $mTodos;
            } else {
                $zoneSize = $mTotal / 6;
                for ($z = 0; $z < 6; $z++) {
                    $ini = (int)floor($z       * $zoneSize);
                    $fin = (int)floor(($z + 1) * $zoneSize) - 1;
                    $fin = max($ini, $fin);
                    $mSeleccion[] = $mTodos[mt_rand($ini, $fin)];
                }
                // Garantizar 6 únicos (puede haber colisión en zonas pequeñas)
                $mSeleccion = array_unique($mSeleccion);
                if (count($mSeleccion) < 6) {
                    $diff = array_values(array_diff($mTodos, $mSeleccion));
                    shuffle($diff);
                    while (count($mSeleccion) < 6 && !empty($diff)) {
                        $mSeleccion[] = array_shift($diff);
                    }
                }
                sort($mSeleccion);
            }

            $multiplosResult = [
                'digit'     => $mDigito,
                'juego'     => $mJuego,
                'todos'     => $mTodos,
                'seleccion' => $mSeleccion,
                'max_n'     => $mMaxN,
                'total'     => $mTotal,
                'seed'      => $mSeed,
            ];
        }
    }
}

// 4) Analizador de pureza
$analisisResult = null;
$analisisError  = null;
if (!empty($_POST['pureza_check'])) {
    $pNums = [];
    for ($i = 1; $i <= 6; $i++) {
        $v = (int)($_POST["p_num_$i"] ?? 0);
        if ($v >= 1 && $v <= 56) $pNums[] = $v;
    }
    $pNums = array_unique($pNums);
    if (count($pNums) === 6) {
        sort($pNums);
        $analisisResult = $repo->analizarCombinacion($pNums);
    } else {
        $analisisError = 'Ingresa exactamente 6 números distintos entre 1 y 56.';
    }
}

// 5) Verificador de boleto
$verificadorResult = null;
if (!empty($_POST['n'])) {
    $nums = array_map('intval', (array)$_POST['n']);
    $nums = array_filter($nums, fn($n) => $n >= 1 && $n <= 56);
    $nums = array_unique($nums);
    sort($nums);

    if (count($nums) === 6) {
        $ultimo = $repo->ultimoMelate();
        if ($ultimo) {
            $naturales = [(int)$ultimo['r1'],(int)$ultimo['r2'],(int)$ultimo['r3'],
                          (int)$ultimo['r4'],(int)$ultimo['r5'],(int)$ultimo['r6']];
            $adicional = (int)$ultimo['r7'];

            $aciertos  = count(array_intersect($nums, $naturales));
            $aciertaAd = in_array($adicional, $nums);

            $verificadorResult = [
                'nums'        => $nums,
                'concurso'    => $ultimo['concurso'],
                'aciertos'    => $aciertos,
                'adicional'   => $aciertaAd,
                'naturales'   => $naturales,
                'adicionalNum'=> $adicional,
            ];
        }
    }
}

// 4) Sugerencia estadística
$sugerencia = StatsCalculator::generarSugerencia($freqMelate, $retardoMelate);
$classSug   = StatsCalculator::clasificarNumeros($sugerencia, $freqMelate);

// Números de la semana
$numsSemana = ZodiacHelper::numerosDeLaSemana();

$signos = ZodiacHelper::getTodosSignos();

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <h2 class="fw-bold mb-1" style="color:var(--ml-verde-oscuro)">
        <i class="bi bi-stars"></i> Predicciones y Herramientas
    </h2>
    <p class="text-muted mb-4">
        Herramientas lúdicas para explorar combinaciones. Recuerda: el Melate es puro azar. 🎲
    </p>

    <!---->

    <div class="row g-4">

        <!-- ==========================================
             COLUMNA IZQUIERDA
             ========================================== -->
        <div class="col-lg-8">

            <!-- ---- Números sugeridos por estadística ---- -->
            <div class="tool-card mb-4">
                <h5><i class="bi bi-graph-up-arrow me-1"></i> Combinación Sugerida por Estadística</h5>
                <p class="small text-muted">
                    Mezcla de 3 números calientes + 2 con mayor retardo + 1 número frío.
                    Generada con cada recarga de página.
                </p>
                <div class="bola-container justify-content-center">
                    <?php foreach ($sugerencia as $n):
                        $nivel = $classSug[$n]['nivel']; ?>
                        <div class="bola bola-<?= $nivel === 'caliente' ? 'caliente' : ($nivel === 'frio' ? 'frio' : 'melate') ?> bola-lg"
                             data-bs-toggle="tooltip"
                             title="Frecuencia: <?= $classSug[$n]['frecuencia'] ?> <?= $nivel === 'caliente' ? '🔥' : ($nivel === 'frio' ? '❄️' : '') ?>">
                            <?= $n ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="predicciones.php" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Nueva sugerencia
                    </a>
                </div>
            </div>

            <!-- ---- Melático (generador aleatorio) ---- -->
            <div class="tool-card mb-4">
                <h5><i class="bi bi-dice-5-fill me-1"></i> Melático — Generador Aleatorio</h5>
                <p class="small text-muted">Genera 5 combinaciones completamente al azar, como el Melático oficial.</p>
                <button class="btn btn-success" id="btn-melatico">
                    <i class="bi bi-dice-6"></i> Generar combinaciones
                </button>
                <div id="melatico-result" class="mt-3"></div>
            </div>

            <!-- ---- Números de la Semana ---- -->
            <div class="tool-card mb-4">
                <h5><i class="bi bi-calendar-week-fill me-1"></i> Números de la Semana</h5>
                <p class="small text-muted">
                    Calculados a partir del día, mes y año actual. Cambian cada semana.
                </p>
                <div class="bola-container">
                    <?php foreach ($numsSemana as $n): ?>
                        <div class="bola bola-sugerida bola-lg"><?= $n ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ---- Zodiaco ---- -->
            <div class="tool-card mb-4">
                <h5><i class="bi bi-stars me-1"></i> Números de la Suerte por Signo Zodiacal</h5>
                <p class="small text-muted">Selecciona tu signo y obtén tu combinación zodiacal.</p>

                <form method="POST" action="#resultado-zodiac">
                    <div class="row g-2 mb-3">
                        <?php foreach ($signos as $signo => $datos): ?>
                            <div class="col-6 col-sm-4 col-md-3">
                                <div class="card zodiac-card text-center p-2
                                    <?= (isset($zodiacResult) && $zodiacResult['signo'] === $signo) ? 'selected' : '' ?>"
                                     data-signo="<?= $signo ?>">
                                    <div class="zodiac-emoji"><?= $datos['emoji'] ?></div>
                                    <small class="fw-bold"><?= $signo ?></small>
                                    <small class="text-muted d-none d-md-block"
                                           style="font-size:.68rem"><?= substr($datos['desc'], 0, 20) ?>…</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="signo_zodiac" id="zodiac-signo-sel"
                           value="<?= htmlspecialchars($_POST['signo_zodiac'] ?? '') ?>">
                    <button type="submit" class="btn btn-purple btn-success">
                        <i class="bi bi-magic"></i> Ver números del signo
                    </button>
                </form>

                <?php if ($zodiacResult): ?>
                    <div id="resultado-zodiac" class="mt-4 p-3 rounded"
                         style="background:var(--ml-verde-light)">
                        <h6 class="fw-bold">
                            <?= $zodiacResult['datos']['emoji'] ?> <?= $zodiacResult['signo'] ?>
                        </h6>
                        <small class="text-muted"><?= $zodiacResult['datos']['desc'] ?></small>
                        <div class="mt-2">
                            <p class="mb-1 fw-semibold">Números base del signo:</p>
                            <div class="bola-container">
                                <?php foreach ($zodiacResult['datos']['numeros'] as $n): ?>
                                    <div class="bola bola-zodiacal bola-sm"><?= $n ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="mb-1 fw-semibold">Combinación personalizada (6 números):</p>
                            <div class="bola-container">
                                <?php foreach ($zodiacResult['combo'] as $n): ?>
                                    <div class="bola bola-zodiacal bola-lg"><?= $n ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="resultado-zodiac"></div>
                <?php endif; ?>
            </div>

            <!-- ---- Verificador de boleto ---- -->
            <div class="tool-card mb-4">
                <h5><i class="bi bi-check2-circle me-1"></i> Verificar Mis Números</h5>
                <p class="small text-muted">
                    Ingresa tus 6 números y comprueba cuántos coinciden
                    con el <strong>último sorteo Melate</strong>.
                </p>

                <form method="POST" id="form-verificador">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <input type="number" class="form-control num-input text-center fw-bold"
                                   name="n_input_<?= $i ?>"
                                   min="1" max="56" placeholder="<?= $i ?>"
                                   style="width:70px;font-size:1.1rem"
                                   value="<?= htmlspecialchars($_POST["n_input_$i"] ?? '') ?>">
                        <?php endfor; ?>
                    </div>
                    <!-- hidden inputs para los números procesados -->
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <input type="hidden" name="n[]" value="">
                    <?php endfor; ?>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search"></i> Verificar
                    </button>
                </form>

                <?php if ($verificadorResult): ?>
                    <?php
                        $a = $verificadorResult['aciertos'];
                        $ad = $verificadorResult['adicional'];
                        $alerta = match(true) {
                            $a === 6               => ['success', '🏆 ¡PRIMER LUGAR! 6 naturales'],
                            $a === 5 && $ad        => ['warning', '🥈 Segundo lugar: 5 nat. + adicional'],
                            $a === 5               => ['info',    '🥉 Tercer lugar: 5 naturales'],
                            $a === 4 && $ad        => ['info',    '4° lugar: 4 nat. + adicional'],
                            $a === 4               => ['info',    '5° lugar: 4 naturales'],
                            $a === 3 && $ad        => ['secondary','6° lugar: 3 nat. + adicional ($161.29)'],
                            $a === 3               => ['secondary','7° lugar: 3 naturales ($43.01)'],
                            $a === 2 && $ad        => ['secondary','8° lugar: 2 nat. + adicional ($32.25)'],
                            $a === 2               => ['secondary','9° lugar: 2 naturales ($26.88)'],
                            default                => ['danger',  'Sin premio esta vez 😔'],
                        };
                    ?>
                    <div class="mt-3 p-3 rounded border alert-<?= $alerta[0] ?>"
                         style="background:var(--ml-verde-light)">
                        <strong>Concurso #<?= $verificadorResult['concurso'] ?></strong>
                        <div class="mb-2">
                            <span class="text-muted small">Resultado oficial:</span><br>
                            <div class="bola-container justify-content-start mt-1">
                                <?php foreach ($verificadorResult['naturales'] as $n): ?>
                                    <div class="bola bola-melate bola-sm"><?= $n ?></div>
                                <?php endforeach; ?>
                                <span class="sep-adicional" style="font-size:1rem">+</span>
                                <div class="bola bola-adicional bola-sm"><?= $verificadorResult['adicionalNum'] ?></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small">Tus números:</span><br>
                            <div class="bola-container justify-content-start mt-1">
                                <?php foreach ($verificadorResult['nums'] as $n): ?>
                                    <?php $enNaturales = in_array($n, $verificadorResult['naturales']); ?>
                                    <div class="bola bola-sm <?= $enNaturales ? 'bola-caliente' : 'bola-normal' ?>">
                                        <?= $n ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="alert alert-<?= $alerta[0] ?> mb-0 py-2">
                            <strong><?= $alerta[1] ?></strong>
                            — Aciertos: <?= $a ?>/6<?= $ad ? ' + adicional' : '' ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ---- Analizador de Pureza ---- -->
            <div class="tool-card mb-4" id="analizador-pureza">
                <h5><i class="bi bi-shield-check me-1"></i> Analizador de Pureza de Combinación</h5>
                <p class="small text-muted">
                    Ingresa 6 números y descubre qué tan "limpia" es tu combinación:
                    si ya fue sorteada, cuántas veces han salido juntos sus pares y
                    cuáles son los sorteos históricos más parecidos.
                </p>

                <form method="POST" action="#analizador-pureza" class="mb-3">
                    <input type="hidden" name="pureza_check" value="1">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <input type="number" name="p_num_<?= $i ?>"
                                   class="form-control text-center fw-bold"
                                   min="1" max="56" placeholder="<?= $i ?>"
                                   style="width:70px;font-size:1.1rem"
                                   value="<?= htmlspecialchars($_POST["p_num_$i"] ?? '') ?>">
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-shield-check"></i> Analizar combinación
                    </button>
                </form>

                <?php if ($analisisError): ?>
                    <div class="alert alert-warning"><?= $analisisError ?></div>
                <?php endif; ?>

                <?php if ($analisisResult): ?>
                    <?php
                    $ar  = $analisisResult;
                    $pur = $ar['pureza'];
                    [$purLabel, $purColor, $purIcon] = match(true) {
                        !empty($ar['exactos'])  => ['¡Ya fue sorteada!',   'danger',  'bi-x-circle-fill'],
                        $pur >= 85              => ['Muy limpia',          'success', 'bi-stars'],
                        $pur >= 65              => ['Limpia',              'success', 'bi-check-circle'],
                        $pur >= 45              => ['Moderada',            'warning', 'bi-dash-circle'],
                        $pur >= 25              => ['Bastante usada',      'warning', 'bi-exclamation-circle'],
                        default                 => ['Muy usada',           'danger',  'bi-exclamation-triangle'],
                    };
                    ?>

                    <!-- Combinación ingresada -->
                    <div class="bola-container justify-content-center mb-3">
                        <?php foreach ($ar['nums'] as $n): ?>
                            <div class="bola bola-melate bola-lg"><?= $n ?></div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Meter de pureza -->
                    <div class="p-3 rounded mb-3" style="background:var(--ml-verde-light)">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">
                                <i class="bi <?= $purIcon ?> text-<?= $purColor ?> me-1"></i>
                                <?= $purLabel ?>
                            </span>
                            <span class="fw-bold fs-5 text-<?= $purColor ?>"><?= $pur ?>%</span>
                        </div>
                        <div class="progress" style="height:14px">
                            <div class="progress-bar bg-<?= $purColor ?>"
                                 style="width:<?= $pur ?>%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1">
                            <span>Muy usada</span>
                            <span>Promedio histórico (≈59%)</span>
                            <span>Virgen</span>
                        </div>
                        <p class="small text-muted mt-2 mb-0">
                            Promedio de apariciones por par: <strong><?= $ar['avg_pair_freq'] ?></strong> veces &nbsp;|&nbsp;
                            Pares vírgenes (nunca juntos): <strong><?= $ar['virgin_pairs'] ?>/15</strong>
                        </p>
                    </div>

                    <!-- Alerta si fue sorteada -->
                    <?php if (!empty($ar['exactos'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>¡Esta combinación ya fue sorteada <?= count($ar['exactos']) ?> vez/veces!</strong>
                            <?php foreach ($ar['exactos'] as $ex): ?>
                                <div class="small mt-1">
                                    Concurso #<?= $ex['concurso'] ?> — <?= formatFecha($ex['fecha']) ?>
                                    <?= renderBolassMelate($ex, 'bola-sm') ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success py-2">
                            <i class="bi bi-check-circle-fill"></i>
                            Esta combinación <strong>nunca ha sido sorteada</strong> exactamente.
                        </div>
                    <?php endif; ?>

                    <!-- Análisis de los 15 pares -->
                    <h6 class="fw-bold mt-3 mb-2">Frecuencia de los 15 pares posibles</h6>
                    <p class="small text-muted mb-2">
                        El histórico promedio por par es ≈41 veces.
                        <span class="badge bg-danger">Muy frecuente</span>
                        <span class="badge bg-warning text-dark">Moderado</span>
                        <span class="badge bg-info text-dark">Poco frecuente</span>
                        <span class="badge bg-secondary">Virgen</span>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-success"><tr>
                                <th>Par</th><th class="text-center">Veces juntos</th><th>Nivel</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($ar['pares'] as $par):
                                [$pBadge, $pLabel] = match(true) {
                                    $par['veces'] === 0  => ['secondary', 'Virgen ⚪'],
                                    $par['veces'] <= 20  => ['info',      'Poco frecuente'],
                                    $par['veces'] <= 60  => ['warning',   'Moderado'],
                                    default              => ['danger',    'Muy frecuente'],
                                };
                            ?>
                                <tr>
                                    <td>
                                        <?= renderBola($par['a'], 'melate', 'bola-sm') ?>
                                        <?= renderBola($par['b'], 'melate', 'bola-sm') ?>
                                    </td>
                                    <td class="text-center fw-bold"><?= $par['veces'] ?></td>
                                    <td><span class="badge bg-<?= $pBadge ?>"><?= $pLabel ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Sorteos similares -->
                    <?php if (!empty($ar['similares'])): ?>
                        <h6 class="fw-bold mt-4 mb-2">Sorteos históricos más similares</h6>
                        <p class="small text-muted">Sorteos con 4 o 5 números en común con tu combinación.</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-success"><tr>
                                    <th>Concurso</th><th>Números</th>
                                    <th class="text-center">Coincidencias</th><th>Fecha</th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($ar['similares'] as $sim): ?>
                                    <tr>
                                        <td><span class="badge-concurso"><?= $sim['concurso'] ?></span></td>
                                        <td>
                                            <?php
                                            foreach (['r1','r2','r3','r4','r5','r6'] as $k) {
                                                $n   = (int)$sim[$k];
                                                $cls = in_array($n, $ar['nums']) ? 'bola-caliente' : 'bola-normal';
                                                echo renderBola($n, $cls, 'bola-sm');
                                            }
                                            ?>
                                            <span class="sep-adicional">+</span>
                                            <?= renderBola((int)$sim['r7'], 'adicional', 'bola-sm') ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= (int)$sim['coincidencias'] >= 5 ? 'warning text-dark' : 'secondary' ?>">
                                                <?= $sim['coincidencias'] ?>/6
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?= formatFecha($sim['fecha']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

            <!-- ---- Generador por Múltiplos ---- -->
            <div class="tool-card mb-4" id="generador-multiplos">
                <h5><i class="bi bi-calculator me-1"></i> Combinación por Múltiplos</h5>
                <p class="small text-muted">
                    Elige un dígito del 1 al 9 y generaremos una combinación usando
                    <strong>solo los múltiplos exactos</strong> de ese número dentro del
                    rango válido del juego. La selección se distribuye automáticamente
                    entre valores bajos, medios y altos para evitar combinaciones
                    desequilibradas.
                </p>

                <form method="POST" action="#generador-multiplos" class="mb-4">
                    <input type="hidden" name="multiplos_check" value="1">
                    <input type="hidden" name="m_seed" id="m_seed_input"
                           value="<?= htmlspecialchars($_POST['m_seed'] ?? '') ?>">

                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-12 col-sm-5">
                            <label class="form-label fw-semibold">Juego</label>
                            <select name="m_juego" class="form-select">
                                <?php
                                $mJuegoSel = $_POST['m_juego'] ?? 'melate';
                                foreach (['melate'=>'Melate (1–56)','revancha'=>'Revancha (1–56)','revanchita'=>'Revanchita (1–39)'] as $v => $l):
                                ?>
                                <option value="<?= $v ?>" <?= $mJuegoSel === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label fw-semibold">
                                Dígito
                                <span class="text-muted fw-normal">(1–9)</span>
                            </label>
                            <input type="number" name="m_digito" id="m_digito"
                                   class="form-control text-center fw-bold"
                                   style="font-size:1.4rem"
                                   min="1" max="9" required
                                   value="<?= htmlspecialchars($_POST['m_digito'] ?? '') ?>"
                                   placeholder="7">
                        </div>
                        <div class="col-6 col-sm-4 d-flex gap-2">
                            <button type="submit" class="btn btn-success flex-fill">
                                <i class="bi bi-calculator"></i> Generar
                            </button>
                            <?php if ($multiplosResult): ?>
                            <button type="submit" class="btn btn-outline-secondary" id="btn-rerandom"
                                    title="Regenerar selección aleatoria con los mismos múltiplos">
                                <i class="bi bi-shuffle"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Guía contextual en tiempo real -->
                    <div id="multiplos-guia" class="small mb-2"></div>
                </form>

                <?php if ($multiplosError): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <?= htmlspecialchars($multiplosError) ?>
                    </div>
                <?php endif; ?>

                <?php if ($multiplosResult):
                    $mr  = $multiplosResult;
                    $tipo = $mr['juego'];
                    // Metadatos por cantidad de múltiplos
                    [$mInsight, $mInsightColor] = match(true) {
                        $mr['total'] === 6   => ['Solo existe esta combinación posible para este dígito — máxima pureza temática.', 'success'],
                        $mr['total'] <= 9    => ['Pocos múltiplos disponibles — combinación muy temática y concentrada.', 'info'],
                        $mr['total'] <= 14   => ['Múltiplos moderados — buen equilibrio entre temática y variedad.', 'primary'],
                        default              => ['Muchos múltiplos disponibles — alta variedad, menos temática pura.', 'warning'],
                    };
                    $dígito = $mr['digit'];
                ?>

                <!-- Resultado combinación -->
                <div class="p-3 rounded mb-3" style="background:var(--ml-verde-light)">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold">Tu combinación basada en múltiplos de <?= $dígito ?></span>
                        <span class="badge bg-secondary"><?= strtoupper($tipo) ?></span>
                    </div>
                    <div class="bola-container justify-content-start gap-2 mb-2">
                        <?php foreach ($mr['seleccion'] as $n): ?>
                            <div class="bola bola-<?= $tipo === 'revanchita' ? 'revanchita' : $tipo ?> bola-lg">
                                <?= $n ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        <small class="text-muted me-2">Verifica:</small>
                        <?php foreach ($mr['seleccion'] as $n): ?>
                            <code class="small"><?= $n ?>÷<?= $dígito ?>=<?= $n/$dígito ?></code>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Insight inteligente -->
                <div class="alert alert-<?= $mInsightColor ?> py-2 small mb-3">
                    <i class="bi bi-lightbulb-fill me-1"></i>
                    <strong><?= $mr['total'] ?> múltiplos</strong> de <?= $dígito ?>
                    en rango 1–<?= $mr['max_n'] ?> &nbsp;|&nbsp;
                    <?= $mInsight ?>
                </div>

                <!-- Todos los múltiplos disponibles -->
                <div>
                    <p class="small fw-semibold mb-2 text-muted">
                        Tabla completa de múltiplos disponibles
                        <span class="text-info">(resaltados = seleccionados)</span>
                    </p>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($mr['todos'] as $n):
                            $sel = in_array($n, $mr['seleccion']);
                        ?>
                            <span class="badge rounded-pill <?= $sel ? 'bg-success text-white' : 'bg-secondary bg-opacity-25 text-secondary' ?>"
                                  style="font-size:.82rem;min-width:2.1rem;padding:.35em .6em<?= $sel ? ';outline:2px solid #2ecc71;outline-offset:1px' : '' ?>">
                                <?= $n ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php endif; ?>
            </div><!-- /generador-multiplos -->

        </div><!-- /col-lg-8 -->

        <!-- ==========================================
             COLUMNA DERECHA - Numerología
             ========================================== -->
        <div class="col-lg-4">

            <!-- Numerología personal -->
            <div class="tool-card mb-4 sticky-top" style="top:80px">
                <h5><i class="bi bi-123 me-1"></i> Numerología Personal</h5>
                <p class="small text-muted">
                    Ingresa tu fecha de nacimiento y descubre tus números de la suerte
                    y tu signo zodiacal.
                </p>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fecha de nacimiento</label>
                        <input type="date" name="fecha_nac" class="form-control"
                               value="<?= htmlspecialchars($_POST['fecha_nac'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-magic"></i> Calcular mis números
                    </button>
                </form>

                <?php if ($resultNumerologia): ?>
                    <div class="mt-3 p-3 rounded" style="background:var(--ml-verde-light)">
                        <p class="mb-2">
                            <strong>Número personal:</strong>
                            <span class="fs-4 fw-bold ms-2"
                                  style="color:var(--ml-verde-oscuro)">
                                <?= $resultNumerologia['numero'] ?>
                            </span>
                        </p>

                        <?php if ($resultNumerologia['signo']): ?>
                            <p class="mb-2">
                                <strong>Signo zodiacal:</strong>
                                <?= $resultNumerologia['datos_signo']['emoji'] ?>
                                <?= $resultNumerologia['signo'] ?>
                            </p>
                            <p class="small text-muted mb-2">
                                <?= $resultNumerologia['datos_signo']['desc'] ?>
                            </p>
                        <?php endif; ?>

                        <p class="mb-1 fw-semibold">Números de la suerte:</p>
                        <div class="bola-container">
                            <?php foreach ($resultNumerologia['numeros'] as $n): ?>
                                <div class="bola bola-sugerida bola-sm"><?= $n ?></div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($resultNumerologia['signo']): ?>
                            <?php
                            $combo = ZodiacHelper::combinacionPersonalizada(
                                $resultNumerologia['signo'],
                                $resultNumerologia['numero']
                            );
                            ?>
                            <p class="mb-1 fw-semibold mt-3">Combinación para el Melate:</p>
                            <div class="bola-container">
                                <?php foreach ($combo as $n): ?>
                                    <div class="bola bola-zodiacal bola-lg"><?= $n ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Info educativa -->
                <div class="mt-4 p-3 rounded border-start border-warning border-3 bg-white">
                    <p class="small text-muted mb-0">
                        <i class="bi bi-info-circle-fill text-warning"></i>
                        <strong>Aviso:</strong> Todas las predicciones son puramente
                        lúdicas. El Melate es un juego de azar; las estadísticas
                        no garantizan ningún resultado. ¡Juega con responsabilidad!
                    </p>
                </div>

                <!-- Probabilidades reales -->
                <div class="mt-3 stat-card">
                    <h6 class="fw-bold" style="color:var(--ml-verde-oscuro)">
                        <i class="bi bi-percent me-1"></i> Probabilidades reales
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td><small>6 naturales (1er lugar)</small></td>
                                <td class="text-end"><small><strong>1 : 32.4M</strong></small></td>
                            </tr>
                            <tr>
                                <td><small>5 + adicional (2do)</small></td>
                                <td class="text-end"><small>1 : 5.4M</small></td>
                            </tr>
                            <tr>
                                <td><small>5 naturales (3er)</small></td>
                                <td class="text-end"><small>1 : 101,270</small></td>
                            </tr>
                            <tr>
                                <td><small>4 naturales (5to)</small></td>
                                <td class="text-end"><small>1 : 895</small></td>
                            </tr>
                            <tr>
                                <td><small>3 naturales (7mo)</small></td>
                                <td class="text-end"><small>1 : 47</small></td>
                            </tr>
                            <tr>
                                <td><small>2 naturales (9no)</small></td>
                                <td class="text-end"><small>1 : 7.2</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div><!-- /col-lg-4 -->

    </div><!-- /row -->

    <!---->

</div><!-- /container -->

<script>
// Parchar el formulario del verificador para copiar los inputs visibles
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-verificador');
    if (form) {
        form.addEventListener('submit', e => {
            const hiddens  = form.querySelectorAll('input[name="n[]"]');
            const visibles = form.querySelectorAll('input.num-input');
            let idx = 0;
            visibles.forEach(inp => {
                const v = parseInt(inp.value, 10);
                if (!isNaN(v) && v >= 1 && v <= 56 && hiddens[idx]) {
                    hiddens[idx].value = v;
                    idx++;
                }
            });
        });
    }

    // ── Generador por Múltiplos ──────────────────────────────────────────
    // Tabla de múltiplos en cada rango para guía en tiempo real
    function contarMultiplos(digito, maxN) {
        let c = 0;
        for (let n = digito; n <= maxN; n += digito) c++;
        return c;
    }

    const digitoInput = document.getElementById('m_digito');
    const juegoSelect = document.querySelector('select[name="m_juego"]');
    const guiaDiv     = document.getElementById('multiplos-guia');
    const seedInput   = document.getElementById('m_seed_input');
    const btnShuffle  = document.getElementById('btn-rerandom');

    // Regenerar: al hacer clic en shuffle asigna una semilla nueva
    if (btnShuffle && seedInput) {
        btnShuffle.addEventListener('click', () => {
            seedInput.value = Math.floor(Math.random() * 2147483647);
        });
    }

    function actualizarGuia() {
        if (!guiaDiv || !digitoInput || !juegoSelect) return;
        const d    = parseInt(digitoInput.value, 10);
        const jg   = juegoSelect.value;
        const maxN = jg === 'revanchita' ? 39 : 56;
        if (isNaN(d) || d < 1 || d > 9) {
            guiaDiv.innerHTML = '';
            return;
        }
        const total = contarMultiplos(d, maxN);
        const msgs = {
            insuf: `<span class="text-danger"><i class="bi bi-x-circle-fill"></i>
                    Solo <strong>${total}</strong> múltiplo(s) en 1–${maxN}.
                    Necesitas al menos 6. Prueba un dígito menor.</span>`,
            unico: `<span class="text-success"><i class="bi bi-gem"></i>
                    <strong>${total}</strong> múltiplos exactos — solo existe una combinación posible.
                    ¡Máxima pureza temática!</span>`,
            bajo:  `<span class="text-info"><i class="bi bi-funnel-fill"></i>
                    <strong>${total}</strong> múltiplos disponibles — combinación muy temática.
                    Se elegirán 6 distribuidos.</span>`,
            medio: `<span class="text-primary"><i class="bi bi-sliders2"></i>
                    <strong>${total}</strong> múltiplos disponibles — buen equilibrio
                    entre temática y variedad.</span>`,
            alto:  `<span class="text-warning"><i class="bi bi-shuffle"></i>
                    <strong>${total}</strong> múltiplos disponibles — mucha variedad,
                    se elige una muestra balanceada en 6 zonas.</span>`,
        };
        let tipo;
        if (total < 6)   tipo = 'insuf';
        else if (total === 6) tipo = 'unico';
        else if (total <= 9)  tipo = 'bajo';
        else if (total <= 14) tipo = 'medio';
        else                  tipo = 'alto';
        guiaDiv.innerHTML = msgs[tipo];
    }

    if (digitoInput) {
        digitoInput.addEventListener('input', actualizarGuia);
        // Disparar al cargar si hay un valor previo (tras POST)
        actualizarGuia();
    }
    if (juegoSelect) {
        juegoSelect.addEventListener('change', actualizarGuia);
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
