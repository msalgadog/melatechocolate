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

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <?php if (!$post): ?>
        <div class="stat-card">
            <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">Artículo no encontrado</h1>
            <p class="text-muted">El contenido solicitado no existe o fue removido.</p>
            <a href="blog.php" class="btn btn-success btn-sm">Volver al blog</a>
        </div>
    <?php else: ?>
        <article class="stat-card">
            <p class="small text-muted mb-2">Publicado: <?= !empty($post['published_at']) ? date('d/m/Y', strtotime($post['published_at'])) : '' ?></p>
            <h1 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)"><?= htmlspecialchars($post['title']) ?></h1>
            <p class="lead text-muted"><?= htmlspecialchars($post['excerpt']) ?></p>
            <hr>
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
        </article>

        <div class="text-end mt-3">
            <a href="blog.php" class="btn btn-outline-success btn-sm">Volver al blog</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
