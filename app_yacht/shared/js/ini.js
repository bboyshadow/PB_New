// ARCHIVO shared\js\ini.js
//***** Inicialización de la Aplicación *****\\

// Sistema de logging centralizado para debugging
window.AppYacht = window.AppYacht || {};
window.AppYacht.DEBUG = false; // Cambiar a true para debugging en desarrollo

// Logger centralizado que respeta el flag DEBUG
window.AppYacht.log = function(...args) {
    if (window.AppYacht.DEBUG) {
        console.log('[AppYacht]', ...args);
    }
};

window.AppYacht.warn = function(...args) {
    if (window.AppYacht.DEBUG) {
        console.warn('[AppYacht]', ...args);
    }
};

// Mantener errores siempre visibles independientemente del flag DEBUG
window.AppYacht.error = function(...args) {
    console.error('[AppYacht]', ...args);
};

function initializeCharterRate() {
    const container = document.getElementById('charterRateContainer');
    if (container.children.length === 0) addCharterRate(true);

    updateUIConstraints(); // Asegúrate de llamar a la función al cargar
}
document.addEventListener('DOMContentLoaded', () => {
    // Usa requestAnimationFrame para minimizar impacto inicial
    requestAnimationFrame(() => {
        initializeCharterRate();
    });

    // Optimiza la inicialización de eventos
    document.getElementById('currency').addEventListener('change', updateCurrencySymbols);
    document.getElementById('enableMixedSeasons').addEventListener('change', addMix);
    document.getElementById('enableOneDayCharter').addEventListener('change', () => {
        toggleOneDayCharter(document.getElementById('enableOneDayCharter').checked);
    });
});
// alert('El archivo ini.js se ha cargado correctamente.');
