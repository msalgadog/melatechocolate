Quiero mejorar y reorganizar la sección de **Estadísticas de Melate** de este proyecto.

Antes de modificar código, **analiza la estructura actual del proyecto**, identifica cómo se obtienen y procesan los datos estadísticos, qué tecnologías se están usando en frontend/backend y qué componentes pueden reutilizarse.

El objetivo es **evolucionar la sección existente sin rehacer la aplicación y sin romper ninguna funcionalidad actual**.

# Objetivo general

Actualmente todas las estadísticas se muestran una debajo de otra en una página vertical muy larga.

Quiero convertir esta sección en una especie de:

## Laboratorio Estadístico del Melate

La idea es que el usuario pueda **explorar visualmente los datos históricos**, utilizando **Apache ECharts**, en lugar de limitarse a leer tablas, porcentajes y rankings.

No debe presentarse ninguna estadística como predicción de resultados futuros. Todo debe manejarse como **análisis y visualización de información histórica**.

---

# 1. Reorganizar la sección de estadísticas

La sección actual debe dejar de mostrar todas las herramientas verticalmente.

Crear dentro de Estadísticas un **submenú tipo pestañas** para dividir la información.

Propuesta inicial:

- Resumen
- Radiografía de un número
- Tendencias
- Matriz de parejas
- Mapa de relaciones
- Analizar combinación

En escritorio puede mostrarse como una barra horizontal de pestañas.

En dispositivos pequeños debe seguir siendo usable. Puede utilizarse:

- pestañas con desplazamiento horizontal,
- un selector,
- o alguna solución responsive equivalente.

Evitar que las pestañas rompan el layout o generen scroll horizontal de toda la página.

Al seleccionar una pestaña:

- mostrar únicamente el contenido correspondiente;
- ocultar el resto;
- evitar recargar toda la página cuando no sea necesario;
- mantener una transición ligera y limpia;
- si es posible, permitir acceso directo mediante hash o estado de URL, por ejemplo:

```text
/estadisticas#tendencias
/estadisticas#numero
/estadisticas#relaciones
```

Esto último sólo si puede incorporarse limpiamente sin alterar la arquitectura actual.

---

# 2. Mantener las estadísticas existentes

NO eliminar las estadísticas que ya funcionan.

Actualmente existen datos como:

- Mapa de calor de frecuencia histórica 1-56
- Top 10 números calientes
- Top 10 números fríos
- Frecuencia histórica 1-56
- Par / Impar
- Alto / Bajo
- Números consecutivos
- Números más atrasados
- Pares más frecuentes
- Distribución de la suma de los seis números

Estas estadísticas deben conservarse, pero reorganizadas dentro de la pestaña:

## Resumen

Cuando tenga sentido, sustituir representaciones básicas por gráficas ECharts más atractivas e interactivas.

No cambiar las fórmulas actuales salvo que se detecte un error real.

---

# 3. Incorporar Apache ECharts

Utilizar:

**Apache ECharts**

Antes de instalarlo, revisar cómo maneja actualmente el proyecto las dependencias JavaScript.

Si ya existe npm/build system, integrarlo apropiadamente ahí.

Si es un proyecto tradicional sin bundler, utilizar la alternativa menos invasiva posible.

Evitar introducir React, Vue, Angular u otro framework únicamente para implementar las gráficas.

La intención es que ECharts se integre a la arquitectura existente.

Organizar el código para evitar tener configuraciones gigantes de ECharts dentro de las vistas HTML/PHP.

Preferentemente separar responsabilidades en módulos o archivos JS, por ejemplo:

```text
assets/js/statistics/
    overview.js
    number-profile.js
    trends.js
    pairs-matrix.js
    relations.js
    combination-profile.js
```

Adapta la estructura a la que ya tenga el proyecto; no crear carpetas innecesarias únicamente por seguir este ejemplo.

---

# 4. Radiografía de un número

Crear una herramienta donde el usuario pueda seleccionar cualquiera de los números:

```text
1 - 56
```

Por ejemplo:

```text
Número seleccionado: 23
```

Mostrar una especie de expediente estadístico.

Debe incluir, siempre que pueda obtenerse de los datos actuales:

- número de apariciones históricas;
- porcentaje de aparición;
- última aparición;
- sorteos transcurridos desde su última aparición;
- retardo promedio;
- máximo histórico de sorteos sin aparecer;
- frecuencia en los últimos 20 sorteos;
- frecuencia en los últimos 50;
- frecuencia en los últimos 100;
- frecuencia histórica;
- números con los que más ha aparecido;
- evolución de sus apariciones en el tiempo.

Utilizar ECharts para mostrar estos datos.

Especialmente quiero una gráfica temporal que permita visualizar:

```text
apariciones del número seleccionado
vs
tiempo / número de sorteo
```

El usuario debe poder cambiar del número 23 al 24, por ejemplo, sin recargar toda la página si la arquitectura actual lo permite.

---

# 5. Tendencias

Crear una sección específica para observar tendencias históricas.

Permitir seleccionar uno o varios números.

Ejemplo:

```text
[07] [23] [41]
```

