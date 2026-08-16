/**
 * Mellatron - Gestor de Pestañas y Enrutamiento de Hash para Estadísticas ECharts
 */
(function() {
    'use strict';

    const DEFAULT_TAB = 'resumen';
    const VALID_TABS = ['resumen', 'numero', 'tendencias', 'matriz', 'relaciones', 'combinacion'];

    window.MellatronStats = window.MellatronStats || {};
    window.MellatronStats.activeTab = DEFAULT_TAB;
    window.MellatronStats.chartInstances = {};

    /**
     * Registra una instancia ECharts para redimensionamiento automático
     */
    window.MellatronStats.registerChart = function(key, chartInstance) {
        if (window.MellatronStats.chartInstances[key]) {
            try {
                window.MellatronStats.chartInstances[key].dispose();
            } catch (e) {}
        }
        window.MellatronStats.chartInstances[key] = chartInstance;
    };

    /**
     * Redimensiona todas las gráficas ECharts activas
     */
    window.MellatronStats.resizeAll = function() {
        Object.values(window.MellatronStats.chartInstances).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                try {
                    chart.resize();
                } catch (e) {}
            }
        });
    };

    /**
     * Activa una pestaña por su ID
     */
    window.MellatronStats.activateTab = function(tabId, updateHash = true) {
        if (!VALID_TABS.includes(tabId)) {
            tabId = DEFAULT_TAB;
        }

        window.MellatronStats.activeTab = tabId;

        // Actualizar botones del submenú
        document.querySelectorAll('.stats-tab-link').forEach(btn => {
            if (btn.getAttribute('data-tab') === tabId) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Alternar visibilidad de paneles de contenido
        document.querySelectorAll('.stats-tab-pane').forEach(pane => {
            if (pane.id === 'tab-pane-' + tabId) {
                pane.classList.add('active');
                pane.style.display = 'block';
            } else {
                pane.classList.remove('active');
                pane.style.display = 'none';
            }
        });

        if (updateHash) {
            history.replaceState(null, '', '#' + tabId);
        }

        // Carga diferida e inicialización del módulo de la pestaña activa
        setTimeout(() => {
            window.MellatronStats.triggerTabLoad(tabId);
            window.MellatronStats.resizeAll();
        }, 50);
    };

    /**
     * Dispara la carga o actualización de datos de la pestaña activa
     */
    window.MellatronStats.triggerTabLoad = function(tabId) {
        switch (tabId) {
            case 'resumen':
                if (typeof window.MellatronOverview !== 'undefined' && window.MellatronOverview.init) {
                    window.MellatronOverview.init();
                }
                break;
            case 'numero':
                if (typeof window.MellatronNumberProfile !== 'undefined' && window.MellatronNumberProfile.init) {
                    window.MellatronNumberProfile.init();
                }
                break;
            case 'tendencias':
                if (typeof window.MellatronTrends !== 'undefined' && window.MellatronTrends.init) {
                    window.MellatronTrends.init();
                }
                break;
            case 'matriz':
                if (typeof window.MellatronPairsMatrix !== 'undefined' && window.MellatronPairsMatrix.init) {
                    window.MellatronPairsMatrix.init();
                }
                break;
            case 'relaciones':
                if (typeof window.MellatronRelations !== 'undefined' && window.MellatronRelations.init) {
                    window.MellatronRelations.init();
                }
                break;
            case 'combinacion':
                if (typeof window.MellatronCombination !== 'undefined' && window.MellatronCombination.init) {
                    window.MellatronCombination.init();
                }
                break;
        }
    };

    /**
     * Inicializador de eventos al cargar el DOM
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Asignar listeners a botones de pestañas
        document.querySelectorAll('.stats-tab-link').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetTab = this.getAttribute('data-tab');
                window.MellatronStats.activateTab(targetTab, true);
            });
        });

        // Obtener pestaña desde hash de URL
        let initialTab = DEFAULT_TAB;
        if (window.location.hash) {
            const hash = window.location.hash.substring(1).toLowerCase();
            if (VALID_TABS.includes(hash)) {
                initialTab = hash;
            }
        }

        window.MellatronStats.activateTab(initialTab, false);

        // Listener de cambios en el hash de navegación
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1).toLowerCase();
            if (VALID_TABS.includes(hash)) {
                window.MellatronStats.activateTab(hash, false);
            }
        });

        // Listener de redimensionamiento de ventana
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                window.MellatronStats.resizeAll();
            }, 150);
        });
    });

})();
