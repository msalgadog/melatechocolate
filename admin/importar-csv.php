<?php
/**
 * Melate el Chocolate Admin — Importación de CSV
 * Acepta el formato oficial de Lotería Nacional:
 *   NPRODUCTO,CONCURSO,R1..R6[,R7],BOLSA,FECHA(dd/mm/yyyy)
 *
 * Flujo: subir → preview (primeras 10 filas) → confirmar → import
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getConnection();

// ── Helpers ─────────────────────────────────────────────────────────────────

function parseFechaCSV(string $s): ?string
{
    $s = trim($s);
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $s, $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    }
    // Intentar formato yyyy-mm-dd también
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $s)) return $s;
    return null;
}

/**
 * Detecta automáticamente el juego a partir del valor NPRODUCTO o de la
 * cantidad de columnas si el campo no está presente.
 */
function detectarJuego(array $header, array $firstRow): ?string
{
    // Por NPRODUCTO
    $npIdx = array_search('NPRODUCTO', array_map('strtoupper', array_map('trim', $header)));
    if ($npIdx !== false && isset($firstRow[$npIdx])) {
        return match((int)$firstRow[$npIdx]) {
            40 => 'melate',
            41 => 'revancha',
            34 => 'revanchita',
            default => null,
        };
    }
    // Por conteo de columnas numéricas
    $cols = count($header);
    if ($cols >= 11) return 'melate';      // NPRODUCTO+CONCURSO+R1-R7+BOLSA+FECHA = 11
    if ($cols >= 10) return 'revancha';    // 10 col
    if ($cols >= 10) return 'revanchita';
    return null;
}

/** Parsea una fila CSV → array de parámetros para INSERT, o null si inválida */
function parsearFila(array $row, string $juego): ?array
{
    // Índices fijos basados en el formato oficial
    // [0]=NPRODUCTO [1]=CONCURSO [2]=R1 ... [n-2]=BOLSA [n-1]=FECHA
    $n = count($row);
    if ($n < 9) return null;

    $concurso = (int)trim($row[1]);
    if ($concurso <= 0) return null;

    $bolsa = (int)trim($row[$n - 2]);
    $fecha = parseFechaCSV(trim($row[$n - 1]));
    if (!$fecha) return null;

    if ($juego === 'melate') {
        if ($n < 11) return null;
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
        if ($n < 10) return null;
        return [
            'concurso' => $concurso,
            'r1' => (int)$row[2], 'r2' => (int)$row[3], 'r3' => (int)$row[4],
            'r4' => (int)$row[5], 'r5' => (int)$row[6], 'r6' => (int)$row[7],
            'bolsa' => $bolsa,
            'fecha' => $fecha,
        ];
    }
    if ($juego === 'revanchita') {
        if ($n < 10) return null;
        return [
            'concurso' => $concurso,
            'f1' => (int)$row[2], 'f2' => (int)$row[3], 'f3' => (int)$row[4],
            'f4' => (int)$row[5], 'f5' => (int)$row[6], 'f6' => (int)$row[7],
            'bolsa' => $bolsa,
            'fecha' => $fecha,
        ];
    }
    return null;
}

/** Inserta o ignora duplicado. Devuelve 'inserted' | 'duplicate' | 'error' */
function insertarFila(PDO $db, array $data, string $juego): string
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

// ── Estado de la página ──────────────────────────────────────────────────────

$stage   = 'upload';   // upload | preview | result
$flash   = null;
$preview = [];
$juego   = '';
$csvRows = [];         // todas las filas parseadas
$tmpPath = '';         // ruta del archivo temporal en sesión

