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
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Sobre nosotros: La Ciencia y Pasión tras "Melate el Chocolate"</h1>

        <p>¡Hola! Soy Mauricio, desarrollador y entusiasta del análisis de datos. Bienvenidos a Melate el Chocolate, un proyecto que nació de una curiosidad personal y se transformó en una plataforma dedicada a desmenuzar la complejidad estadística de los sorteos más queridos de México.</p>

        <h2 class="h5 mt-4">Nuestra Historia: De una Hoja de Cálculo a un Portal de Análisis</h2>
        <p>Todo comenzó con una pregunta sencilla: ¿Existe algún patrón visual en los resultados históricos del Melate? Lo que empezó como un pequeño archivo personal de Excel para organizar resultados de Melate, Revancha y Revanchita, pronto evolucionó.</p>
        <p>Me di cuenta de que, aunque los resultados son públicos, la forma de consultarlos suele ser plana y poco amigable para quienes disfrutan del análisis profundo. Así nació Melate el Chocolate: con la idea de reunir en un solo lugar no solo los números ganadores, sino una interpretación visual y métrica que facilite el estudio recreativo de la probabilidad.</p>

        <h2 class="h5 mt-4">Nuestra Misión: Democratizar el Análisis de Datos</h2>
        <p>En Melate el Chocolate, nuestra misión es transformar la consulta de resultados en una experiencia educativa y entretenida. Creemos que la estadística no tiene por qué ser aburrida ni exclusiva para matemáticos.</p>
        <p>Buscamos:</p>
        <ul>
            <li><strong>Centralizar la Información:</strong> Ofrecer históricos actualizados y validados de fuentes oficiales.</li>
            <li><strong>Fomentar la Cultura Numérica:</strong> Mostrar frecuencias, tendencias y métricas (como la paridad y la distribución de sumas) de manera clara y accesible.</li>
            <li><strong>Promover el Ocio Inteligente:</strong> Brindar herramientas que permitan a los usuarios tomar decisiones basadas en datos históricos, alejándose de los sesgos comunes y el azar ciego.</li>
        </ul>

        <h2 class="h5 mt-4">¿Por qué "El Chocolate"?</h2>
        <p>En México, decir que algo está "de chocolate" a veces implica que es algo sencillo o por pura diversión. Ese es el espíritu de este sitio: queremos que el análisis de datos sea algo disfrutable, "dulce" y sin las complicaciones técnicas que suelen alejar a las personas de la estadística. Aquí, el rigor de los datos se encuentra con la ligereza del entretenimiento.</p>

        <h2 class="h5 mt-4">Compromiso con la Transparencia y la Independencia</h2>
        <p>Es fundamental para nosotros establecer una relación de confianza con nuestra comunidad. Por ello, mantenemos una política de transparencia total:</p>
        <ul>
            <li><strong>Independencia:</strong> Este portal es un proyecto independiente. No tenemos relación, afiliación ni respaldo oficial de la Lotería Nacional de México.</li>
            <li><strong>Rigor Informativo:</strong> Los datos se procesan mediante algoritmos de validación para asegurar que la información que consultas sea fiel a los registros oficiales.</li>
            <li><strong>Expectativas Realistas:</strong> En este sitio no garantizamos premios ni resultados. La lotería es, por definición, un evento aleatorio. Nuestro papel es mostrarte lo que ha pasado, no asegurarte lo que pasará.</li>
        </ul>

        <h2 class="h5 mt-4">Juega con Responsabilidad</h2>
        <p>Como desarrollador de este proyecto, mi mayor interés es que disfrutes del proceso de análisis. Te invito a usar nuestras herramientas para ejercitar tu mente, establecer tus propias rutinas de estudio y, sobre todo, a jugar siempre con responsabilidad, respetando tus límites y viendo el sorteo como lo que es: una forma de esparcimiento.</p>

        <p>Gracias por ser parte de esta comunidad de entusiastas de los datos.</p>
        <p><strong>Mauricio</strong><br>Desarrollador de Melate el Chocolate</p>

        <p class="mb-0">¿Quieres saber más sobre cómo manejamos la información? Visita nuestro <a href="<?= APP_URL ?>/aviso-legal">Aviso Legal</a> y nuestra <a href="<?= APP_URL ?>/blog/metodologia-de-datos-en-mellatron">Metodología de Datos</a>.</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
