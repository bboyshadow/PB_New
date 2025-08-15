// ARCHIVO modules\calc\js\calculate.js
/**
 * Recolecta datos de la calculadora (Guests, Nights, Base Rate, Extras, etc.)
 * para enviarlos a WP (calculate.php). Luego muestra el resultado en #result.
 * También maneja el cambio de texto en #calculateButton a "Recalculate"
 * y el efecto en #copyButton.
 */

document.addEventListener('DOMContentLoaded', () => {
    const calculateButton = document.getElementById('calculateButton');
    const copyButton      = document.getElementById('copyButton');

    if (calculateButton) {
        calculateButton.addEventListener('click', handleCalculateButtonClick);
    }

    if (copyButton) {
        copyButton.addEventListener('click', copyToClipboard);
    }

    // Mostrar/Ocultar campos de Mixed Seasons
    document.getElementById('enableMixedSeasons').addEventListener('change', function() {
        const mixedSeasonsFields = document.getElementById('mixedSeasonsFields');
        if (mixedSeasonsFields) {
            if (this.checked) {
                mixedSeasonsFields.style.display = 'block';
            } else {
                mixedSeasonsFields.style.display = 'none';
            }
        }
    });
});

/**
 * Maneja el clic en el botón de cálculo principal.
 * Valida los datos del formulario, recolecta todos los campos de entrada,
 * envía una solicitud AJAX al endpoint `calculate_charter` de WordPress,
 * y muestra los resultados en la UI.
 * 
 * @async
 * @function handleCalculateButtonClick
 * @returns {Promise<void>} Promesa que se resuelve cuando el cálculo se completa
 * 
 * @description
 * - Valida campos obligatorios usando validateFields()
 * - Recolecta datos: currency, VAT, APA, relocation, security, extras, guests, nights, base rates
 * - Procesa extras dinámicos y campos de fecha
 * - Maneja caché de resultados en sessionStorage
 * - Actualiza UI con loading states y notificaciones
 * - Publica eventos en eventBus si está disponible
 * 
 * @throws {Error} Si falla la validación, la solicitud de red o el procesamiento de respuesta
 */
