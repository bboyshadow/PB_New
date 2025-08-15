/**
 * @file promotion.js
 * @description Gestiona la funcionalidad de Promotion para la calculadora de chárter.
 */

const Promotion = {
    /**
     * Inicializa listeners y estado para la sección de promoción.
     * Actualmente no requiere configuración adicional.
     * @function init
     * @returns {void}
     */
    init: function() {
        // Inicialización mínima
    },

    /**
     * Muestra u oculta el campo de promoción según el checkbox.
     * @function toggleVisibility
     * @param {HTMLInputElement} checkbox - Checkbox que habilita la promoción
     * @returns {void}
     */
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