/**
 * Mellatron - Visualizaciones ECharts para la Pestaña Resumen
 */
(function() {
    'use strict';

    window.MellatronOverview = {
        initialized: false,
        init: function() {
            if (this.initialized) {
                window.MellatronStats.resizeAll();
                return;
            }
            this.renderCharts();
            this.initialized = true;
        },

        renderCharts: function() {
            if (typeof echarts === 'undefined') return;

            this.renderFrequencyChart();
            this.renderParImparChart();
            this.renderAltoBajoChart();
            this.renderSumasChart();
        },

        renderFrequencyChart: function() {
            const container = document.getElementById('echart-frecuencia');
            if (!container || !window.overviewData) return;

            const chart = echarts.init(container);
            const freqData = window.overviewData.freqData || [];
            const labelsData = window.overviewData.labelsData || [];
            const maxF = Math.max(...freqData, 1);

            const seriesData = freqData.map((val, idx) => {
                let color = '#27ae60';
                if (val >= maxF * 0.7) color = '#e53935'; // Caliente
                else if (val <= maxF * 0.3) color = '#1976d2'; // Frío
                return {
                    value: val,
                    itemStyle: { color: color, borderRadius: [4, 4, 0, 0] }
                };
            });

            const option = {
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        const p = params[0];
                        return `<b>Número ${p.name}</b><br/>Frecuencia histórica: <b>${p.value} veces</b>`;
                    }
                },
                grid: { left: '3%', right: '3%', bottom: '8%', top: '5%', containLabel: true },
                xAxis: {
                    type: 'category',
                    data: labelsData,
                    axisLabel: { fontSize: 11, interval: 0 }
                },
                yAxis: {
                    type: 'value',
                    splitLine: { lineStyle: { type: 'dashed', color: '#e0e0e0' } }
                },
                series: [{
                    name: 'Frecuencia',
                    type: 'bar',
                    barWidth: '75%',
                    data: seriesData
                }]
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('overview-freq', chart);
        },

        renderParImparChart: function() {
            const container = document.getElementById('echart-par-impar');
            if (!container || !window.overviewData) return;

            const chart = echarts.init(container);
            const pares = window.overviewData.pares || 0;
            const impares = window.overviewData.impares || 0;

            const option = {
                tooltip: { trigger: 'item', formatter: '{b}: <b>{c} ({d}%)</b>' },
                legend: { bottom: '0', left: 'center' },
                series: [{
                    name: 'Par / Impar',
                    type: 'pie',
                    radius: ['45%', '75%'],
                    avoidLabelOverlap: false,
                    itemStyle: { borderRadius: 6, borderColor: '#fff', borderWidth: 2 },
                    label: { show: false },
                    emphasis: { label: { show: true, fontSize: 14, fontWeight: 'bold' } },
                    data: [
                        { value: pares, name: 'Pares', itemStyle: { color: '#27ae60' } },
                        { value: impares, name: 'Impares', itemStyle: { color: '#1976d2' } }
                    ]
                }]
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('overview-par-impar', chart);
        },

        renderAltoBajoChart: function() {
            const container = document.getElementById('echart-alto-bajo');
            if (!container || !window.overviewData) return;

            const chart = echarts.init(container);
            const bajos = window.overviewData.bajos || 0;
            const altos = window.overviewData.altos || 0;

            const option = {
                tooltip: { trigger: 'item', formatter: '{b}: <b>{c} ({d}%)</b>' },
                legend: { bottom: '0', left: 'center' },
                series: [{
                    name: 'Alto / Bajo',
                    type: 'pie',
                    radius: ['45%', '75%'],
                    avoidLabelOverlap: false,
                    itemStyle: { borderRadius: 6, borderColor: '#fff', borderWidth: 2 },
                    label: { show: false },
                    emphasis: { label: { show: true, fontSize: 14, fontWeight: 'bold' } },
                    data: [
                        { value: bajos, name: 'Bajos (1-28)', itemStyle: { color: '#66bb6a' } },
                        { value: altos, name: 'Altos (29-56)', itemStyle: { color: '#ef5350' } }
                    ]
                }]
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('overview-alto-bajo', chart);
        },

        renderSumasChart: function() {
            const container = document.getElementById('echart-sumas');
            if (!container || !window.overviewData || !window.overviewData.sumasLabels) return;

            const chart = echarts.init(container);
            const labels = window.overviewData.sumasLabels;
            const values = window.overviewData.sumasValues;

            const option = {
                tooltip: {
                    trigger: 'axis',
                    formatter: function(params) {
                        const p = params[0];
                        return `Rango de Suma <b>${p.name}</b><br/>Sorteos: <b>${p.value}</b>`;
                    }
                },
                grid: { left: '3%', right: '3%', bottom: '10%', top: '5%', containLabel: true },
                xAxis: {
                    type: 'category',
                    data: labels,
                    axisLabel: { fontSize: 11 }
                },
                yAxis: {
                    type: 'value',
                    splitLine: { lineStyle: { type: 'dashed', color: '#e0e0e0' } }
                },
                series: [{
                    name: 'Sorteos',
                    type: 'bar',
                    data: values,
                    itemStyle: { color: '#27ae60', borderRadius: [4, 4, 0, 0] }
                }]
            };

            chart.setOption(option);
            window.MellatronStats.registerChart('overview-sumas', chart);
        }
    };
})();
