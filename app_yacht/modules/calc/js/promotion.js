/**
 * @file promotion.js
 * @description Gestiona la funcionalidad de Promotion para la calculadora de chárter.
 */

const Promotion = {
    init: function() {
        // Inicialización mínima
    },

    toggleVisibility: function(checkbox) {
        const promotionField = document.getElementById('promotionField');
        if (!promotionField) return;

        const isEnabled = checkbox.checked;
        promotionField.style.display = isEnabled ? 'flex' : 'none';
    }
};

// Inicializar el script cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    Promotion.init();
});