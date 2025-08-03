// ARCHIVO shared\js\ini.js
//***** Inicialización de la Aplicación *****\\

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
