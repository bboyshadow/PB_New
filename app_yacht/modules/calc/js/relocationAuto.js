/*
 * relocationAuto.js
 *
 * This module implements a mini-calculator for the "Relocation Fee".
 * When the "Relocation Fee auto" checkbox is enabled, configurable input
 * fields (distance, consumption, fuel price, crew, etc.) are shown.
 * The module uses default values from the global object `yachtInfoData` if present,
 * allowing the user to modify them. When clicking "Apply", it sends an AJAX
 * request to the `calculate_relocation` endpoint which returns the calculated fee
 * and injects it into the `relocationFee` field of the main calculator.
 */

document.addEventListener('DOMContentLoaded', () => {
    const autoCheckbox = document.getElementById('relocationAutoCheck');
    const autoContainer = document.getElementById('relocationAutoContainer');

    if (autoCheckbox && autoContainer) {
        // Show or hide the calculator based on the checkbox state
        autoCheckbox.addEventListener('change', () => {
            autoContainer.style.display = autoCheckbox.checked ? 'block' : 'none';
            if (autoCheckbox.checked) {
                prefillRelocationFields();
                // Configure required fields that do not need a checkbox
                setupRequiredFields();
            }
        });
    }

    // Handle click on the apply button
    const applyButton = document.getElementById('applyRelocationButton');
    if (applyButton) {
        applyButton.addEventListener('click', () => {
            calculateRelocation();
        });
    }

    // Auto-calculate hours when distance or speed changes
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
    
    // Configure required and optional fields on page load
    if (document.getElementById('relocationAutoContainer')) {
        setupRequiredFields();
    }

    // Prefill speed and consumption values if yacht data exists
    prefillRelocationFields();
 });

/**
 * Configures required (no checkbox) and optional (with checkbox)
 * fields according to client requirements.
 */
function setupRequiredFields() {
    // Required fields (always visible, no checkbox)
    const requiredFields = [
        { fieldId: 'reloc-distance',           checkboxId: 'reloc-distance-check' },
        { fieldId: 'reloc-cruising-speed',    checkboxId: 'reloc-speed-check' },
        { fieldId: 'reloc-hours',             checkboxId: 'reloc-hours-check' },
        { fieldId: 'reloc-fuel-consumption',  checkboxId: 'reloc-fuel-consumption-check' },
        { fieldId: 'reloc-fuel-price',        checkboxId: 'reloc-fuel-price-check' }
    ];
    
    // Optional fields (with checkbox)
    const optionalFields = [
        'reloc-crew-count',
        'reloc-crew-wage',
        'reloc-port-fees',
        'reloc-extra'
    ];
    
    // Setup required fields (always visible)
    requiredFields.forEach(({ fieldId, checkboxId }) => {
        const field = document.getElementById(fieldId);
        const checkbox = document.getElementById(checkboxId);
        
        if (field && checkbox) {
            // Make checkbox invisible (hide only the control, keep the text label)
            checkbox.style.display = 'none';
            // Mark checkbox as selected to include in calculations
            checkbox.checked = true;
            // Show input field
            field.style.display = 'block';
        }
    });
    
    // Setup optional fields (require checkbox)
    optionalFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const checkbox = document.getElementById(fieldId + '-check');
        
        if (field && checkbox) {
            // Ensure the field is hidden initially
            field.style.display = 'none';
            
            // Show or hide field based on checkbox state
            checkbox.addEventListener('change', () => {
                field.style.display = checkbox.checked ? 'block' : 'none';
            });
        }
    });
}

/**
 * Prefills values in the calculator fields using information available
 * in `window.yachtInfoData`. If not present, they are left blank.
 */
function prefillRelocationFields() {
    // Add a log for debugging
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
    
    // Log values for debugging
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
    
    // Log values after filling
    (window.AppYacht?.log || console.log)('Field values after filling:',
        'fuelConsumption:', fuelConsumption?.value,
        'cruisingSpeed:', cruisingSpeed?.value,
        'crewCount:', crewCount?.value);
}

/**
 * Collects the values of the selected fields and performs an AJAX request
 * to the server to calculate the Relocation Fee. The result is inserted
 * into the `relocationFee` field of the main calculator.
 */
function calculateRelocation() {
    // Create object with selected data
    const params = {};
    // Include only fields checked by the user
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
    // Currency (to format the response)
    const currency = document.getElementById('currency')?.value || '€';
    params.currency = currency;
    // Add security nonce if available
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
            alert(result.data.error || 'Error calculating relocation fee');
        }
    })
    .catch(() => alert('Connection error while calculating relocation fee'));
}

