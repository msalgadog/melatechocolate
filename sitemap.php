<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=utf-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$appUrl = (string)(APP_URL ?? '');

if (preg_match('#^https?://#i', $appUrl) === 1) {
    $baseUrl = rtrim($appUrl, '/');
} else {
    $baseUrl = $scheme . '://' . $host . rtrim($appUrl, '/');
}

function mellatronXmlEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function mellatronIsoDateFromFile($filePath)
{
    if (!is_file($filePath)) {
        return gmdate('c');
    }
    $ts = filemtime($filePath);
    if ($ts === false) {
        return gmdate('c');
    }
    return gmdate('c', $ts);
}

$staticRoutes = [
    ['path' => '/', 'file' => '/index.php', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['path' => '/blog', 'file' => '/blog.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['path' => '/estadisticas/juego/melate', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas/juego/revancha', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas/juego/revanchita', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas#resumen', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas#numero', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas#tendencias', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas#matriz', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas#relaciones', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/estadisticas#combinacion', 'file' => '/estadisticas.php', 'changefreq' => 'daily', 'priority' => '0.9'],
    ['path' => '/historial/juego/melate', 'file' => '/historial.php', 'changefreq' => 'daily', 'priority' => '0.8'],
    ['path' => '/historial/juego/revancha', 'file' => '/historial.php', 'changefreq' => 'daily', 'priority' => '0.8'],
    ['path' => '/historial/juego/revanchita', 'file' => '/historial.php', 'changefreq' => 'daily', 'priority' => '0.8'],
];

$items = [];

foreach ($staticRoutes as $route) {
    $items[] = [
        'loc' => $baseUrl . $route['path'],
        'lastmod' => mellatronIsoDateFromFile(__DIR__ . $route['file']),
        'changefreq' => $route['changefreq'],
        'priority' => $route['priority'],
    ];
}

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->query(
        "SELECT slug, published_at
         FROM blog_posts
         WHERE status = 'published'
         ORDER BY updated_at DESC, published_at DESC, id DESC"
    );

    $posts = $stmt->fetchAll();
    foreach ($posts as $post) {
        $slug = trim((string)($post['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }

        $publishedAt = (string)($post['published_at'] ?? '');
        $lastmod = ($publishedAt !== '' && strtotime($publishedAt) !== false)
            ? gmdate('c', strtotime($publishedAt))
            : gmdate('c');

        $items[] = [
            'loc' => $baseUrl . '/blog/' . rawurlencode($slug),
            'lastmod' => $lastmod,
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
    }
} catch (Throwable $e) {
    // Si falla DB, se entrega al menos sitemap de rutas estáticas.
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($items as $item): ?>
  <url>
    <loc><?= mellatronXmlEscape($item['loc']) ?></loc>
    <lastmod><?= mellatronXmlEscape($item['lastmod']) ?></lastmod>
    <changefreq><?= mellatronXmlEscape($item['changefreq']) ?></changefreq>
    <priority><?= mellatronXmlEscape($item['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
