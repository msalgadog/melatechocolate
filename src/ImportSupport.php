<?php

function mellatronEnsureImportInfrastructure(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS import_logs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        log_type VARCHAR(40) NOT NULL,
        status VARCHAR(20) NOT NULL,
        juego VARCHAR(20) NOT NULL DEFAULT 'all',
        message VARCHAR(255) NOT NULL,
        context_json LONGTEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at),
        INDEX idx_log_type (log_type),
        INDEX idx_status (status),
        INDEX idx_juego (juego)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function mellatronDefaultSourceUrls(): array
{
    return [
        'melate' => 'https://www.loterianacional.gob.mx/Home/Historicos?ARHP=TQBlAGwAYQB0AGUA',
        'revancha' => 'https://www.loterianacional.gob.mx/Home/Historicos?ARHP=UgBlAHYAYQBuAGMAaABhAA==',
        'revanchita' => 'https://www.loterianacional.gob.mx/Home/Historicos?ARHP=UgBlAHYAYQBuAGMAaABpAHQAYQA=',
    ];
}

function mellatronGetSetting(PDO $db, string $key, string $default = ''): string
{
    $stmt = $db->prepare('SELECT setting_value FROM app_settings WHERE setting_key = :k LIMIT 1');
    $stmt->execute(['k' => $key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : (string)$value;
}

function mellatronSetSetting(PDO $db, string $key, string $value): void
{
    $stmt = $db->prepare("INSERT INTO app_settings (setting_key, setting_value)
                         VALUES (:k, :v)
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute(['k' => $key, 'v' => $value]);
}

function mellatronGetSourceUrls(PDO $db): array
{
    $defaults = mellatronDefaultSourceUrls();
    return [
        'melate' => mellatronGetSetting($db, 'source_url_melate', $defaults['melate']),
        'revancha' => mellatronGetSetting($db, 'source_url_revancha', $defaults['revancha']),
        'revanchita' => mellatronGetSetting($db, 'source_url_revanchita', $defaults['revanchita']),
    ];
}

function mellatronSaveSourceUrls(PDO $db, array $urls): void
{
    mellatronSetSetting($db, 'source_url_melate', trim((string)($urls['melate'] ?? '')));
    mellatronSetSetting($db, 'source_url_revancha', trim((string)($urls['revancha'] ?? '')));
    mellatronSetSetting($db, 'source_url_revanchita', trim((string)($urls['revanchita'] ?? '')));
}

function mellatronGetCronConfig(PDO $db): array
{
    return [
        'enabled' => mellatronGetSetting($db, 'cron_enabled', '1') === '1',
        'interval_minutes' => max(10, (int)mellatronGetSetting($db, 'cron_interval_minutes', '1440')),
        'last_run_at' => mellatronGetSetting($db, 'cron_last_run_at', ''),
    ];
}

function mellatronSaveCronConfig(PDO $db, bool $enabled, int $intervalMinutes): void
{
    mellatronSetSetting($db, 'cron_enabled', $enabled ? '1' : '0');
    mellatronSetSetting($db, 'cron_interval_minutes', (string)max(10, $intervalMinutes));
}

function mellatronAppendFileLog(string $line): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    $logFile = $logDir . '/import-cron.log';
    @file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND);
}

function mellatronLog(PDO $db, string $type, string $status, string $juego, string $message, array $context = []): void
{
    $stmt = $db->prepare('INSERT INTO import_logs (log_type, status, juego, message, context_json) VALUES (:t,:s,:j,:m,:c)');
    $stmt->execute([
        't' => $type,
        's' => $status,
        'j' => $juego,
        'm' => mb_substr($message, 0, 255),
        'c' => empty($context) ? null : json_encode($context, JSON_UNESCAPED_UNICODE),
    ]);

    $date = date('Y-m-d H:i:s');
    $ctx = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    mellatronAppendFileLog("[$date] [$type] [$status] [$juego] $message$ctx");
}

function mellatronGetRecentLogs(PDO $db, int $limit = 60): array
{
    $limit = max(1, min(200, $limit));
    $stmt = $db->query("SELECT id, log_type, status, juego, message, context_json, created_at
                        FROM import_logs
                        ORDER BY id DESC
                        LIMIT $limit");
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function mellatronParseFechaCSV(string $s): string
{
    $s = trim($s);
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $s, $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    }
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $s)) {
        return $s;
    }
    return '';
}

