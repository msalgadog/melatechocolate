<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ContentRepository.php';

$contentRepo = new ContentRepository();
$contentRepo->ensureSeedPosts();

function mellatronSanitizeBlogHtml(string $html): string
{
    $html = trim($html);
    $html = preg_replace('#<\s*(script|style)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html) ?? '';
    $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
    $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^\2]*\2/i', '$1="#"', $html) ?? '';

    return strip_tags($html, '<p><br><strong><b><em><i><u><span><h2><h3><h4><ul><ol><li><blockquote><a><img><table><thead><tbody><tr><th><td><hr>');
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

function mellatronHandleBlogImageUpload(array $file)
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo cargar la imagen.');
    }
    if ((int)($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('La imagen supera el límite de 5MB.');
    }

    $tmpPath = (string)($file['tmp_name'] ?? '');
    if (!is_uploaded_file($tmpPath)) {
        throw new RuntimeException('Archivo de imagen inválido.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($tmpPath);
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($extMap[$mime])) {
        throw new RuntimeException('Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.');
    }

    $uploadDir = __DIR__ . '/../public/uploads/blog';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('No se pudo crear la carpeta de imágenes del blog.');
    }

    $filename = 'blog-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extMap[$mime];
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmpPath, $targetPath)) {
        throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
    }

    return (APP_URL ?: '') . '/public/uploads/blog/' . $filename;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit = $id > 0;

$post = [
    'title' => '',
    'excerpt' => '',
    'content' => '',
    'status' => 'draft',
    'published_at' => '',
    'image_url' => '',
];

if ($isEdit) {
    $row = $contentRepo->getPostByIdAdmin($id);
    if (!$row) {
        $_SESSION['blog_flash'] = ['type' => 'error', 'msg' => 'La entrada solicitada no existe.'];
        header('Location: ' . (APP_URL ?: '') . '/admin/blog-posts.php');
        exit;
    }
    $post = [
        'title' => (string)$row['title'],
        'excerpt' => (string)$row['excerpt'],
        'content' => (string)$row['content'],
        'status' => (string)$row['status'],
        'published_at' => !empty($row['published_at']) ? date('Y-m-d', strtotime((string)$row['published_at'])) : '',
        'image_url' => (string)$row['image_url'],
    ];
}

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_post') {
    try {
        $title = trim((string)($_POST['title'] ?? ''));
        $excerpt = trim((string)($_POST['excerpt'] ?? ''));
        $content = mellatronSanitizeBlogHtml((string)($_POST['content'] ?? ''));
        $status = (string)($_POST['status'] ?? 'draft');
        $publishedAtInput = trim((string)($_POST['published_at'] ?? ''));
        $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

        if ($title === '') {
            throw new RuntimeException('El título es obligatorio.');
        }
        if ($excerpt === '') {
            throw new RuntimeException('El extracto es obligatorio.');
        }
        if ($content === '') {
            throw new RuntimeException('El contenido es obligatorio.');
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            $status = 'draft';
        }

        $publishedAt = null;
        if ($status === 'published') {
            if ($publishedAtInput === '') {
                $publishedAt = date('Y-m-d H:i:s');
            } else {
                $ts = strtotime($publishedAtInput);
                if ($ts === false) {
                    throw new RuntimeException('La fecha de publicación no es válida.');
                }
                $publishedAt = date('Y-m-d H:i:s', $ts);
            }
        }

        $currentImage = (string)($_POST['current_image_url'] ?? '');
        $imageUrl = $currentImage;

        if ($removeImage) {
            mellatronDeleteManagedBlogImage($currentImage);
            $imageUrl = '';
        }

        $newImage = mellatronHandleBlogImageUpload($_FILES['image_file'] ?? []);
        if ($newImage !== null) {
            if ($currentImage !== '') {
                mellatronDeleteManagedBlogImage($currentImage);
            }
            $imageUrl = $newImage;
        }

        $payload = [
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'status' => $status,
            'published_at' => $publishedAt,
            'image_url' => $imageUrl,
        ];

        if ($isEdit) {
            $contentRepo->updatePost($id, $payload);
            $_SESSION['blog_flash'] = ['type' => 'success', 'msg' => 'Entrada actualizada correctamente.'];
        } else {
            $id = $contentRepo->createPost($payload);
            $_SESSION['blog_flash'] = ['type' => 'success', 'msg' => 'Entrada creada correctamente.'];
        }

        header('Location: ' . (APP_URL ?: '') . '/admin/blog-posts.php');
        exit;
    } catch (Throwable $e) {
        $flash = ['type' => 'error', 'msg' => $e->getMessage()];
        $post = [
            'title' => (string)($_POST['title'] ?? ''),
            'excerpt' => (string)($_POST['excerpt'] ?? ''),
            'content' => (string)($_POST['content'] ?? ''),
            'status' => (string)($_POST['status'] ?? 'draft'),
            'published_at' => (string)($_POST['published_at'] ?? ''),
            'image_url' => (string)($_POST['current_image_url'] ?? ''),
        ];
    }
}

