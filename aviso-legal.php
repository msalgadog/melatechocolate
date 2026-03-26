<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'legal';
$page_title = 'Aviso legal';
$page_desc = 'Aviso legal y descargo de responsabilidad de Melate el Chocolate.';
$adsense_script = true;
$fecha_actual = date('d/m/Y');
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Aviso Legal</h1>

        <p>Este sitio web es un espacio independiente dedicado al análisis estadístico y la cultura numérica. Al acceder a este portal, el usuario acepta los siguientes términos de deslinde de responsabilidad:</p>

        <h2 class="h5 mt-4">1. Independencia Institucional</h2>
        <p>Este sitio no está afiliado, asociado, patrocinado ni respaldado por la Lotería Nacional de México ni por ninguna otra institución gubernamental o comercial relacionada con la organización de sorteos.</p>

        <h2 class="h5 mt-4">2. Propiedad Intelectual</h2>
        <p>Las marcas registradas, nombres de juegos (como Melate, Melate Retro, Revancha y Revanchita), logotipos y resultados oficiales mencionados en este portal son propiedad exclusiva de sus respectivos titulares. Su uso en este sitio es estrictamente informativo, de referencia y bajo el principio de uso legítimo.</p>

        <h2 class="h5 mt-4">3. Ausencia de Garantías</h2>
        <p>El análisis de datos, las métricas de frecuencia, los históricos y las combinaciones sugeridas o validadas en este portal son herramientas de estudio recreativo. No existe garantía de ganar ni de obtener premios mediante el uso de la información aquí presentada. La lotería es un juego de azar puro donde cada sorteo es un evento independiente.</p>

        <h2 class="h5 mt-4">4. Propósito del Contenido</h2>
        <p>Todo el contenido publicado se ofrece con fines exclusivamente informativos, educativos y recreativos. Bajo ninguna circunstancia este portal debe considerarse como una plataforma de apuestas, asesoría financiera o gestoría de premios.</p>

        <h2 class="h5 mt-4">5. Juego Responsable y Restricciones</h2>
        <p><strong>Prohibido para menores de edad:</strong> El contenido de este sitio está dirigido exclusivamente a personas mayores de 18 años.</p>
        <p><strong>Responsabilidad:</strong> El usuario es el único responsable de las decisiones tomadas con base en la información del portal.</p>
        <p><strong>Juego Saludable:</strong> Te instamos a jugar con responsabilidad, estableciendo límites de tiempo y presupuesto. El juego debe ser una forma de entretenimiento, nunca una solución a problemas financieros.</p>

        <p class="mb-0"><strong>Última actualización:</strong> <?= $fecha_actual ?></p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
