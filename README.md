# Mellatron 🍀

Sitio de estadísticas y predicciones para los sorteos **Melate**, **Revancha** y **Revanchita** de la Lotería Nacional de México.

Construido con **PHP 8**, **MariaDB**, **Bootstrap 5** y **Chart.js**.

---

## Características

- Últimos resultados de los tres juegos en tiempo real
- Estadísticas completas: números calientes/fríos, mapa de calor, pares, retardo, distribución de sumas
- Herramientas de predicción: sugerencia estadística, Melático, signos zodiacales, numerología, verificador de boletos
- Historial paginado y buscador por número de concurso
- Reglas y tablas de premios oficiales
- Espacios listos para Google AdSense
- Diseño responsivo (móvil y escritorio) con tema verde

---

## Requisitos del servidor

| Componente | Versión mínima |
|---|---|
| PHP | 8.0 o superior |
| MariaDB / MySQL | 10.4 o superior |
| Servidor web | Apache 2.4 / Nginx / XAMPP / Laragon |
| Extensiones PHP | `pdo`, `pdo_mysql`, `mbstring`, `intl` |

---

## Instalación paso a paso

### 1. Copiar los archivos al servidor

Coloca la carpeta del proyecto dentro del directorio raíz de tu servidor web.

**XAMPP (Windows)**
```
C:\xampp\htdocs\mellatron\
```

**Laragon (Windows)**
```
C:\laragon\www\mellatron\
```

**Apache Linux**
```
/var/www/html/mellatron/
```

---

### 2. Crear la base de datos

Abre **phpMyAdmin**, **MySQL Workbench** o la línea de comandos y ejecuta el script de instalación:

```bash
mysql -u root -p < install/schema.sql
```

O bien, abre el archivo [install/schema.sql](install/schema.sql) en phpMyAdmin y haz clic en **Importar**.

Esto creará la base de datos `mellatron` y las tres tablas:
- `sorteos_melate`
- `sorteos_revancha`
- `sorteos_revanchita`

---

### 3. Configurar la conexión a la base de datos

Edita el archivo [config/database.php](config/database.php) con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'mellatron');
define('DB_USER', 'root');        // ← cambia esto
define('DB_PASS', '');            // ← cambia esto
```

Si el sitio estará en un subdirectorio diferente a `/mellatron/`, actualiza también:

```php
define('APP_URL', 'http://localhost/mellatron');
```

---

### 4. Importar los datos históricos

Coloca los archivos CSV dentro de la carpeta `sources/`:
```
sources/
├── melate.csv
├── revancha.csv
└── revanchita.csv
```

Luego ejecuta el script de importación desde la **línea de comandos**:

```bash
php install/import.php
```

**Salida esperada:**
```
=== Importando Melate ===
  ✓ 4185 registros insertados correctamente.

=== Importando Revancha ===
  ✓ 3177 registros insertados correctamente.

=== Importando Revanchita ===
  ✓ 1815 registros insertados correctamente.
```

> **Nota de seguridad:** Una vez completada la importación, elimina o protege la carpeta `install/` para evitar exponer el script en producción.

---

### 5. Abrir el sitio

Navega a la dirección del sitio en tu navegador:

```
http://localhost/mellatron/
```

---

## Actualización semanal de datos

La Lotería Nacional publica los resultados en formato CSV en su sitio oficial. Para mantener el sitio actualizado:

1. Descarga los nuevos archivos CSV desde:
   - [https://pronosticos.gob.mx](https://pronosticos.gob.mx) → sección Resultados / Descargas

2. Reemplaza los archivos en la carpeta `sources/` (o agrega solo las filas nuevas al final de cada CSV).

3. Vuelve a ejecutar el script de importación:
   ```bash
   php install/import.php
   ```
   El script usa `INSERT IGNORE` — los concursos ya existentes no se duplicarán.

---

## Importación remota desde Admin (URLs oficiales)

En el panel admin ahora existe la sección **Fuentes & Cron**:

1. Ve a `/admin/fuentes.php`
2. Configura las URLs de históricos para:
  - Melate
  - Revancha
  - Revanchita
3. Guarda URLs.
4. Usa **Importar todo ahora** o botones por juego.

La importación remota valida:
- que la respuesta sea CSV y no HTML,
- que existan columnas esperadas (`NPRODUCTO`, `CONCURSO`),
- que el `NPRODUCTO` corresponda al juego (40, 41, 34).

Si la URL cambia o deja de entregar CSV, el sistema registra error en el log.

---

## Cron configurable (Plesk / SSH)

Puedes ejecutar el importador remoto por cron con:

```bash
php cron/import_remote.php
```

En **Fuentes & Cron** puedes definir:
- Activar/desactivar cron lógico.
- Intervalo mínimo en minutos (anti-ejecución excesiva).

Si Plesk lo ejecuta más seguido del intervalo configurado, el script se salta automáticamente.

### Ejemplo en Plesk

- Tipo: **Run a PHP script** o **Comando Bash**
- Comando sugerido:
  ```bash
  php /ruta/absoluta/mellatron/cron/import_remote.php
  ```
- Frecuencia: cada 6h o diaria (según tu operación)

---

## Logs de importación y cron

Se guardan en dos lugares:

- Tabla `import_logs` (visible en Admin → Fuentes & Cron)
- Archivo `logs/import-cron.log`

Esto permite auditar errores de descarga, validación CSV, importación y ejecuciones de cron.

---

## Integrar Google AdSense

El sitio incluye espacios publicitarios listos para AdSense. Busca los bloques con clase `.ad-slot` en los archivos PHP:

```html
<div class="ad-slot text-center">
    📢 Espacio publicitario - Google AdSense
