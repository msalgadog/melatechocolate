<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class ContentRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS blog_posts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(180) NOT NULL UNIQUE,
            title VARCHAR(255) NOT NULL,
            excerpt TEXT NOT NULL,
            image_url VARCHAR(500) NOT NULL DEFAULT '',
            content LONGTEXT NOT NULL,
            status ENUM('draft','published') NOT NULL DEFAULT 'published',
            published_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status_published (status, published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Migración: agrega image_url si falta en instalaciones previas
        $cols = $this->db->query("SHOW COLUMNS FROM blog_posts LIKE 'image_url'")->fetchAll();
        if (empty($cols)) {
            $this->db->exec("ALTER TABLE blog_posts ADD COLUMN image_url VARCHAR(500) NOT NULL DEFAULT '' AFTER excerpt");
        }
    }

    private function canonicalPosts(): array
    {
        return [
            [
                'slug'      => 'ciencia-numeros-calientes-melate',
                'title'     => 'La Ciencia tras el Melate: ¿Existen los números "Calientes"?',
                'excerpt'   => 'Exploramos la Ley de los Grandes Números aplicada al Melate y si tiene sentido seguir las tendencias históricas de frecuencia.',
                'image_url' => '',
                'content'   => "En el mundo de la lotería en México, específicamente en el Melate, uno de los conceptos más debatidos es el de los \"números calientes\" y \"números fríos\". Pero, ¿hay una base científica detrás de esto o es simple superstición? En este análisis exploramos cómo la Ley de los Grandes Números influye en los resultados históricos.\n\n## ¿Qué son los números calientes?\n\nLlamamos \"números calientes\" a aquellas esferas que han aparecido con mayor frecuencia en la tómbola durante los últimos sorteos, por ejemplo en los últimos 50 o 100 resultados. Por el contrario, los \"números fríos\" son aquellos que parecen haber quedado en el olvido.\n\n## El factor de la Probabilidad\n\nDesde un punto de vista puramente matemático, cada sorteo es un evento independiente. La probabilidad de que salga cualquier combinación de 6 números es de:\n\n" . '$$\frac{1}{32{,}468{,}436}$$' . "\n\nSin embargo, al observar el histórico que actualizamos semanalmente, notamos que ciertos números tienden a equilibrar su aparición a largo plazo. Si un número ha salido muy poco, la estadística descriptiva sugiere que su frecuencia debería normalizarse para alcanzar el promedio esperado.\n\n## Conclusión\n\nSeguir las tendencias no garantiza el premio mayor, pero permite al jugador tomar decisiones informadas. Utilizar un historial de resultados es la mejor forma de visualizar estas rachas y entender el comportamiento de la tómbola de la Lotería Nacional.",
            ],
            [
                'slug'      => 'pureza-combinacion-optimiza-jugada',
                'title'     => '¿Qué es la "Pureza" de una Combinación? Optimiza tu Jugada',
                'excerpt'   => 'Descubre el algoritmo de Validación de Pureza y los criterios estadísticos que filtran combinaciones con baja probabilidad histórica.',
                'image_url' => '',
                'content'   => "Muchos jugadores de Melate eligen sus números basados en fechas de nacimiento o números de la suerte. Sin embargo, estas combinaciones suelen ser \"estadísticamente impuras\". En nuestro portal, hemos desarrollado un algoritmo de Validación de Pureza para filtrar jugadas que tienen bajísimas probabilidades de ocurrir.\n\n## Los criterios de una combinación \"Pura\"\n\nPara que una combinación sea considerada equilibrada o pura, debe cumplir con ciertos patrones observados en los sorteos ganadores a lo largo de las décadas:\n\nBalance de Pares e Impares: Es extremadamente raro que los 6 números ganadores sean todos pares o todos impares. Una combinación pura suele tener una distribución de 3:3 o 4:2.\n\nRango de Suma Total: Si sumas los 6 números de tu boleto, el resultado debería oscilar entre 120 y 180. Las combinaciones que suman menos de 60 o más de 250 ocurren en menos del 5% de los sorteos históricos.\n\nEvitar Consecutivos: Aunque es posible, es poco común ver más de dos números consecutivos como 12, 13, 14. Una jugada limpia evita estas escaleras largas.\n\n## ¿Por qué usar un validador?\n\nAl usar herramientas de validación, eliminas jugadas que la tómbola casi nunca arroja, permitiéndote concentrar tu presupuesto en combinaciones que se alinean con el comportamiento histórico del juego.",
            ],
            [
                'slug'      => 'melate-retro-vs-melate-clasico',
                'title'     => 'Melate Retro vs. Melate Clásico: ¿Cuál te conviene jugar?',
                'excerpt'   => 'Análisis de probabilidades, universo de números y ventajas de cada modalidad para ayudarte a elegir dónde poner tu boleto.',
                'image_url' => '',
                'content'   => "Si vives en México, seguramente te has preguntado si es mejor buscar la bolsa millonaria del Melate Clásico o probar suerte con el Melate Retro. La respuesta depende de tu perfil como jugador y de qué tanto entiendas las probabilidades de cada sorteo.\n\n## Análisis de Probabilidades\n\nLa diferencia principal radica en el universo de números:\n\nMelate Clásico: Eliges 6 números de un total de 56. La probabilidad de ganar es 1 entre 32.4 millones.\n\nMelate Retro: Eliges 6 números de un total de solo 39. Aquí la probabilidad mejora drásticamente a 1 entre 3,262,623.\n\n## Ventajas y Desventajas\n\nMelate Retro: Es \"10 veces más fácil\" de ganar que el clásico. Es ideal para quienes buscan premios constantes, aunque la bolsa inicial suele ser de 5 millones de pesos.\n\nMelate Clásico: Es un reto monumental, pero las bolsas suelen superar los 100 o 200 millones de pesos, especialmente cuando hay varios sorteos sin ganador.\n\n## Veredicto Estadístico\n\nSi buscas optimizar tu inversión y tener retornos más frecuentes, el Melate Retro es la opción lógica desde la estadística. Si buscas cambiar tu vida con un solo golpe de suerte y no te importa la baja probabilidad, el Clásico es tu sorteo. En nuestro sitio puedes generar combinaciones para ambos casos usando nuestro Generador de Apuestas basado en tendencias.",
            ],
        ];
    }

    private function extraSeedPosts(): array
    {
        return [
            [
                'slug' => 'probabilidad-melate-retro-vs-melate',
                'title' => '¿Cómo funciona la probabilidad en Melate Retro vs Melate?',
                'excerpt' => 'Comparativa práctica de combinaciones posibles, tamaño de universo y por qué la percepción de dificultad cambia entre modalidades.',
                'content' => "La probabilidad en loterías se entiende mejor cuando traducimos cada juego a una pregunta sencilla: ¿cuántas combinaciones posibles existen y cuántas de ellas me benefician? En Melate, Retro o cualquier variante similar, esta cifra crece muy rápido cuando aumenta el rango de números o la cantidad de aciertos requeridos. Por eso, la sensación de que un juego es ‘más accesible’ no siempre coincide con la realidad matemática.\n\nEn términos prácticos, la probabilidad no tiene memoria. Si un número salió ayer, su probabilidad teórica para el siguiente sorteo no cambia por ese hecho aislado. Lo que sí cambia con el tiempo es nuestra base de observación: conforme acumulamos miles de sorteos, podemos estudiar distribución, frecuencia y patrones de repetición de corto plazo. Es importante separar análisis estadístico (útil para entender comportamiento histórico) de garantía de resultado (que no existe).\n\nCuando comparamos Melate Retro con Melate, la conversación más relevante es el tamaño del espacio muestral. Un juego con menos combinaciones totales genera mayor frecuencia relativa de aciertos parciales, pero también depende de reglas adicionales como número extra o estructura de premios. En otras palabras, la ‘dificultad’ no depende de un solo factor, sino de una combinación de universo de números, cantidad de bolas elegidas y reglas de premiación.\n\nTambién influye cómo apostamos. Una misma probabilidad base puede percibirse distinta si el jugador usa combinaciones sistemáticas, aleatorias o sesgadas por fechas. Matemáticamente, ninguna estrategia de selección garantiza incremento de probabilidad por jugada individual, pero sí puede mejorar cobertura del espacio cuando el presupuesto permite más combinaciones no repetidas.\n\nEl punto clave para una estrategia saludable es entender expectativa y varianza. Las loterías tienen alta varianza: periodos largos sin premio relevante y eventos poco frecuentes de cobro alto. Analizar probabilidades ayuda a poner expectativas realistas, administrar presupuesto y evitar sesgos como la falacia del jugador.\n\nConclusión: comparar Retro vs Melate vale la pena cuando se hace con datos y reglas completas, no solo con intuición. El análisis estadístico sirve para tomar decisiones más informadas, siempre recordando que cada sorteo sigue siendo un evento aleatorio independiente y sin garantía de ganar.",
            ],
            [
                'slug' => 'historia-de-los-sorteos-mas-grandes-en-mexico',
                'title' => 'Historia de los sorteos más grandes en México',
                'excerpt' => 'Un repaso histórico de bolsas acumuladas, contexto social y cómo crecieron los sorteos de alta expectativa en México.',
                'content' => "Los sorteos de mayor bolsa en México no solo llaman la atención por el premio, también reflejan cambios en hábitos de consumo, tecnología y comunicación pública. Con el paso de los años, la difusión digital permitió que más personas siguieran resultados, históricos y acumulados en tiempo real. Esta mayor visibilidad incrementó la conversación social y, en muchos casos, también la expectativa sobre cada sorteo especial.\n\nLas bolsas históricas suelen aparecer tras periodos de acumulación prolongada. A nivel estadístico esto no implica que ‘ya toque’, pero sí aumenta interés del público porque el valor esperado emocional percibido crece. Es común que los jugadores ocasionales reaparezcan justo en esas semanas, elevando el volumen de participación.\n\nDesde un enfoque técnico, estos episodios sirven para estudiar comportamiento colectivo: mayor tráfico en portales de resultados, más consultas de combinaciones y crecimiento de búsquedas sobre frecuencia de números. También se observan más sesgos cognitivos, por ejemplo elegir números populares o combinaciones consecutivas, algo relevante porque podría aumentar la probabilidad de compartir premio en caso de acierto.\n\nOtro elemento histórico es la normalización del análisis de datos recreativo. Antes predominaban listados simples de resultados; hoy muchos portales incorporan métricas de calor, retardos, distribución de suma y repeticiones. Eso no cambia la naturaleza aleatoria del sorteo, pero sí mejora alfabetización estadística del usuario.\n\nEn México, los sorteos emblemáticos han funcionado como eventos culturales: conversación en oficina, familia y redes. Cuando la bolsa alcanza máximos, la narrativa de ‘oportunidad única’ se intensifica. Por eso es esencial mantener un mensaje de juego responsable y expectativas realistas.\n\nMirar la historia de grandes sorteos ayuda a entender dos cosas: la dimensión social del juego y la importancia de comunicar claramente riesgos y límites. El mejor enfoque combina entretenimiento, información transparente y una postura ética: jugar por diversión, no por presión financiera.",
            ],
            [
                'slug' => 'pureza-de-una-combinacion-explicacion-tecnica',
                'title' => 'Pureza de una combinación: explicación técnica y uso práctico',
                'excerpt' => 'Qué mide la pureza de una combinación, cómo interpretarla y por qué debe verse como métrica descriptiva, no predictiva.',
                'content' => "La pureza de una combinación es una métrica descriptiva que intenta resumir qué tan ‘equilibrado’ luce un conjunto de números según ciertos criterios estadísticos: distribución por rangos, paridad, dispersión y distancia entre valores. No existe una definición universal; cada modelo puede ponderar variables distintas.\n\nEn términos simples, una combinación con pureza alta suele evitar concentraciones extremas (por ejemplo, demasiados números muy bajos o muy altos) y mantiene cierta diversidad interna. Esto puede hacerla más ‘natural’ respecto a patrones históricos observados, aunque no la vuelve más probable en el siguiente sorteo desde la teoría base de azar uniforme.\n\n¿Para qué sirve entonces? Principalmente para comparar combinaciones entre sí dentro de un mismo marco analítico. Si tienes veinte opciones candidatas, la pureza puede ayudarte a ordenar y filtrar según criterios consistentes, en lugar de elegir al azar total o por intuición aislada.\n\nLa clave está en usarla con honestidad metodológica: es una herramienta de organización, no una promesa de acierto. En analítica aplicada, muchas métricas son útiles para priorizar sin implicar causalidad directa. Pureza entra en esa categoría.\n\nPara usuarios nuevos, conviene combinar pureza con otras lecturas: frecuencia histórica de cada número, retardo, repetición reciente y distribución de suma. El resultado final es una evaluación más completa del perfil estadístico de cada jugada.\n\nEn resumen, hablar de pureza aporta valor cuando se explica bien: no predice el futuro, pero sí mejora disciplina en la selección y documentación de combinaciones. Eso hace que el análisis sea más transparente, replicable y educativo para la comunidad.",
            ],
            [
                'slug' => 'mitos-numeros-calientes-y-frios',
                'title' => 'Mitos comunes sobre números calientes y fríos',
                'excerpt' => 'Desmontamos ideas populares sobre rachas, números atrasados y supuestos patrones infalibles en loterías.',
                'content' => "Los números calientes y fríos son una forma popular de leer históricos: calientes para los que aparecen más, fríos para los que aparecen menos en una ventana temporal. El problema comienza cuando esta descripción se convierte en promesa de predicción.\n\nPrimer mito: ‘si está caliente, va a seguir saliendo’. En realidad, cada sorteo es independiente. Un número que salió varias veces no adquiere derecho estadístico a repetir por sí mismo.\n\nSegundo mito: ‘si está frío, ya le toca’. Este es un caso clásico de falacia del jugador. Que un número no haya aparecido recientemente no obliga al sistema aleatorio a compensar en el corto plazo.\n\nTercer mito: ‘los números tienen memoria’. No la tienen en el sentido probabilístico de un sorteo justo. Lo que sí tiene memoria es nuestra base de datos y nuestra percepción.\n\nEntonces, ¿para qué sirven calientes y fríos? Como indicadores descriptivos para explorar el histórico, construir visualizaciones y entender distribución temporal. Son útiles para aprendizaje estadístico y para diversificar jugadas si ese es tu objetivo recreativo, pero no para asegurar premios.\n\nUna práctica responsable es tratarlos como parte de un tablero más amplio: paridad, suma total, distancia, repetición de pares y contexto de concursos previos. Ningún indicador aislado explica todo.\n\nConclusión: calientes y fríos tienen valor informativo, no valor mágico. El usuario que entiende esta diferencia toma mejores decisiones, evita sesgos y disfruta el análisis sin expectativas irreales.",
            ],
            [
                'slug' => 'como-interpretar-la-distribucion-de-sumas',
                'title' => 'Cómo interpretar la distribución de sumas en Melate',
                'excerpt' => 'Qué significa la suma de los seis números, rangos frecuentes y cómo usar esta métrica en análisis estadístico recreativo.',
                'content' => "La distribución de sumas se obtiene agregando los seis números principales de cada sorteo y observando en qué rangos cae ese total a lo largo del tiempo. Es una métrica sencilla, pero muy útil para identificar concentraciones históricas.\n\nEn muchos históricos se observa una zona de mayor densidad: intervalos donde la suma aparece con más frecuencia relativa. Esto no significa que los extremos sean imposibles; solo indica que en la muestra acumulada han sido menos comunes.\n\nUsar distribución de sumas puede ayudar a filtrar combinaciones excesivamente extremas. Por ejemplo, jugadas con todos números muy bajos o muy altos tienden a producir sumas alejadas del centro histórico.\n\nSin embargo, hay que evitar sobreajuste. Si excluyes de forma rígida cualquier combinación fuera del rango frecuente, podrías estar descartando resultados válidos que eventualmente ocurren.\n\nLa mejor práctica es usar la suma como una señal más dentro de un panel: paridad, dispersión, repeticiones y cobertura de rangos. Este enfoque balancea intuición y evidencia sin caer en reglas absolutas.\n\nEn síntesis, la suma no predice por sí sola, pero mejora la calidad del análisis cuando se interpreta como distribución y no como sentencia.",
            ],
            [
                'slug' => 'sesgos-cognitivos-al-elegir-numeros',
                'title' => 'Sesgos cognitivos al elegir números: lo que más afecta tus decisiones',
                'excerpt' => 'Falacia del jugador, ilusión de control y sesgo de disponibilidad aplicados a loterías y selección de combinaciones.',
                'content' => "Elegir números parece una tarea simple, pero está profundamente influida por sesgos cognitivos. El primero es la falacia del jugador: creer que una racha debe compensarse pronto. En loterías justas, esa compensación no está garantizada.\n\nOtro sesgo común es la ilusión de control. Pensamos que una estrategia personal compleja incrementa probabilidad por jugada individual, cuando en realidad solo cambia la forma de seleccionar, no la base aleatoria del sorteo.\n\nTambién aparece el sesgo de disponibilidad: recordamos casos llamativos (premios grandes, combinaciones famosas) y sobreestimamos su frecuencia real.\n\nReconocer estos sesgos no quita la diversión; al contrario, mejora la experiencia porque alinea expectativas con realidad estadística.\n\nSi quieres una rutina más sana, define presupuesto fijo, evita perseguir pérdidas y usa el análisis como herramienta educativa.\n\nLa mejor estrategia no es adivinar el próximo sorteo; es jugar con información, límites claros y sin autoengaño.",
            ],
            [
                'slug' => 'por-que-no-existe-combinacion-infalible',
                'title' => 'Por qué no existe una combinación infalible',
                'excerpt' => 'Revisión matemática breve sobre independencia de eventos y límites de los sistemas milagro en loterías.',
                'content' => "Cada cierto tiempo surge una ‘fórmula infalible’ para lotería. La idea vende porque promete certeza en un entorno incierto. Matemáticamente, en un sorteo justo no existe combinación privilegiada que garantice premio mayor.\n\nLas combinaciones sistemáticas pueden aumentar cobertura si compras más boletos distintos, pero ese beneficio viene del volumen, no de una magia estadística escondida.\n\nLos sistemas milagro suelen confundir tres conceptos: patrón histórico, causalidad futura y administración de riesgo. Un patrón observado no implica que se repetirá en el próximo sorteo.\n\nUna visión honesta del juego reconoce incertidumbre y se enfoca en responsabilidad: presupuesto, límites y expectativa realista.\n\nConclusión: la mejor defensa contra promesas falsas es comprender probabilidades básicas y exigir transparencia metodológica.",
            ],
            [
                'slug' => 'como-leer-un-historico-sin-autoengano',
                'title' => 'Cómo leer un histórico sin autoengaño',
                'excerpt' => 'Buenas prácticas para analizar resultados pasados sin convertir coincidencias en supuestas leyes inevitables.',
                'content' => "Un histórico de sorteos es valioso, pero también puede inducir errores de interpretación. El primer paso es separar descripción de predicción. Describir es observar qué ocurrió; predecir implica estimar qué podría ocurrir, siempre con incertidumbre.\n\nPara evitar autoengaño, trabaja con ventanas temporales claras, define métricas antes de mirar resultados y evita cambiar reglas a mitad del análisis solo para confirmar intuición.\n\nOtra buena práctica es usar más de un indicador: frecuencia, retardo, suma, paridad y dispersión. Cuando un patrón aparece solo en una métrica aislada, suele ser ruido.\n\nFinalmente, documenta tus supuestos. Si no puedes explicar por qué una regla debería funcionar, probablemente no sea una regla robusta.\n\nAnalizar bien no elimina el azar, pero sí mejora la calidad de tus decisiones recreativas.",
            ],
            [
                'slug' => 'juego-responsable-estadistica-y-entretenimiento',
                'title' => 'Juego responsable: estadística y entretenimiento',
                'excerpt' => 'Cómo disfrutar el análisis del Melate sin convertirlo en presión financiera ni expectativa irreal.',
                'content' => "La estadística puede hacer más interesante el seguimiento del Melate, pero nunca debe reemplazar el sentido de responsabilidad. Jugar responsablemente significa entender que el premio mayor es improbable y que el objetivo principal debe ser entretenimiento.\n\nUn marco útil es tratar cada boleto como gasto recreativo de bajo monto. Define presupuesto mensual, evita usar dinero destinado a necesidades básicas y no persigas pérdidas.\n\nTambién ayuda separar emoción de decisión. Si un sorteo acumulado te genera impulso, pausa y revisa tus límites antes de comprar.\n\nEl análisis estadístico aporta cultura numérica: enseña probabilidad, variación y lectura crítica de datos. Ese valor existe incluso cuando no hay premio.\n\nLa combinación ideal es simple: información clara, límites firmes y disfrute consciente.",
            ],
            [
                'slug' => 'como-armar-tu-rutina-semanal-de-analisis',
                'title' => 'Cómo armar tu rutina semanal de análisis de sorteos',
                'excerpt' => 'Una rutina de 20 minutos para revisar históricos, detectar tendencias descriptivas y guardar tus notas.',
                'content' => "Una rutina corta de análisis mejora consistencia y evita decisiones impulsivas. Puedes hacerla en 20 minutos semanales:\n\n1) Revisa últimos sorteos y anota cambios de frecuencia.\n2) Observa retardo de números y distribución por rangos.\n3) Evalúa suma total y paridad de combinaciones recientes.\n4) Selecciona candidatas con criterios estables.\n5) Registra por qué elegiste cada combinación.\n\nEste proceso no garantiza aciertos, pero reduce ruido emocional y te da trazabilidad sobre tus decisiones.\n\nCon el tiempo, tendrás un histórico personal que permite comparar intuición contra datos y aprender de forma progresiva.\n\nLa disciplina analítica es más útil que cualquier promesa rápida.",
            ],
            [
                'slug' => 'preguntas-frecuentes-sobre-melate-y-analisis',
                'title' => 'Preguntas frecuentes sobre Melate y análisis estadístico',
                'excerpt' => 'Respuestas claras a dudas comunes: números repetidos, sorteos consecutivos, patrones y límites del análisis.',
                'content' => "¿Se pueden repetir números entre sorteos? Sí, es completamente normal en procesos aleatorios.\n\n¿Un número muy atrasado está por salir? No necesariamente. El atraso no obliga compensación inmediata.\n\n¿Sirve analizar históricos? Sí, como herramienta educativa y descriptiva; no como garantía de premio.\n\n¿Hay mejores días para jugar? No existe evidencia robusta de ventaja por día cuando el mecanismo es equivalente.\n\n¿Qué sí puedo controlar? Tu presupuesto, tus reglas de juego responsable y la calidad de tu proceso analítico.\n\nEstas respuestas parecen simples, pero ayudan a sostener expectativas realistas y evitar decisiones apresuradas.",
            ],
            [
                'slug' => 'metodologia-de-datos-en-mellatron',
                'title' => 'Metodología de datos en este sitio: transparencia total',
                'excerpt' => 'Cómo se descargan históricos, cómo se validan CSV y cómo se construyen métricas públicas del portal.',
                'content' => "Este sitio usa fuentes públicas para construir visualizaciones y métricas de seguimiento. La metodología tiene tres pasos: descarga, validación e importación.\n\nPrimero se obtienen archivos históricos desde URLs oficiales configurables. Después se valida que el archivo sea CSV real (no HTML), que tenga cabeceras esperadas y que el código de producto corresponda al juego correcto.\n\nLuego se importa con control de duplicados para mantener estabilidad del histórico sin repetir concursos existentes. Además, se registran logs de errores y ejecuciones para auditoría.\n\nEn la capa de análisis se calculan frecuencias, retardos, distribución de sumas y otras métricas descriptivas. El objetivo es educativo y recreativo: facilitar lectura de datos sin afirmar predicción garantizada.\n\nLa transparencia metodológica es clave para construir confianza, especialmente en sitios que buscan cumplir lineamientos de calidad y claridad informativa.",
            ],
        ];
    }

    public function ensureSeedPosts(): void
    {
        $count = (int)$this->db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
        if ($count > 0) {
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO blog_posts (slug, title, excerpt, image_url, content, status, published_at)
             VALUES (:slug, :title, :excerpt, :image_url, :content, 'published', NOW())"
        );

        foreach (array_merge($this->canonicalPosts(), $this->extraSeedPosts()) as $post) {
            if (!isset($post['image_url'])) {
                $post['image_url'] = '';
            }
            $stmt->execute($post);
        }
    }

    private function buildUniqueSlug(string $title, $excludeId = null): string
    {
        $base = trim((string)preg_replace('/-+/', '-', (string)preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title))));
        $base = strtolower($base);
        if ($base === '') {
            $base = 'entrada-blog';
        }

        $slug = $base;
        $n = 1;

        while (true) {
            if ($excludeId) {
                $stmt = $this->db->prepare("SELECT id FROM blog_posts WHERE slug = :slug AND id <> :id LIMIT 1");
                $stmt->execute(['slug' => $slug, 'id' => $excludeId]);
            } else {
                $stmt = $this->db->prepare("SELECT id FROM blog_posts WHERE slug = :slug LIMIT 1");
                $stmt->execute(['slug' => $slug]);
            }

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                return $slug;
            }

            $n++;
            $slug = $base . '-' . $n;
        }
    }

    public function allPostsForAdmin(): array
    {
        $stmt = $this->db->query("SELECT id, slug, title, excerpt, image_url, status, published_at, created_at, updated_at
                                  FROM blog_posts
                                  ORDER BY COALESCE(published_at, created_at) DESC, id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPostByIdAdmin(int $id)
    {
        $stmt = $this->db->prepare("SELECT id, slug, title, excerpt, image_url, content, status, published_at
                                    FROM blog_posts
                                    WHERE id = :id
                                    LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createPost(array $data): int
    {
        $slug = $this->buildUniqueSlug((string)$data['title']);
        $stmt = $this->db->prepare(
            "INSERT INTO blog_posts (slug, title, excerpt, image_url, content, status, published_at)
             VALUES (:slug, :title, :excerpt, :image_url, :content, :status, :published_at)"
        );
        $stmt->execute([
            'slug' => $slug,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'image_url' => $data['image_url'] ?? '',
            'content' => $data['content'],
            'status' => $data['status'],
            'published_at' => $data['published_at'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updatePost(int $id, array $data): void
    {
        $slug = $this->buildUniqueSlug((string)$data['title'], $id);
        $stmt = $this->db->prepare(
            "UPDATE blog_posts
             SET slug = :slug,
                 title = :title,
                 excerpt = :excerpt,
                 image_url = :image_url,
                 content = :content,
                 status = :status,
                 published_at = :published_at
             WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'slug' => $slug,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'image_url' => $data['image_url'] ?? '',
            'content' => $data['content'],
            'status' => $data['status'],
            'published_at' => $data['published_at'],
        ]);
    }

    public function deletePost(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function latestPosts(int $limit = 6): array
    {
        $limit = max(1, min(30, $limit));
        $stmt = $this->db->query("SELECT id, slug, title, excerpt, image_url, published_at
                                  FROM blog_posts
                                  WHERE status='published'
                                  ORDER BY updated_at DESC, published_at DESC, id DESC
                                  LIMIT $limit");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function allPosts(): array
    {
        $stmt = $this->db->query("SELECT id, slug, title, excerpt, image_url, published_at
                                  FROM blog_posts
                                  WHERE status='published'
                                  ORDER BY updated_at DESC, published_at DESC, id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getPostBySlug(string $slug)
    {
        $stmt = $this->db->prepare("SELECT id, slug, title, excerpt, image_url, content, published_at
                                    FROM blog_posts
                                    WHERE status='published' AND slug=:slug
                                    LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
