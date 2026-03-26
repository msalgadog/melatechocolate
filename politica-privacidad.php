<?php
require_once __DIR__ . '/config/database.php';
$pagina_actual = 'legal';
$page_title = 'Política de Privacidad y Cookies';
$page_desc = 'Política de Privacidad y Cookies de Melate el Chocolate.';
$adsense_script = true;
$fecha_actual = date('d/m/Y');
include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="stat-card">
        <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Política de Privacidad y Cookies</h1>

        <p>En Melate el Chocolate (melatechocolate.online), la privacidad de nuestros visitantes es de extrema importancia. Este documento detalla los tipos de información personal que se reciben y recopilan, y cómo se utilizan.</p>
        <p>Esta política está diseñada para cumplir con los estándares de transparencia exigidos por proveedores de publicidad de terceros, incluyendo Google AdSense.</p>

        <h2 class="h5 mt-4">1. Recopilación de Información Técnica (Archivos de Registro)</h2>
        <p>Al igual que muchos otros sitios web, nuestro portal utiliza archivos de registro (log files). La información dentro de estos archivos incluye:</p>
        <ul>
            <li>Direcciones de protocolo de Internet (IP).</li>
            <li>Tipo de navegador.</li>
            <li>Proveedor de servicios de Internet (ISP).</li>
            <li>Sello de fecha y hora.</li>
            <li>Páginas de referencia y salida.</li>
            <li>Número de clics para analizar tendencias y administrar el sitio.</li>
        </ul>
        <p><strong>Nota:</strong> Esta información no está vinculada a ninguna información que sea personalmente identificable. Se utiliza exclusivamente para fines de seguridad, analítica y mejora de la experiencia del usuario.</p>

        <h2 class="h5 mt-4">2. Cookies y Web Beacons</h2>
        <p>Utilizamos cookies para almacenar información sobre las preferencias de los visitantes, registrar información específica sobre qué páginas accede o visita el usuario, y personalizar el contenido de nuestra página web según el tipo de navegador u otra información que el visitante envía a través de su navegador.</p>

        <h2 class="h5 mt-4">3. Galleta de Google DoubleClick DART (Requerido por AdSense)</h2>
        <p>Como proveedor de terceros, Google utiliza cookies para publicar anuncios en este sitio.</p>
        <p>El uso de la cookie de DART permite a Google mostrar anuncios a los usuarios basados en su visita a este sitio y a otros sitios en Internet.</p>
        <p>Los usuarios pueden inhabilitar el uso de la cookie de DART a través de la política de anuncios de Google en:
            <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener noreferrer">https://policies.google.com/technologies/ads</a>.
        </p>

        <h2 class="h5 mt-4">4. Socios Publicitarios de Terceros</h2>
        <p>Algunos de nuestros socios publicitarios pueden utilizar cookies y web beacons en nuestro sitio. Nuestros socios publicitarios incluyen a:</p>
        <ul>
            <li>Google AdSense.</li>
        </ul>
        <p>Estos servidores de anuncios de terceros o redes publicitarias utilizan tecnología en los anuncios y enlaces que aparecen en nuestro portal y que se envían directamente a su navegador. Reciben automáticamente su dirección IP cuando esto ocurre.</p>
        <p>Otras tecnologías (como cookies, JavaScript o Web Beacons) también pueden ser utilizadas por las redes publicitarias de terceros para medir la eficacia de sus anuncios y/o para personalizar el contenido publicitario que usted ve.</p>
        <p>Melate el Chocolate no tiene acceso ni control sobre estas cookies que son utilizadas por terceros anunciantes.</p>

        <h2 class="h5 mt-4">5. Gestión de la Privacidad por el Usuario</h2>
        <p>Usted tiene el control total sobre su privacidad:</p>
        <ul>
            <li><strong>Configuración del Navegador:</strong> Puede desactivar o deshabilitar selectivamente nuestras cookies o las cookies de terceros en la configuración de su navegador.</li>
            <li><strong>Inhabilitación de Publicidad:</strong> Puede optar por no recibir publicidad personalizada visitando <a href="https://www.aboutads.info" target="_blank" rel="noopener noreferrer">www.aboutads.info</a>.</li>
        </ul>

        <h2 class="h5 mt-4">6. Uso de la Información y No Divulgación</h2>
        <p>La información recabada directamente por nuestro sitio (datos estadísticos) es utilizada únicamente para mejorar nuestro contenido analítico y educativo.</p>
        <p>No vendemos, alquilamos ni compartimos datos personales con terceros para fines de marketing.</p>
        <p>Los datos se tratan con fines operativos internos y estadísticos de forma agregada.</p>

        <h2 class="h5 mt-4">7. Responsabilidad del Usuario</h2>
        <p>El contenido de este sitio es de carácter informativo y recreativo. Los usuarios son libres de utilizar las herramientas de análisis y consulta bajo su propia responsabilidad. El sitio no se hace responsable de las decisiones tomadas basadas en la información estadística aquí presentada.</p>

        <h2 class="h5 mt-4">8. Consentimiento</h2>
        <p>Al utilizar nuestro sitio web, usted acepta nuestra política de privacidad y acepta sus términos.</p>

        <h2 class="h5 mt-4">9. Contacto</h2>
        <p>Si necesita más información o tiene alguna pregunta sobre nuestra política de privacidad, no dude en ponerse en contacto con nosotros a través de nuestra página de <a href="<?= APP_URL ?>/contacto">Contacto</a>.</p>

        <p class="mb-0"><strong>Última actualización:</strong> <?= $fecha_actual ?></p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
