/**
 * AVENTURE - Controle de Acessibilidade Visual (A+ / A-)
 */

(function () {
    const FONT_KEY = 'aventure_font_scale';
    let currentScale = parseFloat(localStorage.getItem(FONT_KEY)) || 1.0;

    function applyScale(scale) {
        currentScale = Math.min(Math.max(scale, 0.85), 1.3);
        document.documentElement.style.setProperty('--font-scale', currentScale);
        localStorage.setItem(FONT_KEY, currentScale);
    }

    // Aplicar escala salva imediatamente
    applyScale(currentScale);

    document.addEventListener('DOMContentLoaded', () => {
        const btnIncrease = document.getElementById('btn-font-increase');
        const btnDecrease = document.getElementById('btn-font-decrease');
        const btnReset = document.getElementById('btn-font-reset');

        if (btnIncrease) {
            btnIncrease.addEventListener('click', () => applyScale(currentScale + 0.1));
        }
        if (btnDecrease) {
            btnDecrease.addEventListener('click', () => applyScale(currentScale - 0.1));
        }
        if (btnReset) {
            btnReset.addEventListener('click', () => applyScale(1.0));
        }
    });
})();
