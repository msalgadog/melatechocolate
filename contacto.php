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
        <p>¿Tienes comentarios, correcciones o sugerencias?</p>

        <p>Tu opinión es fundamental para que este proyecto siga creciendo. Si tienes alguna duda, comentario o sugerencia para mejorar el portal, no dudes en escribirme.</p>

        <p><strong>Sitio web:</strong> <a href="https://melatechocolate.online" target="_blank" rel="noopener noreferrer">melatechocolate.online</a></p>
        <p><strong>Correo electrónico:</strong> <a href="mailto:contacto@melatechocolate.online">contacto@melatechocolate.online</a></p>

        <p>Gracias por ser parte de esta comunidad de entusiastas de los datos.</p>

        <p class="mb-0"><strong>Mauricio</strong><br>Desarrollador de Melate el Chocolate</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
