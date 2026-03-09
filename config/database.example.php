<?php
// ============================================================
//  Melate el Chocolate — Configuración de Base de Datos
//  COPIA este archivo como config/database.php y ajusta los valores
// ============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'mellatron');
define('DB_USER', 'tu_usuario_db');
define('DB_PASS', 'tu_password_db');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
//  Admin Dashboard — Credenciales
//  Genera el hash con:
//  php -r "echo password_hash('TuNuevaPass', PASSWORD_BCRYPT, ['cost'=>12]);"
// ============================================================
define('ADMIN_USER',      'admin');
define('ADMIN_PASS_HASH', 'REEMPLAZA_CON_TU_HASH_BCRYPT');

// Versión y nombre de la app
define('APP_NAME',    'Melate el Chocolate');
define('APP_SLOGAN',  'Tu portal de estadísticas y predicciones del Melate');
define('APP_VERSION', '1.0.0');
define('APP_URL',     '');       // déjalo vacío para rutas relativas
