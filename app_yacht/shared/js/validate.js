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
            ? Array.from(form.querySelectorAll('[required]')).filter(field => field.id !== 'yachtUrl')
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