</div>
```

Reemplaza el contenido de esos `div` con tu código de AdSense:

```html
<div class="ad-slot text-center">
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-XXXXXXXXXXXXXXXX"
         data-ad-slot="XXXXXXXXXX"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
</div>
```

Los espacios se encuentran en:
- [index.php](index.php) — 2 slots (hero y sección lateral)
- [estadisticas.php](estadisticas.php) — 1 slot superior
- [predicciones.php](predicciones.php) — 1 slot lateral
- [historial.php](historial.php) — 1 slot superior

---

## Estructura del proyecto

```
mellatron/
├── config/
│   └── database.php          # Credenciales y constantes de la app
├── install/
│   ├── schema.sql             # Script SQL para crear la BD
│   └── import.php             # Script CLI de importación de CSVs
├── src/
│   ├── Database.php           # Singleton PDO
│   ├── MelateRepository.php   # Todas las consultas a la BD
│   ├── StatsCalculator.php    # Cálculos estadísticos
│   └── ZodiacHelper.php       # Signos zodiacales y numerología
├── public/
│   ├── css/
│   │   └── style.css          # Tema verde personalizado
│   └── js/
│       └── app.js             # Interactividad del cliente
├── includes/
│   ├── header.php             # <head> + navbar
│   ├── footer.php             # Footer + CDN scripts
│   └── helpers.php            # Funciones de renderizado compartidas
├── sources/
│   ├── melate.csv             # Datos históricos Melate
│   ├── revancha.csv           # Datos históricos Revancha
│   └── revanchita.csv         # Datos históricos Revanchita
├── index.php                  # Página principal
├── estadisticas.php           # Estadísticas y gráficas
├── predicciones.php           # Herramientas de predicción
├── historial.php              # Historial paginado
├── reglas.php                 # Reglas y tablas de premios
└── README.md                  # Este archivo
```

---

## Dependencias (todas via CDN)

| Librería | Versión | Uso |
|---|---|---|
| Bootstrap | 5.3.3 | Layout y componentes UI |
| Bootstrap Icons | 1.11.3 | Iconografía |
| Chart.js | 4.4.3 | Gráficas de estadísticas |

No se requiere `npm` ni `composer` — todas las dependencias se cargan desde CDN.

---

## Solución de problemas

### Error de conexión a la base de datos
- Verifica que MariaDB/MySQL esté corriendo
- Confirma usuario y contraseña en `config/database.php`
- Asegúrate de que el usuario tenga permisos en la BD `mellatron`

### Página en blanco o error 500
- Activa la visualización de errores en PHP temporalmente:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```
- Revisa el log de errores de PHP y Apache/Nginx

### El script de importación termina sin datos
- Verifica que los CSV existan en `sources/`
- Comprueba que la primera fila sea el encabezado (`NPRODUCTO,CONCURSO,R1,...`)
- Ejecuta el script con `php -f install/import.php` para ver mensajes detallados

### Las gráficas no se muestran
- Verifica la consola del navegador (F12) por errores de JavaScript
- Confirma que el CDN de Chart.js sea accesible (requiere internet)

---

## Licencia

Proyecto personal de uso educativo. Los datos de los sorteos son propiedad de **Pronósticos para la Asistencia Pública (Pronosticos.gob.mx)**.

Los resultados y estadísticas son solo para entretenimiento. **Juega responsablemente.**
