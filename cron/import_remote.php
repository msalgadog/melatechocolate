<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ImportSupport.php';

date_default_timezone_set('America/Mexico_City');

try {
    $db = Database::getConnection();
    mellatronEnsureImportInfrastructure($db);
} catch (Throwable $e) {
    fwrite(STDERR, "No se pudo conectar a la base de datos: {$e->getMessage()}" . PHP_EOL);
    exit(1);
}

$cron = mellatronGetCronConfig($db);
if (!$cron['enabled']) {
    $msg = 'Cron deshabilitado en configuración.';
    mellatronLog($db, 'cron', 'warning', 'all', $msg, ['trigger' => 'cron']);
    echo $msg . PHP_EOL;
    exit(0);
}

$nowTs = time();
$lastRunAt = $cron['last_run_at'];

if ($lastRunAt) {
    $lastTs = strtotime($lastRunAt);
    if ($lastTs !== false) {
        $nextTs = $lastTs + ((int)$cron['interval_minutes'] * 60);
        if ($nowTs < $nextTs) {
            $msg = 'Aún no toca ejecutar según el intervalo configurado.';
            mellatronLog($db, 'cron', 'info', 'all', $msg, [
                'trigger' => 'cron',
                'last_run_at' => $lastRunAt,
                'interval_minutes' => (int)$cron['interval_minutes'],
                'next_run_at' => date('Y-m-d H:i:s', $nextTs),
            ]);
            echo $msg . PHP_EOL;
            exit(0);
        }
    }
}

$urls = mellatronGetSourceUrls($db);
$games = ['melate', 'revancha', 'revanchita'];

mellatronLog($db, 'cron', 'info', 'all', 'Inicio de ejecución de cron remoto.', [
    'trigger' => 'cron',
    'interval_minutes' => (int)$cron['interval_minutes'],
]);

$results = [];
$errors = 0;

foreach ($games as $game) {
    $url = $urls[$game] ?? '';
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $errors++;
        $msg = "URL inválida para {$game}.";
        mellatronLog($db, 'cron', 'error', $game, $msg, ['trigger' => 'cron', 'url' => $url]);
        $results[$game] = $msg;
        continue;
    }

    $res = mellatronImportFromRemote($db, $game, $url, 'cron');
    if (!$res['ok']) {
        $errors++;
    }
    $results[$game] = $res['message'];
}

$runAt = date('Y-m-d H:i:s');
mellatronSetSetting($db, 'cron_last_run_at', $runAt);

$status = $errors > 0 ? 'warning' : 'success';
$summary = sprintf('Cron completado: %d juegos procesados, %d con error.', count($games), $errors);

mellatronLog($db, 'cron', $status, 'all', $summary, [
    'trigger' => 'cron',
    'ran_at' => $runAt,
    'results' => $results,
]);

echo $summary . PHP_EOL;
foreach ($results as $game => $message) {
    echo strtoupper($game) . ': ' . $message . PHP_EOL;
}

exit($errors > 0 ? 1 : 0);
