<?php
declare(strict_types=1);
/**
 * Mellatron - Repositorio de sorteos
 * Consultas para Melate, Revancha y Revanchita
 */

require_once __DIR__ . '/Database.php';

class MelateRepository
{
    /** @var PDO */
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =============================================
    // Último resultado de cada juego
    // =============================================

    public function ultimoMelate(): ?array
    {
        $stmt = $this->db->query(
            "SELECT t.* FROM (
                SELECT *,
                    CASE WHEN LEAD(bolsa) OVER (ORDER BY concurso ASC) < bolsa THEN 1 ELSE 0 END AS ganador
                FROM sorteos_melate
            ) t ORDER BY concurso DESC LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    public function ultimoRevancha(): ?array
    {
        $stmt = $this->db->query(
            "SELECT t.* FROM (
                SELECT *,
                    CASE WHEN LEAD(bolsa) OVER (ORDER BY concurso ASC) < bolsa THEN 1 ELSE 0 END AS ganador
                FROM sorteos_revancha
            ) t ORDER BY concurso DESC LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    public function ultimoRevanchita(): ?array
    {
        $stmt = $this->db->query(
            "SELECT t.* FROM (
                SELECT *,
                    CASE WHEN LEAD(bolsa) OVER (ORDER BY concurso ASC) < bolsa THEN 1 ELSE 0 END AS ganador
                FROM sorteos_revanchita
            ) t ORDER BY concurso DESC LIMIT 1"
        );
        return $stmt->fetch() ?: null;
    }

    // =============================================
    // Historial paginado
    // =============================================

    public function historialMelate(int $pagina = 1, int $porPagina = 20): array
    {
        $offset = ($pagina - 1) * $porPagina;
        // LEAD(bolsa): si el siguiente sorteo tiene bolsa menor, el pozo se reinició → hubo ganador
        $stmt = $this->db->prepare(
            "SELECT t.* FROM (
                SELECT *,
                    CASE WHEN LEAD(bolsa) OVER (ORDER BY concurso ASC) < bolsa THEN 1 ELSE 0 END AS ganador
                FROM sorteos_melate
            ) t
            ORDER BY concurso DESC
            LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function historialRevancha(int $pagina = 1, int $porPagina = 20): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $stmt = $this->db->prepare(
            "SELECT t.* FROM (
                SELECT *,
                    CASE WHEN LEAD(bolsa) OVER (ORDER BY concurso ASC) < bolsa THEN 1 ELSE 0 END AS ganador
                FROM sorteos_revancha
            ) t
            ORDER BY concurso DESC
            LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function historialRevanchita(int $pagina = 1, int $porPagina = 20): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $stmt = $this->db->prepare(
            "SELECT t.* FROM (
                SELECT *,
                    CASE WHEN LEAD(bolsa) OVER (ORDER BY concurso ASC) < bolsa THEN 1 ELSE 0 END AS ganador
                FROM sorteos_revanchita
            ) t
            ORDER BY concurso DESC
            LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // =============================================
    // Total de registros para paginación
    // =============================================

    public function totalMelate(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM sorteos_melate")->fetchColumn();
    }

    public function totalRevancha(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM sorteos_revancha")->fetchColumn();
    }

    public function totalRevanchita(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM sorteos_revanchita")->fetchColumn();
    }

    // =============================================
    // Frecuencia de números (todos los sorteos)
    // =============================================

    public function frecuenciaMelate(): array
    {
        return $this->frecuenciaQuery('sorteos_melate', ['r1','r2','r3','r4','r5','r6']);
    }

    public function frecuenciaMelateConAdicional(): array
    {
        return $this->frecuenciaQuery('sorteos_melate', ['r1','r2','r3','r4','r5','r6','r7']);
    }

    public function frecuenciaAdicionalMelate(): array
    {
        return $this->frecuenciaQuery('sorteos_melate', ['r7']);
    }

    public function frecuenciaRevancha(): array
    {
        return $this->frecuenciaQuery('sorteos_revancha', ['r1','r2','r3','r4','r5','r6']);
    }

    public function frecuenciaRevanchita(): array
    {
        return $this->frecuenciaQuery('sorteos_revanchita', ['f1','f2','f3','f4','f5','f6']);
    }

    private function frecuenciaQuery(string $table, array $cols): array
    {
        $unions = implode(" UNION ALL ", array_map(
            fn($c) => "SELECT $c AS num FROM $table",
            $cols
        ));
        $stmt = $this->db->query(
            "SELECT num, COUNT(*) AS frecuencia FROM ($unions) t
             GROUP BY num ORDER BY num ASC"
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int) $row['num']] = (int) $row['frecuencia'];
        }
        // Garantizar que todos los números 1-56 estén presentes
        for ($i = 1; $i <= 56; $i++) {
            if (!isset($result[$i])) $result[$i] = 0;
        }
        ksort($result);
        return $result;
    }

