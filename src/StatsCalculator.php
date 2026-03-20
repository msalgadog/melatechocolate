<?php
declare(strict_types=1);
/**
 * Mellatron - Calculador de estadísticas avanzadas
 */

class StatsCalculator
{
    /**
     * Ordena frecuencias de mayor a menor → números "calientes"
     */
    public static function numerosCalientes(array $frecuencia, int $top = 10): array
    {
        arsort($frecuencia);
        return array_slice($frecuencia, 0, $top, true);
    }

    /**
     * Ordena frecuencias de menor a mayor → números "fríos"
     */
    public static function numerosFrios(array $frecuencia, int $top = 10): array
    {
        asort($frecuencia);
        return array_slice($frecuencia, 0, $top, true);
    }

    /**
     * Distribución pares vs impares (solo números naturales)
     * Recibe un array de sorteos, cada uno con campos r1-r6 (o f1-f6)
     */
    public static function distribucionParImpar(array $sorteos, array $campos): array
    {
        $pares   = 0;
        $impares = 0;
        foreach ($sorteos as $s) {
            foreach ($campos as $c) {
                if ((int)$s[$c] % 2 === 0) {
                    $pares++;
                } else {
                    $impares++;
                }
            }
        }
        $total = $pares + $impares;
        return [
            'pares'   => $pares,
            'impares' => $impares,
            'pct_par' => $total > 0 ? round($pares   / $total * 100, 1) : 0,
            'pct_imp' => $total > 0 ? round($impares / $total * 100, 1) : 0,
        ];
    }

    /**
     * Distribución bajos (1-28) vs altos (29-56)
     */
    public static function distribucionAltoBajo(array $sorteos, array $campos): array
    {
        $bajos = 0;
        $altos = 0;
        foreach ($sorteos as $s) {
            foreach ($campos as $c) {
                if ((int)$s[$c] <= 28) {
                    $bajos++;
                } else {
                    $altos++;
                }
            }
        }
        $total = $bajos + $altos;
        return [
            'bajos'    => $bajos,
            'altos'    => $altos,
            'pct_bajo' => $total > 0 ? round($bajos / $total * 100, 1) : 0,
            'pct_alto' => $total > 0 ? round($altos / $total * 100, 1) : 0,
        ];
    }

    /**
     * Frecuencia de suma total en últimos N sorteos (rangos de 20)
     */
    public static function distribucionSumaPorRango(array $sumas): array
    {
        $rangos = [];
        foreach ($sumas as $s) {
            // Rango de 30 en 30 (suma mínima ~21, máxima ~336)
            $inicio = (int)(floor(($s - 1) / 30) * 30 + 1);
            $fin    = $inicio + 29;
            $key    = "$inicio-$fin";
            $rangos[$key] = ($rangos[$key] ?? 0) + 1;
        }
        ksort($rangos);
        return $rangos;
    }

    /**
     * Porcentaje de consecutivos en cada sorteo (qué tan seguido salen 2 números seguidos)
     */
    public static function porcentajeConsecutivos(array $sorteos, array $campos): float
    {
        $conConsecutivos = 0;
        foreach ($sorteos as $s) {
            $nums = array_map(fn($c) => (int)$s[$c], $campos);
            sort($nums);
            for ($i = 0; $i < count($nums) - 1; $i++) {
                if ($nums[$i + 1] - $nums[$i] === 1) {
                    $conConsecutivos++;
                    break;
                }
            }
        }
        return count($sorteos) > 0
            ? round($conConsecutivos / count($sorteos) * 100, 1)
            : 0;
    }

    /**
     * Genera una combinación "sugerida" basada en:
     * - 3 números calientes
     * - 2 números atrasados (retardo alto)
     * - 1 número frío (balance)
     */
    public static function generarSugerencia(
        array $frecuencia,
        array $retardo,
        int $cantidad = 6
    ): array {
        // Top 10 calientes
        arsort($frecuencia);
        $calientes = array_keys(array_slice($frecuencia, 0, 15, true));

        // Top 10 atrasados (más sorteos sin salir)
        arsort($retardo);
        $atrasados = array_keys(array_slice($retardo, 0, 15, true));

        // Top 10 fríos
        asort($frecuencia);
        $frios = array_keys(array_slice($frecuencia, 0, 15, true));

        $seleccion = [];

        // 3 calientes
        shuffle($calientes);
        foreach ($calientes as $n) {
            if (count($seleccion) >= 3) break;
            if (!in_array($n, $seleccion)) $seleccion[] = $n;
        }

        // 2 atrasados
        shuffle($atrasados);
        foreach ($atrasados as $n) {
            if (count($seleccion) >= 5) break;
            if (!in_array($n, $seleccion)) $seleccion[] = $n;
        }

        // 1 frío
        shuffle($frios);
        foreach ($frios as $n) {
            if (count($seleccion) >= $cantidad) break;
            if (!in_array($n, $seleccion)) $seleccion[] = $n;
        }

        sort($seleccion);
        return $seleccion;
    }

    /**
     * Genera N combinaciones aleatorias (Melático)
     */
    public static function generarMelatico(int $cantidad = 6, int $combinaciones = 5): array
    {
        $resultado = [];
        for ($i = 0; $i < $combinaciones; $i++) {
            $nums = [];
            while (count($nums) < $cantidad) {
                $n = rand(1, 56);
                if (!in_array($n, $nums)) $nums[] = $n;
            }
            sort($nums);
            $resultado[] = $nums;
        }
        return $resultado;
    }

    /**
     * Número de la suerte basado en fecha de nacimiento (numerología simple)
     * Reduce la fecha a un dígito 1-9 (ó 11, 22, 33 números maestros)
     * Luego mapea a números de melate
     */
    public static function numerosDeLaSuerte(string $fechaNacimiento): array
    {
        // Suma todos los dígitos de la fecha
        $digits = preg_replace('/\D/', '', $fechaNacimiento);
        $suma = array_sum(str_split($digits));
        // Reducir a 1-9
        while ($suma > 9) {
            $suma = array_sum(str_split((string)$suma));
        }

        // Map: número personal → set de números de suerte en 1-56
        $table = [
            1 => [1, 10, 19, 28, 37, 46, 55],
            2 => [2, 11, 20, 29, 38, 47, 56],
            3 => [3, 12, 21, 30, 39, 48],
            4 => [4, 13, 22, 31, 40, 49],
            5 => [5, 14, 23, 32, 41, 50],
            6 => [6, 15, 24, 33, 42, 51],
            7 => [7, 16, 25, 34, 43, 52],
            8 => [8, 17, 26, 35, 44, 53],
            9 => [9, 18, 27, 36, 45, 54],
        ];

        return [
            'numero_personal' => $suma,
            'numeros'         => $table[$suma] ?? [],
        ];
    }

    /**
     * Clasifica los números de un sorteo en "calientes" (top 30%) 
     * o "fríos" (bottom 30%) según la frecuencia histórica
     */
    public static function clasificarNumeros(array $nums, array $frecuencia): array
    {
        $max = max($frecuencia);
        $min = min($frecuencia);
        $rango = $max - $min;
        $resultado = [];
        foreach ($nums as $n) {
            $freq = $frecuencia[$n] ?? 0;
            $nivel = 'normal';
            if ($rango > 0) {
                $pct = ($freq - $min) / $rango;
                if ($pct >= 0.7)       $nivel = 'caliente';
                elseif ($pct <= 0.3)   $nivel = 'frio';
            }
            $resultado[$n] = ['frecuencia' => $freq, 'nivel' => $nivel];
        }
        return $resultado;
    }
}
