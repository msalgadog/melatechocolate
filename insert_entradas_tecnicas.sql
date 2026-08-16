-- Inserta las 6 entradas técnicas de "entradas Técnicas del sitio.md" en `blog_posts`.
-- No se especifica `id`: se agregan a continuación de las existentes (AUTO_INCREMENT).
-- Base de datos: mellatron (MariaDB, utf8mb4).

START TRANSACTION;

-- 1. De la hoja de cálculo a tu propio motor de consultas
INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `image_url`, `content`, `status`, `published_at`, `views`, `likes`) VALUES
('de-la-hoja-de-calculo-a-tu-propio-motor-de-consultas-el-stack-de-datos-del-melate',
'De la hoja de cálculo a tu propio motor de consultas: El stack de datos del Melate',
'Si llevas tiempo analizando los resultados del Melate, seguramente empezaste como todos: descargando el archivo histórico oficial y abriéndolo en una hoja de cálculo. Al principio funciona, pero a medida que agregas fórmulas complejas para miles de sorteos, la hoja empieza a congelarse.

Para hacer un análisis estadístico serio sin que tu computadora colapse, necesitas una línea de ensamblaje de datos real. Aquí te mostramos cómo usar Python y SQL para dominar el histórico.',
'',
'<p>Si llevas tiempo analizando los resultados del Melate, seguramente empezaste como todos: descargando el archivo histórico oficial y abriéndolo en una hoja de cálculo. Al principio funciona, pero a medida que agregas fórmulas complejas para miles de sorteos, la hoja empieza a congelarse.</p>
<p>Para hacer un análisis estadístico serio sin que tu computadora colapse, necesitas una línea de ensamblaje de datos real. Aquí te mostramos cómo usar Python y SQL para dominar el histórico.</p>
<h2>1. Extracción y Limpieza con Python (Pandas)</h2>
<p>El sitio oficial ofrece el histórico en formatos crudos que traen celdas vacías, fechas mal formateadas o caracteres extraños. En lugar de limpiar esto a mano cada semana, puedes usar un script en <strong>Python</strong> con la librería <code>pandas</code> para automatizarlo.</p>
<p>Este pequeño bloque de código lee tu archivo, limpia los datos nulos y estandariza las columnas en segundos:</p>
<pre><code class="language-python">import pandas as pd

# Cargar el archivo CSV descargado de Pronósticos
df = pd.read_csv(''historico_melate_crudo.csv'')

# Eliminar filas vacías o sorteos cancelados
df = df.dropna()

# Asegurar que las esferas sean números enteros
columnas_esferas = [''R1'', ''R2'', ''R3'', ''R4'', ''R5'', ''R6'', ''Adicional'']
df[columnas_esferas] = df[columnas_esferas].astype(int)

# Mostrar los primeros 5 resultados ya limpios
print(df.head())

# Exportar el archivo listo para la base de datos
df.to_csv(''historico_melate_limpio.csv'', index=False)
</code></pre>
<h2>2. El salto técnico: Tu motor SQL local</h2>
<p>Una vez que tienes el archivo limpio, es hora de guardarlo donde pertenece: una base de datos relacional.</p>
<blockquote><p><strong>El consejo técnico:</strong> No instales motores pesados en tu sistema operativo. Levantar un contenedor local con <strong>Docker</strong> o <strong>Podman</strong> te permite tener <strong>MariaDB</strong> o <strong>MySQL</strong> corriendo de forma aislada.</p></blockquote>
<p>Utiliza un cliente visual como <strong>DBeaver</strong> o las extensiones de <strong>Visual Studio Code</strong> para importar tu archivo <code>historico_melate_limpio.csv</code> a tu nueva tabla.</p>
<h2>3. La magia del SQL: Consultas en milisegundos</h2>
<p>Lo que en Excel te tomaba minutos de procesamiento, en SQL se resuelve al instante. Si quieres saber la frecuencia histórica exacta de los números en la primera posición (<code>R1</code>), solo ejecutas esto:</p>
<pre><code class="language-sql">SELECT R1 AS Numero, COUNT(R1) AS Frecuencia
FROM sorteos_melate
GROUP BY R1
ORDER BY Frecuencia DESC;
</code></pre>
<p>Tener este entorno local cambia por completo tu forma de analizar el juego, dándote el poder real sobre los datos para encontrar tendencias de latencia y frecuencia sin bloqueos en la pantalla.</p>',
'published', '2026-08-15 00:00:00', 0, 0);

