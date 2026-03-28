<?php
/**
 * Migración: agrega columnas views y likes a blog_posts
 * Ejecutar una sola vez desde el navegador o CLI.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getConnection();
$results = [];

// views
$col = $db->query("SHOW COLUMNS FROM blog_posts LIKE 'views'")->fetchAll();
if (empty($col)) {
    $db->exec("ALTER TABLE blog_posts ADD COLUMN views INT UNSIGNED NOT NULL DEFAULT 0 AFTER updated_at");
    $results[] = 'OK: columna views agregada.';
} else {
    $results[] = 'INFO: columna views ya existe.';
}

// likes
$col = $db->query("SHOW COLUMNS FROM blog_posts LIKE 'likes'")->fetchAll();
if (empty($col)) {
    $db->exec("ALTER TABLE blog_posts ADD COLUMN likes INT UNSIGNED NOT NULL DEFAULT 0 AFTER views");
    $results[] = 'OK: columna likes agregada.';
} else {
    $results[] = 'INFO: columna likes ya existe.';
}

// Tabla de likes por sesión (evita dobles likes)
$db->exec("CREATE TABLE IF NOT EXISTS blog_post_likes (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id    INT UNSIGNED NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_post_session (post_id, session_id),
    INDEX idx_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$results[] = 'OK: tabla blog_post_likes verificada/creada.';

header('Content-Type: text/plain; charset=utf-8');
echo implode("\n", $results) . "\nMigración completada.\n";