// ── STAGE 1: recibir el archivo ──────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {

    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $flash = ['type'=>'error', 'msg'=>'Error al subir el archivo (código '.$file['error'].').'];
    } elseif (!in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['csv','txt'])) {
        $flash = ['type'=>'error', 'msg'=>'Solo se aceptan archivos .csv o .txt'];
    } else {
        $handle  = fopen($file['tmp_name'], 'r');
        $header  = fgetcsv($handle);

        if (!$header) {
            $flash = ['type'=>'error', 'msg'=>'El archivo está vacío o no es un CSV válido.'];
        } else {
            // Leer todas las filas
            $allRows = [];
            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row)) === 0) continue;
                $allRows[] = $row;
            }
            fclose($handle);

            if (empty($allRows)) {
                $flash = ['type'=>'error', 'msg'=>'El CSV no contiene filas de datos.'];
            } else {
                // Auto-detectar juego
                $juegoDetectado = $_POST['juego_override'] ?? '';
                if (!in_array($juegoDetectado, ['melate','revancha','revanchita'])) {
                    $juegoDetectado = detectarJuego($header, $allRows[0]);
                }

                if (!$juegoDetectado) {
                    $flash = ['type'=>'error', 'msg'=>'No se pudo detectar el juego automáticamente. Selecciónalo manualmente.'];
                } else {
                    // Parsear filas
                    $parsed  = [];
                    $invalid = 0;
                    foreach ($allRows as $row) {
                        $p = parsearFila($row, $juegoDetectado);
                        if ($p) {
                            $parsed[] = $p;
                        } else {
                            $invalid++;
                        }
                    }

                    if (empty($parsed)) {
                        $flash = ['type'=>'error', 'msg'=>"Se encontraron {$invalid} fila(s) pero ninguna pudo parsearse correctamente. Verifica el formato."];
                    } else {
                        // Guardar en sesión para confirmar
                        $_SESSION['csv_rows']  = $parsed;
                        $_SESSION['csv_juego'] = $juegoDetectado;
                        $stage   = 'preview';
                        $juego   = $juegoDetectado;
                        $csvRows = $parsed;
                        $preview = array_slice($parsed, 0, 10);
                        if ($invalid > 0) {
                            $flash = ['type'=>'warning', 'msg'=>"{$invalid} fila(s) con formato inválido fueron omitidas del preview."];
                        }
                    }
                }
            }
        }
    }
}

// ── STAGE 2: confirmar e importar ────────────────────────────────────────────

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_import'])) {
    $csvRows = $_SESSION['csv_rows']  ?? [];
    $juego   = $_SESSION['csv_juego'] ?? '';

    if (empty($csvRows) || !$juego) {
        $flash = ['type'=>'error', 'msg'=>'Sesión expirada, vuelve a subir el archivo.'];
    } else {
        $inserted   = 0;
        $duplicates = 0;
        $errors     = 0;
        $errMsgs    = [];

        foreach ($csvRows as $row) {
            $res = insertarFila($db, $row, $juego);
            if ($res === 'inserted')   $inserted++;
            elseif ($res === 'duplicate') $duplicates++;
            else { $errors++; $errMsgs[] = $res; }
        }

        unset($_SESSION['csv_rows'], $_SESSION['csv_juego']);
        $stage = 'result';
        $flash = ['type' => $errors > 0 ? 'warning' : 'success',
                  'msg'  => "Importación completada: <strong>{$inserted}</strong> nuevos, " .
                            "<strong>{$duplicates}</strong> duplicados omitidos" .
                            ($errors > 0 ? ", <strong>{$errors}</strong> errores." : ".")];
        if (!empty($errMsgs)) {
            $flash['extra'] = implode('<br>', array_unique(array_slice($errMsgs, 0, 5)));
        }
    }
}

// ── Retomar preview si viene de atrás (sesión activa) ───────────────────────

elseif (!empty($_SESSION['csv_rows']) && $_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['stage'] ?? '') === 'preview') {
    $stage   = 'preview';
    $juego   = $_SESSION['csv_juego'] ?? '';
    $csvRows = $_SESSION['csv_rows']  ?? [];
    $preview = array_slice($csvRows, 0, 10);
}

// ── Labels ──────────────────────────────────────────────────────────────────

$juegoLabel  = ['melate'=>'Melate','revancha'=>'Revancha','revanchita'=>'Revanchita'];
$juegoBadge  = ['melate'=>'badge-melate','revancha'=>'badge-revancha','revanchita'=>'badge-revanchita'];
$juegoNumCls = ['melate'=>'num-melate', 'revancha'=>'num-revancha', 'revanchita'=>'num-revanchita'];

