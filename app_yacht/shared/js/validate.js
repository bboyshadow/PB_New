/**
 * FILE shared/js/validate.js
 * Client-side validation and formatting for the app_yacht application
 */

/**
 * Validates required fields in the charter form.
 * @param {boolean} [isApplyMix=false] - Whether to validate only Mixed Seasons fields.
 * @returns {boolean} - True if all required fields are valid, false otherwise.
 */
function validateFields(isApplyMix = false) {
    const form = document.getElementById('charterForm');
    if (!form) {
        return false;
    }

    const isFromCalculateButton = document.activeElement?.id === 'calculateButton';
    let requiredFields = isApplyMix
        ? form.querySelectorAll('#currency[required], #lowSeasonNights[required], #lowSeasonRate[required], #highSeasonNights[required], #highSeasonRate[required]')
        : isFromCalculateButton
            ? Array.from(form.querySelectorAll('[required]')).filter(field => field.id !== 'yacht-url')
            : form.querySelectorAll('[required]');

    let isValid = true;

    requiredFields.forEach(field => {
        if (isApplyMix || isElementVisible(field)) {
            let fieldValid = true;

            if (field.tagName.toLowerCase() === 'select') {
                if (!field.value || field.value === "" || field.value === field.querySelector('option[disabled][selected]')?.value) {
                    fieldValid = false;
                }
            } else if (field.tagName.toLowerCase() === 'input' || field.tagName.toLowerCase() === 'textarea') {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    const checked = form.querySelectorAll(`input[name="${field.name}"]:checked`);
                    if (checked.length === 0) {
                        fieldValid = false;
                    }
                } else {
                    if (!field.value || !field.value.trim()) {
                        fieldValid = false;
                    }
                    if (field.type === 'number' && isNaN(parseFloat(field.value))) {
                        fieldValid = false;
                    }
                }
            }

            if (!fieldValid) {
                markFieldAsError(field);
                isValid = false;
            } else {
                unmarkFieldAsError(field);
            }
        }
    });

    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.textContent = isValid ? '' : 'Please complete all required fields.';
        errorMessage.style.display = isValid ? 'none' : 'block';
    }

    return isValid;
}

/**
 * Checks if an element is visible on the page.
 * @param {HTMLElement} el - The element to check.
 * @returns {boolean} - True if the element is visible, false otherwise.
 */
function isElementVisible(el) {
    while (el) {
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
            return false;
        }
        el = el.parentElement;
    }
    return true;
}

/**
 * Marks a field as having an error.
 * @param {HTMLElement} field - The field to mark.
 */
function markFieldAsError(field) {
    field.classList.add('error');
    const container = field.closest('.form-group, .input-group, .col-md-3, .col-4, .col-6, .col-12');
    if (container) {
        container.classList.add('error-container');
    }
}

/**
 * Removes error marking from a field.
 * @param {HTMLElement} field - The field to unmark.
 */
function unmarkFieldAsError(field) {
    field.classList.remove('error');
    const container = field.closest('.form-group, .input-group, .col-md-3, .col-4, .col-6, .col-12');
    if (container) {
        container.classList.remove('error-container');
    }
}

/**
 * Formats number input with commas and controls decimals.
 * @param {HTMLInputElement} input - The input field to format.
 */
function formatNumber(input) {
    if (input.type === 'number') {
        return;
    }

    const allowDecimals = !['guests', 'nights'].includes(input.name);
    let value = input.value.replace(/[^0-9.]/g, '');

    let integerPart = value;
    let decimalPart = '';
    if (value.includes('.')) {
        const parts = value.split('.');
        integerPart = parts[0];
        decimalPart = parts.length > 1 ? '.' + parts.slice(1).join('') : '';
        if (decimalPart.split('.').length > 2) {
            const decParts = decimalPart.substring(1).split('.');
            decimalPart = '.' + decParts[0];
        }
    }

    if (!allowDecimals) {
        decimalPart = '';
    }

    const formattedIntegerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    input.value = formattedIntegerPart + decimalPart;
}

/**
 * Formats a value as currency.
 * @param {number} value - The value to format.
 * @param {string} currency - The currency code (e.g., 'EUR', 'USD').
 * @param {boolean} [round=false] - Whether to round to no decimals.
 * @returns {string} - The formatted currency string.
 */
