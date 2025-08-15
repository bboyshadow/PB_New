// ARCHIVO shared\js\currency.js

/**
 * Funciones relacionadas con el manejo de monedas
 * Este archivo centraliza todas las funciones relacionadas con monedas
 * para evitar duplicación de código entre módulos
 */

/**
 * Actualiza los símbolos de moneda en todos los campos relevantes de la interfaz
 * Esta función debe ser llamada cuando cambia la moneda seleccionada
 * This function should be called when the selected currency changes
 */
function updateCurrencySymbols() {
    // Obtenemos la moneda seleccionada
    const selectedCurrency = document.getElementById('currency')?.value || '';
    
    // Utilizamos el mismo mapeo de símbolos que en PHP (shared/php/currency-functions.php)
    // y en formatCurrency de validate.js para mantener consistencia
    const symbols = {
        'EUR': '€',
        'USD': '$',
        'AUD': 'A$',
        '€': '€',
        '$USD': '$',
        '$AUD': 'A$'
    };
    
    // Determinar el símbolo correcto
    const symbol = symbols[selectedCurrency] || '€';

    // Actualizar símbolos de Low y High Season (específicos de mix.js)
    const lowSeasonSymbol = document.getElementById('lowSeasonRateCurrencySymbol');
    const highSeasonSymbol = document.getElementById('highSeasonRateCurrencySymbol');
    if (lowSeasonSymbol) lowSeasonSymbol.textContent = symbol;
    if (highSeasonSymbol) highSeasonSymbol.textContent = symbol;

    // Campos fijos comunes
    const relocationSymbol = document.getElementById('relocationCurrencySymbol');
    if (relocationSymbol) relocationSymbol.textContent = symbol;

    const securitySymbol = document.getElementById('securityCurrencySymbol');
    if (securitySymbol) securitySymbol.textContent = symbol;

    const apaSymbol = document.getElementById('apaCurrencySymbol');
    if (apaSymbol) apaSymbol.textContent = symbol;

    // Campos de Charter
    const charterRateGroups = document.querySelectorAll('.charter-rate-group .input-group-text');
    charterRateGroups.forEach(group => {
        group.textContent = symbol;
    });

    // Extras y Guest Fees
    // Aseguramos que todos los símbolos de moneda en extras y guest fees se actualicen
    const extraSymbols = document.querySelectorAll('.extra-currency-symbol');
    extraSymbols.forEach(symbolElem => {
        symbolElem.textContent = symbol;
    });
    
    // Actualizamos también los símbolos en los campos de guest fee que podrían haberse añadido dinámicamente
    const extraPerPersonGroups = document.querySelectorAll('.extra-per-person-group .extra-currency-symbol');
    extraPerPersonGroups.forEach(symbolElem => {
        symbolElem.textContent = symbol;
    });
}