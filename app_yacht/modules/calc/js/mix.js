// ARCHIVO modules\calc\js\mix.js

//===============================add the Mixed Seasons fields ===============================||
/**
 * Gestiona la UI y la lógica de cálculo para "Mixed Seasons".
 * Permite introducir varias filas con noches y selección de temporada (Low/High),
 * valida la entrada, envía los datos al endpoint AJAX y muestra el resultado.
 */

/**
 * Crea una nueva fila de mezcla en el contenedor.
 * @function addMix
 * @returns {void}
 */
function addMix() {
    const container = document.getElementById('mixedSeasonsContainer');

    if (container.children.length > 0) {
        return;
    }

    // Usar createWithFragment para optimizar la creación del DOM
    createWithFragment(fragment => {
        // Mix Nights Div
        const mixNightsDiv = createElement('div', { className: 'col-4 col-md-2' });
        mixNightsDiv.appendChild(createElement('label', {}, 'Mix Nights:'));
        const mixNightsInput = createElement('input', {
            id: 'mix-nights', type: 'number', className: 'form-control mt-2', name: 'mixnights',
            placeholder: 'Mix Nights', required: true, inputmode: 'numeric', pattern: '[0-9]*'
            // oninput eliminado, se añadirá listener después
        });
        mixNightsDiv.appendChild(mixNightsInput);
        fragment.appendChild(mixNightsDiv);

        // Low Season Div
        const lowSeasonDiv = createElement('div', { className: 'col-12 col-md-5' });
        const lowFlexDiv = createElement('div', { className: 'd-flex justify-content-between' });
        const lowNightsCol = createElement('div', { className: 'col-6 pe-2' });
        lowNightsCol.appendChild(createElement('label', { htmlFor: 'lowSeasonNights' }, 'Low Season Nights:'));
        const lowNightsInput = createElement('input', {
            type: 'number', id: 'lowSeasonNights', className: 'form-control mt-2', placeholder: 'Nights for low season',
            required: true, min: '0', step: '1', inputmode: 'numeric', pattern: '[0-9]*'
             // oninput eliminado
        });
        lowNightsCol.appendChild(lowNightsInput);
        const lowRateCol = createElement('div', { className: 'col-6 ps-2' });
        lowRateCol.appendChild(createElement('label', { htmlFor: 'lowSeasonRate', className: 'form-label' }, 'Low Season Rate:'));
        const lowRateGroup = createElement('div', { className: 'input-group' });
        const lowRateInput = createElement('input', {
            type: 'text', id: 'lowSeasonRate', className: 'form-control', placeholder: 'Rate for low season',
            required: true, oninput: 'formatNumber(this)'
        });
        lowRateGroup.appendChild(lowRateInput);
        lowRateGroup.appendChild(createElement('span', { className: 'input-group-text', id: 'lowSeasonRateCurrencySymbol' }));
        lowRateCol.appendChild(lowRateGroup);
        lowFlexDiv.appendChild(lowNightsCol);
        lowFlexDiv.appendChild(lowRateCol);
        lowSeasonDiv.appendChild(lowFlexDiv);
        fragment.appendChild(lowSeasonDiv);

        // High Season Div (similar a Low Season)
        const highSeasonDiv = createElement('div', { className: 'col-12 col-md-5' });
        const highFlexDiv = createElement('div', { className: 'd-flex justify-content-between' });
        const highNightsCol = createElement('div', { className: 'col-6 pe-2' });
        highNightsCol.appendChild(createElement('label', { htmlFor: 'highSeasonNights' }, 'High Season Nights:'));
        const highNightsInput = createElement('input', {
            type: 'number', id: 'highSeasonNights', className: 'form-control mt-2', placeholder: 'Nights for high season',
            required: true, min: '0', step: '1', inputmode: 'numeric', pattern: '[0-9]*'
             // oninput eliminado
        });
        highNightsCol.appendChild(highNightsInput);
        const highRateCol = createElement('div', { className: 'col-6 ps-2' });
        highRateCol.appendChild(createElement('label', { htmlFor: 'highSeasonRate', className: 'form-label' }, 'High Season Rate:'));
        const highRateGroup = createElement('div', { className: 'input-group' });
        const highRateInput = createElement('input', {
            type: 'text', id: 'highSeasonRate', className: 'form-control', placeholder: 'Rate for high season',
            required: true, oninput: 'formatNumber(this)'
        });
        highRateGroup.appendChild(highRateInput);
        highRateGroup.appendChild(createElement('span', { className: 'input-group-text', id: 'highSeasonRateCurrencySymbol' }));
        highRateCol.appendChild(highRateGroup);
        highFlexDiv.appendChild(highNightsCol);
        highFlexDiv.appendChild(highRateCol);
        highSeasonDiv.appendChild(highFlexDiv);
        fragment.appendChild(highSeasonDiv);

        // Mixed Result Div
        const mixedResultDiv = createElement('div', { id: 'mixedResultcontainer', className: 'col-12 col-md-10 mt-2 d-flex align-items-center' });
        mixedResultDiv.appendChild(createElement('p', { className: 'fw-bold me-2' }, 'Mixed Results:'));
        mixedResultDiv.appendChild(createElement('p', { id: 'lowmixedResult', className: 'me-2' }));
        mixedResultDiv.appendChild(createElement('span', { className: 'me-2 mb-4 fw-bold hiddenmixresult' }, '+'));
        mixedResultDiv.appendChild(createElement('p', { id: 'highmixedResult', className: 'me-2' }));
        mixedResultDiv.appendChild(createElement('span', { className: 'me-2 mb-4 fw-bold hiddenmixresult' }, '='));
        mixedResultDiv.appendChild(createElement('p', { id: 'mixedResult' }));
        fragment.appendChild(mixedResultDiv);

        // Apply Mix Button Div
        const applyMixButtonDiv = createElement('div', { className: 'col-2 d-flex' });
        const applyMixButton = createElement('button', {
            id: 'applymixButton', type: 'button',
            className: 'btn btn-secondary ms-auto mt-2 w-100'
        }, 'Apply Mix');
        // Adjuntar listener aquí o después de añadir al DOM
        applyMixButton.addEventListener('click', applyMix);
        applyMixButtonDiv.appendChild(applyMixButton);
        fragment.appendChild(applyMixButtonDiv);

    }, container); // Añadir el fragmento al contenedor

    // Llamadas que deben ocurrir después de que los elementos estén en el DOM
    if (typeof updateCurrencySymbols === 'function') {
        updateCurrencySymbols();
    }

    // Adjuntar listeners a los inputs creados dinámicamente
    // Es más seguro hacerlo después de que createWithFragment haya añadido los elementos al DOM real.
    // Usamos setTimeout para asegurar que el DOM se haya actualizado.
    setTimeout(() => {
        const mixNightsInputEl = document.getElementById('mix-nights');
        const lowNightsInputEl = document.getElementById('lowSeasonNights');
        const lowRateInputEl = document.getElementById('lowSeasonRate');
        const highNightsInputEl = document.getElementById('highSeasonNights');
        const highRateInputEl = document.getElementById('highSeasonRate');

        // Usar debounce para optimizar listeners de input
        const debounceDelay = 300; // ms
        if (mixNightsInputEl) mixNightsInputEl.addEventListener('input', debounce((event) => handleMixNightsInput(event.target), debounceDelay));
        if (lowNightsInputEl) lowNightsInputEl.addEventListener('input', debounce((event) => handleSeasonInput(event.target), debounceDelay));
        if (lowRateInputEl) lowRateInputEl.addEventListener('input', debounce((event) => formatNumber(event.target), debounceDelay));
        if (highNightsInputEl) highNightsInputEl.addEventListener('input', debounce((event) => handleSeasonInput(event.target), debounceDelay));
        if (highRateInputEl) highRateInputEl.addEventListener('input', debounce((event) => formatNumber(event.target), debounceDelay));

        // Verificar si el botón existe y adjuntar listener si no se hizo dentro de createWithFragment
        const applyMixButtonEl = document.getElementById('applymixButton');
        if (!applyMixButtonEl?.onclick) { // Evitar doble listener si ya se añadió
             if(applyMixButtonEl) {
                 applyMixButtonEl.addEventListener('click', applyMix);
             } else {
                 (window.AppYacht?.error || console.error)("The 'applymixButton' button was not found after adding it to the DOM.");
             }
        }
    }, 0);
}

