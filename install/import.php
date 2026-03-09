<?php
/**
 * Mellatron - Script de importación de CSVs históricos
 * Ejecutar UNA SOLA VEZ desde CLI o navegador:
 *   php install/import.php
 */

require_once __DIR__ . '/../config/database.php';

// -------- Conexión PDO --------
try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

// -------- Función auxiliar --------
function parseFecha(string $str): string
{
    // El CSV usa formato dd/mm/yyyy
    [$d, $m, $y] = explode('/', $str);
    return "$y-$m-$d";
}

function importCsv(PDO $pdo, string $file, string $table, array $cols, bool $hasBolsa = true): void
{
    if (!file_exists($file)) {
        echo "⚠️  No encontré el archivo: $file\n";
        return;
    }

    $handle = fopen($file, 'r');
    fgetcsv($handle); // saltar encabezado

    $inserted = 0;
    $skipped  = 0;

    $placeholders = implode(', ', array_fill(0, count($cols), '?'));
    $colNames     = implode(', ', $cols);
    $sql = "INSERT IGNORE INTO $table ($colNames) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 3) continue;
        $values = [];
        foreach ($cols as $col) {
            switch ($col) {
                case 'concurso':
                    $values[] = (int) $row[1];
                    break;
                case 'bolsa':
                    // bolsa está en posición diferente según tabla
                    $idx      = count($row) - 2; // penúltima columna
                    $values[] = (int) $row[$idx];
                    break;
                case 'fecha':
                    $idx      = count($row) - 1; // última columna
                    $values[] = parseFecha(trim($row[$idx]));
                    break;
                default:
                    // r1-r7 o f1-f6: extraer el número
                    preg_match('/\d+$/', $col, $m);
                    $idx      = (int) $m[0] + 1; // columna en CSV (base 0: NPRODUCTO,CONCURSO,R1,...)
                    $values[] = (int) $row[$idx];
                    break;
            }
        }
        try {
            $stmt->execute($values);
            if ($stmt->rowCount()) {
                $inserted++;
            } else {
                $skipped++;
            }
        } catch (PDOException $e) {
            echo "  Error fila: " . implode(',', $row) . " => " . $e->getMessage() . "\n";
        }
    }
    fclose($handle);
    echo "✅ $table: $inserted insertados, $skipped duplicados omitidos.\n";
}

echo "=== Mellatron - Importación de datos históricos ===\n\n";

// -------- Melate --------
importCsv($pdo,
    __DIR__ . '/../sources/melate.csv',
    'sorteos_melate',
    ['concurso', 'r1', 'r2', 'r3', 'r4', 'r5', 'r6', 'r7', 'bolsa', 'fecha']
);

// -------- Revancha --------
importCsv($pdo,
    __DIR__ . '/../sources/revancha.csv',
    'sorteos_revancha',
    ['concurso', 'r1', 'r2', 'r3', 'r4', 'r5', 'r6', 'bolsa', 'fecha']
);

// -------- Revanchita --------
importCsv($pdo,
    __DIR__ . '/../sources/revanchita.csv',
    'sorteos_revanchita',
    ['concurso', 'f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'bolsa', 'fecha']
);

echo "\n¡Importación completa!\n";