    // =============================================
    // Retardo: cuántos sorteos lleva sin salir
    // =============================================

    public function retardoMelate(): array
    {
        return $this->retardoQuery('sorteos_melate', ['r1','r2','r3','r4','r5','r6']);
    }

    public function retardoRevancha(): array
    {
        return $this->retardoQuery('sorteos_revancha', ['r1','r2','r3','r4','r5','r6']);
    }

    public function retardoRevanchita(): array
    {
        return $this->retardoQuery('sorteos_revanchita', ['f1','f2','f3','f4','f5','f6']);
    }

    private function retardoQuery(string $table, array $cols): array
    {
        $total = (int) $this->db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        $retardo = [];
        for ($n = 1; $n <= 56; $n++) {
            $conditions = implode(' OR ', array_map(fn($c) => "$c = $n", $cols));
            $rank = (int) $this->db->query(
                "SELECT COUNT(*) FROM (
                    SELECT concurso FROM $table ORDER BY concurso DESC
                ) sub WHERE concurso > (
                    SELECT COALESCE(MAX(concurso), 0) FROM $table WHERE $conditions
                )"
            )->fetchColumn();
            $retardo[$n] = $rank;
        }
        return $retardo;
    }

    // =============================================
    // Pares más frecuentes (Melate)
    // =============================================
    public function paresMasFrecuentesMelate(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT a, b, COUNT(*) AS veces FROM (
                SELECT LEAST(r1,r2) AS a, GREATEST(r1,r2) AS b FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r3), GREATEST(r1,r3) FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r4), GREATEST(r1,r4) FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r5), GREATEST(r1,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r6), GREATEST(r1,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r3), GREATEST(r2,r3) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r4), GREATEST(r2,r4) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r5), GREATEST(r2,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r6), GREATEST(r2,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r3,r4), GREATEST(r3,r4) FROM sorteos_melate UNION ALL
                SELECT LEAST(r3,r5), GREATEST(r3,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r3,r6), GREATEST(r3,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r4,r5), GREATEST(r4,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r4,r6), GREATEST(r4,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r5,r6), GREATEST(r5,r6) FROM sorteos_melate
            ) pares GROUP BY a, b ORDER BY veces DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // =============================================
    // Suma de números por sorteo (distribución)
    // =============================================
    public function distribucionSumaMelate(): array
    {
        $stmt = $this->db->query(
            "SELECT (r1+r2+r3+r4+r5+r6) AS suma FROM sorteos_melate"
        );
        $sumas = array_column($stmt->fetchAll(), 'suma');
        return [
            'min'     => min($sumas),
            'max'     => max($sumas),
            'promedio'=> round(array_sum($sumas) / count($sumas), 1),
            'valores' => $sumas,
        ];
    }

    // =============================================
    // Últimos N sorteos (para análisis de tendencia)
    // =============================================
    public function ultimosSorteosMelate(int $n = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM sorteos_melate ORDER BY concurso DESC LIMIT :n"
        );
        $stmt->bindValue(':n', $n, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    public function ultimosSorteosRevancha(int $n = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM sorteos_revancha ORDER BY concurso DESC LIMIT :n"
        );
        $stmt->bindValue(':n', $n, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    public function ultimosSorteosRevanchita(int $n = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM sorteos_revanchita ORDER BY concurso DESC LIMIT :n"
        );
        $stmt->bindValue(':n', $n, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    // =============================================
    // Buscar sorteo por número de concurso
    // =============================================
    public function buscarConcurso(int $concurso): array
    {
        $m  = $this->db->prepare("SELECT * FROM sorteos_melate WHERE concurso = ?");
        $m->execute([$concurso]);
        $r  = $this->db->prepare("SELECT * FROM sorteos_revancha WHERE concurso = ?");
        $r->execute([$concurso]);
        $rv = $this->db->prepare("SELECT * FROM sorteos_revanchita WHERE concurso = ?");
        $rv->execute([$concurso]);

        return [
            'melate'     => $m->fetch()  ?: null,
            'revancha'   => $r->fetch()  ?: null,
            'revanchita' => $rv->fetch() ?: null,
        ];
    }

    // =============================================
    // Analizador de pureza de combinación
    // =============================================
    /**
     * Analiza qué tan "limpia" es una combinación de 6 números:
     * - Si fue sorteada exactamente alguna vez
     * - Frecuencia de cada uno de los 15 pares posibles
     * - Sorteos históricos más similares (4-5 coincidencias)
     * - Puntaje de pureza 0-100
     */
    public function analizarCombinacion(array $nums): array
    {
        sort($nums);

        // Generar los 15 pares C(6,2)
        $pairsInput = [];
        for ($i = 0; $i < 6; $i++) {
            for ($j = $i + 1; $j < 6; $j++) {
                $pairsInput[] = [min($nums[$i], $nums[$j]), max($nums[$i], $nums[$j])];
            }
        }

        // Frecuencia de los 15 pares en una sola consulta
        $rowPlaceholders = implode(', ', array_fill(0, 15, '(?,?)'));
        $flatPairParams  = [];
        foreach ($pairsInput as $p) {
            $flatPairParams[] = $p[0];
            $flatPairParams[] = $p[1];
        }
        $stmtPairs = $this->db->prepare(
            "SELECT a, b, COUNT(*) AS veces FROM (
                SELECT LEAST(r1,r2) AS a, GREATEST(r1,r2) AS b FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r3), GREATEST(r1,r3) FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r4), GREATEST(r1,r4) FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r5), GREATEST(r1,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r1,r6), GREATEST(r1,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r3), GREATEST(r2,r3) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r4), GREATEST(r2,r4) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r5), GREATEST(r2,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r2,r6), GREATEST(r2,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r3,r4), GREATEST(r3,r4) FROM sorteos_melate UNION ALL
                SELECT LEAST(r3,r5), GREATEST(r3,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r3,r6), GREATEST(r3,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r4,r5), GREATEST(r4,r5) FROM sorteos_melate UNION ALL
                SELECT LEAST(r4,r6), GREATEST(r4,r6) FROM sorteos_melate UNION ALL
                SELECT LEAST(r5,r6), GREATEST(r5,r6) FROM sorteos_melate
            ) t WHERE (a, b) IN ($rowPlaceholders)
            GROUP BY a, b"
        );
        $stmtPairs->execute($flatPairParams);
        $pairMap = [];
        foreach ($stmtPairs->fetchAll() as $pr) {
            $pairMap[(int)$pr['a'] . '_' . (int)$pr['b']] = (int)$pr['veces'];
        }
        $pares = [];
        foreach ($pairsInput as $p) {
            $pares[] = ['a' => $p[0], 'b' => $p[1],
                        'veces' => $pairMap[$p[0].'_'.$p[1]] ?? 0];
        }
        usort($pares, fn($x, $y) => $y['veces'] - $x['veces']);

        // Sorteos similares (>= 4 coincidencias)
        $cases     = [];
        $paramsSim = [];
        foreach ($nums as $n) {
            $cases[]    = 'CASE WHEN ? IN (r1,r2,r3,r4,r5,r6) THEN 1 ELSE 0 END';
            $paramsSim[] = $n;
        }
        $expr = '(' . implode(' + ', $cases) . ')';
        $stmtSim = $this->db->prepare(
            "SELECT concurso, fecha, r1, r2, r3, r4, r5, r6, r7,
                    $expr AS coincidencias
             FROM sorteos_melate
             HAVING coincidencias >= 4
             ORDER BY coincidencias DESC, concurso DESC
             LIMIT 8"
        );
        $stmtSim->execute($paramsSim);
        $similares = $stmtSim->fetchAll();

        $exactos   = array_values(array_filter($similares, fn($s) => (int)$s['coincidencias'] === 6));
        $similares = array_values(array_filter($similares, fn($s) => (int)$s['coincidencias'] < 6));

        // Puntaje de pureza 0-100
        // avg_pair_freq=0 → 100 (virgen) ; avg≈41 (histórico) → ~59 ; avg=100 → 0
        $totalPairFreq = array_sum(array_column($pares, 'veces'));
        $avgPairFreq   = $totalPairFreq / 15;
        $virginPairs   = count(array_filter($pares, fn($p) => $p['veces'] === 0));
        $pureza        = !empty($exactos) ? 0 : max(0, (int)round(100 - $avgPairFreq));

        return [
            'nums'         => $nums,
            'exactos'      => $exactos,
            'similares'    => $similares,
            'pares'        => $pares,
            'virgin_pairs' => $virginPairs,
            'avg_pair_freq'=> round($avgPairFreq, 1),
            'pureza'       => $pureza,
        ];
    }

    // =============================================
    // Métodos para Laboratorio Estadístico ECharts
    // =============================================

    public function getJuegoConfig(string $juego): array
    {
        switch ($juego) {
            case 'revancha':
                return ['table' => 'sorteos_revancha', 'cols' => ['r1','r2','r3','r4','r5','r6']];
            case 'revanchita':
                return ['table' => 'sorteos_revanchita', 'cols' => ['f1','f2','f3','f4','f5','f6']];
            default:
                return ['table' => 'sorteos_melate', 'cols' => ['r1','r2','r3','r4','r5','r6']];
        }
    }

    /**
     * Expediente estadístico completo de un número (1-56)
     */
    public function obtenerExpedienteNumero(int $num, string $juego = 'melate'): array
    {
        $num = max(1, min(56, $num));
        $cfg = $this->getJuegoConfig($juego);
        $table = $cfg['table'];
        $cols  = $cfg['cols'];
        $cList = implode(',', $cols);

        // Obtener todos los sorteos en orden cronológico
        $stmt = $this->db->query("SELECT concurso, fecha, $cList FROM $table ORDER BY concurso ASC");
        $rows = $stmt->fetchAll();

        $totalSorteos = count($rows);
        if ($totalSorteos === 0) {
            return [];
        }

        $apariciones = [];
        $gaps = [];
        $ultimoIndice = -1;
        $concursoUltimo = 0;
        $fechaUltima = '';

        foreach ($rows as $idx => $r) {
            $drawNums = array_map(fn($c) => (int)$r[$c], $cols);
            if (in_array($num, $drawNums, true)) {
                $gap = ($ultimoIndice === -1) ? $idx : ($idx - $ultimoIndice);
                $gaps[] = $gap;
                $ultimoIndice = $idx;
                $concursoUltimo = (int)$r['concurso'];
                $fechaUltima = (string)$r['fecha'];
                $apariciones[] = [
                    'concurso' => (int)$r['concurso'],
                    'fecha'    => (string)$r['fecha'],
                    'index'    => $idx + 1,
                    'gap'      => $gap
                ];
            }
        }

        $totalApariciones = count($apariciones);
        $pctAparicion = $totalSorteos > 0 ? round(($totalApariciones / $totalSorteos) * 100, 2) : 0;

        $retardoActual = ($ultimoIndice === -1) ? $totalSorteos : ($totalSorteos - 1 - $ultimoIndice);

        $maxGap = 0;
        if (!empty($gaps)) {
            $maxGap = max($gaps);
        }
        if ($retardoActual > $maxGap) {
            $maxGap = $retardoActual;
        }

        $avgGap = !empty($gaps) ? round(array_sum($gaps) / count($gaps), 1) : 0;

        // Frecuencia en las últimas ventanas de sorteos (20, 50, 100, 200)
        $freqInLast = function(int $n) use ($rows, $cols, $num, $totalSorteos) {
            $slice = array_slice($rows, max(0, $totalSorteos - $n));
            $c = 0;
            foreach ($slice as $r) {
                $drawNums = array_map(fn($col) => (int)$r[$col], $cols);
                if (in_array($num, $drawNums, true)) $c++;
            }
            return $c;
        };

        // Números compañeros más frecuentes
        $conditions = implode(' OR ', array_map(fn($c) => "$c = $num", $cols));
        $unions = [];
        foreach ($cols as $c) {
            $unions[] = "SELECT $c AS comp FROM $table WHERE ($conditions) AND $c != $num";
        }
        $sqlCompaneros = implode(" UNION ALL ", $unions);
        $stmtComp = $this->db->query("SELECT comp, COUNT(*) as veces FROM ($sqlCompaneros) t GROUP BY comp ORDER BY veces DESC LIMIT 10");
        $companeros = [];
        foreach ($stmtComp->fetchAll() as $cp) {
            $companeros[] = ['numero' => (int)$cp['comp'], 'veces' => (int)$cp['veces']];
        }

        return [
            'numero'             => $num,
            'total_sorteos'      => $totalSorteos,
            'apariciones_total'  => $totalApariciones,
            'pct_aparicion'      => $pctAparicion,
            'ultimo_concurso'    => $concursoUltimo,
            'ultima_fecha'       => $fechaUltima,
            'retardo_actual'     => $retardoActual,
            'retardo_promedio'   => $avgGap,
            'retardo_maximo'     => $maxGap,
            'freq_ultimos_20'    => $freqInLast(20),
            'freq_ultimos_50'    => $freqInLast(50),
            'freq_ultimos_100'   => $freqInLast(100),
            'freq_ultimos_200'   => $freqInLast(200),
            'timeline'           => $apariciones,
            'companeros'         => $companeros,
        ];
    }

    /**
     * Matriz de parejas 56x56 con frecuencia y último sorteo conjunto
     */
    public function obtenerMatrizParejas(string $juego = 'melate'): array
    {
        $cfg = $this->getJuegoConfig($juego);
        $table = $cfg['table'];
        $cols  = $cfg['cols'];
        $colCount = count($cols);

        $unionParts = [];
        for ($i = 0; $i < $colCount; $i++) {
            for ($j = $i + 1; $j < $colCount; $j++) {
                $c1 = $cols[$i];
                $c2 = $cols[$j];
                $unionParts[] = "SELECT LEAST($c1, $c2) AS a, GREATEST($c1, $c2) AS b, concurso, fecha FROM $table";
            }
        }
        $unions = implode(" UNION ALL ", $unionParts);
        $sql = "SELECT a, b, COUNT(*) AS veces, MAX(concurso) AS ultimo_concurso, MAX(fecha) AS ultima_fecha
                FROM ($unions) t
                GROUP BY a, b";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        $matrix = [];
        $maxVeces = 0;
        foreach ($rows as $r) {
            $a = (int)$r['a'];
            $b = (int)$r['b'];
            $v = (int)$r['veces'];
            if ($v > $maxVeces) $maxVeces = $v;
            $matrix[$a][$b] = [
                'veces' => $v,
                'concurso' => (int)$r['ultimo_concurso'],
                'fecha' => (string)$r['ultima_fecha']
            ];
            $matrix[$b][$a] = $matrix[$a][$b];
        }

        return [
            'matrix' => $matrix,
            'max_veces' => $maxVeces
        ];
    }

    /**
     * Datos para el mapa interactivo de relaciones (Grafo 1-56)
     */
    public function obtenerGrafoRelaciones(string $juego = 'melate'): array
    {
        $frecuencia = ($juego === 'revancha') ? $this->frecuenciaRevancha() :
                     (($juego === 'revanchita') ? $this->frecuenciaRevanchita() : $this->frecuenciaMelate());

        $matrizRes = $this->obtenerMatrizParejas($juego);
        $matrix = $matrizRes['matrix'];
        $maxPair = $matrizRes['max_veces'];

        $nodes = [];
        foreach ($frecuencia as $num => $freq) {
            $nodes[] = [
                'id' => (string)$num,
                'name' => (string)$num,
                'value' => $freq,
                'symbolSize' => max(18, min(48, round(($freq / (max($frecuencia) ?: 1)) * 40 + 10)))
            ];
        }

        $links = [];
        for ($i = 1; $i <= 56; $i++) {
            for ($j = $i + 1; $j <= 56; $j++) {
                if (isset($matrix[$i][$j])) {
                    $veces = $matrix[$i][$j]['veces'];
                    $links[] = [
                        'source' => (string)$i,
                        'target' => (string)$j,
                        'value' => $veces,
                        'lineStyle' => [
                            'width' => max(1, min(6, round(($veces / ($maxPair ?: 1)) * 5)))
                        ]
                    ];
                }
            }
        }

        usort($links, fn($x, $y) => $y['value'] - $x['value']);

        return [
            'nodes' => $nodes,
            'links' => $links,
            'max_num_freq' => max($frecuencia),
            'max_pair_freq' => $maxPair
        ];
    }

    /**
     * Comparativa de tendencias para números seleccionados en ventanas móviles
     */
    public function obtenerTendenciasNumeros(array $numeros, string $juego = 'melate'): array
    {
        if (empty($numeros)) {
            $numeros = [7, 23, 41];
        }
        $numeros = array_map('intval', $numeros);
        $numeros = array_unique(array_filter($numeros, fn($n) => $n >= 1 && $n <= 56));

        $cfg = $this->getJuegoConfig($juego);
        $table = $cfg['table'];
        $cols  = $cfg['cols'];
        $cList = implode(',', $cols);

        $stmt = $this->db->query("SELECT concurso, fecha, $cList FROM $table ORDER BY concurso ASC");
        $rows = $stmt->fetchAll();
        $totalSorteos = count($rows);

        $windows = [20, 50, 100, 200];
        $summary = [];

        foreach ($numeros as $num) {
            $numData = ['numero' => $num, 'windows' => []];
            foreach ($windows as $w) {
                $slice = array_slice($rows, max(0, $totalSorteos - $w));
                $count = 0;
                foreach ($slice as $r) {
                    $drawNums = array_map(fn($col) => (int)$r[$col], $cols);
                    if (in_array($num, $drawNums, true)) $count++;
                }
                $numData['windows'][$w] = [
                    'veces' => $count,
                    'pct' => count($slice) > 0 ? round(($count / count($slice)) * 100, 1) : 0
                ];
            }
            $summary[$num] = $numData;
        }

        // Histórico de frecuencia en ventanas móviles de 20 sorteos a lo largo del tiempo
        $step = max(1, (int)floor($totalSorteos / 50)); // máximo 50 puntos de datos
        $timelineSeries = [];
        $concursosAxis = [];

        for ($i = 20; $i <= $totalSorteos; $i += $step) {
            $windowSlice = array_slice($rows, $i - 20, 20);
            $lastConcurso = $rows[$i - 1]['concurso'];
            $concursosAxis[] = "C" . $lastConcurso;

            foreach ($numeros as $num) {
                $c = 0;
                foreach ($windowSlice as $r) {
                    $drawNums = array_map(fn($col) => (int)$r[$col], $cols);
                    if (in_array($num, $drawNums, true)) $c++;
                }
                $timelineSeries[$num][] = $c;
            }
        }

        return [
            'total_sorteos' => $totalSorteos,
            'summary'       => $summary,
            'concursos'     => $concursosAxis,
            'series'        => $timelineSeries
        ];
    }

    /**
     * ADN Estadístico extendido de una combinación de 6 números
     */
    public function analizarCombinacionAvanzada(array $nums, string $juego = 'melate'): array
    {
        $basic = $this->analizarCombinacion($nums);
        $n = $basic['nums'];
        $suma = array_sum($n);

        // Distribución histórica de sumas para percentil
        $distSum = ($juego === 'melate') ? $this->distribucionSumaMelate() : null;
        $percentilSuma = 50;
        if ($distSum && !empty($distSum['valores'])) {
            $menores = count(array_filter($distSum['valores'], fn($s) => $s < $suma));
            $percentilSuma = round(($menores / count($distSum['valores'])) * 100, 1);
        }

        // Par / Impar
        $paresCount = count(array_filter($n, fn($x) => $x % 2 === 0));
        $imparesCount = 6 - $paresCount;

        // Alto / Bajo (1-28 vs 29-56)
        $bajosCount = count(array_filter($n, fn($x) => $x <= 28));
        $altosCount = 6 - $bajosCount;

        // Consecutivos
        $consecutivos = 0;
        for ($i = 0; $i < 5; $i++) {
            if ($n[$i + 1] - $n[$i] === 1) $consecutivos++;
        }

        // Distancia promedio entre números
        $distancias = [];
        for ($i = 0; $i < 5; $i++) {
            $distancias[] = $n[$i + 1] - $n[$i];
        }
        $distanciaPromedio = round(array_sum($distancias) / 5, 1);

        // Dispersión (Desviación Estándar)
        $mean = $suma / 6;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $n)) / 6;
        $dispersion = round(sqrt($variance), 1);

        $basic['suma']               = $suma;
        $basic['percentil_suma']     = $percentilSuma;
        $basic['par_impar']          = "$paresCount / $imparesCount";
        $basic['alto_bajo']          = "$bajosCount / $altosCount";
        $basic['consecutivos_count'] = $consecutivos;
        $basic['distancia_promedio'] = $distanciaPromedio;
        $basic['dispersion']         = $dispersion;

        return $basic;
    }
}

