<?php
/**
 * Mellatron Admin — Dashboard
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ImportSupport.php';

$db = Database::getConnection();
mellatronEnsureImportInfrastructure($db);

$dashboardFlash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'import_all_now') {
    $urls = mellatronGetSourceUrls($db);
    $labels = ['melate' => 'Melate', 'revancha' => 'Revancha', 'revanchita' => 'Revanchita'];
    $messages = [];
    $hasError = false;

    foreach (['melate', 'revancha', 'revanchita'] as $game) {
        $result = mellatronImportFromRemote($db, $game, $urls[$game] ?? '', 'admin_dashboard');
        $messages[] = $labels[$game] . ': ' . $result['message'];
        if (!$result['ok']) {
            $hasError = true;
        }
    }

    $_SESSION['dashboard_flash'] = [
        'type' => $hasError ? 'warning' : 'success',
        'msg' => implode(' ', $messages),
    ];

    $selfUrl = (APP_URL ?: '') . '/admin/index.php';
    header('Location: ' . $selfUrl);
    exit;
}

if (!empty($_SESSION['dashboard_flash']) && is_array($_SESSION['dashboard_flash'])) {
    $dashboardFlash = $_SESSION['dashboard_flash'];
    unset($_SESSION['dashboard_flash']);
}

// ---- Stats generales ----
$stats = [];
foreach (['melate' => 'sorteos_melate', 'revancha' => 'sorteos_revancha', 'revanchita' => 'sorteos_revanchita'] as $key => $table) {
    $row = $db->query("SELECT COUNT(*) AS total, MAX(concurso) AS ultimo, MAX(fecha) AS ultima_fecha,
                              (SELECT bolsa FROM $table ORDER BY concurso DESC LIMIT 1) AS ultima_bolsa
                       FROM $table")->fetch(PDO::FETCH_ASSOC);
    $stats[$key] = $row;
}

// ---- Últimos 8 sorteos de cada juego ----
$ultimos = [];
$ultimos['melate']     = $db->query("SELECT concurso,fecha,r1,r2,r3,r4,r5,r6,r7,bolsa FROM sorteos_melate     ORDER BY concurso DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
$ultimos['revancha']   = $db->query("SELECT concurso,fecha,r1,r2,r3,r4,r5,r6,bolsa    FROM sorteos_revancha   ORDER BY concurso DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
$ultimos['revanchita'] = $db->query("SELECT concurso,fecha,f1,f2,f3,f4,f5,f6,bolsa    FROM sorteos_revanchita ORDER BY concurso DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

// ---- Total registros cargados hoy ----
$hoy = date('Y-m-d');
$cargadosHoy = (int)$db->query(
    "SELECT SUM(c) FROM (
        SELECT COUNT(*) c FROM sorteos_melate     WHERE DATE(created_at) = '$hoy'
        UNION ALL
        SELECT COUNT(*) c FROM sorteos_revancha   WHERE DATE(created_at) = '$hoy'
        UNION ALL
        SELECT COUNT(*) c FROM sorteos_revanchita WHERE DATE(created_at) = '$hoy'
    ) t"
)->fetchColumn();

$page_title  = 'Dashboard';
$active_page = 'dashboard';

function fmtBolsa(int $b): string {
    return '$' . number_format($b, 0, '.', ',');
}
function fmtFecha(string $f): string {
    $ts = strtotime($f);
    return $ts ? date('d/m/Y', $ts) : $f;
}
$juegoLabel = ['melate' => 'Melate', 'revancha' => 'Revancha', 'revanchita' => 'Revanchita'];
$juegoBadge = ['melate' => 'badge-melate', 'revancha' => 'badge-revancha', 'revanchita' => 'badge-revanchita'];
$juegoNum   = ['melate' => 'num-melate',   'revancha' => 'num-revancha',   'revanchita' => 'num-revanchita'];

require __DIR__ . '/layout_top.php';
?>

<!-- ===== TÍTULO ===== -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Dashboard</h4>
        <small class="text-muted">Resumen de sorteos — <?= date('d/m/Y') ?></small>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" class="m-0">
            <input type="hidden" name="action" value="import_all_now">
            <button type="submit" class="btn btn-admin-primary btn-sm">
                <i class="bi bi-cloud-download me-1"></i> Importar todo ahora
            </button>
        </form>
        <a href="<?= APP_URL ?>/admin/fuentes.php"
           class="btn btn-admin-outline btn-sm">
            <i class="bi bi-link-45deg me-1"></i> Fuentes & Cron
        </a>
        <a href="<?= APP_URL ?>/admin/nuevo-sorteo.php"
           class="btn btn-admin-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Nuevo sorteo
        </a>
        <a href="<?= APP_URL ?>/admin/importar-csv.php"
           class="btn btn-admin-outline btn-sm">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Importar CSV
        </a>
        <a href="<?= APP_URL ?>/admin/blog-posts.php"
           class="btn btn-admin-outline btn-sm">
            <i class="bi bi-journal-richtext me-1"></i> Blog
        </a>
    </div>
</div>

<?php if ($dashboardFlash): ?>
<div class="alert <?= $dashboardFlash['type'] === 'success' ? 'flash-success' : 'alert-warning' ?> mb-4">
    <i class="bi bi-<?= $dashboardFlash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-1"></i>
    <?= htmlspecialchars($dashboardFlash['msg']) ?>
</div>
<?php endif; ?>

<!-- ===== TARJETAS DE ESTADÍSTICAS ===== -->
<div class="row g-3 mb-4">
    <?php foreach ($stats as $juego => $s): ?>
    <div class="col-12 col-md-4">
        <div class="adm-card">
            <h6><span class="badge <?= $juegoBadge[$juego] ?> me-1"><?= $juegoLabel[$juego] ?></span></h6>
            <div class="d-flex align-items-end justify-content-between mt-2">
                <div>
                    <div class="stat-value"><?= number_format((int)$s['total']) ?></div>
                    <div class="stat-label">sorteos registrados</div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold" style="color:var(--adm-gold)">
                        Concurso #<?= $s['ultimo'] ?? '—' ?>
                    </div>
                    <div class="small text-muted"><?= $s['ultima_fecha'] ? fmtFecha($s['ultima_fecha']) : '—' ?></div>
                    <div class="small text-muted"><?= $s['ultima_bolsa'] ? fmtBolsa((int)$s['ultima_bolsa']) : '' ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="col-12 col-md-4 col-lg-2 offset-lg-8" style="display:none"><!-- reservado --></div>
</div>

<?php if ($cargadosHoy > 0): ?>
<div class="alert flash-success mb-4">
    <i class="bi bi-check-circle-fill me-1"></i>
    Hoy se cargaron <strong><?= $cargadosHoy ?></strong> registro(s) nuevo(s).
</div>
<?php endif; ?>

<!-- ===== ÚLTIMOS SORTEOS POR JUEGO (TABS) ===== -->
<div class="adm-card">
    <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i> Últimos sorteos</h5>

    <ul class="nav nav-tabs mb-3" id="recentTabs">
        <?php foreach (array_keys($juegoLabel) as $i => $juego): ?>
        <li class="nav-item">
            <button class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-<?= $juego ?>">
                <span class="badge <?= $juegoBadge[$juego] ?> me-1"><?= $juegoLabel[$juego] ?></span>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php foreach ($ultimos as $juego => $filas): ?>
        <div class="tab-pane fade <?= $juego === 'melate' ? 'show active' : '' ?>" id="tab-<?= $juego ?>">
            <?php if (empty($filas)): ?>
                <p class="text-muted text-center py-3">Sin datos.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table adm-table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Concurso</th>
                            <th>Fecha</th>
                            <th>Números</th>
                            <th>Bolsa</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filas as $f):
                        $campos = $juego === 'revanchita'
                            ? ['f1','f2','f3','f4','f5','f6']
                            : ['r1','r2','r3','r4','r5','r6'];
                    ?>
                        <tr>
                            <td><span class="fw-semibold" style="color:var(--adm-gold)">#<?= $f['concurso'] ?></span></td>
                            <td class="text-muted small"><?= fmtFecha($f['fecha']) ?></td>
                            <td>
                                <?php foreach ($campos as $c): ?>
                                    <span class="num-bola <?= $juegoNum[$juego] ?> me-1"><?= $f[$c] ?></span>
                                <?php endforeach; ?>
                                <?php if ($juego === 'melate'): ?>
                                    <span class="num-bola num-adicional ms-1"><?= $f['r7'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= fmtBolsa((int)$f['bolsa']) ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/admin/editar-sorteo.php?juego=<?= $juego ?>&concurso=<?= $f['concurso'] ?>"
                                   class="btn btn-sm btn-admin-outline rounded-pill px-2 py-0">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/layout_bottom.php'; ?>