// Quitar el contenido Mix
/**
 * Elimina todas las filas de mezcla del contenedor.
 * @function clearMixContainer
 * @returns {void}
 */
function clearMixContainer() {
    const container = document.getElementById('mixedSeasonsContainer');
    container.innerHTML = '';
}

// Manejo de eventos después de DOM cargado
document.addEventListener('DOMContentLoaded', () => {
    const mixedSeasonsCheckbox = document.getElementById('enableMixedSeasons');
    const mixedSeasonsContainer = document.getElementById('mixedSeasonsContainer');

    if (mixedSeasonsCheckbox) {
        mixedSeasonsCheckbox.addEventListener('change', () => {
            if (mixedSeasonsCheckbox.checked) {
                clearMixContainer();
                addMix();
                // Show respecting Bootstrap .row default display:flex
                mixedSeasonsContainer.style.removeProperty('display');
            } else {
                // When disabling, clear and hide the container
                clearMixContainer();
                mixedSeasonsContainer.style.display = 'none';
            }
        });
        // Set initial state respecting Bootstrap layout
        if (mixedSeasonsCheckbox.checked) {
            mixedSeasonsContainer.style.removeProperty('display');
        } else {
            mixedSeasonsContainer.style.display = 'none';
        }
    }

    // Delegación de eventos en el contenedor
    if (mixedSeasonsContainer) {
        mixedSeasonsContainer.addEventListener('input', (event) => {
            if (event.target.classList.contains('mix-nights')) {
                handleMixNightsInput(event);
            } else if (event.target.classList.contains('mix-season')) {
                handleSeasonInput(event);
            }
        });

        mixedSeasonsContainer.addEventListener('keydown', handleArrowKeys);
        mixedSeasonsContainer.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-mix-row')) {
                event.target.closest('.mix-row').remove();
                updateValues(mixedSeasonsContainer);
            }
        });
    }
});

