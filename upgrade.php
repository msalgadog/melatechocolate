<?php
/**
 * upgrade.php — Script de migración/actualización de base de datos
 *
 * Ejecutar UNA VEZ tras actualizar la aplicación en producción.
 * Eliminar o proteger con contraseña después de usarlo.
 *
 * Uso web:  https://tudominio.com/upgrade.php?token=CAMBIA_ESTE_TOKEN
 * Uso CLI:  php upgrade.php
 */

// ── Protección mínima ────────────────────────────────────────────────────────
define('UPGRADE_TOKEN', 'CAMBIA_ESTE_TOKEN');   // Cambia antes de subir

$running_cli = (PHP_SAPI === 'cli');

if (!$running_cli) {
    $provided = trim((string)($_GET['token'] ?? ''));
    if (!hash_equals(UPGRADE_TOKEN, $provided)) {
        http_response_code(403);
        exit('Acceso denegado. Proporciona ?token=... correcto.');
    }
}

// ── Bootstrap ────────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';

$pdo = Database::getConnection();

// Configurar errores para mostrarlos en el script
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ── Helpers de salida ────────────────────────────────────────────────────────
function out(string $msg, string $type = 'info'): void
{
    global $running_cli;
    $icons = ['ok' => '✅', 'skip' => '⏭️ ', 'info' => 'ℹ️ ', 'error' => '❌', 'title' => '🚀'];
    $icon  = $icons[$type] ?? 'ℹ️ ';

    if ($running_cli) {
        echo $icon . ' ' . $msg . PHP_EOL;
    } else {
        $style = ['ok'=>'green','skip'=>'gray','info'=>'#555','error'=>'red','title'=>'#3D2817'];
        $s = $style[$type] ?? '#333';
        echo '<p style="margin:.25rem 0;color:' . $s . '"><b>' . htmlspecialchars($icon) . '</b> ' . htmlspecialchars($msg) . '</p>';
    }
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $rows = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->fetchAll();
    return !empty($rows);
}

function tableExists(PDO $pdo, string $table): bool
{
    $rows = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetchAll();
    return !empty($rows);
}

// ── Inicio de salida HTML ────────────────────────────────────────────────────
if (!$running_cli) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
    echo '<title>Mellatron — Upgrade</title>';
    echo '<style>body{font-family:monospace;max-width:760px;margin:2rem auto;padding:1rem;background:#faf7f2;} h1{color:#3D2817;}</style>';
    echo '</head><body>';
    echo '<h1>Mellatron — Actualización de base de datos</h1>';
    echo '<p style="color:#888;font-size:.85rem;">'.date('Y-m-d H:i:s').' · PHP '.PHP_VERSION.'</p><hr>';
}

out('Iniciando upgrade…', 'title');

// ════════════════════════════════════════════════════════════════════════════
// PASO 1 — Tabla blog_posts
// ════════════════════════════════════════════════════════════════════════════
out('── PASO 1: tabla blog_posts', 'info');

if (!tableExists($pdo, 'blog_posts')) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slug         VARCHAR(180) NOT NULL UNIQUE,
        title        VARCHAR(255) NOT NULL,
        excerpt      TEXT NOT NULL,
        image_url    VARCHAR(500) NOT NULL DEFAULT '',
        content      LONGTEXT NOT NULL,
        status       ENUM('draft','published') NOT NULL DEFAULT 'published',
        published_at DATETIME NULL,
        created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status_published (status, published_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    out('blog_posts creada.', 'ok');
} else {
    out('blog_posts ya existe.', 'skip');

    // Migración: columna image_url
    if (!columnExists($pdo, 'blog_posts', 'image_url')) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN image_url VARCHAR(500) NOT NULL DEFAULT '' AFTER excerpt");
        out('Columna image_url agregada a blog_posts.', 'ok');
    } else {
        out('Columna image_url ya existe en blog_posts.', 'skip');
    }

    // Migración: columna updated_at
    if (!columnExists($pdo, 'blog_posts', 'updated_at')) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        out('Columna updated_at agregada a blog_posts.', 'ok');
    } else {
        out('Columna updated_at ya existe en blog_posts.', 'skip');
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PASO 2 — Tabla app_settings
// ════════════════════════════════════════════════════════════════════════════
out('── PASO 2: tabla app_settings', 'info');

if (!tableExists($pdo, 'app_settings')) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key   VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    out('app_settings creada.', 'ok');
} else {
    out('app_settings ya existe.', 'skip');
}

// ════════════════════════════════════════════════════════════════════════════
// PASO 3 — Tabla import_logs
// ════════════════════════════════════════════════════════════════════════════
out('── PASO 3: tabla import_logs', 'info');

if (!tableExists($pdo, 'import_logs')) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS import_logs (
        id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        log_type     VARCHAR(40)  NOT NULL,
        status       VARCHAR(20)  NOT NULL,
        juego        VARCHAR(20)  NOT NULL DEFAULT 'all',
        message      VARCHAR(255) NOT NULL,
        context_json LONGTEXT NULL,
        created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at),
        INDEX idx_log_type   (log_type),
        INDEX idx_status     (status),
        INDEX idx_juego      (juego)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    out('import_logs creada.', 'ok');
} else {
    out('import_logs ya existe.', 'skip');
}

// ════════════════════════════════════════════════════════════════════════════
// PASO 4 — Índices adicionales en sorteos (seguro si ya existen)
// ════════════════════════════════════════════════════════════════════════════
out('── PASO 4: índices auxiliares', 'info');

$indexChecks = [
    ['sorteos_melate',      'idx_fecha',    'fecha'],
    ['sorteos_revancha',    'idx_fecha',    'fecha'],
    ['sorteos_revanchita',  'idx_fecha',    'fecha'],
];

foreach ($indexChecks as $idxEntry) {
    list($table, $idxName, $col) = $idxEntry;
    if (!tableExists($pdo, $table)) {
        out("Tabla {$table} no existe, saltando índice.", 'skip');
        continue;
    }
    $rows = $pdo->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$idxName}'")->fetchAll();
    if (empty($rows)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD INDEX `{$idxName}` (`{$col}`)");
        out("Índice {$idxName} agregado a {$table}.", 'ok');
    } else {
        out("Índice {$idxName} en {$table} ya existe.", 'skip');
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PASO 5 — Sembrar artículos canónicos del blog
// ════════════════════════════════════════════════════════════════════════════
out('── PASO 5: artículos canónicos del blog', 'info');

require_once __DIR__ . '/src/ContentRepository.php';
$repo = new ContentRepository();
$repo->ensureSeedPosts();
out('Artículos canónicos sincronizados (upsert).', 'ok');

// ════════════════════════════════════════════════════════════════════════════
// FIN
// ════════════════════════════════════════════════════════════════════════════
out('Upgrade completado sin errores.', 'title');

if (!$running_cli) {
    echo '<hr><p style="color:#888;font-size:.82rem;">⚠️ <strong>Elimina o protege este archivo</strong> después de ejecutarlo.</p>';
    echo '</body></html>';
}
