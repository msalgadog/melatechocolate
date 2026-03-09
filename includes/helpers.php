<?php
/**
 * Mellatron - Funciones de renderizado común
 * Incluido en todas las páginas que necesiten pintar bolas o datos de sorteo
 */

/**
 * Renderiza una bola de número
 * @param int    $numero   1-56
 * @param string $tipo     melate|revancha|revanchita|adicional|caliente|frio|normal|sugerida|zodiacal
 * @param string $tamanio  '' | 'bola-sm' | 'bola-lg'
 * @param bool   $animate
 */
function renderBola(int $numero, string $tipo = 'melate', string $tamanio = '', bool $animate = false): string
{
    $cls = "bola bola-{$tipo}";
    if ($tamanio)  $cls .= " $tamanio";
    if ($animate)  $cls .= ' bola-animate';
    return "<div class=\"{$cls}\">{$numero}</div>";
}

/**
 * Renderiza el bloque de bolas de un sorteo Melate (6 naturales + adicional)
 */
function renderBolassMelate(array $sorteo, string $tamanio = '', bool $animate = false): string
{
    $html = '<div class="bola-container">';
    foreach (['r1','r2','r3','r4','r5','r6'] as $k) {
        $html .= renderBola((int)$sorteo[$k], 'melate', $tamanio, $animate);
    }
    $html .= '<span class="sep-adicional">+</span>';
    $html .= renderBola((int)$sorteo['r7'], 'adicional', $tamanio, $animate);
    $html .= '</div>';
    return $html;
}

/**
 * Renderiza bolas Revancha (6 naturales, sin adicional)
 */
function renderBolasRevancha(array $sorteo, string $tamanio = '', bool $animate = false): string
{
    $html = '<div class="bola-container">';
    foreach (['r1','r2','r3','r4','r5','r6'] as $k) {
        $html .= renderBola((int)$sorteo[$k], 'revancha', $tamanio, $animate);
    }
    $html .= '</div>';
    return $html;
}

/**
 * Renderiza bolas Revanchita (F1-F6)
 */
function renderBolasRevanchita(array $sorteo, string $tamanio = '', bool $animate = false): string
{
    $html = '<div class="bola-container">';
    foreach (['f1','f2','f3','f4','f5','f6'] as $k) {
        $html .= renderBola((int)$sorteo[$k], 'revanchita', $tamanio, $animate);
    }
    $html .= '</div>';
    return $html;
}

/**
 * Formatea una bolsa en texto legible (millones/miles)
 */
function formatBolsa(int $bolsa): string
{
    if ($bolsa >= 1_000_000) {
        return '$' . number_format($bolsa / 1_000_000, 1) . ' MDP';
    }
    return '$' . number_format($bolsa, 0, '.', ',');
}

/**
 * Formatea una fecha de BD (YYYY-MM-DD) al español
 */
function formatFecha(string $fecha): string
{
    $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
              'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    [$y, $m, $d] = explode('-', $fecha);
    return "$d de {$meses[(int)$m]} de $y";
}

/**
 * Renderiza fila de la tabla de historial
 * @param string $tipo melate|revancha|revanchita
 */
function renderFilaHistorial(array $row, string $tipo): string
{
    $bolsa    = formatBolsa((int)$row['bolsa']);
    $fecha    = formatFecha($row['fecha']);
    $ganador  = !empty($row['ganador']);
    $trClass  = $ganador ? ' class="fila-ganador"' : '';
    $html     = "<tr{$trClass}>";
    if ($ganador) {
        $html .= '<td><span class="badge-concurso">' . $row['concurso'] . '</span>';
        $html .= ' <span class="badge bg-warning text-dark" title="¡Primer Premio!">🏆 1er Premio</span></td>';
    } else {
        $html .= "<td><span class=\"badge-concurso\">{$row['concurso']}</span></td>";
    }

    if ($tipo === 'melate') {
        foreach (['r1','r2','r3','r4','r5','r6'] as $k) {
            $html .= '<td class="text-center">'
                  . renderBola((int)$row[$k], 'melate', 'bola-sm')
                  . '</td>';
        }
        $html .= '<td class="text-center">'
              . renderBola((int)$row['r7'], 'adicional', 'bola-sm')
              . '</td>';
    } elseif ($tipo === 'revancha') {
        foreach (['r1','r2','r3','r4','r5','r6'] as $k) {
            $html .= '<td class="text-center">'
                  . renderBola((int)$row[$k], 'revancha', 'bola-sm')
                  . '</td>';
        }
    } else {
        foreach (['f1','f2','f3','f4','f5','f6'] as $k) {
            $html .= '<td class="text-center">'
                  . renderBola((int)$row[$k], 'revanchita', 'bola-sm')
                  . '</td>';
        }
    }

    $html .= "<td><span class=\"badge-bolsa\">{$bolsa}</span></td>";
    $html .= "<td class=\"text-muted small\">{$fecha}</td>";
    $html .= "</tr>\n";
    return $html;
}

/**
 * Renderiza paginación Bootstrap
 */
function renderPaginacion(int $paginaActual, int $totalPaginas, string $url): string
{
    if ($totalPaginas <= 1) return '';
    $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center flex-wrap">';

    $html .= sprintf(
        '<li class="page-item %s"><a class="page-link" href="%s&pagina=%d">«</a></li>',
        $paginaActual <= 1 ? 'disabled' : '',
        $url, max(1, $paginaActual - 1)
    );

    $from = max(1, $paginaActual - 3);
    $to   = min($totalPaginas, $paginaActual + 3);

    if ($from > 1) {
        $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}&pagina=1\">1</a></li>";
        if ($from > 2) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }

    for ($i = $from; $i <= $to; $i++) {
        $html .= sprintf(
            '<li class="page-item %s"><a class="page-link" href="%s&pagina=%d">%d</a></li>',
            $i === $paginaActual ? 'active' : '', $url, $i, $i
        );
    }

    if ($to < $totalPaginas) {
        if ($to < $totalPaginas - 1) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        $html .= "<li class=\"page-item\"><a class=\"page-link\" href=\"{$url}&pagina={$totalPaginas}\">{$totalPaginas}</a></li>";
    }

    $html .= sprintf(
        '<li class="page-item %s"><a class="page-link" href="%s&pagina=%d">»</a></li>',
        $paginaActual >= $totalPaginas ? 'disabled' : '',
        $url, min($totalPaginas, $paginaActual + 1)
    );

    $html .= '</ul></nav>';
    return $html;
}