-- 2. Automatizando el "Filtro de Pureza"
INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `image_url`, `content`, `status`, `published_at`, `views`, `likes`) VALUES
('automatizando-el-filtro-de-pureza-como-evaluar-tus-combinaciones-en-segundos',
'Automatizando el "Filtro de Pureza": Cómo evaluar tus combinaciones en segundos',
'Todo analista de sorteos sabe que no todas las combinaciones tienen el mismo peso histórico. Jugar 1, 2, 3, 4, 5, 6 es estadísticamente posible, pero su "pureza" (probabilidad basada en tendencias históricas de sumas y equilibrios) es bajísima.

En lugar de calcular esto a mano, puedes automatizarlo creando un pequeño script.',
'',
'<p>Todo analista de sorteos sabe que no todas las combinaciones tienen el mismo peso histórico. Jugar <code>1, 2, 3, 4, 5, 6</code> es estadísticamente posible, pero su "pureza" (probabilidad basada en tendencias históricas de sumas y equilibrios) es bajísima.</p>
<p>En lugar de calcular esto a mano, puedes automatizarlo creando un pequeño script.</p>
<h2>Los criterios de pureza</h2>
<p>Una combinación "pura" generalmente cumple con dos reglas de oro basadas en la campana de Gauss histórica:</p>
<ol>
<li><strong>La Suma Total:</strong> La suma de los 6 números debe caer entre 120 y 190.</li>
<li><strong>Equilibrio Par/Impar:</strong> Las proporciones 3/3 o 4/2 (en cualquier orden) son las más frecuentes.</li>
</ol>
<h2>El motor de validación</h2>
<p>Puedes escribir un script rápido en <strong>PHP</strong> para pasar por el filtro cualquier combinación antes de ir a la agencia. La lógica es sumamente directa:</p>
<pre><code class="language-php">function evaluarPureza($combinacion) {
    $suma = array_sum($combinacion);
    $pares = 0;

    foreach($combinacion as $numero) {
        if($numero % 2 == 0) $pares++;
    }
    $impares = 6 - $pares;

    $sumaValida = ($suma &gt;= 120 &amp;&amp; $suma &lt;= 190);
    $equilibrioValido = in_array($pares, [2, 3, 4]);

    if($sumaValida &amp;&amp; $equilibrioValido) {
        return "Combinación Óptima (Suma: $suma, Pares: $pares, Impares: $impares)";
    }
    return "Combinación Fuera de Rango Histórico";
}

// Prueba rápida
echo evaluarPureza([5, 12, 23, 34, 45, 51]);
</code></pre>
<p>Automatizar este proceso te quita el sesgo emocional al momento de elegir. Si el script dice que está fuera de rango, es mejor buscar otra secuencia.</p>',
'published', '2026-08-15 00:00:00', 0, 0);

-- 3. Simulador de Montecarlo
INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `image_url`, `content`, `status`, `published_at`, `views`, `likes`) VALUES
('creando-tu-propio-simulador-de-montecarlo-para-desmitificar-las-rachas',
'Creando tu propio simulador de Montecarlo para desmitificar las rachas',
'El cerebro humano está diseñado para encontrar patrones donde no los hay. Cuando vemos que el número 15 ha salido en tres sorteos seguidos, asumimos que está "caliente" o, por el contrario, que ya "gastó" su suerte. Para destruir estos sesgos cognitivos, nada mejor que un Simulador de Montecarlo.',
'',
'<p>El cerebro humano está diseñado para encontrar patrones donde no los hay. Cuando vemos que el número 15 ha salido en tres sorteos seguidos, asumimos que está "caliente" o, por el contrario, que ya "gastó" su suerte. Para destruir estos sesgos cognitivos, nada mejor que un <strong>Simulador de Montecarlo</strong>.</p>
<h2>¿Qué es la simulación de Montecarlo?</h2>
<p>Es una técnica matemática que utiliza el muestreo aleatorio repetido para predecir la probabilidad de diferentes resultados. En el contexto del Melate, significa simular 10,000 o 100,000 sorteos virtuales en segundos para observar cómo se comporta la varianza.</p>
<h2>Ejecutando la simulación</h2>
<p>En lugar de mirar el histórico de 3,000 sorteos reales, puedes usar un ciclo en tu entorno de desarrollo para generar sorteos aleatorios masivos. Al almacenar estos resultados en tu base de datos y graficarlos, notarás algo fascinante:</p>
<ul>
<li>En el corto plazo (100 sorteos), hay números que parecen mágicos y otros que desaparecen.</li>
<li>En el largo plazo (100,000 sorteos), la gráfica se aplana por completo. Todos los números tienden a salir exactamente la misma cantidad de veces.</li>
</ul>
<h2>La lección matemática</h2>
<p>Construir y correr este simulador te enseña la "Ley de los Grandes Números". Te ayuda a comprender que jugar a las "rachas" es una ilusión a corto plazo, y que tu estrategia debe centrarse en la estructura de la combinación (como vimos en el filtro de pureza) y no en la cacería de un número en solitario.</p>',
'published', '2026-08-15 00:00:00', 0, 0);