async function handleCalculateButtonClick() {
    try {
        // Validación
        if (typeof validateFields === 'function') {
            const isValid = validateFields();
            try { typeof validateFieldsWithWarnings === 'function' && validateFieldsWithWarnings(); } catch (e) {}
            if (!isValid) {
                return; // Detener la ejecución si la validación falla
            }
        }

        // Crear FormData
        const formData = new FormData();
        formData.append('action', 'calculate_charter');
        formData.append('nonce', ajaxCalculatorData?.nonce || '');

        // Recolectar currency
        const currency = document.getElementById('currency')?.value || '';
        formData.append('currency', currency);
        
        // Recolectar preferencias de elementos a ocultar
        const hideVAT = document.getElementById('hideVAT')?.checked || false;
        const hideAPA = document.getElementById('hideAPA')?.checked || false;
        const hideRelocation = document.getElementById('hideRelocation')?.checked || false;
        const hideSecurity = document.getElementById('hideSecurity')?.checked || false;
        const hideExtras = document.getElementById('hideExtras')?.checked || false;
        const hideGratuity = document.getElementById('hideGratuity')?.checked || false;
        
        formData.append('hideVAT', hideVAT ? '1' : '0');
        formData.append('hideAPA', hideAPA ? '1' : '0');
        formData.append('hideRelocation', hideRelocation ? '1' : '0');
        formData.append('hideSecurity', hideSecurity ? '1' : '0');
        formData.append('hideExtras', hideExtras ? '1' : '0');
        formData.append('hideGratuity', hideGratuity ? '1' : '0');

        // VAT, APA, etc.
        const vatCheckbox = document.getElementById('vatCheck');
        if (vatCheckbox?.checked) {
            formData.append('vatRate', document.getElementById('vatRate')?.value || '');
        }

        const apaCheckbox = document.getElementById('apaCheck');
        if (apaCheckbox?.checked) {
            formData.append('apaAmount', document.getElementById('apaAmount')?.value || '');
        }

        const apaPercentageCheckbox = document.getElementById('apaPercentageCheck');
        if (apaPercentageCheckbox?.checked) {
            formData.append('apaPercentage', document.getElementById('apaPercentage')?.value || '');
        }

        const relocationCheckbox = document.getElementById('relocationCheck');
        if (relocationCheckbox?.checked) {
            formData.append('relocationFee', document.getElementById('relocationFee')?.value || '');
        }

        const securityCheckbox = document.getElementById('securityCheck');
        if (securityCheckbox?.checked) {
            formData.append('securityFee', document.getElementById('securityFee')?.value || '');
        }

        // One Day Charter
        const enableOneDayCharter = document.getElementById('enableOneDayCharter');
        const isOneDayActive = (enableOneDayCharter && enableOneDayCharter.checked) ? '1' : '0';
        formData.append('enableOneDayCharter', isOneDayActive);

        // Add + Expenses to Base Charter Rate
        const enableExpenses = document.getElementById('enableExpenses');
        const isExpensesActive = (enableExpenses && enableExpenses.checked) ? '1' : '0';
        formData.append('enableExpenses', isExpensesActive);

        // Mixed Seasons
        const enableMixedSeasons = document.getElementById('enableMixedSeasons');
        const isMixedActive = (enableMixedSeasons && enableMixedSeasons.checked) ? '1' : '0';
        formData.append('enableMixedSeasons', isMixedActive);

        // Agregar campos de Mixed Seasons si está activo
        if (isMixedActive === '1') {
            const lowSeasonNights = document.getElementById('lowSeasonNights')?.value || '';
            const lowSeasonRate = document.getElementById('lowSeasonRate')?.value || '';
            const highSeasonNights = document.getElementById('highSeasonNights')?.value || '';
            const highSeasonRate = document.getElementById('highSeasonRate')?.value || '';

            formData.append('lowSeasonNights', lowSeasonNights);
            formData.append('lowSeasonRate', lowSeasonRate);
            formData.append('highSeasonNights', highSeasonNights);
            formData.append('highSeasonRate', highSeasonRate);
        }

        // Mixed Taxes
const vatRateMixEnabled = document.getElementById('vatRateMix')?.checked;
formData.append('vatRateMix', vatRateMixEnabled ? '1' : '0');
if (vatRateMixEnabled) {
    const vatItems = document.querySelectorAll('.country-vat-item-wrapper');
    vatItems.forEach(item => {
        const country = item.querySelector('input[name^="vatCountryName"]')?.value || '';
        const nights = item.querySelector('input[name^="vatNights"]')?.value || '';
        const vatRate = item.querySelector('input[name^="vatRate"]')?.value || '';
        if (country && nights) {
            formData.append('vatCountryName[]', country);
            formData.append('vatNights[]', nights);
            formData.append('vatRate[]', vatRate);
        }
    });
}

        // Recolectar Charter Rates
        const charterRateGroups = document.querySelectorAll('.charter-rate-group');
        charterRateGroups.forEach((group, i) => {
            const guests = group.querySelector('input[name="guests"]')?.value || '';
            const nights = group.querySelector('input[name="nights"]')?.value || '';
            const hoursElem = group.querySelector('input[name="hours"]');
            let hours = '';

            // "One day Charter"
            if (isOneDayActive === '1' && hoursElem) {
                hours = hoursElem.value || '';
            }

            const baseRate = group.querySelector('input[name="baseRate"]')?.value || '';
            const discountContainer = group.querySelector('.discount-container');
            const discountActive = discountContainer && discountContainer.style.display !== 'none';
            
            // Solo obtener valores de descuento si el contenedor está visible
            let discountType = '';
            let discountAmount = '';
            
            if (discountActive) {
                discountType = group.querySelector('select[name="discountType"]')?.value || '';
                discountAmount = group.querySelector('input[name="discountAmount"]')?.value || '';
            }

            const promotionContainer = group.querySelector('.promotion-container');
            const promotionActive = promotionContainer && promotionContainer.style.display !== 'none';
            
            let promotionNights = '';
            
            if (promotionActive) {
                promotionNights = group.querySelector('input[name="promotionNights"]')?.value || '';
            }

            formData.append(`charterRates[${i}][guests]`, guests);
            formData.append(`charterRates[${i}][nights]`, nights);
            formData.append(`charterRates[${i}][hours]`, hours);
            formData.append(`charterRates[${i}][baseRate]`, baseRate);
            formData.append(`charterRates[${i}][discountType]`, discountType);
            formData.append(`charterRates[${i}][discountAmount]`, discountAmount);
            formData.append(`charterRates[${i}][discountActive]`, discountActive ? '1' : '0');
            formData.append(`charterRates[${i}][promotionNights]`, promotionNights);
            formData.append(`charterRates[${i}][promotionActive]`, promotionActive ? '1' : '0');
        });

        // Extras
        const extrasContainer = document.getElementById('extrasContainer');
        if (extrasContainer) {
            const extraGroups = extrasContainer.querySelectorAll('.extra-group');
            extraGroups.forEach((extra, i) => {
                const extraName = extra.querySelector('input[name="extraName"]')?.value || '';
                const extraCost = extra.querySelector('input[name="extraCost"]')?.value || '';
                formData.append(`extras[${i}][extraName]`, extraName);
                formData.append(`extras[${i}][extraCost]`, extraCost);
            });
            
            // Guest Fee
            const extraPerPersonGroups = extrasContainer.querySelectorAll('.extra-per-person-group');
            extraPerPersonGroups.forEach((extra, i) => {
                const extraName = extra.querySelector('input[name="extraPerPersonName"]')?.value || '';
                const extraTotal = extra.querySelector('input[name="extraPerPersonTotal"]')?.value || '';
                const guests = extra.querySelector('input[name="extraPerPersonGuests"]')?.value || '';
                const costPerPerson = extra.querySelector('input[name="extraPerPersonCost"]')?.value || '';
                
                // Añadimos el prefijo "Guest Fee:" para que sea fácilmente identificable en PHP
                const formattedName = `Guest Fee: ${extraName} (${guests} guests x ${currency} ${costPerPerson} pp)`;
                
                // Añadimos el guest fee como un extra normal para el cálculo final
                formData.append(`extras[${extraGroups.length + i}][extraName]`, formattedName);
                formData.append(`extras[${extraGroups.length + i}][extraCost]`, extraTotal);
            });
        }

        // === Cache básico por sesión ===
        let cacheKey = null;
        try {
            const obj = {};
            for (let [key, value] of formData.entries()) {
                obj[key] = value;
            }
            const json = JSON.stringify(obj, Object.keys(obj).sort());
            let hash = 0;
            for (let i = 0; i < json.length; i++) {
                const chr = json.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0;
            }
            cacheKey = `pb_calc_${Math.abs(hash).toString(16)}`;
        } catch (e) {
            (window.AppYacht?.warn || console.warn)('Could not generate cache key:', e);
        }

        const cacheMaxAge = (window.AppYacht?.config?.cacheMaxAgeMs) ?? (60 * 60 * 1000); // 1h por defecto
        const enableCache = (window.AppYacht?.config?.enableCache) ?? true;

        if (enableCache && cacheKey && typeof sessionStorage !== 'undefined') {
            try {
                const cachedStr = sessionStorage.getItem(cacheKey);
                if (cachedStr) {
                    const cached = JSON.parse(cachedStr);
                    if (cached && cached.timestamp && (Date.now() - cached.timestamp) <= cacheMaxAge) {
                        (window.AppYacht?.log || console.log)('Usando resultado cacheado (legacy) para', cacheKey);
                        displayCalculatorResult(cached.result);
                        const calculateBtn = document.getElementById('calculateButton');
                        if (calculateBtn) calculateBtn.textContent = 'Recalculate';
                        if (window.eventBus) window.eventBus.publish('calculator:success', cached.result);
                        return; // Evitar petición si devolvemos cache
                    }
                }
            } catch (e) {
                (window.AppYacht?.warn || console.warn)('Failed to access session cache (legacy):', e);
            }
        }

        // Enviar AJAX usando async/await
        // Loading state start (feature-flagged)
        try { window.AppYacht?.ui?.setLoading?.(true); } catch (e) {}
        const response = await fetch(ajaxCalculatorData.ajaxurl, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            // Enhanced error handling (feature-flagged)
            try { window.AppYacht?.ui?.notifyError?.('Server error during calculation'); } catch (e) {}
            throw new Error('Server response error (calculate.php).');
        }
        
        const result = await response.json();
        // Loading state end (feature-flagged)
        try { window.AppYacht?.ui?.setLoading?.(false); } catch (e) {}
        
        if (result.success) {
            // Guardar en caché si aplica
            if (enableCache && cacheKey && typeof sessionStorage !== 'undefined') {
                try {
                    sessionStorage.setItem(cacheKey, JSON.stringify({ result: result.data, timestamp: Date.now() }));
                } catch (e) {
                    (window.AppYacht?.warn || console.warn)('Could not save result to cache (legacy):', e);
                }
            }

            displayCalculatorResult(result.data);
            const calculateBtn = document.getElementById('calculateButton');
            if (calculateBtn) calculateBtn.textContent = 'Recalculate';
            
            // Notificación de éxito y limpieza de errores
            try { window.AppYacht?.ui?.notifySuccess?.('Calculation completed'); } catch (e) {}
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage) { errorMessage.style.display = 'none'; errorMessage.textContent = ''; }
            
            // Publicar evento si hay eventBus disponible
            if (window.eventBus) {
                window.eventBus.publish('calculator:success', result.data);
            }
        } else {
            (window.AppYacht?.error || console.error)('Calculation error:', result.data);
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage) {
                errorMessage.textContent = 'Calculation error.';
                errorMessage.style.display = 'block';
            }
            
            // Unified error notification
            try { window.AppYacht?.ui?.notifyError?.('Calculation error'); } catch (e) {}
            
            // Publicar evento si hay eventBus disponible
            if (window.eventBus) {
                window.eventBus.publish('calculator:error', { message: 'Calculation error' });
            }
        }
    } catch (err) {
        (window.AppYacht?.error || console.error)('Request error:', err);
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            errorMessage.textContent = 'General error.';
            errorMessage.style.display = 'block';
        }
        
        // Notificación de error unificada
        try { window.AppYacht?.ui?.notifyError?.('Error during calculation: ' + err.message); } catch (e) {}
        
        // Publicar evento si hay eventBus disponible
        if (window.eventBus) {
            window.eventBus.publish('calculator:error', { message: err.message });
        }
        
        // Asegurar desactivación del estado de carga
        try { window.AppYacht?.ui?.setLoading?.(false); } catch (e) {}
    }
}


