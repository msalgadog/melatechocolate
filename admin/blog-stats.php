<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ContentRepository.php';

$contentRepo = new ContentRepository();
$posts = $contentRepo->allPostsForAdmin();

// Totales
$totalViews = array_sum(array_column($posts, 'views'));
$totalLikes = array_sum(array_column($posts, 'likes'));
$totalPublished = count(array_filter($posts, fn($p) => ($p['status'] ?? '') === 'published'));

// Top por vistas (max 10)
$byViews = $posts;
usort($byViews, fn($a, $b) => (int)$b['views'] - (int)$a['views']);
$topViews = array_slice($byViews, 0, 10);
$maxViews = (int)($topViews[0]['views'] ?? 1);
if ($maxViews === 0) $maxViews = 1;

// Top por likes (max 10)
$byLikes = $posts;
usort($byLikes, fn($a, $b) => (int)$b['likes'] - (int)$a['likes']);
$topLikes = array_slice($byLikes, 0, 10);
$maxLikes = (int)($topLikes[0]['likes'] ?? 1);
if ($maxLikes === 0) $maxLikes = 1;

$page_title = 'Estadísticas del Blog';
$active_page = 'blog';

require __DIR__ . '/layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Estadísticas del Blog</h4>
        <small class="text-muted">Lecturas, likes y rendimiento de cada entrada.</small>
    </div>
    <a href="<?= APP_URL ?>/admin/blog-posts.php" class="btn btn-sm btn-admin-outline">
        <i class="bi bi-arrow-left me-1"></i>Volver al listado
    </a>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="adm-card text-center">
            <h6>Entradas publicadas</h6>
            <div class="stat-value"><?= $totalPublished ?></div>
            <div class="stat-label">de <?= count($posts) ?> totales</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card text-center">
            <h6><i class="bi bi-eye me-1"></i>Total lecturas</h6>
            <div class="stat-value" style="color:var(--adm-green)"><?= number_format($totalViews) ?></div>
            <div class="stat-label">vistas acumuladas</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card text-center">
            <h6><i class="bi bi-hand-thumbs-up me-1"></i>Total likes</h6>
            <div class="stat-value" style="color:var(--adm-gold)"><?= number_format($totalLikes) ?></div>
            <div class="stat-label">me gusta acumulados</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card text-center">
            <h6>Promedio de vistas</h6>
            <div class="stat-value" style="color:#9b59b6">
                <?= count($posts) > 0 ? number_format($totalViews / count($posts), 1) : '0' ?>
            </div>
            <div class="stat-label">por entrada</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top por vistas -->
    <div class="col-12 col-lg-6">
        <div class="adm-card h-100">
            <h6 class="mb-3" style="color:var(--adm-green);font-size:.9rem;text-transform:none;letter-spacing:0">
                <i class="bi bi-eye-fill me-1"></i>Top entradas por lecturas
            </h6>
            <?php if (empty($posts)): ?>
                <p class="text-muted small mb-0">Sin datos.</p>
            <?php else: ?>
            <div class="d-flex flex-column gap-3">
            <?php foreach ($topViews as $i => $p):
                $pct = $maxViews > 0 ? round((int)$p['views'] / $maxViews * 100) : 0;
            ?>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small" style="color:var(--adm-text);max-width:75%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                              title="<?= htmlspecialchars($p['title']) ?>">
                            <span class="me-1" style="color:var(--adm-muted);font-size:.75rem">#<?= $i + 1 ?></span>
                            <?= htmlspecialchars($p['title']) ?>
                        </span>
                        <span class="fw-bold small" style="color:var(--adm-green)"><?= number_format((int)$p['views']) ?></span>
                    </div>
                    <div style="height:7px;background:#1e2530;border-radius:999px;overflow:hidden">
                        <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,#27ae60,#2ecc71);border-radius:999px;transition:width .5s"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top por likes -->
    <div class="col-12 col-lg-6">
        <div class="adm-card h-100">
            <h6 class="mb-3" style="color:var(--adm-gold);font-size:.9rem;text-transform:none;letter-spacing:0">
                <i class="bi bi-hand-thumbs-up-fill me-1"></i>Top entradas por likes
            </h6>
            <?php if (empty($posts)): ?>
                <p class="text-muted small mb-0">Sin datos.</p>
            <?php else: ?>
            <div class="d-flex flex-column gap-3">
            <?php foreach ($topLikes as $i => $p):
                $pct = $maxLikes > 0 ? round((int)$p['likes'] / $maxLikes * 100) : 0;
            ?>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small" style="color:var(--adm-text);max-width:75%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                              title="<?= htmlspecialchars($p['title']) ?>">
                            <span class="me-1" style="color:var(--adm-muted);font-size:.75rem">#<?= $i + 1 ?></span>
                            <?= htmlspecialchars($p['title']) ?>
                        </span>
                        <span class="fw-bold small" style="color:var(--adm-gold)"><?= number_format((int)$p['likes']) ?></span>
                    </div>
                    <div style="height:7px;background:#1e2530;border-radius:999px;overflow:hidden">
                        <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,#e09515,#f5a623);border-radius:999px;transition:width .5s"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabla completa -->
<div class="adm-card">
    <h6 class="mb-3" style="font-size:.9rem;text-transform:none;letter-spacing:0;color:var(--adm-text)">
        <i class="bi bi-table me-1"></i>Todas las entradas
    </h6>
    <div class="table-responsive">
        <table class="table adm-table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Publicado</th>
                    <th class="text-center" style="color:var(--adm-green)"><i class="bi bi-eye me-1"></i>Vistas</th>
                    <th class="text-center" style="color:var(--adm-gold)"><i class="bi bi-hand-thumbs-up me-1"></i>Likes</th>
                    <th class="text-center">Ratio</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $rank => $p):
                $views = (int)$p['views'];
                $likes = (int)$p['likes'];
                $ratio = $views > 0 ? round($likes / $views * 100, 1) : 0;
            ?>
                <tr>
                    <td class="small text-muted"><?= $rank + 1 ?></td>
                    <td>
                        <div class="fw-semibold" style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?= htmlspecialchars($p['title']) ?>
                        </div>
                    </td>
                    <td>
                        <?php if (($p['status'] ?? '') === 'published'): ?>
                            <span class="badge badge-revancha">Publicado</span>
                        <?php else: ?>
                            <span class="badge" style="background:#2d3145;color:#c8cfdd">Borrador</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                        <?= !empty($p['published_at']) ? date('d/m/Y', strtotime((string)$p['published_at'])) : '—' ?>
                    </td>
                    <td class="text-center">
                        <span class="fw-bold" style="color:var(--adm-green)"><?= number_format($views) ?></span>
                    </td>
                    <td class="text-center">
                        <span class="fw-bold" style="color:var(--adm-gold)"><?= number_format($likes) ?></span>
                    </td>
                    <td class="text-center small text-muted">
                        <?= $ratio ?>%
                    </td>
                    <td class="text-end">
                        <a href="<?= APP_URL ?>/blog/<?= urlencode((string)$p['slug']) ?>"
                           class="btn btn-sm btn-admin-outline rounded-pill px-2 py-0"
                           target="_blank" title="Ver entrada">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <a href="<?= APP_URL ?>/admin/blog-post-edit.php?id=<?= (int)$p['id'] ?>"
                           class="btn btn-sm btn-admin-outline rounded-pill px-2 py-0"
                           title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/layout_bottom.php'; ?>
