/**
 * Mellatron - JavaScript principal
 */

'use strict';

// ============================================================
// Animación de entrada de bolas
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    animateBolas();
    initTooltips();
    initCounters();
});

function animateBolas() {
    const bolas = document.querySelectorAll('.bola');
    bolas.forEach((b, i) => {
        b.style.opacity = '0';
        b.style.transform = 'scale(0)';
        setTimeout(() => {
            b.style.transition = `opacity .3s ease, transform .3s ease`;
            b.style.opacity = '1';
            b.style.transform = 'scale(1)';
        }, i * 80);
    });
}

function initTooltips() {
    const els = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    els.forEach(el => new bootstrap.Tooltip(el));
}

// ============================================================
// Contador animado de bolsas
// ============================================================
function initCounters() {
    document.querySelectorAll('[data-count-to]').forEach(el => {
        const target = parseInt(el.dataset.countTo, 10);
        if (isNaN(target)) return;
        let start = 0;
        const step = Math.ceil(target / 60);
        const timer = setInterval(() => {
            start = Math.min(start + step, target);
            el.textContent = formatMXN(start);
            if (start >= target) clearInterval(timer);
        }, 24);
    });
}

function formatMXN(n) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency', currency: 'MXN', maximumFractionDigits: 0
    }).format(n);
}

// ============================================================
// Generador de Melático (cliente)
// ============================================================
function generarMelatico(containerId, btnId) {
    const btn = document.getElementById(btnId);
    const out = document.getElementById(containerId);
    if (!btn || !out) return;

    btn.addEventListener('click', () => {
        const combinaciones = [];
        for (let i = 0; i < 5; i++) {
            const nums = new Set();
            while (nums.size < 6) nums.add(Math.floor(Math.random() * 56) + 1);
            combinaciones.push([...nums].sort((a, b) => a - b));
        }

        out.innerHTML = '';
        combinaciones.forEach((combo, idx) => {
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 mb-2';
            row.innerHTML = `<small class="text-muted fw-bold" style="width:60px">Juego ${idx + 1}</small>`;
            combo.forEach(n => {
                const b = document.createElement('div');
                b.className = 'bola bola-sm bola-melate bola-animate';
                b.style.animationDelay = `${idx * 0.1 + 0.05}s`;
                b.textContent = n;
                row.appendChild(b);
            });
            out.appendChild(row);
        });

        btn.textContent = '🎲 Generar de nuevo';
    });
}

// ============================================================
// Verificador de boleto (cliente-side)
// ============================================================
function initVerificador() {
    const form = document.getElementById('form-verificador');
    if (!form) return;

    form.addEventListener('submit', e => {
        e.preventDefault();
        const inputs = form.querySelectorAll('input.num-input');
        const nums = [...inputs].map(i => parseInt(i.value, 10)).filter(n => !isNaN(n) && n >= 1 && n <= 56);

        if (nums.length !== 6) {
            alert('Por favor ingresa exactamente 6 números entre 1 y 56.');
            return;
        }

        const unique = [...new Set(nums)];
        if (unique.length !== 6) {
            alert('Todos los números deben ser distintos.');
            return;
        }

        // Enviar al servidor para verificar
        form.querySelectorAll('input[name="n[]"]').forEach(i => i.remove());
        nums.forEach(n => {
            const h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'n[]';
            h.value = n;
            form.appendChild(h);
        });
        form.submit();
    });

    // Sólo dígitos en inputs de número
    form.querySelectorAll('input.num-input').forEach(inp => {
        inp.addEventListener('input', () => {
            let v = parseInt(inp.value, 10);
            if (isNaN(v) || v < 1) { inp.value = ''; return; }
            if (v > 56) inp.value = 56;
        });
    });
}

// ============================================================
// Inicializar heatmap con nivel de calor por frecuencia
// ============================================================
function initHeatmap(heatData) {
    const grid = document.getElementById('heatmap-grid');
    if (!grid || !heatData) return;

    const values = Object.values(heatData);
    const min  = Math.min(...values);
    const max  = Math.max(...values);

    Object.entries(heatData).forEach(([num, freq]) => {
        const pct = max > min ? (freq - min) / (max - min) : 0;
        const level = Math.round(pct * 9);
        const cell = document.createElement('div');
        cell.className = `heatmap-cell heat-${level}`;
        cell.textContent = num;
        cell.setAttribute('data-bs-toggle', 'tooltip');
        cell.setAttribute('title', `Número ${num}: ${freq} veces`);
        grid.appendChild(cell);
    });

    initTooltips();
}

// ============================================================
// Zodiac selector
// ============================================================
function initZodiacSelector() {
    document.querySelectorAll('.zodiac-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.zodiac-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            const signo = card.dataset.signo;
            document.getElementById('zodiac-signo-sel').value = signo;
            document.getElementById('resultado-zodiac').innerHTML = '';
        });
    });
}

// ============================================================
// Inicializar TODO al cargar
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    generarMelatico('melatico-result', 'btn-melatico');
    initVerificador();
    initZodiacSelector();
});
