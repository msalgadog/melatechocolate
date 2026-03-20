<?php
declare(strict_types=1);
/**
 * Mellatron - Repositorio de sorteos
 * Consultas para Melate, Revancha y Revanchita
 */

require_once __DIR__ . '/Database.php';

class MelateRepository
{
    private PDO $db;

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
}
