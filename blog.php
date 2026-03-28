<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/ContentRepository.php';

$pagina_actual = 'blog';
$page_title = 'Blog de análisis';
$page_desc = 'Artículos originales sobre probabilidad, estadística aplicada al Melate y juego responsable.';
$adsense_script = true;

$contentRepo = new ContentRepository();
$contentRepo->ensureSeedPosts();
$posts = $contentRepo->allPosts();

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">

        <!-- Columna principal: artículos -->
        <div class="col-12 col-lg-8">
            <h1 class="fw-bold mb-1" style="color:var(--ml-cafe-oscuro)">Blog de análisis</h1>
            <p class="text-muted mb-4">Contenido original sobre estadística, probabilidades y juego responsable.</p>

            <?php
            $blogColors = ['blog-img-1','blog-img-2','blog-img-3','blog-img-4','blog-img-5'];
            $blogIcons  = ['bi-journal-richtext','bi-bar-chart-line','bi-trophy','bi-stars','bi-dice-6'];
            ?>
            <div class="row g-4">
            <?php foreach ($posts as $i => $post):
                $imgClass = $blogColors[$i % 5];
                $icon     = $blogIcons[$i % 5];
            ?>
            <div class="col-12 col-sm-6">
                <article class="blog-card">
                    <a class="blog-card-thumb <?= $imgClass ?>"
                       href="<?= APP_URL ?>/blog/<?= urlencode($post['slug']) ?>">
                        <?php if (!empty($post['image_url'])): ?>
                            <img src="<?= htmlspecialchars($post['image_url']) ?>"
                                 alt="<?= htmlspecialchars($post['title']) ?>">
                        <?php else: ?>
                            <i class="bi <?= $icon ?> blog-card-icon"></i>
                        <?php endif; ?>
                    </a>
                    <div class="blog-card-body">
                        <p class="blog-card-meta">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= !empty($post['published_at']) ? date('d M Y', strtotime($post['published_at'])) : '' ?>
                            <?php if ((int)$post['views'] > 0): ?>
                            <span class="ms-2"><i class="bi bi-eye"></i> <?= number_format((int)$post['views']) ?></span>
                            <?php endif; ?>
                            <?php if ((int)$post['likes'] > 0): ?>
                            <span class="ms-1"><i class="bi bi-hand-thumbs-up"></i> <?= number_format((int)$post['likes']) ?></span>
                            <?php endif; ?>
                        </p>
                        <h2 class="blog-card-title">
                            <a href="<?= APP_URL ?>/blog/<?= urlencode($post['slug']) ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h2>
                        <p class="blog-card-excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
                        <a class="blog-card-readmore"
                           href="<?= APP_URL ?>/blog/<?= urlencode($post['slug']) ?>">
                            Leer art&iacute;culo completo <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
            </div><!-- /row cards -->
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-4">
            <div class="stat-card sticky-top" style="top:1rem">
                <h6 class="fw-bold mb-3" style="color:var(--ml-cafe-oscuro)">
                    <i class="bi bi-list-ul me-1"></i> Artículos destacados
                </h6>
                <ul class="list-unstyled small mb-0">
                    <?php foreach (array_slice($posts, 0, 5) as $p): ?>
                    <li class="mb-2 pb-2 border-bottom">
                        <a href="<?= APP_URL ?>/blog/<?= urlencode($p['slug']) ?>"
                           class="text-decoration-none" style="color:var(--ml-cafe-oscuro)">
                            <?= htmlspecialchars($p['title']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