Mostrar cómo se comporta su frecuencia utilizando ventanas móviles como:

- últimos 20 sorteos;
- últimos 50;
- últimos 100;
- últimos 200.

Idealmente utilizar una gráfica `line` de ECharts.

Debe ser posible activar/desactivar números desde la leyenda.

No interpretar una subida de frecuencia como una mayor posibilidad de aparecer en el siguiente sorteo.

Mostrar claramente que se trata únicamente de comportamiento histórico.

---

# 6. Matriz de parejas 56 × 56

Crear una visualización interactiva utilizando un **heatmap de ECharts**.

Debe representar:

```text
Número X
vs
Número Y
```

El valor de cada celda será:

> cantidad de sorteos históricos en que ambos números aparecieron juntos.

Ejemplo:

```text
23 + 41 = 48 coincidencias
```

Al pasar el mouse o tocar una celda mostrar un tooltip como:

```text
23 + 41

Han aparecido juntos: 48 veces
Última coincidencia: Sorteo XXXX
```

Si existe información suficiente también se puede mostrar la fecha.

La diagonal:

```text
23 + 23
```

no debe contarse como pareja.

La escala visual debe permitir identificar rápidamente parejas poco y muy frecuentes.

Debe funcionar razonablemente bien en móvil.

Si representar las 56 etiquetas simultáneamente genera problemas visuales, implementar zoom, desplazamiento o `dataZoom` antes que reducir información.

---

# 7. Mapa interactivo de relaciones

Esta será una de las visualizaciones principales.

Utilizar el tipo:

```text
graph
```

de Apache ECharts.

Representar los números del 1 al 56 como nodos.

Ejemplo conceptual:

```text
        14
       /  \
     23----31
    /  \    \
  07----42---52
```

Cada nodo representa un número.

El tamaño del nodo puede representar su frecuencia histórica.

Las conexiones representan parejas de números que han aparecido juntas.

El grosor de la línea debe representar qué tan frecuente es esa relación.

IMPORTANTE:

No mostrar las 1,540 combinaciones posibles de parejas simultáneamente si eso hace ilegible el gráfico.

Aplicar un criterio inteligente.

Por ejemplo:

- mostrar inicialmente únicamente relaciones superiores a cierto percentil;
- mostrar las N relaciones más frecuentes;
- permitir modificar el nivel mediante un control;
- o mostrar las conexiones principales del número seleccionado.

Al seleccionar un número:

```text
23
```

resaltarlo y mostrar principalmente sus relaciones.

Ejemplo:

```text
Número 23

Compañeros frecuentes

23 + 41 → 48 veces
23 + 07 → 45 veces
23 + 52 → 43 veces
23 + 18 → 42 veces
```

Permitir:

- zoom;
- pan;
- selección de nodo;
- tooltip;
- resaltado de relaciones.

El gráfico debe sentirse exploratorio y no como una simple decoración.

---

# 8. Analizar combinación — ADN estadístico

Aprovechar la herramienta existente de análisis de combinación y evolucionarla visualmente.

El usuario introduce seis números:

```text
07
13
22
34
41
53
```

Generar una especie de:

## ADN de la combinación

Mostrar indicadores como:

- suma total;
- posición de esa suma frente al histórico;
- pares / impares;
- altos / bajos;
- consecutivos;
- distancia promedio entre números;
- dispersión;
- pares históricamente frecuentes contenidos;
- combinaciones históricas más similares;
- si esa combinación exacta ya apareció;
- cantidad máxima de coincidencias encontradas en sorteos anteriores.

Si tiene sentido estadístico con los datos disponibles, calcular percentiles.

Ejemplo:

```text
Suma: 170
Percentil histórico de suma: 63%

Par / impar:
2 / 4

Bajo / alto:
3 / 3

Distancia promedio:
9.2

Sorteo histórico más parecido:
07 13 21 34 46 53

Coincidencias:
4 de 6
```

Crear una representación ECharts atractiva para esta sección.

Puede utilizarse radar, barras, gauges u otra visualización adecuada, pero **no utilizar gráficas únicamente porque se ven bonitas**.

Cada gráfica debe comunicar información útil.

---

# 9. UX / UI

Quiero que esta sección se sienta como una herramienta moderna de exploración de datos.

Mantener el estilo visual actual de la aplicación.

NO quiero que ECharts parezca un elemento pegado encima del diseño existente.

Las gráficas deben respetar:

- colores actuales;
- tipografía;
- espaciados;
- bordes;
- cards;
- dark/light mode si actualmente existe;
- comportamiento responsive.

Agregar estados para:

```text
Cargando datos...
No hay información disponible
Error al cargar estadísticas
```

Evitar layouts que salten de tamaño mientras cargan las gráficas.

Las gráficas deben responder correctamente cuando:

- cambia el tamaño de la ventana;
- cambia la pestaña;
- cambia la orientación móvil.

Llamar correctamente a `resize()` de las instancias ECharts cuando sea necesario.

Especial cuidado con gráficas creadas dentro de elementos inicialmente ocultos por las pestañas, ya que ECharts puede calcular incorrectamente sus dimensiones.

---

# 10. Rendimiento

