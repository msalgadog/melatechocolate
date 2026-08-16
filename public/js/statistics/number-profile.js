/**
 * Mellatron - Visualización y Expediente para Pestaña Radiografía de un Número
 */
(function() {
    'use strict';

    window.MellatronNumberProfile = {
        selectedNumber: 23,
        loaded: false,

        init: function() {
            if (!this.loaded) {
                this.renderNumberSelector();
                this.bindEvents();
                this.loaded = true;
            }
            this.loadProfile(this.selectedNumber);
        },

        renderNumberSelector: function() {
            const container = document.getElementById('num-selector-grid');
            if (!container) return;

            let html = '';
            for (let i = 1; i <= 56; i++) {
                const activeClass = (i === this.selectedNumber) ? 'selected' : '';
                html += `<button class="num-chip-btn ${activeClass}" data-num="${i}">${i}</button>`;
            }
            container.innerHTML = html;

            const dropdown = document.getElementById('num-selector-select');
            if (dropdown) {
                let optHtml = '';
                for (let i = 1; i <= 56; i++) {
                    const sel = (i === this.selectedNumber) ? 'selected' : '';
                    optHtml += `<option value="${i}" ${sel}>Número ${i}</option>`;
                }
                dropdown.innerHTML = optHtml;
            }
        },

        bindEvents: function() {
            const self = this;
            const container = document.getElementById('num-selector-grid');
            if (container) {
                container.addEventListener('click', function(e) {
                    const btn = e.target.closest('.num-chip-btn');
                    if (btn) {
                        const num = parseInt(btn.getAttribute('data-num'), 10);
                        self.selectNumber(num);
                    }
                });
            }

            const dropdown = document.getElementById('num-selector-select');
            if (dropdown) {
                dropdown.addEventListener('change', function() {
                    const num = parseInt(this.value, 10);
                    self.selectNumber(num);
                });
            }
        },

        selectNumber: function(num) {
            if (num < 1 || num > 56) return;
            this.selectedNumber = num;

            // Actualizar estado visual de los chips y el select
            document.querySelectorAll('#num-selector-grid .num-chip-btn').forEach(btn => {
                if (parseInt(btn.getAttribute('data-num'), 10) === num) {
                    btn.classList.add('selected');
                } else {
                    btn.classList.remove('selected');
                }
            });

            const dropdown = document.getElementById('num-selector-select');
            if (dropdown) dropdown.value = num;

            this.loadProfile(num);
        },

        loadProfile: function(num) {
            const currentGame = window.MellatronStats ? window.MellatronStats.activeGame || 'melate' : 'melate';
            const loadingEl = document.getElementById('num-profile-loading');
            const contentEl = document.getElementById('num-profile-content');

            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            fetch(`${window.APP_URL || ''}/api/stats.php?action=number_profile&numero=${num}&juego=${currentGame}`)
                .then(res => res.json())
                .then(res => {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (contentEl) contentEl.style.display = 'block';

                    if (res.success && res.data) {
                        this.renderDossier(res.data);
                        this.renderTimelineChart(res.data);
                    }
                })
                .catch(err => {
                    console.error("Error al cargar radiografía del número:", err);
                    if (loadingEl) loadingEl.innerHTML = '<div class="alert alert-danger">Error al cargar datos del número.</div>';
                });
        },

        renderDossier: function(data) {
            document.querySelectorAll('.val-num-title').forEach(el => el.textContent = data.numero);
            document.querySelectorAll('.val-total-apariciones').forEach(el => el.textContent = `${data.apariciones_total} veces`);
            document.querySelectorAll('.val-pct-aparicion').forEach(el => el.textContent = `${data.pct_aparicion}%`);
            document.querySelectorAll('.val-ultimo-concurso').forEach(el => el.textContent = data.ultimo_concurso ? `C${data.ultimo_concurso} (${data.ultima_fecha})` : 'N/A');
            document.querySelectorAll('.val-retardo-actual').forEach(el => el.textContent = `${data.retardo_actual} sorteos`);
            document.querySelectorAll('.val-retardo-promedio').forEach(el => el.textContent = `${data.retardo_promedio} sorteos`);
            document.querySelectorAll('.val-retardo-maximo').forEach(el => el.textContent = `${data.retardo_maximo} sorteos`);

            document.querySelectorAll('.val-freq-20').forEach(el => el.textContent = data.freq_ultimos_20);
            document.querySelectorAll('.val-freq-50').forEach(el => el.textContent = data.freq_ultimos_50);
            document.querySelectorAll('.val-freq-100').forEach(el => el.textContent = data.freq_ultimos_100);
            document.querySelectorAll('.val-freq-200').forEach(el => el.textContent = data.freq_ultimos_200);

            // Compañeros más frecuentes
            const compContainer = document.getElementById('num-companions-list');
            if (compContainer) {
                if (data.companeros && data.companeros.length > 0) {
                    let html = '';
                    data.companeros.forEach(cp => {
                        html += `
                            <div class="companion-card">
                                <div class="bola bola-melate bola-sm mx-auto mb-1">${cp.numero}</div>
                                <div class="small fw-bold text-dark">${cp.veces} veces</div>
                            </div>
                        `;
                    });
                    compContainer.innerHTML = html;
                } else {
                    compContainer.innerHTML = '<span class="text-muted small">Sin datos de compañeros</span>';
                }
            }
        },

        renderTimelineChart: function(data) {
            const container = document.getElementById('echart-number-timeline');
            if (!container || typeof echarts === 'undefined') return;

            const chart = echarts.init(container);
            const timeline = data.timeline || [];

            const xData = timeline.map(t => `C${t.concurso}`);
            const yData = timeline.map(t => t.gap);

            const option = {
                title: { text: `Historial de Espaciado (Sorteos sin salir previo a la aparición)`, left: 'center', textStyle: { fontSize: 13, color: '#666' } },
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        const idx = params[0].dataIndex;
                        const item = timeline[idx];
                        return `<b>Concurso ${item.concurso} (${item.fecha})</b><br/>Apareció tras <b>${item.gap} sorteos sin salir</b>`;
                    }
                },
                grid: { left: '3%', right: '3%', bottom: '12%', top: '15%', containLabel: true },
                xAxis: {
                    type: 'category',
                    data: xData,
                    axisLabel: { fontSize: 10 }
                },
                yAxis: {
                    type: 'value',
                    name: 'Sorteos sin salir',
                    splitLine: { lineStyle: { type: 'dashed', color: '#e0e0e0' } }
                },
                dataZoom: [
                    { type: 'inside', start: 50, end: 100 },
                    { type: 'slider', start: 50, end: 100, bottom: '2%' }
                ],
                series: [{
                    name: 'Retardo Previo',
                    type: 'line',
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 6,
                    lineStyle: { color: '#27ae60', width: 2 },
                    itemStyle: { color: '#27ae60' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(39, 174, 96, 0.4)' },
                            { offset: 1, color: 'rgba(39, 174, 96, 0.05)' }
                        ])
                    },
                    data: yData
                }]
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('number-timeline', chart);
        }
    };
})();