function fmtBolsaC(int $b): string { return '$'.number_format($b, 0, '.', ','); }

$page_title  = 'Importar CSV';
$active_page = 'importar';
require __DIR__ . '/layout_top.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-arrow-up me-1"></i> Importar CSV</h4>
        <small class="text-muted">Carga masiva desde el formato oficial de Lotería Nacional</small>
    </div>
    <?php if ($stage !== 'upload'): ?>
    <a href="importar-csv.php" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Nuevo archivo
    </a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
<div class="alert <?= $flash['type'] === 'success' ? 'flash-success' : ($flash['type'] === 'warning' ? '' : 'flash-error') ?> mb-4
    <?= $flash['type'] === 'warning' ? 'alert-warning' : '' ?>">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-1"></i>
    <?= $flash['msg'] ?>
    <?php if (!empty($flash['extra'])): ?>
        <div class="mt-2 small opacity-75"><?= $flash['extra'] ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════
     STAGE: UPLOAD
═══════════════════════════════════════════════ -->
<?php if ($stage === 'upload'): ?>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="adm-card">
            <h6 class="fw-bold mb-3">Seleccionar archivo</h6>
            <form method="POST" enctype="multipart/form-data" id="form-upload">

                <!-- Drop zone -->
                <div id="drop-zone"
                     onclick="document.getElementById('csv_file').click()"
                     class="d-flex flex-column align-items-center justify-content-center rounded mb-3"
                     style="border:2px dashed var(--adm-border);background:var(--adm-bg);
                            min-height:160px;cursor:pointer;transition:border-color .2s">
                    <i class="bi bi-cloud-arrow-up" style="font-size:2.5rem;color:var(--adm-muted)"></i>
                    <p class="mb-1 mt-2 fw-semibold" style="color:var(--adm-muted)">
                        Arrastra el CSV aquí o haz clic para seleccionar
                    </p>
                    <small style="color:var(--adm-muted)">Formatos aceptados: .csv, .txt</small>
                    <div id="drop-filename" class="mt-2 small fw-semibold" style="color:var(--adm-gold)"></div>
                </div>
                <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt"
                       class="d-none" required>

                <!-- Detección manual de juego -->
                <div class="mb-3">
                    <label class="form-label">
                        Juego
                        <span class="text-muted fw-normal">(se detecta automáticamente por NPRODUCTO)</span>
                    </label>
                    <select name="juego_override" class="form-select">
                        <option value="">— Auto-detectar —</option>
                        <option value="melate">Melate (NPRODUCTO=40)</option>
                        <option value="revancha">Revancha (NPRODUCTO=41)</option>
                        <option value="revanchita">Revanchita (NPRODUCTO=34)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-admin-primary px-4" id="btn-upload" disabled>
                    <i class="bi bi-eye me-1"></i> Previsualizar
                </button>
            </form>
        </div>
    </div>

    <!-- Instrucciones -->
    <div class="col-12 col-lg-5">
        <div class="adm-card h-100">
            <h6 class="fw-bold mb-3">Formato esperado</h6>
            <p class="small text-muted mb-2">El CSV debe tener la siguiente estructura:</p>
            <div class="table-responsive mb-3">
                <table class="table adm-table table-sm" style="font-size:.75rem">
                    <thead><tr>
                        <th>#</th><th>Columna</th><th>Ejemplo</th>
                    </tr></thead>
                    <tbody>
                        <tr><td>0</td><td>NPRODUCTO</td><td>40 / 41 / 34</td></tr>
                        <tr><td>1</td><td>CONCURSO</td><td>4183</td></tr>
                        <tr><td>2–7</td><td>R1–R6 (ó F1–F6)</td><td>6, 14, 24…</td></tr>
                        <tr><td>8</td><td>R7 (solo Melate)</td><td>22</td></tr>
                        <tr><td>penúltima</td><td>BOLSA</td><td>98800000</td></tr>
                        <tr><td>última</td><td>FECHA</td><td>06/03/2026</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="alert py-2 small mb-0" style="background:rgba(245,166,35,.1);border-color:rgba(245,166,35,.3);color:var(--adm-gold)">
                <i class="bi bi-info-circle me-1"></i>
                Los concursos ya existentes en la BD se <strong>omiten automáticamente</strong>
                (INSERT IGNORE), por lo que puedes subir el CSV completo sin riesgo de duplicados.
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     STAGE: PREVIEW
═══════════════════════════════════════════════ -->
<?php elseif ($stage === 'preview'): ?>

