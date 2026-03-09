<!-- ===== FOOTER ===== -->
<footer>
    <div class="container">
        <div class="row gy-4">

            <div class="col-md-4">
                <p class="footer-title fs-5 mb-2">
                    <i class="bi bi-award-fill"></i> Melate el Chocolate
                </p>
                <p class="small mb-0">
                    Portal de estadísticas, predicciones y resultados históricos
                    del Melate, Revancha y Revanchita de la Lotería Nacional de México.
                </p>
            </div>

            <div class="col-md-4">
                <p class="footer-title">Navegar</p>
                <ul class="list-unstyled small mb-0">
                    <li><a href="<?= APP_URL ?>/index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
                    <li><a href="<?= APP_URL ?>/estadisticas.php"><i class="bi bi-bar-chart"></i> Estadísticas</a></li>
                    <li><a href="<?= APP_URL ?>/predicciones.php"><i class="bi bi-stars"></i> Predicciones</a></li>
                    <li><a href="<?= APP_URL ?>/historial.php"><i class="bi bi-clock-history"></i> Historial</a></li>
                    <li><a href="<?= APP_URL ?>/reglas.php"><i class="bi bi-book"></i> Reglas del juego</a></li>
                </ul>
            </div>

            <div class="col-md-4">
                <p class="footer-title">Aviso Legal</p>
                <p class="small mb-0">
                    Este sitio <strong>no está afiliado</strong> a la Lotería Nacional para la
                    Asistencia Pública. Los datos son de carácter público e informativo.
                    <br><br>
                    Recuerda que el juego es <strong>puro azar</strong>. Juega con responsabilidad.
                    Prohibido para menores de edad.
                </p>
            </div>

        </div>
        <hr class="mt-4 mb-3" style="border-color:rgba(255,255,255,.15)">
        <p class="text-center small mb-0" style="color:rgba(255,255,255,.45)">
            &copy; <?= date('Y') ?> Melate el Chocolate &mdash; Datos obtenidos de fuentes públicas de la Lotería Nacional.
            &nbsp;<a href="<?= APP_URL ?>/admin/login.php"
                     style="color:rgba(255,255,255,.2);font-size:.75rem;text-decoration:none"
                     title="Administración">&#9679;</a>
        </p>
    </div>
</footer>
<!-- ===== /FOOTER ===== -->

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

<!-- Chart.js para gráficas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<!-- App JS -->
<script src="<?= APP_URL ?>/public/js/app.js"></script>
<?php if (isset($page_scripts)): ?>
    <?= $page_scripts ?>
<?php endif; ?>
</body>
</html>
