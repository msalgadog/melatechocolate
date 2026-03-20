<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'contacto';
$page_title = 'Contacto';
$page_desc = 'Página de contacto de Melate el Chocolate.';
$adsense_script = true;
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Contacto</h1>
        <p>¿Tienes comentarios, correcciones o sugerencias para mejorar el portal?</p>
        <p>Puedes escribir a: <a href="mailto:contacto@mellatron.local">contacto@mellatron.local</a></p>
        <p class="small text-muted mb-0">Recomendación: cambia este correo por tu dirección real de soporte antes de producción.</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