<div class="adm-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <span class="badge <?= $juegoBadge[$juego] ?> me-2 fs-6"><?= $juegoLabel[$juego] ?></span>
            <span class="fw-semibold"><?= number_format(count($csvRows)) ?> filas listas para importar</span>
        </div>
        <span class="small text-muted">
            Mostrando primeras <?= count($preview) ?> de <?= count($csvRows) ?>
        </span>
    </div>

    <div class="table-responsive mb-4">
        <table class="table adm-table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Concurso</th>
                    <th>Números</th>
                    <th>Bolsa</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $camposNum = $juego === 'revanchita'
                ? ['f1','f2','f3','f4','f5','f6']
                : ['r1','r2','r3','r4','r5','r6'];
            foreach ($preview as $row):
            ?>
                <tr>
                    <td><span class="fw-semibold" style="color:var(--adm-gold)">#<?= $row['concurso'] ?></span></td>
                    <td>
                        <?php foreach ($camposNum as $c): ?>
                            <span class="num-bola <?= $juegoNumCls[$juego] ?> me-1"><?= $row[$c] ?></span>
                        <?php endforeach; ?>
                        <?php if ($juego === 'melate'): ?>
                            <span class="num-bola num-adicional ms-1"><?= $row['r7'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= fmtBolsaC((int)$row['bolsa']) ?></td>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (count($csvRows) > 10): ?>
    <p class="text-muted small mb-3 text-center">
        <i class="bi bi-three-dots"></i>
        y <?= number_format(count($csvRows) - 10) ?> filas más…
    </p>
    <?php endif; ?>

    <form method="POST" class="d-flex gap-3 align-items-center">
        <input type="hidden" name="confirmar_import" value="1">
        <button type="submit" class="btn btn-admin-primary px-4">
            <i class="bi bi-cloud-upload me-1"></i>
            Confirmar e importar <?= number_format(count($csvRows)) ?> registros
        </button>
        <a href="importar-csv.php" class="btn btn-admin-outline">
            <i class="bi bi-x-circle me-1"></i> Cancelar
        </a>
    </form>
</div>

<!-- ═══════════════════════════════════════════════
     STAGE: RESULT
═══════════════════════════════════════════════ -->
<?php elseif ($stage === 'result'): ?>

<div class="adm-card text-center py-5">
    <i class="bi bi-check-circle-fill"
       style="font-size:3rem;color:var(--adm-green)"></i>
    <h5 class="mt-3 fw-bold">¡Importación completada!</h5>
    <div class="d-flex justify-content-center gap-4 mt-4 mb-4">
        <a href="importar-csv.php" class="btn btn-admin-primary px-4">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Importar otro archivo
        </a>
        <a href="index.php" class="btn btn-admin-outline px-4">
            <i class="bi bi-speedometer2 me-1"></i> Ir al Dashboard
        </a>
    </div>
</div>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input    = document.getElementById('csv_file');
    const dropZone = document.getElementById('drop-zone');
    const fname    = document.getElementById('drop-filename');
    const btnUp    = document.getElementById('btn-upload');

    function setFile(file) {
        if (!file) return;
        fname.textContent = file.name;
        if (btnUp) btnUp.disabled = false;
        dropZone && (dropZone.style.borderColor = 'var(--adm-gold)');
    }

    input && input.addEventListener('change', () => setFile(input.files[0]));

    if (dropZone) {
        dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.style.borderColor = 'var(--adm-gold)'; });
        dropZone.addEventListener('dragleave', e => { dropZone.style.borderColor = 'var(--adm-border)'; });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (file && input) {
                // Asignar al input via DataTransfer
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                setFile(file);
            }
        });
    }
});
</script>

<?php require __DIR__ . '/layout_bottom.php'; ?>
