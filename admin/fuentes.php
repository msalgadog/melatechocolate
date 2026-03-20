<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ImportSupport.php';

$db = Database::getConnection();
mellatronEnsureImportInfrastructure($db);

$flash = null;
$labels = ['melate' => 'Melate', 'revancha' => 'Revancha', 'revanchita' => 'Revanchita'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_urls') {
        $urls = [
            'melate' => trim((string)($_POST['url_melate'] ?? '')),
            'revancha' => trim((string)($_POST['url_revancha'] ?? '')),
            'revanchita' => trim((string)($_POST['url_revanchita'] ?? '')),
        ];

        $errors = [];
        foreach ($urls as $key => $url) {
            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = "La URL de {$labels[$key]} no es válida.";
            }
        }

        if (!empty($errors)) {
            $flash = ['type' => 'error', 'msg' => implode(' ', $errors)];
        } else {
            mellatronSaveSourceUrls($db, $urls);
            mellatronLog($db, 'config', 'success', 'all', 'Se actualizaron las URLs de fuentes.', ['actor' => 'admin']);
            $flash = ['type' => 'success', 'msg' => 'URLs guardadas correctamente.'];
        }
    }

    if ($action === 'save_cron') {
        $enabled = isset($_POST['cron_enabled']) && $_POST['cron_enabled'] === '1';
        $interval = max(10, (int)($_POST['cron_interval_minutes'] ?? 1440));

        mellatronSaveCronConfig($db, $enabled, $interval);
        mellatronLog($db, 'config', 'success', 'all', 'Se actualizó la configuración de cron.', [
            'actor' => 'admin',
            'enabled' => $enabled,
            'interval_minutes' => $interval,
        ]);

        $flash = ['type' => 'success', 'msg' => 'Configuración de cron guardada.'];
    }

    if ($action === 'import_now') {
        $target = $_POST['target'] ?? '';
        $urls = mellatronGetSourceUrls($db);
        $targets = $target === 'all' ? ['melate', 'revancha', 'revanchita'] : [$target];

        $messages = [];
        $hasError = false;
        foreach ($targets as $game) {
            if (!isset($urls[$game])) {
                continue;
            }
            $result = mellatronImportFromRemote($db, $game, $urls[$game], 'admin');
            $messages[] = $labels[$game] . ': ' . $result['message'];
            if (!$result['ok']) {
                $hasError = true;
            }
        }

        $flash = [
            'type' => $hasError ? 'warning' : 'success',
            'msg' => implode(' ', $messages),
        ];
    }
}

$urls = mellatronGetSourceUrls($db);
$cron = mellatronGetCronConfig($db);
$logs = mellatronGetRecentLogs($db, 60);

$page_title = 'Fuentes y Cron';
$active_page = 'fuentes';
require __DIR__ . '/layout_top.php';

$cronCommand = 'php ' . str_replace('\\', '/', realpath(__DIR__ . '/../cron/import_remote.php'));

function logBadge(string $status): string
{
    if ($status === 'success') {
        return 'bg-success-subtle text-success border border-success-subtle';
    }
    if ($status === 'warning') {
        return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    }
    if ($status === 'error') {
        return 'bg-danger-subtle text-danger border border-danger-subtle';
    }
    return 'bg-secondary-subtle text-secondary border border-secondary-subtle';
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-link-45deg me-1"></i> Fuentes y Cron</h4>
        <small class="text-muted">Configura URLs oficiales, ejecuta importación remota y monitorea logs.</small>
    </div>
    <form method="POST" class="m-0">
        <input type="hidden" name="action" value="import_now">
        <input type="hidden" name="target" value="all">
        <button type="submit" class="btn btn-admin-primary btn-sm">
            <i class="bi bi-cloud-download me-1"></i> Importar todo ahora
        </button>
    </form>
</div>

<?php if ($flash): ?>
<div class="alert <?= $flash['type'] === 'success' ? 'flash-success' : ($flash['type'] === 'warning' ? 'alert-warning' : 'flash-error') ?> mb-4">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-1"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="adm-card mb-4">
            <h6 class="fw-bold mb-3">URLs de históricos</h6>
            <form method="POST" class="vstack gap-3">
                <input type="hidden" name="action" value="save_urls">

                <div>
                    <label class="form-label">Melate</label>
                    <input type="url" name="url_melate" class="form-control" required value="<?= htmlspecialchars($urls['melate']) ?>">
                </div>

                <div>
                    <label class="form-label">Revancha</label>
                    <input type="url" name="url_revancha" class="form-control" required value="<?= htmlspecialchars($urls['revancha']) ?>">
                </div>

                <div>
                    <label class="form-label">Revanchita</label>
                    <input type="url" name="url_revanchita" class="form-control" required value="<?= htmlspecialchars($urls['revanchita']) ?>">
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-admin-primary" type="submit">
                        <i class="bi bi-save me-1"></i> Guardar URLs
                    </button>
                </div>
            </form>
        </div>

        <div class="adm-card">
            <h6 class="fw-bold mb-3">Importación directa</h6>
            <p class="small text-muted mb-3">Descarga el CSV remoto, valida estructura y NPRODUCTO, e importa con control de duplicados.</p>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($labels as $key => $label): ?>
                <form method="POST" class="m-0">
                    <input type="hidden" name="action" value="import_now">
                    <input type="hidden" name="target" value="<?= $key ?>">
                    <button type="submit" class="btn btn-admin-outline btn-sm">
                        <i class="bi bi-download me-1"></i> Importar <?= $label ?>
                    </button>
                </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="adm-card mb-4">
            <h6 class="fw-bold mb-3">Configuración de cron</h6>
            <form method="POST" class="vstack gap-3">
                <input type="hidden" name="action" value="save_cron">

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="cron_enabled" name="cron_enabled" value="1" <?= $cron['enabled'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="cron_enabled">Cron habilitado</label>
                </div>

                <div>
                    <label class="form-label">Intervalo mínimo (minutos)</label>
                    <input type="number" min="10" step="10" name="cron_interval_minutes" class="form-control" value="<?= (int)$cron['interval_minutes'] ?>">
                    <small class="text-muted">Si Plesk ejecuta más seguido, el script se salta hasta que cumpla el intervalo.</small>
                </div>

                <button class="btn btn-admin-primary" type="submit">
                    <i class="bi bi-clock-history me-1"></i> Guardar cron
                </button>
            </form>

            <hr style="border-color:var(--adm-border)">
            <div class="small text-muted mb-1">Comando para Plesk (SSH/Bash)</div>
            <code style="display:block;white-space:normal;word-break:break-all;color:var(--adm-gold)"><?= htmlspecialchars($cronCommand) ?></code>
            <div class="small text-muted mt-2">
                Última ejecución registrada:
                <strong><?= $cron['last_run_at'] ? htmlspecialchars($cron['last_run_at']) : 'sin ejecuciones aún' ?></strong>
            </div>
        </div>

        <div class="adm-card">
            <h6 class="fw-bold mb-3">Log de importación y cron</h6>
            <?php if (empty($logs)): ?>
                <p class="text-muted small mb-0">Aún no hay registros.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table adm-table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Juego</th>
                                <th>Estado</th>
                                <th>Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                <td class="small"><?= htmlspecialchars($log['log_type']) ?></td>
                                <td class="small"><?= htmlspecialchars($log['juego']) ?></td>
                                <td><span class="badge <?= logBadge($log['status']) ?>"><?= htmlspecialchars($log['status']) ?></span></td>
                                <td class="small"><?= htmlspecialchars($log['message']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layout_bottom.php'; ?>
