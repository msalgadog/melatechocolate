<?php
declare(strict_types=1);
/**
 * Mellatron - Helper Zodiacal y Numerología de la Suerte
 * Puramente lúdico – ¡juega con responsabilidad!
 */

class ZodiacHelper
{
    // Signos del zodiaco con fechas y números de la suerte
    private static array $signos = [
        'Aries'       => ['inicio' => '03-21', 'fin' => '04-19', 'emoji' => '♈',
            'numeros' => [1, 9, 10, 18, 19, 27],
            'desc'    => 'Fuego • Marte • Energía y valentía'],
        'Tauro'       => ['inicio' => '04-20', 'fin' => '05-20', 'emoji' => '♉',
            'numeros' => [2, 6, 11, 15, 20, 24],
            'desc'    => 'Tierra • Venus • Estabilidad y persistencia'],
        'Géminis'     => ['inicio' => '05-21', 'fin' => '06-20', 'emoji' => '♊',
            'numeros' => [3, 5, 12, 14, 21, 23],
            'desc'    => 'Aire • Mercurio • Versatilidad y curiosidad'],
        'Cáncer'      => ['inicio' => '06-21', 'fin' => '07-22', 'emoji' => '♋',
            'numeros' => [2, 7, 11, 16, 20, 25],
            'desc'    => 'Agua • Luna • Intuición y sensibilidad'],
        'Leo'         => ['inicio' => '07-23', 'fin' => '08-22', 'emoji' => '♌',
            'numeros' => [1, 5, 9, 10, 14, 45],
            'desc'    => 'Fuego • Sol • Liderazgo y generosidad'],
        'Virgo'       => ['inicio' => '08-23', 'fin' => '09-22', 'emoji' => '♍',
            'numeros' => [3, 6, 12, 15, 21, 33],
            'desc'    => 'Tierra • Mercurio • Perfección y análisis'],
        'Libra'       => ['inicio' => '09-23', 'fin' => '10-22', 'emoji' => '♎',
            'numeros' => [6, 7, 15, 16, 24, 42],
            'desc'    => 'Aire • Venus • Armonía y justicia'],
        'Escorpio'    => ['inicio' => '10-23', 'fin' => '11-21', 'emoji' => '♏',
            'numeros' => [8, 9, 17, 18, 26, 44],
            'desc'    => 'Agua • Plutón • Intensidad y determinación'],
        'Sagitario'   => ['inicio' => '11-22', 'fin' => '12-21', 'emoji' => '♐',
            'numeros' => [3, 5, 12, 14, 30, 50],
            'desc'    => 'Fuego • Júpiter • Aventura y optimismo'],
        'Capricornio' => ['inicio' => '12-22', 'fin' => '01-19', 'emoji' => '♑',
            'numeros' => [4, 8, 13, 22, 31, 40],
            'desc'    => 'Tierra • Saturno • Disciplina y ambición'],
        'Acuario'     => ['inicio' => '01-20', 'fin' => '02-18', 'emoji' => '♒',
            'numeros' => [4, 7, 13, 16, 22, 52],
            'desc'    => 'Aire • Urano • Innovación e independencia'],
        'Piscis'      => ['inicio' => '02-19', 'fin' => '03-20', 'emoji' => '♓',
            'numeros' => [2, 6, 7, 11, 15, 56],
            'desc'    => 'Agua • Neptuno • Empatía y espiritualidad'],
    ];

    /**
     * Obtiene todos los signos con su información
     */
    public static function getTodosSignos(): array
    {
        return self::$signos;
    }

    /**
     * Determina el signo zodiacal a partir de una fecha (mes-día o fecha completa)
     */
    public static function getSigno(string $fecha): ?string
    {
        // Acepta YYYY-MM-DD o DD/MM/YYYY o MM-DD
        if (preg_match('/(\d{4})[-\/](\d{2})[-\/](\d{2})/', $fecha, $m)) {
            $md = $m[2] . '-' . $m[3];
        } elseif (preg_match('/(\d{2})[-\/](\d{2})[-\/](\d{4})/', $fecha, $m)) {
            $md = $m[2] . '-' . $m[1];
        } else {
            $md = $fecha; // assume MM-DD
        }

        // Casos especiales de año nuevo
        foreach (self::$signos as $nombre => $datos) {
            $ini = $datos['inicio'];
            $fin = $datos['fin'];

            if ($ini <= $fin) {
                if ($md >= $ini && $md <= $fin) return $nombre;
            } else {
                // Cruce de año (Capricornio)
                if ($md >= $ini || $md <= $fin) return $nombre;
            }
        }
        return null;
    }

    /**
     * Devuelve los datos completos de un signo por nombre
     */
    public static function getDatosSigno(string $signo): ?array
    {
        return self::$signos[$signo] ?? null;
    }

    /**
     * Combina números zodiacales con numerología personal para dar 6 números
     */
    public static function combinacionPersonalizada(string $signo, int $numeroPersonal): array
    {
        $zodiacales = self::$signos[$signo]['numeros'] ?? [];

        // Números derivados del número personal
        $personales = [];
        for ($i = 0; $i < 10 && count($personales) < 6; $i++) {
            $n = ($numeroPersonal + $i * 7) % 56;
            $n = $n === 0 ? 56 : $n;
            if (!in_array($n, $personales)) $personales[] = $n;
        }

        // Mezclar y tomar 6 únicos
        $pool = array_unique(array_merge($zodiacales, $personales));
        shuffle($pool);
        $sel = array_slice($pool, 0, 6);
        sort($sel);
        return $sel;
    }

    /**
     * Números "de la semana" basados en el día actual
     */
    public static function numerosDeLaSemana(): array
    {
        $dia   = (int) date('N'); // 1=lunes, 7=domingo
        $mes   = (int) date('n');
        $anno  = (int) date('Y');

        $base = (($dia * 7) + ($mes * 3) + ($anno % 100)) % 50;
        $nums = [];
        for ($i = 0; $i < 6; $i++) {
            $n = (($base + $i * 9) % 56) + 1;
            $nums[] = $n;
        }
        $nums = array_unique($nums);
        // completar si hay duplicados
        while (count($nums) < 6) {
            $n = rand(1, 56);
            if (!in_array($n, $nums)) $nums[] = $n;
        }
        sort($nums);
        return array_slice($nums, 0, 6);
    }
}