/**
 * Maneja cambios en el input de noches dentro de Mixed Seasons.
 * Actualiza automáticamente los campos Low/High Season según el valor total.
 * 
 * @function handleMixNightsInput
 * @param {Element|Event} input - El elemento input o el evento input
 * @returns {void}
 * 
 * @description
 * - Formatea el número en el input
 * - Calcula y sincroniza noches Low/High Season
 * - Mantiene proporciones cuando ambos campos tienen valores
 */
function handleMixNightsInput(input) {
    // Normalizar parámetro: puede ser event.target o el input directamente
    const inputElement = input.target || input;
    
    formatNumber(inputElement);
    const mixNights = parseInt(inputElement.value) || 0;
    const lowSeasonInput = document.getElementById("lowSeasonNights");
    const highSeasonInput = document.getElementById("highSeasonNights");
    
    if (lowSeasonInput && highSeasonInput) {
        if (mixNights === 0) {
            lowSeasonInput.value = "";
            highSeasonInput.value = "";
        } else if (lowSeasonInput.value && highSeasonInput.value) {
            // Solo sincronizar si ambos campos ya tienen valores
            const totalNights = parseInt(lowSeasonInput.value) + parseInt(highSeasonInput.value);
            if (totalNights !== mixNights) {
                const ratio = mixNights / totalNights;
                lowSeasonInput.value = Math.round(parseInt(lowSeasonInput.value) * ratio);
                highSeasonInput.value = mixNights - lowSeasonInput.value;
            }
        }
    }
}

/**
 * Maneja cambios en los inputs de temporada (Low/High Season Nights).
 * Ajusta automáticamente el valor del otro campo para mantener el total.
 * 
 * @function handleSeasonInput
 * @param {Element|Event} input - El elemento input o el evento input
 * @returns {void}
 * 
 * @description
 * - Formatea el número en el input
 * - Calcula el valor complementario para el otro campo de temporada
 * - Asegura que la suma sea igual a Mix Nights
 */
function handleSeasonInput(input) {
    // Normalizar parámetro
    const inputElement = input.target || input;
    
    formatNumber(inputElement);
    const mixNightsInput = document.querySelector("input[name='mixnights']");
    const mixNights = parseInt(mixNightsInput?.value) || 0;
    
    if (mixNights > 0) {
        const otherSeasonInput = inputElement.id === "lowSeasonNights" 
            ? document.getElementById("highSeasonNights")
            : document.getElementById("lowSeasonNights");
            
        if (otherSeasonInput) {
            const currentValue = parseInt(inputElement.value) || 0;
            const otherValue = mixNights - currentValue;
            otherSeasonInput.value = Math.max(1, otherValue);
        }
    }
}