-- 4. El Dashboard del Jugador
INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `image_url`, `content`, `status`, `published_at`, `views`, `likes`) VALUES
('el-dashboard-del-jugador-alertas-visuales-para-numeros-rezagados',
'El Dashboard del Jugador: Alertas visuales para números "rezagados"',
'El análisis de datos no sirve de nada si no puedes interpretar la información rápidamente. Leer tablas llenas de números cansa la vista y oculta oportunidades. El siguiente paso en tu evolución analítica es construir un tablero visual, o Dashboard.',
'',
'<p>El análisis de datos no sirve de nada si no puedes interpretar la información rápidamente. Leer tablas llenas de números cansa la vista y oculta oportunidades. El siguiente paso en tu evolución analítica es construir un tablero visual, o <em>Dashboard</em>.</p>
<h2>De los datos a los colores</h2>
<p>El objetivo del dashboard es tener una representación visual de la matriz de los 56 números del Melate. Dependiendo de los sorteos que lleven sin salir (latencia), la interfaz debe reaccionar.</p>
<p>Si decides programar tu propia interfaz web, frameworks modernos como <strong>Laravel</strong> o <strong>Livewire</strong>, combinados con clases utilitarias de <strong>Tailwind CSS</strong>, te permiten renderizar esta cuadrícula en minutos.</p>
<h2>Reglas de formato condicional</h2>
<p>Puedes establecer reglas simples de negocio para tu interfaz:</p>
<ul>
<li><strong>Zona Verde (Calientes):</strong> Números que han salido en los últimos 5 sorteos.</li>
<li><strong>Zona Amarilla (Promedio):</strong> Números con un rezago de 6 a 15 sorteos.</li>
<li><strong>Zona Roja (Rezagados/Fríos):</strong> Números que llevan más de 16 sorteos sin aparecer.</li>
</ul>
<p>Tener esta vista en tu pantalla te permite armar combinaciones balanceadas visualmente. Por ejemplo, puedes decidir conscientemente incluir un número "rojo" (rezagado) y dos "verdes" (calientes) en tu jugada, basándote en la dispersión actual y no en corazonadas.</p>',
'published', '2026-08-15 00:00:00', 0, 0);

-- 5. Ventanas de latencia
INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `image_url`, `content`, `status`, `published_at`, `views`, `likes`) VALUES
('mineria-de-datos-aplicada-calculando-las-ventanas-de-latencia-en-el-melate',
'Minería de datos aplicada: Calculando las "ventanas de latencia" en el Melate',
'La mayoría de los jugadores se conforman con saber cuál es el número que más ha salido en la historia. Pero ese dato es engañoso. Si un número salió 50 veces en los años 90 y no ha salido en los últimos dos años, su frecuencia histórica total seguirá siendo alta, pero su relevancia actual es nula.

Aquí entra el cálculo de las ventanas de latencia.',
'',
'<p>La mayoría de los jugadores se conforman con saber cuál es el número que más ha salido en la historia. Pero ese dato es engañoso. Si un número salió 50 veces en los años 90 y no ha salido en los últimos dos años, su frecuencia histórica total seguirá siendo alta, pero su relevancia actual es nula.</p>
<p>Aquí entra el cálculo de las <strong>ventanas de latencia</strong>.</p>
<h2>¿Qué es la latencia?</h2>
<p>La latencia es la distancia (medida en días o en cantidad de sorteos) que existe entre una aparición de un número y su siguiente aparición.</p>
<h2>Análisis avanzado con SQL</h2>
<p>Si ya tienes tu histórico en MariaDB o MySQL, puedes utilizar funciones de ventana (<em>Window Functions</em>) como <code>LAG()</code> para calcular la diferencia de sorteos entre apariciones del mismo número.</p>
<p>Al extraer este dato, puedes calcular el <strong>promedio de espera</strong> de cada esfera. Por ejemplo:</p>
<ul>
<li><em>¿Cuántos sorteos pasan en promedio antes de que el número 42 vuelva a caer?</em></li>
<li><em>¿Cuál ha sido la sequía más larga en la historia para el número 8?</em></li>
</ul>
<p>Conocer la ventana de latencia te permite identificar anomalías. Si el número 23 suele aparecer cada 10 sorteos en promedio, y actualmente lleva 35 sorteos sin salir, estás ante una desviación estadística importante que podrías querer incluir en tu próxima jugada.</p>',
'published', '2026-08-15 00:00:00', 0, 0);

