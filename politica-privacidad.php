<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'legal';
$page_title = 'Política de privacidad';
$page_desc = 'Política de privacidad de Melate el Chocolate.';
$adsense_script = true;
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Política de Privacidad</h1>
        <p>Este sitio puede recopilar datos técnicos mínimos (como dirección IP, navegador, páginas visitadas y fecha/hora) para fines de seguridad, analítica y mejora del servicio.</p>
        <p>No vendemos datos personales a terceros. Los datos se tratan con fines operativos y estadísticos.</p>
        <p>Si se muestran anuncios de terceros (por ejemplo, Google AdSense), esos proveedores pueden usar cookies para personalizar anuncios y medir rendimiento.</p>
        <p>Puedes gestionar cookies desde la configuración de tu navegador.</p>
        <p class="mb-0">Para dudas sobre privacidad, usa la página de <a href="contacto.php">Contacto</a>.</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