La aplicación contiene información histórica, así que evitar cálculos pesados repetitivos en frontend.

Analiza qué cálculos deben realizarse:

- en servidor;
- mediante SQL;
- en PHP/backend;
- o en JavaScript.

Preferentemente el frontend debe recibir datasets ya preparados.

Ejemplo conceptual:

```text
/api/statistics/number/23
/api/statistics/trends
/api/statistics/pairs
/api/statistics/relations
/api/statistics/combination
```

NO crear estos endpoints obligatoriamente si la arquitectura actual tiene una forma mejor de entregar los datos.

Son únicamente una referencia.

Reutilizar consultas y estructuras actuales cuando sea posible.

Evitar ejecutar una consulta individual por cada número o pareja si puede obtenerse mediante una consulta agregada.

Cuando un cálculo histórico sea costoso y prácticamente no cambie entre sorteos, valorar caché.

No introducir infraestructura adicional innecesaria.

---

# 11. Código mantenible

No quiero un parche gigantesco.

Crear funciones reutilizables.

Evitar:

```javascript
var chart1
var chart2
var chart3
var chart4
```

dispersos globalmente.

Implementar administración limpia de las instancias ECharts.

Evitar listeners duplicados al cambiar entre pestañas.

Destruir/dispose gráficas si realmente dejan de utilizarse.

Agregar comentarios únicamente donde exista lógica que lo amerite.

Mantener convenciones existentes del proyecto.

---

# 12. Compatibilidad

No modificar:

- herramientas del modo juego;
- numerología;
- generadores;
- verificador de números;
- navegación externa;
- blog;
- cálculos que ya funcionan correctamente.

La intervención debe estar principalmente concentrada en:

## Estadísticas

Si algún cambio compartido es necesario, explicar por qué.

---

# 13. Implementación progresiva

No intentes reescribir toda la sección de una sola vez sin verificar el comportamiento.

Trabaja en este orden:

### Fase 1
Refactor del menú de Estadísticas a pestañas.

Confirmar que todas las estadísticas existentes siguen funcionando.

### Fase 2
Integración base de ECharts.

Migrar/mejorar las visualizaciones existentes que realmente se beneficien.

### Fase 3
Radiografía de número.

### Fase 4
Matriz de parejas.

### Fase 5
Mapa interactivo de relaciones.

### Fase 6
Tendencias.

### Fase 7
ADN estadístico de combinación.

Después de cada fase comprobar que no se introdujeron errores en funcionalidades anteriores.

---

# 14. Pruebas

Verificar como mínimo:

- escritorio;
- móvil;
- tablet/responsive;
- navegación entre pestañas;
- recarga de página;
- cambio de número;
- datasets vacíos;
- datasets incompletos;
- valores extremos;
- números 1 y 56;
- selección de seis números válida;
- entradas inválidas;
- resize de gráficas;
- tooltips;
- zoom;
- funcionamiento de gráficas al cambiar entre pestañas.

Revisar consola JavaScript y eliminar warnings o errores introducidos por la implementación.

---

# 15. Importante: no modificar sin entender

Antes de comenzar:

1. Recorre el proyecto.
2. Identifica los archivos involucrados.
3. Identifica cómo se obtienen actualmente los sorteos.
4. Identifica las consultas y cálculos estadísticos existentes.
5. Identifica qué puede reutilizarse.
6. Identifica posibles problemas de rendimiento.
7. Después implementa.

No reemplaces código funcional simplemente por preferencia personal.

No hagas cambios masivos de arquitectura que no sean necesarios para este objetivo.

---

# Resultado esperado

La sección debe pasar de ser:

```text
Estadística
Estadística
Estadística
Estadística
Estadística
Estadística
...
```

en una página vertical interminable,

a sentirse como:

```text
ESTADÍSTICAS

[ Resumen ]
[ Radiografía ]
[ Tendencias ]
[ Matriz ]
[ Relaciones ]
[ Analizar combinación ]

---------------------------------

      visualización activa

---------------------------------
```

Quiero que la sensación final sea más cercana a un:

## Laboratorio de datos del Melate

que a una página tradicional de rankings.

La información histórica debe poder:

- explorarse;
- compararse;
- filtrarse;
- visualizarse;
- entenderse.

El principal diferenciador debe ser **la interacción con los datos históricos**, no simplemente tener más tablas.

---

# Al finalizar

Entrega también un pequeño resumen técnico indicando:

- archivos modificados;
- archivos nuevos;
- tablas/consultas utilizadas;
- endpoints o acciones nuevas;
- dependencias agregadas;
- cálculos nuevos implementados;
- posibles puntos de rendimiento a vigilar;
- cómo extender en el futuro las nuevas visualizaciones.

Si durante el análisis encuentras que alguna propuesta de este documento no tiene sentido con la arquitectura o los datos reales del proyecto, **no fuerces su implementación**.

Adapta la solución, explica brevemente la decisión y conserva siempre estos objetivos:

1. no romper lo existente;
2. mejorar significativamente la UX;
3. hacer la sección de estadísticas mucho más interactiva;
4. aprovechar ECharts;
5. mantener el código sencillo de mantener.