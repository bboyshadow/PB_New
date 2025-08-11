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
            if (autoCheckbox.checked) {
                prefillRelocationFields();
                // Configurar campos requeridos que no necesitan checkbox
                setupRequiredFields();
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

    // Calcular horas automáticamente cuando cambian distancia o velocidad
    const distanceInput = document.getElementById('reloc-distance');
    const speedInput    = document.getElementById('reloc-cruising-speed');
    const hoursInput    = document.getElementById('reloc-hours');
    if (distanceInput && speedInput && hoursInput) {
        const updateHours = () => {
            const dist = parseFloat(distanceInput.value);
            const spd  = parseFloat(speedInput.value);
            if (isFinite(dist) && dist > 0 && isFinite(spd) && spd > 0) {
                const hrs = dist / spd;
                hoursInput.value = hrs.toFixed(2);
            }
        };
        distanceInput.addEventListener('input', updateHours);
        speedInput.addEventListener('input', updateHours);
    }
    
    // Configurar campos obligatorios y opcionales al cargar la página
    if (document.getElementById('relocationAutoContainer')) {
        setupRequiredFields();
    }

    // Precargar valores de velocidad y consumo si existen datos del yate
    prefillRelocationFields();
 });

/**
 * Configura los campos obligatorios (sin checkbox) y opcionales (con checkbox)
 * según los requisitos del cliente.
 */
function setupRequiredFields() {
    // Campos requeridos (siempre visibles, sin checkbox)
    const requiredFields = [
        { fieldId: 'reloc-distance',           checkboxId: 'reloc-distance-check' },
        { fieldId: 'reloc-cruising-speed',    checkboxId: 'reloc-speed-check' },
        { fieldId: 'reloc-hours',             checkboxId: 'reloc-hours-check' },
        { fieldId: 'reloc-fuel-consumption',  checkboxId: 'reloc-fuel-consumption-check' },
        { fieldId: 'reloc-fuel-price',        checkboxId: 'reloc-fuel-price-check' }
    ];
    
    // Campos opcionales (con checkbox)
    const optionalFields = [
        'reloc-crew-count',
        'reloc-crew-wage',
        'reloc-port-fees',
        'reloc-extra'
    ];
    
    // Configurar campos requeridos (siempre visibles)
    requiredFields.forEach(({ fieldId, checkboxId }) => {
        const field = document.getElementById(fieldId);
        const checkbox = document.getElementById(checkboxId);
        
        if (field && checkbox) {
            // Hacer checkbox invisible (se oculta solo el control, se mantiene la etiqueta de texto)
            checkbox.style.display = 'none';
            // Marcar checkbox como seleccionado para incluir en cálculos
            checkbox.checked = true;
            // Mostrar campo de entrada
            field.style.display = 'block';
        }
    });
    
    // Configurar campos opcionales (necesitan checkbox)
    optionalFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const checkbox = document.getElementById(fieldId + '-check');
        
        if (field && checkbox) {
            // Asegurar que el campo esté oculto inicialmente
            field.style.display = 'none';
            
            // Mostrar u ocultar campo según estado del checkbox
            checkbox.addEventListener('change', () => {
                field.style.display = checkbox.checked ? 'block' : 'none';
            });
        }
    });
}

/**
 * Precarga valores en los campos de la calculadora usando la información
 * disponible en `window.yachtInfoData`. Si no existe, se dejan vacíos.
 */
function prefillRelocationFields() {
    // Añadir un log para depuración
    (window.AppYacht?.log || console.log)('Executing prefillRelocationFields. yachtInfoData:', window.yachtInfoData);

    const data = window.yachtInfoData || {};

    const extractNumber = (val) => {
        if (typeof val === 'number') return val;
        if (typeof val === 'string') {
            if (val === '--') return '';
            const match = val.match(/([0-9]+(?:[.,][0-9]+)?)/); 
            if (match) return match[1].replace(',', '.');
        }
        return '';
    };

    const fuelConsumption = document.getElementById('reloc-fuel-consumption');
    const cruisingSpeed   = document.getElementById('reloc-cruising-speed');
    const crewCount       = document.getElementById('reloc-crew-count');
    
    // Log valores para depuración
    (window.AppYacht?.log || console.log)('Field values before filling:',
        'fuelConsumption:', fuelConsumption?.value,
        'cruisingSpeed:', cruisingSpeed?.value,
        'crewCount:', crewCount?.value);
    (window.AppYacht?.log || console.log)('Data to use:',
        'fuelConsumption:', data.fuelConsumption,
        'cruisingSpeed:', data.cruisingSpeed,
        'crew:', data.crew);
    
    if (fuelConsumption && data.fuelConsumption) {
        fuelConsumption.value = extractNumber(data.fuelConsumption);
    }
    if (cruisingSpeed && data.cruisingSpeed) {
        cruisingSpeed.value = extractNumber(data.cruisingSpeed);
    }
    if (crewCount && data.crew) {
        crewCount.value = extractNumber(data.crew);
    }
    
    // Log valores después de llenar
    (window.AppYacht?.log || console.log)('Field values after filling:',
        'fuelConsumption:', fuelConsumption?.value,
        'cruisingSpeed:', cruisingSpeed?.value,
        'crewCount:', crewCount?.value);
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
        { checkboxId: 'reloc-speed-check',    inputId: 'reloc-cruising-speed', name: 'speed' },
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
    // Añadir nonce de seguridad si existe
    params.nonce = ajaxRelocationData?.nonce || '';

    fetch(ajaxRelocationData.ajaxurl, {
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

