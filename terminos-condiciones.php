<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'legal';
$page_title = 'Términos y Condiciones de Uso';
$page_desc = 'Términos y condiciones de uso de Melate el Chocolate.';
$adsense_script = true;
$fecha_actual = date('d/m/Y');
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Términos y Condiciones de Uso</h1>

        <p>En Melate el Chocolate (melatechocolate.online), la transparencia con nuestros visitantes es un pilar fundamental. Este documento regula el acceso, la navegación y el uso de este portal.</p>

        <h2 class="h5 mt-4">TÉRMINOS Y CONDICIONES DE USO</h2>
        <p>Al acceder y utilizar este sitio web, usted acepta de manera íntegra los siguientes términos:</p>

        <h3 class="h6 mt-3">1.1 Naturaleza del Contenido</h3>
        <p>El uso de este sitio es exclusivamente informativo y recreativo. El contenido aquí publicado, incluyendo análisis estadísticos, históricos y métricas, tiene como fin el entretenimiento y la educación en cultura numérica. Este sitio no es una plataforma de apuestas ni gestiona transacciones relacionadas con juegos de azar.</p>

        <h3 class="h6 mt-3">1.2 Exclusión de Garantías</h3>
        <p>La información se ofrece "tal cual" (as is). Aunque nos esforzamos por mantener la base de datos actualizada, el contenido puede contener retrasos, errores tipográficos u omisiones involuntarias derivadas de las fuentes de origen. No garantizamos la exactitud ni la disponibilidad ininterrumpida de los datos.</p>

        <h3 class="h6 mt-3">1.3 Responsabilidad del Usuario</h3>
        <p>Usted, como usuario, es el único responsable de cualquier decisión que tome basada en la información, gráficas o estadísticas publicadas en este portal. El análisis de datos no constituye una asesoría financiera ni una garantía de éxito en sorteos reales.</p>

        <h3 class="h6 mt-3">1.4 Uso Prohibido</h3>
        <p>Queda estrictamente prohibido utilizar este sitio para:</p>
        <ul>
            <li>Realizar actividades ilícitas o contrarias a la buena fe.</li>
            <li>Difundir contenido engañoso, fraudulento o que infrinja derechos de terceros.</li>
            <li>Intentar vulnerar la seguridad del servidor o realizar extracciones masivas de datos (scraping) sin autorización.</li>
        </ul>

        <h3 class="h6 mt-3">1.5 Modificaciones</h3>
        <p>Nos reservamos el derecho de actualizar, modificar o eliminar estos términos en cualquier momento para adaptarlos a novedades legislativas o cambios en el servicio.</p>

        <h3 class="h6 mt-3">1.6 Contacto y Consentimiento</h3>
        <p>Al utilizar nuestro sitio web, usted acepta estos términos. Si tiene dudas sobre este aviso legal, puede contactarnos a través de nuestra página de <a href="<?= APP_URL ?>/contacto">Contacto</a>.</p>

        <p class="mb-0"><strong>Última actualización:</strong> <?= $fecha_actual ?></p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