/**
 * Recalcula y actualiza los totales de noches Low/High en la UI.
 * Sincroniza los valores según el input activo.
 * 
 * @function updateValues
 * @param {HTMLElement} container - Contenedor principal de mixed seasons
 * @returns {void}
 * 
 * @description
 * - Valida que Mix Nights sea mayor a 0
 * - Ajusta valores según el campo que tiene el foco
 * - Asegura que la suma no exceda Mix Nights
 * - Limpia campos si Mix Nights es 0
 */
function updateValues(container) {
    const mixNightsInput = document.querySelector("input[name='mixnights']");
    const lowSeasonInput = document.getElementById("lowSeasonNights");
    const highSeasonInput = document.getElementById("highSeasonNights");

    if (!mixNightsInput || !lowSeasonInput || !highSeasonInput) return;

    const mixNights = parseInt(mixNightsInput?.value) || 0;
    let lowSeasonNights = parseInt(lowSeasonInput?.value) || 0;
    let highSeasonNights = parseInt(highSeasonInput?.value) || 0;

    if (mixNights === 0) {
        lowSeasonInput.value = "";
        highSeasonInput.value = "";
        return;
    }

    if (document.activeElement === lowSeasonInput) {
        lowSeasonNights = Math.min(lowSeasonNights, mixNights - 1);
        highSeasonNights = mixNights - lowSeasonNights;
    } else if (document.activeElement === highSeasonInput) {
        highSeasonNights = Math.min(highSeasonNights, mixNights - 1);
        lowSeasonNights = mixNights - highSeasonNights;
    }

    lowSeasonInput.value = lowSeasonNights;
    highSeasonInput.value = highSeasonNights;
}

/**
 * Permite navegar entre inputs de temporada con teclas de flecha.
 * Ajusta automáticamente los valores complementarios según la dirección.
 * 
 * @function handleArrowKeys
 * @param {KeyboardEvent} e - Evento de teclado
 * @returns {void}
 * 
 * @description
 * - Valida que Mix Nights sea mayor a 0
 * - Intercepta teclas ArrowUp/ArrowDown en inputs de temporada
 * - Previene comportamiento por defecto del navegador
 * - Mantiene total igual a Mix Nights ajustando el campo opuesto
 * - Asegura valores mínimos (≥1) para ambos campos
 */
function handleArrowKeys(e) {
    const mixNightsInput = document.querySelector("input[name='mixnights']");
    const lowSeasonInput = document.getElementById("lowSeasonNights");
    const highSeasonInput = document.getElementById("highSeasonNights");

    if (!mixNightsInput || !lowSeasonInput || !highSeasonInput) return;

    const inputElement = e.target;
    if (!inputElement || !(inputElement instanceof HTMLElement)) return;

    const mixNights = parseInt(mixNightsInput.value) || 0;
    if (mixNights <= 0) return;

    if (inputElement.id === "lowSeasonNights") {
        if (e.key === "ArrowUp") {
            e.preventDefault();
            lowSeasonInput.stepUp();
            lowSeasonInput.value = Math.min(parseInt(lowSeasonInput.value) || 0, mixNights - 1);
            highSeasonInput.value = Math.max(1, mixNights - (parseInt(lowSeasonInput.value) || 0));
        } else if (e.key === "ArrowDown") {
            e.preventDefault();
            lowSeasonInput.stepDown();
            lowSeasonInput.value = Math.max(parseInt(lowSeasonInput.value) || 0, 1);
            highSeasonInput.value = Math.max(0, mixNights - (parseInt(lowSeasonInput.value) || 0));
        }
    } else if (inputElement.id === "highSeasonNights") {
        if (e.key === "ArrowUp") {
            e.preventDefault();
            highSeasonInput.stepUp();
            highSeasonInput.value = Math.min(parseInt(highSeasonInput.value) || 0, mixNights - 1);
            lowSeasonInput.value = Math.max(1, mixNights - (parseInt(highSeasonInput.value) || 0));
        } else if (e.key === "ArrowDown") {
            e.preventDefault();
            highSeasonInput.stepDown();
            highSeasonInput.value = Math.max(parseInt(highSeasonInput.value) || 0, 1);
            lowSeasonInput.value = Math.max(0, mixNights - (parseInt(highSeasonInput.value) || 0));
        }
    }
}

//===============================calculate the mix result===============================||

