<?php
/**
 * Mellatron Admin — Login
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// Si ya está autenticado, directo al dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . (APP_URL ?: '') . '/admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['usuario'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        header('Location: ' . (APP_URL ?: '') . '/admin/index.php');
        exit;
    }

    $error = 'Usuario o contraseña incorrectos.';
    // Pequeño delay para evitar fuerza bruta
    sleep(1);
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — <?= APP_NAME ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #0f1117; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card {
            width: 100%; max-width: 400px;
            background: #1a1d27;
            border: 1px solid #2d3145;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,.5);
        }
        .login-logo { font-size: 2rem; color: #f5a623; }
        .form-control {
            background: #0f1117;
            border-color: #2d3145;
            color: #e8eaf0;
        }
        .form-control:focus {
            background: #0f1117;
            border-color: #f5a623;
            color: #e8eaf0;
            box-shadow: 0 0 0 .2rem rgba(245,166,35,.25);
        }
        .btn-admin { background: #f5a623; border: none; color: #000; font-weight: 700; }
        .btn-admin:hover { background: #e09515; color: #000; }
        label { color: #9aa3b5; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="login-logo"><i class="bi bi-shield-lock-fill"></i></div>
        <h4 class="text-white mt-2 mb-0"><?= APP_NAME ?> Admin</h4>
        <small class="text-muted">Panel de administración</small>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Usuario</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#0f1117;border-color:#2d3145;color:#9aa3b5">
                    <i class="bi bi-person-fill"></i>
                </span>
                <input type="text" name="usuario" class="form-control"
                       value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                       required autofocus placeholder="admin">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#0f1117;border-color:#2d3145;color:#9aa3b5">
                    <i class="bi bi-key-fill"></i>
                </span>
                <input type="password" name="password" class="form-control"
                       required placeholder="••••••••••">
            </div>
        </div>
        <button type="submit" class="btn btn-admin w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="<?= (APP_URL ?: '') ?>/index.php" class="text-muted small">
            <i class="bi bi-arrow-left"></i> Volver al sitio
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>