-- 6. Tu primera Macro en Excel
INSERT INTO `blog_posts` (`slug`, `title`, `excerpt`, `image_url`, `content`, `status`, `published_at`, `views`, `likes`) VALUES
('tu-primera-macro-en-excel-automatiza-la-busqueda-de-combinaciones-ganadoras',
'Tu primera Macro en Excel: Automatiza la búsqueda de combinaciones ganadoras',
'Si todavía no estás listo para instalar Python o configurar bases de datos en Docker, no te preocupes. Puedes exprimir al máximo tu hoja de cálculo utilizando Macros (VBA).

Una de las tareas más tediosas es verificar si la combinación que tienes en mente ya ha ganado el primer lugar en el pasado. Vamos a automatizarlo.',
'',
'<p>Si todavía no estás listo para instalar Python o configurar bases de datos en Docker, no te preocupes. Puedes exprimir al máximo tu hoja de cálculo utilizando <strong>Macros (VBA)</strong>.</p>
<p>Una de las tareas más tediosas es verificar si la combinación que tienes en mente ya ha ganado el primer lugar en el pasado (algo que estadísticamente querrás evitar). Hacer esto a mano o con la herramienta "Buscar" sorteo por sorteo es agotador. Vamos a automatizarlo.</p>
<h2>Preparando tu archivo</h2>
<ol>
<li>Abre tu Excel con el histórico del Melate.</li>
<li>Asegúrate de tener los números ganadores (R1 a R6) en las columnas <code>C</code> a <code>H</code>.</li>
<li>Guarda tu archivo como <strong>"Libro de Excel habilitado para macros (*.xlsm)"</strong>.</li>
</ol>
<h2>El Código VBA</h2>
<p>Presiona <code>ALT + F11</code> para abrir el editor de Visual Basic, inserta un "Módulo" nuevo y pega el siguiente código. Esta macro tomará una combinación que tú definas y buscará en todo el historial si alguna vez ha salido exacta.</p>
<pre><code class="language-vb">Sub BuscarCombinacionHistorica()
    Dim ws As Worksheet
    Dim ultimaFila As Long
    Dim i As Long
    Dim matchCount As Integer
    Dim miCombinacion(1 To 6) As Integer
    Dim encontrada As Boolean

    Set ws = ThisWorkbook.Sheets("Historico") '' Cambia el nombre si tu hoja se llama distinto
    ultimaFila = ws.Cells(ws.Rows.Count, "A").End(xlUp).Row
    encontrada = False

    '' Define aquí los 6 números que quieres buscar
    miCombinacion(1) = 4
    miCombinacion(2) = 15
    miCombinacion(3) = 23
    miCombinacion(4) = 38
    miCombinacion(5) = 42
    miCombinacion(6) = 51

    '' Recorrer todo el histórico (asumiendo que los datos empiezan en la fila 2)
    For i = 2 To ultimaFila
        matchCount = 0
        '' Comparar columnas C a H (3 a 8)
        If ws.Cells(i, 3).Value = miCombinacion(1) And _
           ws.Cells(i, 4).Value = miCombinacion(2) And _
           ws.Cells(i, 5).Value = miCombinacion(3) And _
           ws.Cells(i, 6).Value = miCombinacion(4) And _
           ws.Cells(i, 7).Value = miCombinacion(5) And _
           ws.Cells(i, 8).Value = miCombinacion(6) Then

            MsgBox "¡Alerta! Esta combinación ya ganó en el sorteo de la fila " &amp; i, vbExclamation, "Búsqueda Completada"
            encontrada = True
            Exit Sub
        End If
    Next i

    If Not encontrada Then
        MsgBox "Combinación inédita. Nunca ha salido el primer lugar con estos números exactos.", vbInformation, "Búsqueda Completada"
    End If
End Sub
</code></pre>
<h2>¿Cómo funciona?</h2>
<p>La macro define un arreglo con tus 6 números y utiliza un ciclo <code>For</code> para escanear fila por fila (desde el sorteo más antiguo hasta el actual). Si encuentra una coincidencia exacta, detiene la búsqueda y te lanza una alerta indicando en qué fila está el sorteo. Si termina de leer los más de 3,000 sorteos y no encuentra nada, te confirma que tu combinación es "inédita".</p>
<p>Con este pequeño script, acabas de convertir tu hoja de Excel estática en una herramienta de validación automatizada.</p>',
'published', '2026-08-15 00:00:00', 0, 0);

COMMIT;