/**
 * Valida las filas, construye el payload y llama al endpoint AJAX para calcular la mezcla.
 * Muestra el resultado en un contenedor específico dentro de la calculadora principal.
 * 
 * @async
 * @function applyMix
 * @param {HTMLElement} container - Contenedor principal de mixed seasons
 * @returns {Promise<void>}
 * 
 * @description
 * - Valida que haya al menos una fila con noches > 0
 * - Normaliza números de noches
 * - Construye FormData con action, nonce y filas
 * - Envía POST a handle_calculate_mix (calculatemix.php)
 * - Maneja errores de red y del backend
 * - Escribe el resultado y lo cachea para la calculadora principal
 */
async function applyMix(container) {
    // Validación
    if (typeof validateFields === 'function') {
        const isValid = validateFields(true); // Pasar true para indicar que es una validación de Apply Mix
        if (!isValid) {
            return; // Detener la ejecución si la validación falla
        }
    }

    // Recolectar datos
    const mixNights = document.getElementById('mix-nights')?.value || '';
    const lowSeasonRate = document.getElementById('lowSeasonRate')?.value || '';
    const lowSeasonNights = document.getElementById('lowSeasonNights')?.value || '';
    const highSeasonRate = document.getElementById('highSeasonRate')?.value || '';
    const highSeasonNights = document.getElementById('highSeasonNights')?.value || '';
    const currency = document.getElementById('currency')?.value || '';

    // Validar datos
    if (!mixNights || !lowSeasonRate || !lowSeasonNights || !highSeasonRate || !highSeasonNights || !currency) {
        alert('Please complete all fields in the mixed calculator.');
        return;
    }

    // Normalizar valores numéricos para el backend
    const toNumber = (val) => {
        if (val == null) return '';
        const s = String(val).replace(/\s/g, '').replace(/,/g, '');
        const n = parseFloat(s);
        return Number.isFinite(n) ? String(n) : '';
    };
    const toInt = (val) => {
        if (val == null) return '';
        const s = String(val).replace(/[^\d-]/g, '');
        const n = parseInt(s, 10);
        return Number.isFinite(n) ? String(n) : '';
    };

    const mixNightsNum = toInt(mixNights);
    const lowSeasonRateNum = toNumber(lowSeasonRate);
    const lowSeasonNightsNum = toInt(lowSeasonNights);
    const highSeasonRateNum = toNumber(highSeasonRate);
    const highSeasonNightsNum = toInt(highSeasonNights);

    // Crear FormData
    const formData = new FormData();
    formData.append('action', 'calculate_mix');
    formData.append('nonce', ajaxData?.nonce || ''); // Añadir nonce para seguridad CSRF

    // Datos específicos del mix (compatibilidad con backend en minúsculas y legado camelCase)
    formData.append('mixnights', mixNightsNum);
    formData.append('lowseasonrate', lowSeasonRateNum);
    formData.append('lowseasonnights', lowSeasonNightsNum);
    formData.append('highseasonrate', highSeasonRateNum);
    formData.append('highseasonnights', highSeasonNightsNum);
    formData.append('currency', currency);

    // Claves camelCase por compatibilidad si algún entorno aún las espera
    formData.append('mixNights', mixNightsNum);
    formData.append('lowSeasonRate', lowSeasonRateNum);
    formData.append('lowSeasonNights', lowSeasonNightsNum);
    formData.append('highSeasonRate', highSeasonRateNum);
    formData.append('highSeasonNights', highSeasonNightsNum);
    // formData.append('currency', currency); // (ya añadida arriba)

    try {
        // Loading state start
        try { window.AppYacht?.ui?.setLoading?.(true); } catch (e) {}

        const response = await fetch(ajaxData.ajaxurl, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Server response error');
        }

        const result = await response.json();

        // Loading state end
        try { window.AppYacht?.ui?.setLoading?.(false); } catch (e) {}

        if (result.success) {
            // Mostrar resultados
            const lowResult = document.getElementById('lowmixedResult');
            const highResult = document.getElementById('highmixedResult');
            const mixedResult = document.getElementById('mixedResult');
            const hiddenResults = document.querySelectorAll('.hiddenmixresult');

            if (lowResult && highResult && mixedResult) {
                lowResult.textContent = result.data.lowSeasonResult || '';
                highResult.textContent = result.data.highSeasonResult || '';
                mixedResult.textContent = result.data.mixedResult || '';

                // Mostrar elementos ocultos
                hiddenResults.forEach(el => el.style.display = 'inline');

                // Populate main calculator fields with mixed values
                try {
                    // Ensure at least one charter rate group exists
                    const charterRateContainer = document.getElementById('charterRateContainer');
                    const existingGroups = document.querySelectorAll('.charter-rate-group');
                    (window.AppYacht?.log || console.log)('[mix] existing charter-rate groups:', existingGroups.length);
                    
                    if (existingGroups.length === 0 && typeof addCharterRate === 'function') {
                        (window.AppYacht?.log || console.log)('[mix] No groups found. Calling addCharterRate(true)');
                        // Add first charter rate group if none exists
                        addCharterRate(true);
                        // Wait a bit for DOM to update
                        await new Promise(resolve => setTimeout(resolve, 150));
                    }
                    
                    // Get the first available inputs (could be in first charter-rate-group)
                    const nightsInput = document.querySelector('.charter-rate-group input[name="nights"], .charter-rate-group input[name="hours"], input[name="nights"], input[name="hours"]');
                    const baseRateInput = document.querySelector('.charter-rate-group input[name="baseRate"], input[name="baseRate"]');
                    (window.AppYacht?.log || console.log)('[mix] inputs found', { hasNights: !!nightsInput, hasBaseRate: !!baseRateInput });
                    
                    if (nightsInput && mixNights) {
                        nightsInput.value = mixNights;
                        (window.AppYacht?.log || console.log)('[mix] setting nights input to', mixNights);
                        // Don't trigger input event to avoid formatNumber interference
                        nightsInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    if (baseRateInput && result.data.mixedResult) {
                        // Extract total value from "Total: € 66,666.67" format
                        const totalMatch = result.data.mixedResult.match(/[\d,]+\.?\d*/);
                        if (totalMatch) {
                            const totalValue = totalMatch[0]; // Keep formatting
                            baseRateInput.value = totalValue;
                            (window.AppYacht?.log || console.log)('[mix] setting baseRate input to', totalValue);
                            // Use change event instead of input to avoid formatNumber clearing
                            baseRateInput.dispatchEvent(new Event('change', { bubbles: true }));
                        } else {
                            (window.AppYacht?.warn || console.warn)('[mix] Could not parse mixedResult for baseRate:', result.data.mixedResult);
                        }
                    } else {
                        (window.AppYacht?.warn || console.warn)('[mix] baseRateInput or mixedResult missing.', { hasBaseRate: !!baseRateInput, mixedResult: result.data.mixedResult });
                    }
                    
                    // Publish event to notify other components
                    if (window.eventBus) {
                        window.eventBus.publish('mix:applied', {
                            nights: mixNights,
                            baseRate: result.data.mixedResult,
                            lowSeasonResult: result.data.lowSeasonResult,
                            highSeasonResult: result.data.highSeasonResult
                        });
                    }
                } catch (e) {
                    (window.AppYacht?.warn || console.warn)('Could not populate main calculator fields:', e);
                }
            }

            // Notificación de éxito
            try { window.AppYacht?.ui?.notifySuccess?.('Mix calculation completed'); } catch (e) {}
        } else {
            // Manejar error del servidor
            (window.AppYacht?.error || console.error)('Mixed calculation error:', result.data);
            try { window.AppYacht?.ui?.notifyError?.('Mixed calculation error'); } catch (e) {
                alert('Mixed calculation error: ' + (result.data || 'Unknown error'));
            }
        }
    } catch (error) {
        (window.AppYacht?.error || console.error)('Error on mixed calculation request:', error);
        try { window.AppYacht?.ui?.notifyError?.('Connection error on mixed calculation'); } catch (e) {
            alert('Connection error. Please try again.');
        }
        // Asegurar desactivación del estado de carga
        try { window.AppYacht?.ui?.setLoading?.(false); } catch (e) {}
    }
}

// Utilizamos la función updateCurrencySymbols de shared/js/currency.js
// La función ha sido centralizada para evitar duplicación de código

// Remove debounced listeners for rates
// if (lowRateInputEl) lowRateInputEl.addEventListener('input', debounce((event) => formatNumber(event.target), debounceDelay));
// if (highRateInputEl) highRateInputEl.addEventListener('input', debounce((event) => formatNumber(event.target), debounceDelay));