/**
 * Muestra los resultados en #result, permitiendo múltiples bloques incluso si son idénticos.
 */
/**
 * Muestra los resultados del cálculo en la interfaz de usuario.
 * Incluye resultados de Mixed Seasons si están disponibles.
 * 
 * @function displayCalculatorResult
 * @param {Array|Object} data - Datos del resultado del cálculo
 * @returns {void}
 * 
 * @description
 * - Procesa los datos del resultado (array o objeto único)
 * - Integra resultados de Mixed Seasons si están activos
 * - Renderiza los resultados en divs responsivos
 * - Actualiza el estado de los botones (Calculate → Recalculate)
 * - Habilita el botón de copia
 */
function displayCalculatorResult(data) {
    let finalTextArray = [];

    // Simplemente asignar los datos recibidos al array final
    if (Array.isArray(data)) {
        finalTextArray = data;
    } else {
        finalTextArray.push(data);
    }

    // Obtener los valores de Mixed Seasons si están disponibles
    const lowMixedResult = document.getElementById('lowmixedResult')?.textContent || '';
    const highMixedResult = document.getElementById('highmixedResult')?.textContent || '';

    // Si Mixed Seasons está activo, agregar las líneas de Low y High Season al inicio
    if (lowMixedResult && highMixedResult) {
        const mixHeader = `${lowMixedResult}\n${highMixedResult}\n`;
        finalTextArray[0] = mixHeader + finalTextArray[0];
    }

    // Insertar en el <div id="result"> con <div class="col-12 col-md-6 col-lg-4 p-2">
    const resultDiv = document.getElementById('result');
    if (resultDiv) {
        resultDiv.innerHTML = ''; // Limpiar resultados anteriores
        finalTextArray.forEach(text => {
            const resultChildDiv = document.createElement('div');
            resultChildDiv.classList.add('col-12', 'col-md-6', 'col-lg-4', 'p-2');
            // Reemplazar saltos de línea por <br> para mantener el formato
            resultChildDiv.innerHTML = text.replace(/\n/g, '<br>');
            resultDiv.appendChild(resultChildDiv);
        });
    }

    // Actualizar botones
    const calculateBtn = document.getElementById('calculateButton');
    if (calculateBtn) calculateBtn.textContent = 'Recalculate';

    const copyBtn = document.getElementById('copyButton');
    if (copyBtn) {
        copyBtn.disabled = false;
        copyBtn.style.display = 'block';
    }
}