function formatCurrency(value, currency, round = false) {
    const symbols = {
        'EUR': '€',
        'USD': '$',
        'AUD': 'A$',
        '€': '€',
        '$USD': '$',
        '$AUD': 'A$'
    };
    
    const symbol = symbols[currency] || '€';
    const formatOptions = {
        style: 'currency',
        currency: currency.replace('$', ''),
        minimumFractionDigits: round ? 0 : 2,
        maximumFractionDigits: round ? 0 : 2,
        currencyDisplay: 'symbol'
    };
    
    return new Intl.NumberFormat('en-US', formatOptions).format(round ? Math.ceil(value) : value);
}

// Función para validar campos obligatorios con warnings preventivos
function validateFieldsWithWarnings() {
    // Limpiar warnings previos
    document.querySelectorAll('.warning-text').forEach(el => el.remove());
    
    let warnings = [];
    
    // Validar Mixed Seasons
    const enableMixedSeasons = document.getElementById('enableMixedSeasons');
    if (enableMixedSeasons && enableMixedSeasons.checked) {
        const lowSeasonNights = document.getElementById('lowSeasonNights');
        const highSeasonNights = document.getElementById('highSeasonNights');
        const mixNights = document.getElementById('mix-nights');
        
        const lowValue = parseInt(lowSeasonNights?.value) || 0;
        const highValue = parseInt(highSeasonNights?.value) || 0;
        const mixValue = parseInt(mixNights?.value) || 0;
        
        if (mixValue <= 0) {
            warnings.push('Mixed Seasons: Mix Nights must be greater than 0');
            addFieldWarning(mixNights, 'Must be greater than 0');
        }
        
        if (lowValue <= 0) {
            warnings.push('Mixed Seasons: Low Season Nights must be greater than 0');
            addFieldWarning(lowSeasonNights, 'Must be greater than 0');
        }
        
        if (highValue <= 0) {
            warnings.push('Mixed Seasons: High Season Nights must be greater than 0');
            addFieldWarning(highSeasonNights, 'Must be greater than 0');
        }
        
        if (mixValue > 0 && (lowValue + highValue !== mixValue)) {
            warnings.push('Mixed Seasons: The sum of Low + High Season must equal Mix Nights');
            addFieldWarning(lowSeasonNights, 'Sum does not match Mix Nights');
            addFieldWarning(highSeasonNights, 'Sum does not match Mix Nights');
        }
    }
    
    // Validar VAT Mix
    const vatRateMix = document.getElementById('vatRateMix');
    if (vatRateMix && vatRateMix.checked) {
        const vatCountryItems = document.querySelectorAll('.country-vat-item-wrapper');
        let hasValidVatEntry = false;
        
        vatCountryItems.forEach(item => {
            const countryName = item.querySelector('input[name="vatCountryName[]"]');
            const vatRate = item.querySelector('input[name="vatRate[]"]');
            const nights = item.querySelector('input[name="vatNights[]"]');
            
            const country = countryName?.value.trim() || '';
            const rate = parseFloat(vatRate?.value) || 0;
            const nightsValue = parseInt(nights?.value) || 0;
            
            if (country && rate > 0 && nightsValue > 0) {
                hasValidVatEntry = true;
            }
            
            if (country && rate <= 0) {
                addFieldWarning(vatRate, 'Must be greater than 0%');
            }
            
            if (country && nightsValue <= 0) {
                addFieldWarning(nights, 'Must be greater than 0');
            }
            
            if (!country && (rate > 0 || nightsValue > 0)) {
                addFieldWarning(countryName, 'Country name is required');
            }
        });
        
        if (!hasValidVatEntry) {
            warnings.push('VAT Mix: You must add at least one country with valid data');
        }
    }
    
    // Mostrar warnings si existen
    if (warnings.length > 0) {
        showWarnings(warnings);
    }
    
    // Retornar si hay warnings críticos (opcional - no bloquear por ahora)
    return warnings.length === 0;
}

function addFieldWarning(field, message) {
    if (!field) return;
    
    // Remover warning previo en este campo
    const existingWarning = field.parentNode.querySelector('.warning-text');
    if (existingWarning) {
        existingWarning.remove();
    }
    
    // Crear nuevo elemento de warning
    const warningEl = document.createElement('small');
    warningEl.className = 'warning-text text-warning d-block';
    warningEl.textContent = '⚠ ' + message;
    
    // Insertar después del campo
    field.parentNode.insertBefore(warningEl, field.nextSibling);
    
    // Añadir clase visual al campo
    field.classList.add('border-warning');
    
    // Remover la clase después de 5 segundos
    setTimeout(() => {
        field.classList.remove('border-warning');
        if (warningEl.parentNode) {
            warningEl.remove();
        }
    }, 5000);
}

