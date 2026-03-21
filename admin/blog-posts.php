<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ContentRepository.php';

$contentRepo = new ContentRepository();
$contentRepo->ensureSeedPosts();

function mellatronExcerptPreview(string $text, int $max = 95): string
{
    if (strlen($text) <= $max) {
        return $text;
    }
    return rtrim(substr($text, 0, $max - 1)) . '…';
}

function mellatronDeleteManagedBlogImage(string $imageUrl): void
{
    if ($imageUrl === '') {
        return;
    }

    $path = (string)(parse_url($imageUrl, PHP_URL_PATH) ?? '');
    if (!preg_match('#/public/uploads/blog/([a-zA-Z0-9._-]+)$#', $path, $m)) {
        return;
    }

    $baseDir = realpath(__DIR__ . '/../public/uploads/blog');
    if ($baseDir === false) {
        return;
    }

    $fileName = basename($m[1]);
    $filePath = $baseDir . DIRECTORY_SEPARATOR . $fileName;
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_post') {
    $id = (int)($_POST['post_id'] ?? 0);
    if ($id > 0) {
        $post = $contentRepo->getPostByIdAdmin($id);
        if ($post) {
            mellatronDeleteManagedBlogImage((string)($post['image_url'] ?? ''));
            $contentRepo->deletePost($id);
            $_SESSION['blog_flash'] = ['type' => 'success', 'msg' => 'Entrada eliminada correctamente.'];
        } else {
            $_SESSION['blog_flash'] = ['type' => 'error', 'msg' => 'La entrada no existe.'];
        }
    }

    header('Location: ' . (APP_URL ?: '') . '/admin/blog-posts.php');
    exit;
}

$flash = null;
if (!empty($_SESSION['blog_flash']) && is_array($_SESSION['blog_flash'])) {
    $flash = $_SESSION['blog_flash'];
    unset($_SESSION['blog_flash']);
}

$posts = $contentRepo->allPostsForAdmin();

$page_title = 'Entradas de blog';
$active_page = 'blog';

require __DIR__ . '/layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Entradas del blog</h4>
        <small class="text-muted">Administra el contenido publicado y los borradores.</small>
    </div>
    <a href="<?= APP_URL ?>/admin/blog-post-edit.php" class="btn btn-admin-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva entrada
    </a>
</div>

<?php if ($flash): ?>
<div class="alert <?= $flash['type'] === 'success' ? 'flash-success' : 'flash-error' ?> mb-4">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="adm-card">
    <?php if (empty($posts)): ?>
        <p class="text-muted mb-0">No hay entradas registradas.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table adm-table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Publicado</th>
                    <th>Slug</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($post['title']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars(mellatronExcerptPreview((string)$post['excerpt'])) ?></div>
                    </td>
                    <td>
                        <?php if (($post['status'] ?? 'draft') === 'published'): ?>
                            <span class="badge badge-revancha">Publicado</span>
                        <?php else: ?>
                            <span class="badge" style="background:#2d3145;color:#c8cfdd">Borrador</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                        <?= !empty($post['published_at']) ? date('d/m/Y H:i', strtotime((string)$post['published_at'])) : '—' ?>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($post['slug']) ?></td>
                    <td class="text-end">
                        <a href="<?= APP_URL ?>/blog-articulo.php?slug=<?= urlencode((string)$post['slug']) ?>"
                           class="btn btn-sm btn-admin-outline rounded-pill px-2 py-0"
                           target="_blank"
                           title="Ver">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= APP_URL ?>/admin/blog-post-edit.php?id=<?= (int)$post['id'] ?>"
                           class="btn btn-sm btn-admin-outline rounded-pill px-2 py-0"
                           title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta entrada?');">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-admin-outline rounded-pill px-2 py-0" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/layout_bottom.php'; ?>
