/**
 * AVENTURE - Script Principal
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicialização Lucide Icons
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Modal / Confirm Handler para Ações Importantes
    const confirmActions = document.querySelectorAll('[data-confirm]');
    confirmActions.forEach(element => {
        element.addEventListener('click', (e) => {
            const message = element.getAttribute('data-confirm') || 'Tem certeza que deseja realizar esta operação?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
