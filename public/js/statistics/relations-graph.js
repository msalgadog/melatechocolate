/**
 * Mellatron - Mapa Interactivo de Relaciones (ECharts Graph Force Layout)
 */
(function() {
    'use strict';

    window.MellatronRelations = {
        graphData: null,
        minWeight: 15,
        chartInstance: null,
        loaded: false,

        init: function() {
            if (!this.loaded) {
                this.bindControls();
                this.loaded = true;
            }
            this.loadGraph();
        },

        bindControls: function() {
            const self = this;
            const slider = document.getElementById('relations-threshold-slider');
            const valLabel = document.getElementById('relations-threshold-val');

            if (slider) {
                slider.addEventListener('input', function() {
                    self.minWeight = parseInt(this.value, 10);
                    if (valLabel) valLabel.textContent = self.minWeight;
                    self.updateGraph();
                });
            }
        },

        loadGraph: function() {
            const currentGame = window.MellatronStats ? window.MellatronStats.activeGame || 'melate' : 'melate';
            const loadingEl = document.getElementById('relations-loading');
            const contentEl = document.getElementById('relations-content');

            if (loadingEl) loadingEl.style.display = 'block';
            if (contentEl) contentEl.style.display = 'none';

            fetch(`${window.APP_URL || ''}/api/stats.php?action=relations_graph&juego=${currentGame}`)
                .then(res => res.json())
                .then(res => {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (contentEl) contentEl.style.display = 'block';

                    if (res.success && res.data) {
                        this.graphData = res.data;

                        // Ajustar valores del slider según el rango de frecuencias de parejas
                        const maxPair = res.data.max_pair_freq || 50;
                        const slider = document.getElementById('relations-threshold-slider');
                        const valLabel = document.getElementById('relations-threshold-val');
                        if (slider) {
                            slider.max = maxPair;
                            this.minWeight = Math.round(maxPair * 0.4);
                            slider.value = this.minWeight;
                            if (valLabel) valLabel.textContent = this.minWeight;
                        }

                        this.updateGraph();
                    }
                })
                .catch(err => {
                    console.error("Error al cargar grafo de relaciones:", err);
                    if (loadingEl) loadingEl.innerHTML = '<div class="alert alert-danger">Error al cargar el mapa de relaciones.</div>';
                });
        },

        updateGraph: function() {
            const container = document.getElementById('echart-relations-graph');
            if (!container || !this.graphData || typeof echarts === 'undefined') return;

            if (!this.chartInstance) {
                this.chartInstance = echarts.init(container);
                window.MellatronStats.registerChart('relations-graph', this.chartInstance);

                // Evento de clic en nodo
                const self = this;
                this.chartInstance.on('click', function(params) {
                    if (params.dataType === 'node') {
                        const num = parseInt(params.data.name, 10);
                        self.showNodeDetails(num);
                    }
                });
            }

            const rawNodes = this.graphData.nodes || [];
            const rawLinks = this.graphData.links || [];
            const minW = this.minWeight;

            // Filtrar aristas por umbral de peso
            const filteredLinks = rawLinks.filter(l => l.value >= minW);

            // Obtener conjunto de nodos conectados
            const connectedNodeIds = new Set();
            filteredLinks.forEach(l => {
                connectedNodeIds.add(l.source);
                connectedNodeIds.add(l.target);
            });

            // Si hay pocos nodos conectados, incluir los de mayor frecuencia
            const nodes = rawNodes.map(n => ({
                ...n,
                itemStyle: {
                    color: connectedNodeIds.has(n.id) ? '#27ae60' : '#bdc3c7'
                }
            }));

            const option = {
                title: { text: `Visualización de Relaciones Históricas (Coincidencias ≥ ${minW} veces)`, left: 'center', textStyle: { fontSize: 13, color: '#666' } },
                tooltip: {
                    formatter: function(params) {
                        if (params.dataType === 'edge') {
                            return `<b>Pareja ${params.data.source} + ${params.data.target}</b><br/>Coincidencias: <b>${params.data.value} veces</b>`;
                        } else {
                            return `<b>Número ${params.data.name}</b><br/>Frecuencia general: <b>${params.data.value} veces</b>`;
                        }
                    }
                },
                series: [{
                    name: 'Relaciones Melate',
                    type: 'graph',
                    layout: 'force',
                    data: nodes,
                    links: filteredLinks,
                    roam: true,
                    label: {
                        show: true,
                        position: 'inside',
                        fontSize: 11,
                        fontWeight: 'bold',
                        color: '#fff'
                    },
                    force: {
                        repulsion: 120,
                        edgeLength: [40, 150],
                        gravity: 0.1
                    },
                    lineStyle: {
                        color: 'source',
                        curveness: 0.1,
                        opacity: 0.6
                    },
                    emphasis: {
                        focus: 'adjacency',
                        lineStyle: { width: 4, opacity: 1 }
                    }
                }]
            };

            this.chartInstance.setOption(option, true);
        },

        showNodeDetails: function(num) {
            const sidePanel = document.getElementById('relations-node-details');
            if (!sidePanel || !this.graphData) return;

            const links = this.graphData.links || [];
            const nodeLinks = links.filter(l => (parseInt(l.source, 10) === num || parseInt(l.target, 10) === num));

            nodeLinks.sort((a, b) => b.value - a.value);

            let html = `
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-success mb-2">
                            <i class="bi bi-diagram-3-fill me-1"></i> Expediente de Compañeros — Número ${num}
                        </h5>
                        <p class="small text-muted mb-3">Relaciones con mayor frecuencia conjunta histórica:</p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
            `;

            if (nodeLinks.length > 0) {
                nodeLinks.slice(0, 8).forEach(l => {
                    const compNum = (parseInt(l.source, 10) === num) ? l.target : l.source;
                    html += `
                        <div class="badge bg-light text-dark border p-2 text-center" style="min-width:70px">
                            <div class="bola bola-melate bola-sm mx-auto mb-1">${compNum}</div>
                            <span class="small fw-bold text-success">${l.value} veces</span>
                        </div>
                    `;
                });
            } else {
                html += '<span class="text-muted small">Sin relaciones registradas en este nivel.</span>';
            }

            html += `
                        </div>
                        <button class="btn btn-sm btn-outline-success" onclick="MellatronStats.activateTab('numero', true); MellatronNumberProfile.selectNumber(${num});">
                            Ver Radiografía Completa del ${num} &rarr;
                        </button>
                    </div>
                </div>
            `;

            sidePanel.innerHTML = html;
        }
    };
})();
