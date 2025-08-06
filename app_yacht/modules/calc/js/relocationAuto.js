/*
 * relocationAuto.js
 *
 * Este módulo implementa una minicalculadora para la "Relocation Fee".
 * Al activar la casilla "Relocation Fee auto", se muestran campos de entrada
 * configurables (distancia, consumo, precio combustible, tripulación, etc.).
 * El módulo toma valores predeterminados del objeto global `yachtInfoData` si existe,
 * permitiendo al usuario modificarlos. Al pulsar "Aplicar", se envía una petición
 * AJAX al endpoint `calculate_relocation` que devuelve la tarifa calculada y
 * la inyecta en el campo `relocationFee` de la calculadora principal.
 */

document.addEventListener('DOMContentLoaded', () => {
    const autoCheckbox = document.getElementById('relocationAutoCheck');
    const autoContainer = document.getElementById('relocationAutoContainer');

    if (autoCheckbox && autoContainer) {
        // Mostrar u ocultar la calculadora según el estado del checkbox
        autoCheckbox.addEventListener('change', () => {
            autoContainer.style.display = autoCheckbox.checked ? 'block' : 'none';
            // Al mostrar el contenedor, precargar valores si están disponibles
            if (autoCheckbox.checked) {
                prefillRelocationFields();
            }
        });
    }

    // Manejar clic en el botón de aplicar
    const applyButton = document.getElementById('applyRelocationButton');
    if (applyButton) {
        applyButton.addEventListener('click', () => {
            calculateRelocation();
        });
    }
});

/**
 * Precarga valores en los campos de la calculadora usando la información
 * disponible en `window.yachtInfoData`. Si no existe, se dejan vacíos.
 */
function prefillRelocationFields() {
    const data = window.yachtInfoData || {};
    const fuelConsumption = document.getElementById('reloc-fuel-consumption');
    const cruisingSpeed   = document.getElementById('reloc-cruising-speed');
    const crewCount       = document.getElementById('reloc-crew-count');
    if (fuelConsumption && data.fuelConsumption && fuelConsumption.value === '') {
        fuelConsumption.value = data.fuelConsumption;
    }
    if (cruisingSpeed && data.cruisingSpeed && cruisingSpeed.value === '') {
        cruisingSpeed.value = data.cruisingSpeed;
    }
    if (crewCount && data.crew && crewCount.value === '') {
        crewCount.value = data.crew;
    }
}

/**
 * Recolecta los valores de los campos seleccionados y realiza una solicitud
 * AJAX al servidor para calcular la Relocation Fee. El resultado se inserta
 * en el campo `relocationFee` de la calculadora principal.
 */
function calculateRelocation() {
    // Crear objeto con los datos seleccionados
    const params = {};
    // Solo incluir campos marcados por el usuario
    const fields = [
        { checkboxId: 'reloc-distance-check', inputId: 'reloc-distance', name: 'distance' },
        { checkboxId: 'reloc-hours-check',    inputId: 'reloc-hours',    name: 'hours' },
        { checkboxId: 'reloc-fuel-consumption-check', inputId: 'reloc-fuel-consumption', name: 'fuelConsumption' },
        { checkboxId: 'reloc-fuel-price-check', inputId: 'reloc-fuel-price', name: 'fuelPrice' },
        { checkboxId: 'reloc-crew-count-check', inputId: 'reloc-crew-count', name: 'crewCount' },
        { checkboxId: 'reloc-crew-wage-check', inputId: 'reloc-crew-wage', name: 'crewWage' },
        { checkboxId: 'reloc-port-fees-check', inputId: 'reloc-port-fees', name: 'portFees' },
        { checkboxId: 'reloc-extra-check', inputId: 'reloc-extra', name: 'extraCosts' }
    ];
    fields.forEach(field => {
        const chk = document.getElementById(field.checkboxId);
        const input = document.getElementById(field.inputId);
        if (chk && chk.checked && input) {
            params[field.name] = input.value;
        }
    });
    // Moneda (para formatear la respuesta)
    const currency = document.getElementById('currency')?.value || '€';
    params.currency = currency;

    // Datos de AJAX
    const ajaxData = window.ajaxRelocationData;
    if (!ajaxData || !ajaxData.ajaxurl) {
        alert('AJAX configuration missing');
        return;
    }

    // Añadir nonce de seguridad si existe
    params.nonce = ajaxData.nonce || '';

    fetch(ajaxData.ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(Object.assign({ action: 'calculate_relocation' }, params))
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const fee = result.data.fee;
            const relocationField = document.getElementById('relocationFee');
            if (relocationField) {
                relocationField.value = fee;
                relocationField.dispatchEvent(new Event('input'));
            }
            const output = document.getElementById('relocation-auto-result');
            if (output) output.textContent = fee;
        } else {
            alert(result.data.error || 'Error calculando la relocation fee');
        }
    })
    .catch(() => alert('Error de conexión al calcular la relocation fee'));
}