/**
 * Copia el contenido de los resultados al portapapeles del usuario.
 * Incluye fallback para navegadores sin Clipboard API.
 * 
 * @function copyToClipboard
 * @returns {void}
 * 
 * @description
 * - Recopila todo el texto de los resultados mostrados
 * - Intenta usar la Clipboard API moderna
 * - Proporciona fallback con document.execCommand
 * - Muestra feedback visual en el botón
 * - Maneja errores de copia graciosamente
 */
function copyToClipboard() {
    const resultDiv  = document.getElementById('result');
    const copyButton = document.getElementById('copyButton');
    if (!resultDiv || !copyButton) return;

    // Obtener todo el texto de los hijos div
    let textToCopy = '';
    const childDivs = resultDiv.querySelectorAll('div');
    childDivs.forEach(div => {
        textToCopy += div.innerText + '\n\n';
    });

    // Copy to clipboard
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy.trim()).then(() => {
            // Success notification using UI helpers
            try { window.AppYacht?.ui?.notifySuccess?.('Result copied to clipboard'); } catch (e) {
                // Visual fallback on the button
                copyButton.textContent = 'Copied!';
                copyButton.classList.add('btn-success');
                setTimeout(() => {
                    copyButton.textContent = 'Copy Result';
                    copyButton.classList.remove('btn-success');
                }, 2000);
            }
        }).catch(err => {
            (window.AppYacht?.error || console.error)('Error copying:', err);
            try { window.AppYacht?.ui?.notifyError?.('Could not copy the result'); } catch (e) {
                alert('Error copying to clipboard');
            }
        });
    } else {
        // Fallback for browsers without Clipboard API
        const tempEl = document.createElement('textarea');
        tempEl.value = textToCopy.trim();
        document.body.appendChild(tempEl);
        tempEl.select();
        try {
            const success = document.execCommand('copy');
            if (success) {
                try { window.AppYacht?.ui?.notifySuccess?.('Result copied to clipboard'); } catch (e) {
                    copyButton.textContent = 'Copied!';
                    copyButton.classList.add('btn-success');
                    setTimeout(() => {
                        copyButton.textContent = 'Copy Result';
                        copyButton.classList.remove('btn-success');
                    }, 2000);
                }
            } else {
                try { window.AppYacht?.ui?.notifyError?.('Could not copy result'); } catch (e) {
                    alert('Error copying to clipboard');
                }
            }
        } catch (err) {
            (window.AppYacht?.error || console.error)('Error copying (fallback):', err);
            try { window.AppYacht?.ui?.notifyError?.('Could not copy result'); } catch (e) {
                alert('Error copying to clipboard');
            }
        } finally {
            document.body.removeChild(tempEl);
        }
    }
}
  