function mellatronDetectarJuego(array $header, array $firstRow): string
{
    $npIdx = array_search('NPRODUCTO', array_map('strtoupper', array_map('trim', $header)), true);
    if ($npIdx !== false && isset($firstRow[$npIdx])) {
        $code = (int)$firstRow[$npIdx];
        if ($code === 40) {
            return 'melate';
        }
        if ($code === 41) {
            return 'revancha';
        }
        if ($code === 34) {
            return 'revanchita';
        }
        return '';
    }

    $cols = count($header);
    if ($cols >= 11) {
        return 'melate';
    }
    if ($cols >= 10) {
        return 'revancha';
    }
    return '';
}

function mellatronParsearFila(array $row, string $juego): array
{
    $n = count($row);
    if ($n < 9) {
        return [];
    }

    $concurso = (int)trim($row[1] ?? '0');
    if ($concurso <= 0) {
        return [];
    }

    $bolsa = (int)trim($row[$n - 2] ?? '0');
    $fecha = mellatronParseFechaCSV(trim($row[$n - 1] ?? ''));
    if (!$fecha) {
        return [];
    }

    if ($juego === 'melate') {
        if ($n < 11) {
            return [];
        }
        return [
            'concurso' => $concurso,
            'r1' => (int)$row[2], 'r2' => (int)$row[3], 'r3' => (int)$row[4],
            'r4' => (int)$row[5], 'r5' => (int)$row[6], 'r6' => (int)$row[7],
            'r7' => (int)$row[8],
            'bolsa' => $bolsa,
            'fecha' => $fecha,
        ];
    }

    if ($juego === 'revancha') {
        if ($n < 10) {
            return [];
        }
        return [
            'concurso' => $concurso,
            'r1' => (int)$row[2], 'r2' => (int)$row[3], 'r3' => (int)$row[4],
            'r4' => (int)$row[5], 'r5' => (int)$row[6], 'r6' => (int)$row[7],
            'bolsa' => $bolsa,
            'fecha' => $fecha,
        ];
    }

    if ($juego === 'revanchita') {
        if ($n < 10) {
            return [];
        }
        return [
            'concurso' => $concurso,
            'f1' => (int)$row[2], 'f2' => (int)$row[3], 'f3' => (int)$row[4],
            'f4' => (int)$row[5], 'f5' => (int)$row[6], 'f6' => (int)$row[7],
            'bolsa' => $bolsa,
            'fecha' => $fecha,
        ];
    }

    return [];
}

function mellatronInsertarFila(PDO $db, array $data, string $juego): string
{
    if ($juego === 'melate') {
        $sql = "INSERT IGNORE INTO sorteos_melate
                    (concurso,r1,r2,r3,r4,r5,r6,r7,bolsa,fecha)
                VALUES
                    (:concurso,:r1,:r2,:r3,:r4,:r5,:r6,:r7,:bolsa,:fecha)";
    } elseif ($juego === 'revancha') {
        $sql = "INSERT IGNORE INTO sorteos_revancha
                    (concurso,r1,r2,r3,r4,r5,r6,bolsa,fecha)
                VALUES
                    (:concurso,:r1,:r2,:r3,:r4,:r5,:r6,:bolsa,:fecha)";
    } else {
        $sql = "INSERT IGNORE INTO sorteos_revanchita
                    (concurso,f1,f2,f3,f4,f5,f6,bolsa,fecha)
                VALUES
                    (:concurso,:f1,:f2,:f3,:f4,:f5,:f6,:bolsa,:fecha)";
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        return $stmt->rowCount() > 0 ? 'inserted' : 'duplicate';
    } catch (PDOException $e) {
        return 'error:' . $e->getMessage();
    }
}

