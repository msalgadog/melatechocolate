/**
 * Mellatron - Visualización de Tendencias Históricas
 */
(function() {
    'use strict';

    window.MellatronTrends = {
        selectedNumbers: [7, 23, 41],
        loaded: false,

        init: function() {
            if (!this.loaded) {
                this.renderSelectorGrid();
                this.bindEvents();
                this.loaded = true;
            }
            this.loadTrends();
        },

        renderSelectorGrid: function() {
            const container = document.getElementById('trends-selector-grid');
            if (!container) return;

            let html = '';
            for (let i = 1; i <= 56; i++) {
                const sel = this.selectedNumbers.includes(i) ? 'selected' : '';
                html += `<button class="num-chip-btn ${sel}" data-num="${i}">${i}</button>`;
            }
            container.innerHTML = html;
        },

        bindEvents: function() {
            const self = this;
            const container = document.getElementById('trends-selector-grid');
            if (container) {
                container.addEventListener('click', function(e) {
                    const btn = e.target.closest('.num-chip-btn');
                    if (btn) {
                        const num = parseInt(btn.getAttribute('data-num'), 10);
                        self.toggleNumber(num);
                    }
                });
            }
        },

        toggleNumber: function(num) {
            const idx = this.selectedNumbers.indexOf(num);
            if (idx > -1) {
                if (this.selectedNumbers.length <= 1) return; // Mantener al menos 1
                this.selectedNumbers.splice(idx, 1);
            } else {
                if (this.selectedNumbers.length >= 6) {
                    Swal.fire({ icon: 'info', title: 'Máximo 6 números', text: 'Puedes seleccionar hasta 6 números simultáneamente para comparar.' });
                    return;
                }
                this.selectedNumbers.push(num);
            }
            this.selectedNumbers.sort((a, b) => a - b);
            this.renderSelectorGrid();
            this.loadTrends();
        },

        loadTrends: function() {
            const currentGame = window.MellatronStats ? window.MellatronStats.activeGame || 'melate' : 'melate';
            const numsStr = this.selectedNumbers.join(',');
            const loadingEl = document.getElementById('trends-loading');
            const contentEl = document.getElementById('trends-content');

            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            fetch(`${window.APP_URL || ''}/api/stats.php?action=trends&numeros=${numsStr}&juego=${currentGame}`)
                .then(res => res.json())
                .then(res => {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (contentEl) contentEl.style.display = 'block';

                    if (res.success && res.data) {
                        this.renderSummaryTable(res.data);
                        this.renderLineChart(res.data);
                    }
                })
                .catch(err => {
                    console.error("Error al cargar tendencias:", err);
                    if (loadingEl) loadingEl.innerHTML = '<div class="alert alert-danger">Error al cargar datos de tendencias.</div>';
                });
        },

        renderSummaryTable: function(data) {
            const tbody = document.getElementById('trends-summary-tbody');
            if (!tbody || !data.summary) return;

            let html = '';
            Object.values(data.summary).forEach(item => {
                const w = item.windows;
                html += `
                    <tr>
                        <td><span class="bola bola-melate bola-sm d-inline-block">${item.numero}</span></td>
                        <td><b>${w[20].veces}</b> (${w[20].pct}%)</td>
                        <td><b>${w[50].veces}</b> (${w[50].pct}%)</td>
                        <td><b>${w[100].veces}</b> (${w[100].pct}%)</td>
                        <td><b>${w[200].veces}</b> (${w[200].pct}%)</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        },

        renderLineChart: function(data) {
            const container = document.getElementById('echart-trends-line');
            if (!container || typeof echarts === 'undefined') return;

            const chart = echarts.init(container);
            const concursos = data.concursos || [];
            const seriesData = data.series || {};

            const colors = ['#27ae60', '#1976d2', '#e53935', '#f39c12', '#8e44ad', '#16a085'];
            const seriesList = [];

            let colorIdx = 0;
            Object.keys(seriesData).forEach(num => {
                seriesList.push({
                    name: `Número ${num}`,
                    type: 'line',
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 6,
                    itemStyle: { color: colors[colorIdx % colors.length] },
                    lineStyle: { width: 2.5 },
                    data: seriesData[num]
                });
                colorIdx++;
            });

            const option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'cross' }
                },
                legend: { top: '0', left: 'center' },
                grid: { left: '3%', right: '4%', bottom: '12%', top: '12%', containLabel: true },
                xAxis: {
                    type: 'category',
                    name: 'Ventana Móvil',
                    boundaryGap: false,
                    data: concursos,
                    axisLabel: { fontSize: 10 }
                },
                yAxis: {
                    type: 'value',
                    name: 'Apariciones en 20 sorteos',
                    minInterval: 1,
                    splitLine: { lineStyle: { type: 'dashed', color: '#e0e0e0' } }
                },
                dataZoom: [
                    { type: 'inside', start: 0, end: 100 },
                    { type: 'slider', start: 0, end: 100, bottom: '2%' }
                ],
                series: seriesList
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('trends-line', chart);
        }
    };
})();
