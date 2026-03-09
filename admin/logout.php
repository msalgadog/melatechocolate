<?php
/**
 * Mellatron Admin — Logout
 */
session_start();
session_destroy();
require_once __DIR__ . '/../config/database.php';
header('Location: ' . (APP_URL ?: '') . '/admin/login.php');
exit;
