<?php
/**
 * Mellatron - API de Estadísticas ECharts
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/MelateRepository.php';
require_once __DIR__ . '/../src/StatsCalculator.php';

$action = $_GET['action'] ?? 'overview';
$juegoInput = $_GET['juego'] ?? 'melate';
$juego = in_array($juegoInput, ['melate', 'revancha', 'revanchita'], true) ? $juegoInput : 'melate';

$repo = new MelateRepository();

try {
    switch ($action) {
        case 'number_profile':
            $numero = (int)($_GET['numero'] ?? 23);
            $data = $repo->obtenerExpedienteNumero($numero, $juego);
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'pairs_matrix':
            $data = $repo->obtenerMatrizParejas($juego);
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'relations_graph':
            $data = $repo->obtenerGrafoRelaciones($juego);
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'trends':
            $rawNums = $_GET['numeros'] ?? '7,23,41';
            $numsArr = array_filter(array_map('intval', explode(',', $rawNums)));
            $data = $repo->obtenerTendenciasNumeros($numsArr, $juego);
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        case 'combination_dna':
            $rawNums = $_GET['numeros'] ?? '7,13,22,34,41,53';
            $numsArr = array_filter(array_map('intval', explode(',', $rawNums)));
            if (count($numsArr) !== 6) {
                echo json_encode(['success' => false, 'error' => 'Se requieren exactamente 6 números entre 1 y 56.']);
                exit;
            }
            $data = $repo->analizarCombinacionAvanzada($numsArr, $juego);
            echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
