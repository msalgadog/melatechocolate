/**
 * Mellatron - Visualización y Análisis del ADN Estadístico de Combinación
 */
(function() {
    'use strict';

    window.MellatronCombination = {
        selectedNums: [7, 13, 22, 34, 41, 53],
        loaded: false,

        init: function() {
            if (!this.loaded) {
                this.renderInputs();
                this.bindEvents();
                this.loaded = true;
            }
            this.analyze();
        },

        renderInputs: function() {
            const container = document.getElementById('comb-inputs-container');
            if (!container) return;

            let html = '';
            for (let i = 0; i < 6; i++) {
                html += `
                    <div class="col-4 col-sm-2">
                        <input type="number" min="1" max="56" class="form-control form-control-lg text-center fw-bold comb-num-input"
                               data-idx="${i}" value="${this.selectedNums[i]}" />
                    </div>
                `;
            }
            container.innerHTML = html;
        },

        bindEvents: function() {
            const self = this;
            const btn = document.getElementById('comb-btn-analyze');
            if (btn) {
                btn.addEventListener('click', function() {
                    self.readInputsAndAnalyze();
                });
            }

            const btnRandom = document.getElementById('comb-btn-random');
            if (btnRandom) {
                btnRandom.addEventListener('click', function() {
                    self.generateRandom();
                });
            }
        },

        generateRandom: function() {
            const nums = [];
            while (nums.length < 6) {
                const r = Math.floor(Math.random() * 56) + 1;
                if (!nums.includes(r)) nums.push(r);
            }
            nums.sort((a, b) => a - b);
            this.selectedNums = nums;

            document.querySelectorAll('.comb-num-input').forEach((input, idx) => {
                input.value = nums[idx];
            });

            this.analyze();
        },

        readInputsAndAnalyze: function() {
            const inputs = document.querySelectorAll('.comb-num-input');
            const arr = [];
            inputs.forEach(inp => {
                const v = parseInt(inp.value, 10);
                if (!isNaN(v) && v >= 1 && v <= 56) {
                    arr.push(v);
                }
            });

            const unique = Array.from(new Set(arr));
            if (unique.length !== 6) {
                Swal.fire({ icon: 'warning', title: 'Entrada Inválida', text: 'Por favor ingresa 6 números distintos entre 1 y 56.' });
                return;
            }

            unique.sort((a, b) => a - b);
            this.selectedNums = unique;
            this.analyze();
        },

        analyze: function() {
            const currentGame = window.MellatronStats ? window.MellatronStats.activeGame || 'melate' : 'melate';
            const numsStr = this.selectedNums.join(',');
            const loadingEl = document.getElementById('comb-loading');
            const contentEl = document.getElementById('comb-content');

            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            fetch(`${window.APP_URL || ''}/api/stats.php?action=combination_dna&numeros=${numsStr}&juego=${currentGame}`)
                .then(res => res.json())
                .then(res => {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (contentEl) contentEl.style.display = 'block';

                    if (res.success && res.data) {
                        this.renderDossier(res.data);
                        this.renderRadarChart(res.data);
                        this.renderSimilarDraws(res.data);
                    }
                })
                .catch(err => {
                    console.error("Error al analizar ADN de combinación:", err);
                    if (loadingEl) loadingEl.innerHTML = '<div class="alert alert-danger">Error al analizar la combinación.</div>';
                });
        },

        renderDossier: function(data) {
            document.querySelectorAll('.val-comb-suma').forEach(el => el.textContent = data.suma);
            document.querySelectorAll('.val-comb-percentil-suma').forEach(el => el.textContent = `${data.percentil_suma}%`);
            document.querySelectorAll('.val-comb-par-impar').forEach(el => el.textContent = data.par_impar);
            document.querySelectorAll('.val-comb-alto-bajo').forEach(el => el.textContent = data.alto_bajo);
            document.querySelectorAll('.val-comb-consecutivos').forEach(el => el.textContent = `${data.consecutivos_count} pareja(s)`);
            document.querySelectorAll('.val-comb-dist-promedio').forEach(el => el.textContent = data.distancia_promedio);
            document.querySelectorAll('.val-comb-dispersion').forEach(el => el.textContent = data.dispersion);
            document.querySelectorAll('.val-comb-pureza').forEach(el => el.textContent = `${data.pureza} / 100`);

            const ballsContainer = document.getElementById('comb-balls-display');
            if (ballsContainer && data.nums) {
                let html = '';
                data.nums.forEach(n => {
                    html += `<div class="bola bola-melate bola-lg">${n}</div>`;
                });
                ballsContainer.innerHTML = html;
            }
        },

        renderRadarChart: function(data) {
            const container = document.getElementById('echart-comb-radar');
            if (!container || typeof echarts === 'undefined') return;

            const chart = echarts.init(container);

            // Escalar métricas a 0-100 para radar
            const sumPct = Math.min(100, Math.max(0, data.percentil_suma));
            const pureza = Math.min(100, Math.max(0, data.pureza));
            const distScore = Math.min(100, Math.max(0, Math.round((data.distancia_promedio / 11) * 100)));
            const dispScore = Math.min(100, Math.max(0, Math.round((data.dispersion / 20) * 100)));
            const pairScore = Math.min(100, Math.max(0, Math.round((data.avg_pair_freq / 50) * 100)));

            const option = {
                title: { text: 'ADN Estadístico — Perfil Multidimensional', left: 'center', textStyle: { fontSize: 13, color: '#666' } },
                tooltip: { trigger: 'item' },
                radar: {
                    indicator: [
                        { name: 'Percentil de Suma', max: 100 },
                        { name: 'Índice de Pureza', max: 100 },
                        { name: 'Equilibrio de Distancia', max: 100 },
                        { name: 'Dispersión', max: 100 },
                        { name: 'Frecuencia de Parejas', max: 100 }
                    ],
                    radius: '65%'
                },
                series: [{
                    name: 'Perfil de Combinación',
                    type: 'radar',
                    data: [{
                        value: [sumPct, pureza, distScore, dispScore, pairScore],
                        name: 'Combinación Seleccionada',
                        itemStyle: { color: '#27ae60' },
                        areaStyle: { color: 'rgba(39, 174, 96, 0.4)' }
                    }]
                }]
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('comb-radar', chart);
        },

        renderSimilarDraws: function(data) {
            const container = document.getElementById('comb-similar-draws');
            if (!container) return;

            const exactos = data.exactos || [];
            const similares = data.similares || [];

            let html = '';
            if (exactos.length > 0) {
                html += `<div class="alert alert-warning fw-bold mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> ¡Esta combinación ya fue sorteada previamente en el concurso ${exactos[0].concurso}!</div>`;
            } else {
                html += `<div class="alert alert-success small mb-3"><i class="bi bi-check-circle-fill me-1"></i> Esta combinación exacta NO ha salido previamente en el histórico.</div>`;
            }

            if (similares.length > 0) {
                html += '<h6><i class="bi bi-clock-history me-1"></i> Sorteos Históricos más Similares (4 o 5 coincidencias):</h6>';
                html += '<div class="list-group list-group-flush small">';
                similares.forEach(s => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-2">
                            <div>
                                <b>Concurso ${s.concurso}</b> (${s.fecha}):
                                <span class="ms-2 font-monospace">${s.r1} ${s.r2} ${s.r3} ${s.r4} ${s.r5} ${s.r6}</span>
                            </div>
                            <span class="badge bg-success">${s.coincidencias} / 6 coincidencias</span>
                        </div>
                    `;
                });
                html += '</div>';
            } else {
                html += '<p class="small text-muted mb-0">No se encontraron sorteos históricos con 4 o más coincidencias.</p>';
            }

            container.innerHTML = html;
        }
    };
})();
