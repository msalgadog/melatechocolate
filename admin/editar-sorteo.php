<?php
/**
 * Mellatron Admin — Editar sorteo existente
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getConnection();

$flash    = null;
$sorteo   = null;
$juego    = $_GET['juego']    ?? $_POST['juego']    ?? '';
$concurso = (int)($_GET['concurso'] ?? $_POST['concurso'] ?? 0);

$juegoOpts = ['melate' => 'Melate', 'revancha' => 'Revancha', 'revanchita' => 'Revanchita'];

// ============================================================
// Búsqueda de sorteo
// ============================================================
if ($juego && $concurso > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $table = match($juego) {
        'melate'     => 'sorteos_melate',
        'revancha'   => 'sorteos_revancha',
        'revanchita' => 'sorteos_revanchita',
        default      => null,
    };
    if ($table) {
        $stmt = $db->prepare("SELECT * FROM $table WHERE concurso = ?");
        $stmt->execute([$concurso]);
        $sorteo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sorteo) {
            $flash = ['type' => 'error', 'msg' => "No se encontró el concurso #{$concurso} en {$juegoOpts[$juego]}."];
        }
    }
}

// ============================================================
// Guardado de edición
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_submit'])) {
    $juego    = $_POST['juego']    ?? '';
    $concurso = (int)($_POST['concurso'] ?? 0);
    $toInt    = fn(string $k): int => (int)($_POST[$k] ?? 0);

    try {
        if ($juego === 'melate') {
            $fecha = $_POST['fecha'] ?? '';
            $bolsa = $toInt('bolsa');
            $nums  = ['r1'=>$toInt('r1'),'r2'=>$toInt('r2'),'r3'=>$toInt('r3'),
                      'r4'=>$toInt('r4'),'r5'=>$toInt('r5'),'r6'=>$toInt('r6'),
                      'r7'=>$toInt('r7')];
            $nat   = [$nums['r1'],$nums['r2'],$nums['r3'],$nums['r4'],$nums['r5'],$nums['r6']];
            if (count(array_unique($nat)) < 6) throw new Exception('Los 6 números naturales deben ser únicos.');
            foreach ($nat as $n) if ($n < 1 || $n > 56) throw new Exception("Número fuera de rango: $n");

            $stmt = $db->prepare(
                "UPDATE sorteos_melate SET fecha=:fecha,r1=:r1,r2=:r2,r3=:r3,r4=:r4,r5=:r5,r6=:r6,r7=:r7,bolsa=:bolsa
                 WHERE concurso=:concurso"
            );
            $stmt->execute(['fecha'=>$fecha,'bolsa'=>$bolsa,'concurso'=>$concurso] + $nums);

        } elseif ($juego === 'revancha') {
            $fecha = $_POST['fecha'] ?? '';
            $bolsa = $toInt('bolsa');
            $nums  = ['r1'=>$toInt('r1'),'r2'=>$toInt('r2'),'r3'=>$toInt('r3'),
                      'r4'=>$toInt('r4'),'r5'=>$toInt('r5'),'r6'=>$toInt('r6')];
            if (count(array_unique(array_values($nums))) < 6) throw new Exception('Los 6 números deben ser únicos.');
            foreach ($nums as $n) if ($n < 1 || $n > 56) throw new Exception("Número fuera de rango: $n");

            $stmt = $db->prepare(
                "UPDATE sorteos_revancha SET fecha=:fecha,r1=:r1,r2=:r2,r3=:r3,r4=:r4,r5=:r5,r6=:r6,bolsa=:bolsa
                 WHERE concurso=:concurso"
            );
            $stmt->execute(['fecha'=>$fecha,'bolsa'=>$bolsa,'concurso'=>$concurso] + $nums);

        } elseif ($juego === 'revanchita') {
            $fecha = $_POST['fecha'] ?? '';
            $bolsa = $toInt('bolsa');
            $nums  = ['f1'=>$toInt('f1'),'f2'=>$toInt('f2'),'f3'=>$toInt('f3'),
                      'f4'=>$toInt('f4'),'f5'=>$toInt('f5'),'f6'=>$toInt('f6')];
            if (count(array_unique(array_values($nums))) < 6) throw new Exception('Los 6 números deben ser únicos.');
            foreach ($nums as $n) if ($n < 1 || $n > 39) throw new Exception("Número fuera de rango (1-39): $n");

            $stmt = $db->prepare(
                "UPDATE sorteos_revanchita SET fecha=:fecha,f1=:f1,f2=:f2,f3=:f3,f4=:f4,f5=:f5,f6=:f6,bolsa=:bolsa
                 WHERE concurso=:concurso"
            );
            $stmt->execute(['fecha'=>$fecha,'bolsa'=>$bolsa,'concurso'=>$concurso] + $nums);
        }

        $flash = ['type' => 'success', 'msg' => "✓ Concurso #{$concurso} de {$juegoOpts[$juego]} actualizado."];
        // Recargar sorteo para mostrar datos actualizados
        $table = "sorteos_{$juego}";
        $stmt2 = $db->prepare("SELECT * FROM $table WHERE concurso = ?");
        $stmt2->execute([$concurso]);
        $sorteo = $stmt2->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $flash  = ['type' => 'error', 'msg' => $e->getMessage()];
        $sorteo = $_POST; // repopulate form with submitted values
    }
}

// ============================================================
// Recientes de cada juego para la tabla de búsqueda rápida
// ============================================================
$recientes = [];
foreach (['melate','revancha','revanchita'] as $j) {
    $t = "sorteos_{$j}";
    $recientes[$j] = $db->query("SELECT concurso, fecha FROM $t ORDER BY concurso DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
}

$page_title  = 'Editar sorteo';
$active_page = 'editar';
require __DIR__ . '/layout_top.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-0">Editar sorteo</h4>
    <small class="text-muted">Busca un concurso existente para corregir sus datos.</small>
</div>

<?php if ($flash): ?>
<div class="alert <?= $flash['type'] === 'success' ? 'flash-success' : 'flash-error' ?> mb-4">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- Buscador -->
    <div class="col-12 col-lg-4">
        <div class="adm-card h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-search me-1"></i>Buscar concurso</h6>
            <form method="GET">
                <div class="mb-3">
                    <label class="form-label">Juego</label>
                    <select name="juego" class="form-select" required id="buscarJuego">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($juegoOpts as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $juego === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Concurso #</label>
                    <input type="number" name="concurso" class="form-control text-center fw-bold"
                           min="1" required value="<?= $concurso ?: '' ?>">
                </div>
                <button type="submit" class="btn btn-admin-primary w-100">
                    <i class="bi bi-search me-1"></i>Buscar
                </button>
            </form>

            <!-- Accesos rápidos -->
            <?php foreach (['melate','revancha','revanchita'] as $j): ?>
            <?php if (!empty($recientes[$j])): ?>
            <hr style="border-color:var(--adm-border)">
            <div class="mb-2">
                <span class="small text-muted fw-semibold"><?= $juegoOpts[$j] ?> — recientes:</span>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    <?php foreach (array_slice($recientes[$j], 0, 5) as $r): ?>
                    <a href="?juego=<?= $j ?>&concurso=<?= $r['concurso'] ?>"
                       class="badge text-decoration-none"
                       style="background:var(--adm-border);color:var(--adm-text)">
                        #<?= $r['concurso'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Formulario de edición -->
    <div class="col-12 col-lg-8">
        <?php if ($sorteo): ?>
        <div class="adm-card">
            <h6 class="fw-bold mb-3">
                <span class="badge badge-<?= $juego ?> me-1"><?= $juegoOpts[$juego] ?></span>
                Concurso #<?= $concurso ?>
            </h6>

            <form method="POST" novalidate>
                <input type="hidden" name="editar_submit" value="1">
                <input type="hidden" name="juego"    value="<?= htmlspecialchars($juego) ?>">
                <input type="hidden" name="concurso" value="<?= (int)$concurso ?>">

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <label class="form-label">Fecha del sorteo</label>
                        <input type="date" name="fecha" class="form-control" required
                               value="<?= htmlspecialchars($sorteo['fecha']) ?>">
                    </div>
                    <div class="col-6 col-md-8">
                        <label class="form-label">Bolsa acumulada ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="bolsa" class="form-control"
                                   min="0" value="<?= htmlspecialchars($sorteo['bolsa'] ?? 0) ?>">
                        </div>
                    </div>
                </div>

                <?php
                $camposNat = $juego === 'revanchita'
                    ? ['f1','f2','f3','f4','f5','f6']
                    : ['r1','r2','r3','r4','r5','r6'];
                $maxNum = $juego === 'revanchita' ? 39 : 56;
                ?>
                <label class="form-label fw-semibold mb-2">
                    Números <?= $juego === 'revanchita' ? '(1–39)' : '(1–56)' ?>
                </label>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <?php foreach ($camposNat as $i => $campo): ?>
                    <div>
                        <label class="form-label text-center d-block small">N<?= $i+1 ?></label>
                        <input type="number" name="<?= $campo ?>"
                               class="form-control text-center fw-bold num-edit"
                               style="width:72px;font-size:1.2rem"
                               min="1" max="<?= $maxNum ?>" required
                               value="<?= (int)($sorteo[$campo] ?? 0) ?>">
                    </div>
                    <?php endforeach; ?>

                    <?php if ($juego === 'melate'): ?>
                    <div style="border-left:1px solid var(--adm-border);padding-left:1rem">
                        <label class="form-label text-center d-block small" style="color:#f0a040">Adicional</label>
                        <input type="number" name="r7"
                               class="form-control text-center fw-bold"
                               style="width:72px;font-size:1.2rem;border-color:#f0a040"
                               min="1" max="56" required
                               value="<?= (int)($sorteo['r7'] ?? 0) ?>">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-admin-primary px-4">
                        <i class="bi bi-save me-1"></i> Actualizar
                    </button>
                    <a href="editar-sorteo.php" class="btn btn-admin-outline">
                        <i class="bi bi-arrow-left me-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

        <?php else: ?>
        <div class="adm-card d-flex align-items-center justify-content-center"
             style="min-height:220px;border-style:dashed">
            <div class="text-center text-muted">
                <i class="bi bi-search" style="font-size:2.5rem;opacity:.3"></i>
                <p class="mt-2 mb-0">Busca un concurso para editarlo</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /row -->

<script>
document.querySelectorAll('.num-edit').forEach(input => {
    input.addEventListener('change', () => {
        const campos = [...document.querySelectorAll('.num-edit')];
        const vals   = campos.map(c => c.value).filter(v => v !== '');
        const dupes  = vals.length !== new Set(vals).size;
        campos.forEach(c => { c.style.borderColor = dupes ? 'var(--adm-red)' : ''; });
    });
});
</script>

<?php require __DIR__ . '/layout_bottom.php'; ?>