$page_title = $isEdit ? 'Editar entrada' : 'Nueva entrada';
$active_page = 'blog';
require __DIR__ . '/layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0"><?= $isEdit ? 'Editar entrada' : 'Nueva entrada de blog' ?></h4>
        <small class="text-muted">Usa el editor visual para estructurar el contenido.</small>
    </div>
    <a href="<?= APP_URL ?>/admin/blog-posts.php" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver al listado
    </a>
</div>

<?php if ($flash): ?>
<div class="alert flash-error mb-4">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="adm-card">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_post">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <input type="hidden" name="current_image_url" value="<?= htmlspecialchars((string)$post['image_url']) ?>">

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-8">
                <label class="form-label">Título</label>
                <input type="text" name="title" class="form-control" maxlength="255" required
                       value="<?= htmlspecialchars((string)$post['title']) ?>">
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Borrador</option>
                    <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicado</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <label class="form-label">Publicar en</label>
                  <input type="date" name="published_at" class="form-control"
                       value="<?= htmlspecialchars((string)$post['published_at']) ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Extracto</label>
            <textarea name="excerpt" rows="3" class="form-control" required><?= htmlspecialchars((string)$post['excerpt']) ?></textarea>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-6">
                <label class="form-label">Imagen destacada</label>
                <input type="file" name="image_file" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif">
                <small class="text-muted">Máximo 5MB. Formatos: JPG, PNG, WEBP, GIF.</small>
            </div>
            <div class="col-12 col-lg-6">
                <?php if (!empty($post['image_url'])): ?>
                    <label class="form-label d-block">Imagen actual</label>
                    <img src="<?= htmlspecialchars((string)$post['image_url']) ?>" alt="Imagen actual"
                         style="max-height:120px;border:1px solid var(--adm-border);border-radius:8px">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                        <label class="form-check-label text-muted" for="remove_image">Eliminar imagen actual</label>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Contenido</label>
            <textarea id="content_editor" name="content" rows="14" class="form-control" required><?= htmlspecialchars((string)$post['content']) ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-admin-primary px-4">
                <i class="bi bi-save me-1"></i>Guardar entrada
            </button>
            <a href="<?= APP_URL ?>/admin/blog-posts.php" class="btn btn-admin-outline">Cancelar</a>
        </div>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/sl8e1bccvpdk2n1r1xg57rjvo8qic4lq359dkfjmztztyp68/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#content_editor',
    menubar: false,
    height: 520,
    skin: 'oxide-dark',
    content_css: ['dark', '<?= APP_URL ?>/public/css/style.css'],
    body_class: 'blog-article-content',
    content_style: 'body{background:#3D2817;padding:16px;} table{width:100% !important;}',
    plugins: 'link lists table image code advlist autolink charmap fullscreen quickbars',
    toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table charmap | removeformat | code fullscreen'
});
</script>

<?php require __DIR__ . '/layout_bottom.php'; ?>