function mellatronLooksLikeHtml(string $content): bool
{
    $sample = ltrim(substr($content, 0, 500));
    return (bool)preg_match('/^<(?:!doctype|html|head|body)\b/i', $sample);
}

function mellatronDownloadRemoteCsv(string $url): array
{
    $url = trim($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['ok' => false, 'error' => 'La URL no es válida.'];
    }

    $content = '';
    $httpCode = 0;
    $contentType = '';
    $error = '';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'MellatronImporter/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $content = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($content === false) {
            return ['ok' => false, 'error' => 'Error de descarga: ' . ($error ?: 'desconocido')];
        }
    } else {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 60,
                'ignore_errors' => true,
                'header' => "User-Agent: MellatronImporter/1.0\r\n",
            ],
        ]);
        $content = @file_get_contents($url, false, $ctx);
        if ($content === false) {
            return ['ok' => false, 'error' => 'No se pudo descargar el archivo remoto.'];
        }

        global $http_response_header;
        if (is_array($http_response_header) && !empty($http_response_header[0])) {
            if (preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
                $httpCode = (int)$m[1];
            }
            foreach ($http_response_header as $h) {
                if (stripos($h, 'Content-Type:') === 0) {
                    $contentType = trim(substr($h, 13));
                    break;
                }
            }
        }
    }

    if ($httpCode >= 400) {
        return ['ok' => false, 'error' => "El servidor remoto respondió HTTP $httpCode."];
    }
    if (trim($content) === '') {
        return ['ok' => false, 'error' => 'El contenido descargado está vacío.'];
    }
    if (mellatronLooksLikeHtml($content)) {
        return ['ok' => false, 'error' => 'La URL devolvió HTML, no un CSV descargable.'];
    }

    $tmp = tempnam(sys_get_temp_dir(), 'mellatron_csv_');
    if ($tmp === false || @file_put_contents($tmp, $content) === false) {
        return ['ok' => false, 'error' => 'No se pudo guardar temporalmente el CSV.'];
    }

    return [
        'ok' => true,
        'tmp_file' => $tmp,
        'bytes' => strlen($content),
        'http_code' => $httpCode,
        'content_type' => $contentType,
    ];
}

function mellatronParseCsvFile(string $filePath, string $forcedJuego = ''): array
{
    if (!is_file($filePath)) {
        return ['ok' => false, 'error' => 'Archivo CSV no encontrado.'];
    }

    $handle = fopen($filePath, 'r');
    if (!$handle) {
        return ['ok' => false, 'error' => 'No se pudo abrir el CSV.'];
    }

    $header = fgetcsv($handle);
    if (!$header) {
        fclose($handle);
        return ['ok' => false, 'error' => 'El CSV no tiene encabezado válido.'];
    }

    $headerNorm = array_map('strtoupper', array_map('trim', $header));
    if (!in_array('NPRODUCTO', $headerNorm, true) || !in_array('CONCURSO', $headerNorm, true)) {
        fclose($handle);
        return ['ok' => false, 'error' => 'El CSV no contiene columnas esperadas (NPRODUCTO, CONCURSO).'];
    }

    $allRows = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (count(array_filter($row, function ($v) {
            return trim((string)$v) !== '';
        })) === 0) {
            continue;
        }
        $allRows[] = $row;
    }
    fclose($handle);

    if (empty($allRows)) {
        return ['ok' => false, 'error' => 'El CSV no contiene filas de datos.'];
    }

    $juego = $forcedJuego ?: mellatronDetectarJuego($header, $allRows[0]);
    if (!$juego || !in_array($juego, ['melate', 'revancha', 'revanchita'], true)) {
        return ['ok' => false, 'error' => 'No se pudo detectar el juego del CSV.'];
    }

    $expectedCodeMap = ['melate' => 40, 'revancha' => 41, 'revanchita' => 34];
    $expectedCode = $expectedCodeMap[$juego] ?? null;

    $parsed = [];
    $invalid = 0;
    $productMismatch = 0;

    foreach ($allRows as $row) {
        $rawProduct = trim((string)($row[0] ?? ''));
        if ($expectedCode !== null && ctype_digit($rawProduct) && (int)$rawProduct !== $expectedCode) {
            $invalid++;
            $productMismatch++;
            continue;
        }

        $p = mellatronParsearFila($row, $juego);
        if ($p) {
            $parsed[] = $p;
        } else {
            $invalid++;
        }
    }

    if (empty($parsed)) {
        $msg = 'Ninguna fila del CSV pudo parsearse correctamente.';
        if ($productMismatch > 0) {
            $msg .= ' El código NPRODUCTO no coincide con el juego esperado.';
        }
        return ['ok' => false, 'error' => $msg];
    }

    return [
        'ok' => true,
        'juego' => $juego,
        'rows' => $parsed,
        'invalid_rows' => $invalid,
        'total_rows' => count($allRows),
        'product_mismatch_rows' => $productMismatch,
    ];
}

