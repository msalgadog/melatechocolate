<?php
/**
 * Mellatron Admin — Guard de sesión
 * Incluir al inicio de cada página protegida del admin.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/database.php';
}
if (empty($_SESSION['admin_logged_in'])) {
    $loginUrl = (APP_URL ?: '') . '/admin/login.php';
    header('Location: ' . $loginUrl);
    exit;
}
