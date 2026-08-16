/**
 * Mellatron - Matriz de Parejas 56x56 (ECharts Heatmap)
 */
(function() {
    'use strict';

    window.MellatronPairsMatrix = {
        loaded: false,

        init: function() {
            if (!this.loaded) {
                this.loadMatrix();
                this.loaded = true;
            } else {
                window.MellatronStats.resizeAll();
            }
        },

        loadMatrix: function() {
            const currentGame = window.MellatronStats ? window.MellatronStats.activeGame || 'melate' : 'melate';
            const loadingEl = document.getElementById('matrix-loading');
            const contentEl = document.getElementById('matrix-content');

            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            fetch(`${window.APP_URL || ''}/api/stats.php?action=pairs_matrix&juego=${currentGame}`)
                .then(res => res.json())
                .then(res => {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (contentEl) contentEl.style.display = 'block';

                    if (res.success && res.data) {
                        this.renderHeatmap(res.data);
                    }
                })
                .catch(err => {
                    console.error("Error al cargar matriz de parejas:", err);
                    if (loadingEl) loadingEl.innerHTML = '<div class="alert alert-danger">Error al cargar la matriz de parejas.</div>';
                });
        },

        renderHeatmap: function(data) {
            const container = document.getElementById('echart-pairs-heatmap');
            if (!container || typeof echarts === 'undefined') return;

            const chart = echarts.init(container);
            const matrix = data.matrix || {};
            const maxVeces = data.max_veces || 50;

            const numbers = [];
            for (let i = 1; i <= 56; i++) numbers.push(i.toString());

            const heatmapData = [];

            for (let i = 1; i <= 56; i++) {
                for (let j = 1; j <= 56; j++) {
                    if (i === j) {
                        heatmapData.push([i - 1, j - 1, '-']);
                    } else if (matrix[i] && matrix[i][j]) {
                        const cell = matrix[i][j];
                        heatmapData.push([
                            i - 1,
                            j - 1,
                            cell.veces,
                            cell.concurso,
                            cell.fecha
                        ]);
                    } else {
                        heatmapData.push([i - 1, j - 1, 0, null, '']);
                    }
                }
            }

            const option = {
                tooltip: {
                    position: 'top',
                    formatter: function(params) {
                        const xNum = params.value[0] + 1;
                        const yNum = params.value[1] + 1;
                        const veces = params.value[2];
                        const concurso = params.value[3];
                        const fecha = params.value[4];

                        if (xNum === yNum || veces === '-') {
                            return `<b>Mismo número: ${xNum}</b>`;
                        }
                        return `
                            <div style="font-size:13px">
                                <b>Pareja ${xNum} + ${yNum}</b><br/>
                                Coincidencias históricas: <b style="color:#27ae60">${veces} veces</b><br/>
                                ${concurso ? `Última coincidencia: <b>Concurso ${concurso}</b> (${fecha})` : 'Sin coincidencias'}
                            </div>
                        `;
                    }
                },
                grid: { left: '4%', right: '8%', bottom: '10%', top: '5%', containLabel: true },
                xAxis: {
                    type: 'category',
                    data: numbers,
                    splitArea: { show: true },
                    axisLabel: { fontSize: 9, interval: 0 }
                },
                yAxis: {
                    type: 'category',
                    data: numbers,
                    splitArea: { show: true },
                    axisLabel: { fontSize: 9, interval: 0 }
                },
                visualMap: {
                    min: 0,
                    max: maxVeces,
                    calculable: true,
                    orient: 'vertical',
                    right: '1%',
                    top: 'center',
                    inRange: {
                        color: ['#f7fbff', '#deebf7', '#9ecae1', '#3182bd', '#e53935']
                    }
                },
                dataZoom: [
                    { type: 'slider', xAxisIndex: 0, start: 0, end: 100, bottom: '2%' },
                    { type: 'slider', yAxisIndex: 0, start: 0, end: 100, left: '0%' },
                    { type: 'inside', xAxisIndex: 0 },
                    { type: 'inside', yAxisIndex: 0 }
                ],
                series: [{
                    name: 'Parejas Frecuentes',
                    type: 'heatmap',
                    data: heatmapData,
                    label: { show: false },
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('pairs-heatmap', chart);
        }
    };
})();
