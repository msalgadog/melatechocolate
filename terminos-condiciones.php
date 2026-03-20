<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'legal';
$page_title = 'Términos y condiciones';
$page_desc = 'Términos y condiciones de uso de Melate el Chocolate.';
$adsense_script = true;
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Términos y Condiciones</h1>
        <p>Al usar este sitio aceptas su uso exclusivamente informativo y recreativo.</p>
        <p>La información publicada se ofrece "tal cual" y puede contener retrasos, errores u omisiones involuntarias.</p>
        <p>El usuario es responsable de cualquier decisión que tome con base en la información del portal.</p>
        <p>Queda prohibido usar este sitio para actividades ilícitas o para difundir contenido engañoso.</p>
        <p class="mb-0">Nos reservamos el derecho de actualizar estos términos en cualquier momento.</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
