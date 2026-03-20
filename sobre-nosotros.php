<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'sobre';
$page_title = 'Sobre nosotros';
$page_desc = 'Conoce al creador de Melate el Chocolate y el objetivo estadístico del proyecto.';
$adsense_script = true;
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Sobre nosotros</h1>
        <p>Hola, soy <strong>Mauricio</strong>, desarrollador de este proyecto.</p>
        <p>Melate el Chocolate nació como un portal para centralizar resultados y análisis estadístico recreativo de Melate, Revancha y Revanchita.</p>
        <p>El objetivo del sitio es educativo e informativo: ayudar a entender tendencias históricas, frecuencias y métricas de datos de forma clara.</p>
        <p class="mb-0"><strong>Importante:</strong> este portal no garantiza premios ni resultados y no representa una afiliación oficial con la Lotería Nacional.</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
