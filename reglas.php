<?php
/**
 * Mellatron - Reglas del juego
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$pagina_actual = 'reglas';
$page_title    = 'Reglas del Juego';

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <h2 class="fw-bold mb-1" style="color:var(--ml-verde-oscuro)">
        <i class="bi bi-book-fill"></i> Reglas del Juego
    </h2>
    <p class="text-muted mb-4">
        Todo lo que necesitas saber sobre el Melate, Revancha y Revanchita
        de la Lotería Nacional de México.
    </p>

    <!---->

    <!-- ---- Tabs ---- -->
    <ul class="nav nav-tabs mb-4" id="reglasTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="melate-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-melate"
                    type="button" role="tab">
                🟢 Melate
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="revancha-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-revancha"
                    type="button" role="tab">
                🔵 Revancha
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="revanchita-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-revanchita"
                    type="button" role="tab">
                🟣 Revanchita
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold" id="costos-tab"
                    data-bs-toggle="tab" data-bs-target="#tab-costos"
                    type="button" role="tab">
                💰 Costos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reglasTabContent">

        <!-- ========== MELATE ========== -->
        <div class="tab-pane fade show active" id="tab-melate" role="tabpanel">
            <div class="row g-4">

                <div class="col-md-7">
                    <div class="stat-card">
                        <h5>¿Qué es el Melate?</h5>
                        <p>
                            <strong>Melate</strong> es el juego más popular de la Lotería Nacional.
                            Debes elegir <strong>6 números del 1 al 56</strong>. La máquina extrae
                            aleatoriamente <strong>7 bolas</strong>: las primeras 6 son los
                            <em>números naturales</em> y la séptima es el <em>número adicional</em>.
                        </p>
                        <p>Los sorteos se realizan los <strong>miércoles, viernes y domingos</strong>
                            a las 21:00 hrs.</p>
                        <p>Bolsa Mínima Garantizada: <span class="badge-bolsa">$30 millones</span></p>

                        <h6 class="mt-3 fw-bold">Formas de participar:</h6>
                        <ol>
                            <li><strong>Números propios</strong> – Eliges tú mismo tu combinación.</li>
                            <li><strong>Melático</strong> – El sistema elige al azar por ti.</li>
                            <li><strong>Al Azar</strong> – Similar al Melático pero puedes revisarlos antes de pagar.</li>
                        </ol>

                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-lightbulb-fill"></i>
                            Puedes jugar combinaciones de <strong>7, 8, 9 ó 10 números</strong>
                            (el precio aumenta según combinaciones posibles).
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="stat-card">
                        <h5>Niveles de Premiación</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-success">
                                    <tr>
                                        <th>Lugar</th>
                                        <th>Aciertos</th>
                                        <th>Premio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-warning">
                                        <td><strong>1°</strong></td>
                                        <td>6 naturales</td>
                                        <td>Variable 🏆</td>
                                    </tr>
                                    <tr>
                                        <td><strong>2°</strong></td>
                                        <td>5 nat. + adicional</td>
                                        <td>Variable</td>
                                    </tr>
                                    <tr>
                                        <td><strong>3°</strong></td>
                                        <td>5 naturales</td>
                                        <td>Variable</td>
                                    </tr>
                                    <tr>
                                        <td><strong>4°</strong></td>
                                        <td>4 nat. + adicional</td>
                                        <td>Variable</td>
                                    </tr>
                                    <tr>
                                        <td><strong>5°</strong></td>
                                        <td>4 naturales</td>
                                        <td>Variable</td>
                                    </tr>
                                    <tr>
                                        <td><strong>6°</strong></td>
                                        <td>3 nat. + adicional</td>
                                        <td>$161.29</td>
                                    </tr>
                                    <tr>
                                        <td><strong>7°</strong></td>
                                        <td>3 naturales</td>
                                        <td>$43.01</td>
                                    </tr>
                                    <tr>
                                        <td><strong>8°</strong></td>
                                        <td>2 nat. + adicional</td>
                                        <td>$32.25</td>
                                    </tr>
                                    <tr>
                                        <td><strong>9°</strong></td>
                                        <td>2 naturales</td>
                                        <td>$26.88</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Probabilidades -->
                <div class="col-12">
                    <div class="stat-card">
                        <h5><i class="bi bi-percent me-1"></i> Probabilidades del Melate</h5>
                        <p class="text-muted small">
                            Combinaciones posibles de 6 números en un universo de 56 =
                            <strong>C(56,6) = 32,468,436</strong>
                        </p>
                        <div class="row g-2">
                            <?php
                            $probs = [
                                ['1er lugar (6 nat.)',           '1 en 32,468,436',  'danger'],
                                ['2do lugar (5+adicional)',      '1 en 5,411,406',   'warning'],
                                ['3er lugar (5 nat.)',           '1 en 101,270',     'info'],
                                ['4to lugar (4+adicional)',      '1 en 22,453',      'primary'],
                                ['5to lugar (4 nat.)',           '1 en 895',         'success'],
                                ['6to lugar (3+adicional)',      '1 en 416',         'secondary'],
                                ['7mo lugar (3 nat.)',           '1 en 47',          'secondary'],
                                ['8vo lugar (2+adicional)',      '1 en 64',          'secondary'],
                                ['9no lugar (2 nat.)',           '1 en 7.2',         'secondary'],
                            ];
                            foreach ($probs as [$lugar, $prob, $color]): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="border rounded p-2 text-center">
                                        <div class="small fw-bold text-<?= $color ?>"><?= $lugar ?></div>
                                        <div class="fw-bold"><?= $prob ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Reflexión:</strong> La probabilidad de ganar el 1er lugar es
                            equivalente a tirar un dado y sacar el mismo número
                            <strong>14 veces seguidas</strong>. ¡Juega por diversión!
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========== REVANCHA ========== -->
        <div class="tab-pane fade" id="tab-revancha" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="stat-card">
                        <h5>¿Qué es la Revancha?</h5>
                        <p>
                            La <strong>Revancha</strong> es tu segunda oportunidad de ganar
                            usando <strong>la misma combinación</strong> que elegiste para el Melate.
                            La urna sortea <strong>6 bolas</strong> (sin número adicional).
                        </p>
                        <p>
                            Para participar en Revancha, <strong>debes participar primero en Melate</strong>.
                        </p>
                        <p>Bolsa Mínima Garantizada: <span class="badge-bolsa">$20 millones</span></p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="stat-card">
                        <h5>Premiación Revancha</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-primary">
                                    <tr><th>Lugar</th><th>Aciertos</th><th>Premio</th></tr>
                                </thead>
                                <tbody>
                                    <tr class="table-warning">
                                        <td><strong>1°</strong></td>
                                        <td>6 naturales</td>
                                        <td>Variable 🏆</td>
                                    </tr>
                                    <tr>
                                        <td><strong>2°</strong></td>
                                        <td>5 naturales</td>
                                        <td>Variable</td>
                                    </tr>
                                    <tr>
                                        <td><strong>3°</strong></td>
                                        <td>4 naturales</td>
                                        <td>Variable</td>
                                    </tr>
                                    <tr>
                                        <td><strong>4°</strong></td>
                                        <td>3 naturales</td>
                                        <td>$26.80</td>
                                    </tr>
                                    <tr>
                                        <td><strong>5°</strong></td>
                                        <td>2 naturales</td>
                                        <td>$10.75</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== REVANCHITA ========== -->
        <div class="tab-pane fade" id="tab-revanchita" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="stat-card">
                        <h5>¿Qué es la Revanchita?</h5>
                        <p>
                            La <strong>Revanchita</strong> es tu <em>tercera y última</em>
                            oportunidad, también con la misma combinación del Melate.
                            Se sortean <strong>6 bolas</strong> (sin adicional).
                        </p>
                        <p>Bolsa Mínima Garantizada: <span class="badge-bolsa">$10 millones</span></p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            En Revanchita solo existe <strong>UN nivel de premiación</strong>:
                            acertar los 6 números.
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="stat-card">
                        <h5>Premiación Revanchita</h5>
                        <table class="table table-sm table-striped">
                            <thead class="table-purple" style="background:#7b1fa2;color:#fff">
                                <tr><th>Lugar</th><th>Aciertos</th><th>Premio</th></tr>
                            </thead>
                            <tbody>
                                <tr class="table-warning">
                                    <td><strong>1°</strong></td>
                                    <td>6 naturales</td>
                                    <td>Variable 🏆</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== COSTOS ========== -->
        <div class="tab-pane fade" id="tab-costos" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <h5>Precios por boleto</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center">
                                <thead class="table-success">
                                    <tr>
                                        <th>Modalidad</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Solo Melate (6 núm.)</td>
                                        <td><strong>$15.00</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Melate + Revancha</td>
                                        <td><strong>$25.00</strong></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td>Melate + Revancha + Revanchita</td>
                                        <td><strong>$30.00</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="stat-card">
                        <h5>Combinaciones múltiples</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center">
                                <thead class="table-success">
                                    <tr>
                                        <th>Números</th>
                                        <th>Combinaciones</th>
                                        <th>Solo Melate</th>
                                        <th>+ Revancha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>6</td><td>1</td><td>$15</td><td>$25</td></tr>
                                    <tr><td>7</td><td>7</td><td>$105</td><td>$175</td></tr>
                                    <tr><td>8</td><td>28</td><td>$420</td><td>$700</td></tr>
                                    <tr><td>9</td><td>84</td><td>$1,260</td><td>$2,100</td></tr>
                                    <tr><td>10</td><td>210</td><td>$3,150</td><td>$5,250</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="stat-card">
                        <h5>¿Cómo cobrar tus premios?</h5>
                        <ul>
                            <li>Tienes <strong>60 días naturales</strong> para cobrar desde el sorteo.</li>
                            <li>Hasta <strong>$9,999.99</strong> en agencias de venta directa o expendios.</li>
                            <li>Desde <strong>$3,000</strong> en bancos BBVA, Santander o Scotiabank.</li>
                            <li>Cualquier monto en las oficinas de Lotería Nacional:
                                <em>Av. Insurgentes Sur 1397, Col. Insurgentes Mixcoac, CDMX</em>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->

    <!---->

    <!-- CTA -->
    <div class="text-center">
        <a href="<?= APP_URL ?>/estadisticas" class="btn btn-success btn-lg me-2">
            <i class="bi bi-bar-chart-fill"></i> Ver Estadísticas
        </a>
        <a href="<?= APP_URL ?>/predicciones" class="btn btn-outline-success btn-lg">
            <i class="bi bi-stars"></i> Herramientas de Predicción
        </a>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
