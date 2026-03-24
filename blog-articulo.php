<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/ContentRepository.php';

$contentRepo = new ContentRepository();
$contentRepo->ensureSeedPosts();

$slug = trim((string)($_GET['slug'] ?? ''));
$post = $slug !== '' ? $contentRepo->getPostBySlug($slug) : null;

if (!$post) {
    http_response_code(404);
}

$pagina_actual = 'blog';
$page_title = $post ? $post['title'] : 'Artículo no encontrado';
$page_desc = $post ? $post['excerpt'] : 'Artículo del blog no encontrado.';
$adsense_script = true;
$katex_enabled  = true;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
$appUrl = (string)(APP_URL ?? '');
$siteBaseUrl = preg_match('#^https?://#i', $appUrl) === 1
    ? rtrim($appUrl, '/')
    : $scheme . '://' . $host . rtrim($appUrl, '/');

$blogUrl = $siteBaseUrl . '/blog';
$articlePath = $post ? '/blog/' . rawurlencode((string)$post['slug']) : '/blog';
$page_canonical = $siteBaseUrl . $articlePath;
$page_og_type = $post ? 'article' : 'website';
$page_og_desc = $page_desc;
$page_og_image = !empty($post['image_url'])
    ? ((preg_match('#^https?://#i', (string)$post['image_url']) === 1)
        ? (string)$post['image_url']
        : $siteBaseUrl . (string)$post['image_url'])
    : $siteBaseUrl . '/public/img/logo.png';
$page_robots = $post ? 'index,follow,max-image-preview:large' : 'noindex,follow';

if ($post) {
    $publishedIso = !empty($post['published_at']) ? date(DATE_ATOM, strtotime((string)$post['published_at'])) : date(DATE_ATOM);
    $seo_json_ld = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BlogPosting',
                'headline' => (string)$post['title'],
                'description' => (string)$post['excerpt'],
                'image' => [$page_og_image],
                'datePublished' => $publishedIso,
                'dateModified' => $publishedIso,
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $page_canonical,
                ],
                'author' => [
                    '@type' => 'Organization',
                    'name' => APP_NAME,
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => APP_NAME,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $siteBaseUrl . '/public/img/logo.png',
                    ],
                ],
                'inLanguage' => 'es-MX',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Inicio',
                        'item' => $siteBaseUrl . '/',
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Blog',
                        'item' => $blogUrl,
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => (string)$post['title'],
                        'item' => $page_canonical,
                    ],
                ],
            ],
        ],
    ];
}

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <?php if (!$post): ?>
        <div class="stat-card">
            <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Artículo no encontrado</h1>
            <p class="text-muted">El contenido solicitado no existe o fue removido.</p>
            <a href="<?= APP_URL ?>/blog" class="btn btn-success btn-sm">Volver al blog</a>
        </div>
    <?php else: ?>
        <article class="stat-card blog-article">
            <p class="small text-muted mb-2">Publicado: <?= !empty($post['published_at']) ? date('d/m/Y', strtotime($post['published_at'])) : '' ?></p>
            <h1 class="fw-bold mb-3 blog-article-title"><?= htmlspecialchars($post['title']) ?></h1>
            <p class="lead blog-article-excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
            <hr>
            <div class="blog-article-content">
            <?php
            $rawContent = trim((string)$post['content']);
            $isHtmlContent = preg_match('/<\s*\/?[a-z][^>]*>/i', $rawContent) === 1;

            if ($isHtmlContent) {
                echo $rawContent;
            } else {
                foreach (preg_split('/\n\n+/', $rawContent) as $p):
                    $p = trim($p);
                    if ($p === '') continue;
                    if (preg_match('/^##\s+(.+)/s', $p, $m)):
                        echo '<h2 class="blog-h2">' . htmlspecialchars(trim($m[1])) . '</h2>';
                    elseif (str_starts_with($p, '$$') && str_ends_with($p, '$$') && strlen($p) > 4):
                        echo '<div class="blog-math">' . htmlspecialchars($p) . '</div>';
                    else:
                        echo '<p>' . nl2br(htmlspecialchars($p)) . '</p>';
                    endif;
                endforeach;
            }
            ?>
            </div>
        </article>

        <div class="text-end mt-3">
            <a href="<?= APP_URL ?>/blog" class="btn btn-outline-success btn-sm">Volver al blog</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
