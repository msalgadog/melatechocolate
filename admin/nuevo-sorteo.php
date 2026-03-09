<?php
/**
 * Mellatron Admin — Ingresar nuevo sorteo (Melate / Revancha / Revanchita)
 * Usa UPSERT (INSERT ... ON DUPLICATE KEY UPDATE) para poder corregir resultados.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getConnection();

// ============================================================
// Siguiente número de concurso sugerido para cada juego
// ============================================================
$siguiente = [];
foreach (['melate' => 'sorteos_melate', 'revancha' => 'sorteos_revancha', 'revanchita' => 'sorteos_revanchita'] as $j => $t) {
    $max = (int)$db->query("SELECT COALESCE(MAX(concurso),0) FROM $t")->fetchColumn();
    $siguiente[$j] = $max + 1;
}

// ============================================================
// Procesamiento del formulario
// ============================================================
$flash = null; // ['type' => 'success'|'error', 'msg' => '...']
$activeTab = 'melate'; // tab a mostrar tras POST

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['juego_submit'])) {
    $juego = $_POST['juego_submit'];
    $activeTab = $juego;

    try {
        // ---- Helpers de validación ----
        $toInt = fn(string $k): int => (int)($_POST[$k] ?? 0);

        if ($juego === 'melate') {
            $concurso = $toInt('m_concurso');
            $fecha    = $_POST['m_fecha'] ?? '';
            $bolsa    = $toInt('m_bolsa');
            $nums     = [
                'r1' => $toInt('m_r1'), 'r2' => $toInt('m_r2'),
                'r3' => $toInt('m_r3'), 'r4' => $toInt('m_r4'),
                'r5' => $toInt('m_r5'), 'r6' => $toInt('m_r6'),
                'r7' => $toInt('m_r7'),
            ];
            $naturales = [$nums['r1'],$nums['r2'],$nums['r3'],$nums['r4'],$nums['r5'],$nums['r6']];

            // Validaciones
            if ($concurso <= 0)  throw new Exception('Número de concurso inválido.');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new Exception('Fecha inválida.');
            if (count(array_unique($naturales)) < 6) throw new Exception('Los 6 números naturales deben ser únicos.');
            foreach ($naturales as $n) {
                if ($n < 1 || $n > 56) throw new Exception("Número natural fuera de rango (1-56): $n");
            }
            if ($nums['r7'] < 1 || $nums['r7'] > 56) throw new Exception('Número adicional fuera de rango (1-56).');

            $stmt = $db->prepare(
                "INSERT INTO sorteos_melate (concurso,fecha,r1,r2,r3,r4,r5,r6,r7,bolsa)
                 VALUES (:concurso,:fecha,:r1,:r2,:r3,:r4,:r5,:r6,:r7,:bolsa)
                 ON DUPLICATE KEY UPDATE
                    fecha=VALUES(fecha), r1=VALUES(r1), r2=VALUES(r2), r3=VALUES(r3),
                    r4=VALUES(r4),       r5=VALUES(r5), r6=VALUES(r6), r7=VALUES(r7),
                    bolsa=VALUES(bolsa)"
            );
            $stmt->execute(['concurso'=>$concurso,'fecha'=>$fecha,'bolsa'=>$bolsa] + $nums);

        } elseif ($juego === 'revancha') {
            $concurso = $toInt('rv_concurso');
            $fecha    = $_POST['rv_fecha'] ?? '';
            $bolsa    = $toInt('rv_bolsa');
            $nums     = [
                'r1' => $toInt('rv_r1'), 'r2' => $toInt('rv_r2'),
                'r3' => $toInt('rv_r3'), 'r4' => $toInt('rv_r4'),
                'r5' => $toInt('rv_r5'), 'r6' => $toInt('rv_r6'),
            ];

            if ($concurso <= 0)  throw new Exception('Número de concurso inválido.');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new Exception('Fecha inválida.');
            if (count(array_unique(array_values($nums))) < 6) throw new Exception('Los 6 números deben ser únicos.');
            foreach ($nums as $n) {
                if ($n < 1 || $n > 56) throw new Exception("Número fuera de rango (1-56): $n");
            }

            $stmt = $db->prepare(
                "INSERT INTO sorteos_revancha (concurso,fecha,r1,r2,r3,r4,r5,r6,bolsa)
                 VALUES (:concurso,:fecha,:r1,:r2,:r3,:r4,:r5,:r6,:bolsa)
                 ON DUPLICATE KEY UPDATE
                    fecha=VALUES(fecha), r1=VALUES(r1), r2=VALUES(r2), r3=VALUES(r3),
                    r4=VALUES(r4),       r5=VALUES(r5), r6=VALUES(r6), bolsa=VALUES(bolsa)"
            );
            $stmt->execute(['concurso'=>$concurso,'fecha'=>$fecha,'bolsa'=>$bolsa] + $nums);

        } elseif ($juego === 'revanchita') {
            $concurso = $toInt('ri_concurso');
            $fecha    = $_POST['ri_fecha'] ?? '';
            $bolsa    = $toInt('ri_bolsa');
            $nums     = [
                'f1' => $toInt('ri_f1'), 'f2' => $toInt('ri_f2'),
                'f3' => $toInt('ri_f3'), 'f4' => $toInt('ri_f4'),
                'f5' => $toInt('ri_f5'), 'f6' => $toInt('ri_f6'),
            ];

            if ($concurso <= 0)  throw new Exception('Número de concurso inválido.');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new Exception('Fecha inválida.');
            if (count(array_unique(array_values($nums))) < 6) throw new Exception('Los 6 números deben ser únicos.');
            foreach ($nums as $n) {
                if ($n < 1 || $n > 39) throw new Exception("Número fuera de rango (1-39): $n");
            }

            $stmt = $db->prepare(
                "INSERT INTO sorteos_revanchita (concurso,fecha,f1,f2,f3,f4,f5,f6,bolsa)
                 VALUES (:concurso,:fecha,:f1,:f2,:f3,:f4,:f5,:f6,:bolsa)
                 ON DUPLICATE KEY UPDATE
                    fecha=VALUES(fecha), f1=VALUES(f1), f2=VALUES(f2), f3=VALUES(f3),
                    f4=VALUES(f4),       f5=VALUES(f5), f6=VALUES(f6), bolsa=VALUES(bolsa)"
            );
            $stmt->execute(['concurso'=>$concurso,'fecha'=>$fecha,'bolsa'=>$bolsa] + $nums);
        }

        // Actualizar sugerido
        $max = (int)$db->query("SELECT MAX(concurso) FROM sorteos_{$juego}")->fetchColumn();
        $siguiente[$juego] = $max + 1;

        $flash = ['type' => 'success', 'msg' => '✓ Sorteo ' . ucfirst($juego) . " concurso #{$concurso} guardado correctamente."];

    } catch (Exception $e) {
        $flash = ['type' => 'error', 'msg' => $e->getMessage()];
    }
}

$hoy = date('Y-m-d');
$page_title  = 'Nuevo sorteo';
$active_page = 'nuevo';
require __DIR__ . '/layout_top.php';
?>

<div class="d-flex align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Ingresar resultado</h4>
        <small class="text-muted">Carga el resultado de un sorteo. Si el concurso ya existe, se actualizarán los datos.</small>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert <?= $flash['type'] === 'success' ? 'flash-success' : 'flash-error' ?> mb-4">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="adm-card">

    <!-- TABS JUEGO -->
    <ul class="nav nav-tabs mb-4" id="gameTab">
        <?php
        $tabs = [
            'melate'     => ['Melate',     'badge-melate'],
            'revancha'   => ['Revancha',   'badge-revancha'],
            'revanchita' => ['Revanchita', 'badge-revanchita'],
        ];
        foreach ($tabs as $t => [$label, $badge]):
        ?>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === $t ? 'active' : '' ?>"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-<?= $t ?>">
                <span class="badge <?= $badge ?>"><?= $label ?></span>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">

        <!-- ======== MELATE ======== -->
        <div class="tab-pane fade <?= $activeTab === 'melate' ? 'show active' : '' ?>" id="tab-melate">
            <form method="POST" id="form-melate" novalidate>
                <input type="hidden" name="juego_submit" value="melate">

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <label class="form-label">Concurso #</label>
                        <input type="number" name="m_concurso" class="form-control text-center fw-bold"
                               min="1" required
                               value="<?= htmlspecialchars($_POST['m_concurso'] ?? $siguiente['melate']) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Fecha del sorteo</label>
                        <input type="date" name="m_fecha" class="form-control" required
                               value="<?= htmlspecialchars($_POST['m_fecha'] ?? $hoy) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Bolsa acumulada ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="m_bolsa" class="form-control"
                                   min="0" placeholder="0"
                                   value="<?= htmlspecialchars($_POST['m_bolsa'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Números naturales -->
                <label class="form-label fw-semibold mb-2">
                    Números naturales <small class="text-muted">(6 números únicos, 1–56)</small>
                </label>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div>
                        <label class="form-label text-center d-block small">N<?= $i ?></label>
                        <input type="number" name="m_r<?= $i ?>"
                               class="form-control text-center fw-bold num-input"
                               style="width:72px;font-size:1.2rem"
                               min="1" max="56" required
                               data-grupo="melate-naturales"
                               value="<?= htmlspecialchars($_POST["m_r$i"] ?? '') ?>">
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Número adicional -->
                <label class="form-label fw-semibold mb-2">
                    Número adicional <small class="text-muted">(1–56)</small>
                </label>
                <div class="d-flex mb-4">
                    <div>
                        <label class="form-label text-center d-block small">Adicional</label>
                        <input type="number" name="m_r7"
                               class="form-control text-center fw-bold"
                               style="width:72px;font-size:1.2rem;border-color:#f0a040"
                               min="1" max="56" required
                               value="<?= htmlspecialchars($_POST['m_r7'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-admin-primary px-4">
                        <i class="bi bi-save me-1"></i> Guardar Melate
                    </button>
                    <button type="reset" class="btn btn-admin-outline">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </button>
                </div>
            </form>
        </div><!-- /tab-melate -->

        <!-- ======== REVANCHA ======== -->
        <div class="tab-pane fade <?= $activeTab === 'revancha' ? 'show active' : '' ?>" id="tab-revancha">
            <form method="POST" id="form-revancha" novalidate>
                <input type="hidden" name="juego_submit" value="revancha">

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <label class="form-label">Concurso #</label>
                        <input type="number" name="rv_concurso" class="form-control text-center fw-bold"
                               min="1" required
                               value="<?= htmlspecialchars($_POST['rv_concurso'] ?? $siguiente['revancha']) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Fecha del sorteo</label>
                        <input type="date" name="rv_fecha" class="form-control" required
                               value="<?= htmlspecialchars($_POST['rv_fecha'] ?? $hoy) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Bolsa acumulada ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="rv_bolsa" class="form-control"
                                   min="0" placeholder="0"
                                   value="<?= htmlspecialchars($_POST['rv_bolsa'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <label class="form-label fw-semibold mb-2">
                    Números <small class="text-muted">(6 únicos, 1–56)</small>
                </label>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div>
                        <label class="form-label text-center d-block small">N<?= $i ?></label>
                        <input type="number" name="rv_r<?= $i ?>"
                               class="form-control text-center fw-bold num-input"
                               style="width:72px;font-size:1.2rem"
                               min="1" max="56" required
                               data-grupo="revancha-naturales"
                               value="<?= htmlspecialchars($_POST["rv_r$i"] ?? '') ?>">
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-admin-primary px-4">
                        <i class="bi bi-save me-1"></i> Guardar Revancha
                    </button>
                    <button type="reset" class="btn btn-admin-outline">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </button>
                </div>
            </form>
        </div><!-- /tab-revancha -->

        <!-- ======== REVANCHITA ======== -->
        <div class="tab-pane fade <?= $activeTab === 'revanchita' ? 'show active' : '' ?>" id="tab-revanchita">
            <form method="POST" id="form-revanchita" novalidate>
                <input type="hidden" name="juego_submit" value="revanchita">

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <label class="form-label">Concurso #</label>
                        <input type="number" name="ri_concurso" class="form-control text-center fw-bold"
                               min="1" required
                               value="<?= htmlspecialchars($_POST['ri_concurso'] ?? $siguiente['revanchita']) ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Fecha del sorteo</label>
                        <input type="date" name="ri_fecha" class="form-control" required
                               value="<?= htmlspecialchars($_POST['ri_fecha'] ?? $hoy) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Bolsa acumulada ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="ri_bolsa" class="form-control"
                                   min="0" placeholder="0"
                                   value="<?= htmlspecialchars($_POST['ri_bolsa'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <label class="form-label fw-semibold mb-2">
                    Números <small class="text-muted">(6 únicos, 1–39)</small>
                </label>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div>
                        <label class="form-label text-center d-block small">N<?= $i ?></label>
                        <input type="number" name="ri_f<?= $i ?>"
                               class="form-control text-center fw-bold num-input"
                               style="width:72px;font-size:1.2rem"
                               min="1" max="39" required
                               data-grupo="revanchita-naturales"
                               value="<?= htmlspecialchars($_POST["ri_f$i"] ?? '') ?>">
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-admin-primary px-4">
                        <i class="bi bi-save me-1"></i> Guardar Revanchita
                    </button>
                    <button type="reset" class="btn btn-admin-outline">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </button>
                </div>
            </form>
        </div><!-- /tab-revanchita -->

    </div><!-- /tab-content -->
</div><!-- /adm-card -->

<script>
// Validación cliente: detección de números repetidos
document.querySelectorAll('.num-input').forEach(input => {
    input.addEventListener('change', () => {
        const grupo = input.dataset.grupo;
        const campos = [...document.querySelectorAll(`[data-grupo="${grupo}"]`)];
        const vals = campos.map(c => c.value).filter(v => v !== '');
        const dupes = vals.length !== new Set(vals).size;
        campos.forEach(c => {
            c.style.borderColor = dupes ? 'var(--adm-red)' : '';
        });
        if (dupes) {
            input.setCustomValidity('Números repetidos');
        } else {
            campos.forEach(c => c.setCustomValidity(''));
        }
    });
});
</script>

<?php require __DIR__ . '/layout_bottom.php'; ?>
