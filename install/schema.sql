-- ============================================================
--  Mellatron - Schema de Base de Datos (MariaDB)
--  Lotería Nacional México: Melate, Revancha y Revanchita
-- ============================================================

CREATE DATABASE IF NOT EXISTS mellatron
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mellatron;

-- ============================================================
-- Tabla Melate (NPRODUCTO = 40)
-- R1-R6 = números naturales, R7 = número adicional
-- ============================================================
CREATE TABLE IF NOT EXISTS sorteos_melate (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    concurso    INT UNSIGNED NOT NULL UNIQUE,
    r1          TINYINT UNSIGNED NOT NULL,
    r2          TINYINT UNSIGNED NOT NULL,
    r3          TINYINT UNSIGNED NOT NULL,
    r4          TINYINT UNSIGNED NOT NULL,
    r5          TINYINT UNSIGNED NOT NULL,
    r6          TINYINT UNSIGNED NOT NULL,
    r7          TINYINT UNSIGNED NOT NULL COMMENT 'Número adicional',
    bolsa       BIGINT UNSIGNED NOT NULL DEFAULT 0,
    fecha       DATE NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha   (fecha),
    INDEX idx_concurso (concurso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla Revancha (NPRODUCTO = 41)
-- R1-R6 = números naturales, sin adicional
-- ============================================================
CREATE TABLE IF NOT EXISTS sorteos_revancha (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    concurso    INT UNSIGNED NOT NULL UNIQUE,
    r1          TINYINT UNSIGNED NOT NULL,
    r2          TINYINT UNSIGNED NOT NULL,
    r3          TINYINT UNSIGNED NOT NULL,
    r4          TINYINT UNSIGNED NOT NULL,
    r5          TINYINT UNSIGNED NOT NULL,
    r6          TINYINT UNSIGNED NOT NULL,
    bolsa       BIGINT UNSIGNED NOT NULL DEFAULT 0,
    fecha       DATE NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha    (fecha),
    INDEX idx_concurso (concurso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla Revanchita (NPRODUCTO = 34)
-- F1-F6 = números naturales, sin adicional
-- ============================================================
CREATE TABLE IF NOT EXISTS sorteos_revanchita (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    concurso    INT UNSIGNED NOT NULL UNIQUE,
    f1          TINYINT UNSIGNED NOT NULL,
    f2          TINYINT UNSIGNED NOT NULL,
    f3          TINYINT UNSIGNED NOT NULL,
    f4          TINYINT UNSIGNED NOT NULL,
    f5          TINYINT UNSIGNED NOT NULL,
    f6          TINYINT UNSIGNED NOT NULL,
    bolsa       BIGINT UNSIGNED NOT NULL DEFAULT 0,
    fecha       DATE NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha    (fecha),
    INDEX idx_concurso (concurso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
