<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// ---- Contador de vistas (una vez por sesión por artículo) ----
$sessionId = session_id();
if ($post) {
    $viewedKey = 'viewed_posts';
    if (!isset($_SESSION[$viewedKey]) || !is_array($_SESSION[$viewedKey])) {
        $_SESSION[$viewedKey] = [];
    }
    $postId = (int)$post['id'];
    if (!in_array($postId, $_SESSION[$viewedKey], true)) {
        $contentRepo->incrementViews($postId);
        $_SESSION[$viewedKey][] = $postId;
        $post['views'] = (int)$post['views'] + 1;
    }
    $isLiked = $contentRepo->isLikedBySession($postId, $sessionId);
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
            <?php if (!empty($post['image_url'])): ?>
            <img src="<?= htmlspecialchars((string)$post['image_url']) ?>"
                 alt="<?= htmlspecialchars((string)$post['title']) ?>"
                 class="blog-article-hero">
            <?php endif; ?>
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

            <!-- Estadísticas del artículo -->
            <?php
            $shareUrl   = htmlspecialchars($page_canonical);
            $shareTitle = htmlspecialchars(rawurlencode((string)$post['title']));
            $fbShare    = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($page_canonical);
            $xShare     = 'https://x.com/intent/tweet?url=' . rawurlencode($page_canonical) . '&text=' . rawurlencode((string)$post['title']);
            ?>
            <div class="blog-article-stats d-flex align-items-center gap-2 mt-4 pt-3 flex-wrap">
                <span class="blog-stat-badge">
                    <i class="bi bi-eye"></i> <?= number_format((int)$post['views']) ?> <?= (int)$post['views'] === 1 ? 'lectura' : 'lecturas' ?>
                </span>
                <button id="btn-like"
                        class="blog-like-btn <?= $isLiked ? 'liked' : '' ?>"
                        data-post-id="<?= (int)$post['id'] ?>"
                        data-liked="<?= $isLiked ? '1' : '0' ?>">
                    <i class="bi <?= $isLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' ?>"></i>
                    <span id="like-count"><?= number_format((int)$post['likes']) ?></span>
                    Me gusta
                </button>
                <span class="blog-share-sep">Compartir:</span>
                <a href="<?= $fbShare ?>"
                   target="_blank" rel="noopener noreferrer"
                   class="blog-share-btn blog-share-fb"
                   title="Compartir en Facebook">
                    <i class="bi bi-facebook"></i> Facebook
                </a>
                <a href="<?= $xShare ?>"
                   target="_blank" rel="noopener noreferrer"
                   class="blog-share-btn blog-share-x"
                   title="Compartir en X">
                    <svg class="blog-share-x-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.91-5.622Zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg> X
                </a>
            </div>
        </article>

        <div class="text-end mt-3">
            <a href="<?= APP_URL ?>/blog" class="btn btn-outline-success btn-sm">Volver al blog</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($post): ?>
<script>
(function () {
    var btn = document.getElementById('btn-like');
    if (!btn) return;

    btn.addEventListener('click', function () {
        btn.disabled = true;
        fetch('<?= rtrim(APP_URL, '/') ?>/api/blog-like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: parseInt(btn.dataset.postId, 10) })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var liked = data.liked;
            var count = data.likes;
            btn.dataset.liked = liked ? '1' : '0';
            btn.classList.toggle('liked', liked);
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = liked ? 'bi bi-hand-thumbs-up-fill' : 'bi bi-hand-thumbs-up';
            }
            var counter = document.getElementById('like-count');
            if (counter) counter.textContent = count.toLocaleString('es-MX');
        })
        .catch(function () {})
        .finally(function () { btn.disabled = false; });
    });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