function mellatronImportParsedRows(PDO $db, array $rows, string $juego): array
{
    $inserted = 0;
    $duplicates = 0;
    $errors = 0;
    $errorMessages = [];

    foreach ($rows as $row) {
        $res = mellatronInsertarFila($db, $row, $juego);
        if ($res === 'inserted') {
            $inserted++;
        } elseif ($res === 'duplicate') {
            $duplicates++;
        } else {
            $errors++;
            $errorMessages[] = $res;
        }
    }

    return [
        'inserted' => $inserted,
        'duplicates' => $duplicates,
        'errors' => $errors,
        'error_messages' => array_values(array_unique(array_slice($errorMessages, 0, 10))),
    ];
}

function mellatronImportFromRemote(PDO $db, string $juego, string $url, string $trigger = 'admin'): array
{
    $download = mellatronDownloadRemoteCsv($url);
    if (!$download['ok']) {
        mellatronLog($db, 'import_remote', 'error', $juego, 'Falló la descarga remota.', [
            'trigger' => $trigger,
            'url' => $url,
            'error' => $download['error'] ?? 'desconocido',
        ]);
        return ['ok' => false, 'juego' => $juego, 'message' => $download['error'] ?? 'Error de descarga'];
    }

    $parsed = mellatronParseCsvFile($download['tmp_file'], $juego);
    @unlink($download['tmp_file']);

    if (!$parsed['ok']) {
        mellatronLog($db, 'import_remote', 'error', $juego, 'Falló la validación del CSV remoto.', [
            'trigger' => $trigger,
            'url' => $url,
            'error' => $parsed['error'] ?? 'CSV inválido',
        ]);
        return ['ok' => false, 'juego' => $juego, 'message' => $parsed['error'] ?? 'CSV inválido'];
    }

    $summary = mellatronImportParsedRows($db, $parsed['rows'], $juego);

    $status = $summary['errors'] > 0 ? 'warning' : 'success';
    $message = sprintf(
        'Importación remota %s: %d nuevos, %d duplicados, %d errores, %d inválidos.',
        ucfirst($juego),
        $summary['inserted'],
        $summary['duplicates'],
        $summary['errors'],
        (int)$parsed['invalid_rows']
    );

    mellatronLog($db, 'import_remote', $status, $juego, $message, [
        'trigger' => $trigger,
        'url' => $url,
        'download_bytes' => $download['bytes'] ?? null,
        'download_http_code' => $download['http_code'] ?? null,
        'download_content_type' => $download['content_type'] ?? null,
        'summary' => $summary,
        'parsed' => [
            'total_rows' => (int)$parsed['total_rows'],
            'invalid_rows' => (int)$parsed['invalid_rows'],
            'product_mismatch_rows' => (int)$parsed['product_mismatch_rows'],
        ],
    ]);

    return [
        'ok' => $summary['errors'] === 0,
        'juego' => $juego,
        'message' => $message,
        'summary' => $summary,
        'parsed' => $parsed,
    ];
}