function showWarnings(warnings) {
    // Intentar usar la función de UI si está disponible
    try {
        if (window.AppYacht?.ui?.notifyWarning) {
            warnings.forEach(warning => {
                window.AppYacht.ui.notifyWarning(warning);
            });
            return;
        }
    } catch (e) {
        console.warn('AppYacht.ui.notifyWarning not available:', e);
    }
    
    // Fallback: mostrar en el área de mensajes existente
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        const warningDiv = document.createElement('div');
        warningDiv.className = 'alert alert-warning alert-dismissible fade show mt-2';
        warningDiv.innerHTML = `
            <strong>⚠ Warnings:</strong>
            <ul class="mb-0 mt-1">
                ${warnings.map(w => `<li>${w}</li>`).join('')}
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insertar después del errorMessage
        errorDiv.parentNode.insertBefore(warningDiv, errorDiv.nextSibling);
        
        // Auto-hide después de 7 segundos
        setTimeout(() => {
            if (warningDiv.parentNode) {
                warningDiv.remove();
            }
        }, 7000);
    }
}

// -----------------------------
// Real-time validation helpers
// -----------------------------
function debounce(fn, delay = 300) {
    let timerId;
    return function(...args) {
        if (timerId) clearTimeout(timerId);
        timerId = setTimeout(() => fn.apply(this, args), delay);
    };
}

function validateSingleField(field) {
    if (!field || !isElementVisible(field)) return;

    let fieldValid = true;
    const tag = field.tagName.toLowerCase();

    if (tag === 'select') {
        if (!field.value || field.value === "" || field.value === field.querySelector('option[disabled][selected]')?.value) {
            fieldValid = false;
        }
    } else if (tag === 'input' || tag === 'textarea') {
        const type = (field.getAttribute('type') || '').toLowerCase();
        if (type === 'checkbox' || type === 'radio') {
            // Solo validar checkboxes que son requeridos
            if (field.hasAttribute('required')) {
                const form = field.form || document.getElementById('charterForm');
                const checked = form ? form.querySelectorAll(`input[name="${field.name}"]:checked`) : [];
                if (!checked || checked.length === 0) {
                    fieldValid = false;
                }
            } else {
                // Los checkboxes opcionales siempre son válidos
                fieldValid = true;
            }
        } else {
            const val = field.value != null ? String(field.value).trim() : '';
            if (!val) {
                fieldValid = false;
            }
            if (type === 'number') {
                const num = parseFloat(field.value);
                if (isNaN(num)) fieldValid = false;
            }
        }
    }

    if (!fieldValid) {
        markFieldAsError(field);
    } else {
        unmarkFieldAsError(field);
    }
}

function attachRealTimeValidation() {
    const form = document.getElementById('charterForm');
    if (!form) return;

    const triggerWarnings = debounce(() => {
        try { validateFieldsWithWarnings(); } catch (e) { /* noop */ }
    }, 350);

    const maybeValidateTarget = (target) => {
        if (!target || !target.tagName) return;
        // Validar sólo campos del formulario
        if (!form.contains(target)) return;

        // Actualizar estado del campo requerido si aplica
        if (target.hasAttribute('required') || ['INPUT','SELECT','TEXTAREA'].includes(target.tagName)) {
            validateSingleField(target);
        }

        // Disparar warnings no bloqueantes (debounced)
        triggerWarnings();
    };

    // Delegación de eventos para cubrir campos dinámicos (VAT Mix)
    form.addEventListener('input', (e) => {
        const target = e.target;
        maybeValidateTarget(target);
    });

    form.addEventListener('change', (e) => {
        const target = e.target;
        maybeValidateTarget(target);
    });

    // Cambios en toggles que habilitan secciones (Mixed Seasons / VAT Mix)
    const toggles = ['enableMixedSeasons', 'vatRateMix'];
    toggles.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => {
                try { validateFieldsWithWarnings(); } catch (e) { /* noop */ }
            });
        }
    });
}

// Inicializar al cargar el documento
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachRealTimeValidation);
} else {
    attachRealTimeValidation();
}
