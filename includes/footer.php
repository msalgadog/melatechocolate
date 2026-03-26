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
                <div class="mt-3">
                    <a href="https://www.buymeacoffee.com/msalgadogonza" target="_blank" rel="noopener noreferrer">
                        <img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" >
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <p class="footer-title">Navegar</p>
                <ul class="list-unstyled small mb-0">
                    <li><a href="<?= APP_URL ?>/"><i class="bi bi-house-door"></i> Inicio</a></li>
                    <li><a href="<?= APP_URL ?>/blog"><i class="bi bi-journal-text"></i> Blog</a></li>
                    <li><a href="<?= APP_URL ?>/estadisticas"><i class="bi bi-bar-chart"></i> Estadísticas</a></li>
                    <li><a href="<?= APP_URL ?>/predicciones"><i class="bi bi-stars"></i> Predicciones</a></li>
                    <li><a href="<?= APP_URL ?>/historial"><i class="bi bi-clock-history"></i> Historial</a></li>
                    <li><a href="<?= APP_URL ?>/reglas"><i class="bi bi-book"></i> Reglas del juego</a></li>
                </ul>
            </div>

            <div class="col-md-4">
                <p class="footer-title">Legales y confianza</p>
                <ul class="list-unstyled small mb-2">
                    <li><a href="<?= APP_URL ?>/sobre-nosotros">Sobre nosotros</a></li>
                    <li><a href="<?= APP_URL ?>/contacto">Contacto</a></li>
                    <li><a href="<?= APP_URL ?>/politica-privacidad">Política de privacidad</a></li>
                    <li><a href="<?= APP_URL ?>/terminos-condiciones">Términos y condiciones</a></li>
                    <li><a href="<?= APP_URL ?>/aviso-legal">Aviso legal</a></li>
                </ul>
                <p class="small mb-0">
                    <strong>No hay garantía de ganar.</strong> Este portal es informativo y recreativo,
                    y <strong>no está afiliado a la Lotería Nacional</strong>.
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App JS -->
<script src="<?= APP_URL ?>/public/js/app.js"></script>
<?php if (!empty($katex_enabled)): ?>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.21/dist/katex.min.js" crossorigin="anonymous"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.21/dist/contrib/auto-render.min.js" crossorigin="anonymous"
    onload="renderMathInElement(document.body,{delimiters:[{left:'$$',right:'$$',display:true}],throwOnError:false});"></script>
<?php endif; ?>
<?php if (isset($page_scripts)): ?>
    <?= $page_scripts ?>
<?php endif; ?>
</body>
</html>
