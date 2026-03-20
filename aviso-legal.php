<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'legal';
$page_title = 'Aviso legal';
$page_desc = 'Aviso legal y descargo de responsabilidad de Melate el Chocolate.';
$adsense_script = true;
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Aviso Legal</h1>
        <p><strong>Este sitio no está afiliado, asociado ni respaldado por la Lotería Nacional de México.</strong></p>
        <p>La marca, nombres de juegos y resultados oficiales pertenecen a sus respectivos titulares.</p>
        <p><strong>No existe garantía de ganar</strong> al usar estadísticas, predicciones o combinaciones sugeridas en este portal.</p>
        <p>Todo contenido se publica con fines informativos, educativos y recreativos.</p>
        <p class="mb-0"><strong>Juega con responsabilidad.</strong> Prohibido para menores de edad.</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
