// ARCHIVO modules\calc\js\mix.js

//===============================add the Mixed Seasons fields ===============================||
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
                 (window.AppYacht?.error || console.error)("El botón 'applymixButton' no se encontró después de agregarlo al DOM.");
             }
        }
    }, 0);
}

// Quitar el contenido Mix
function clearMixContainer() {
    const container = document.getElementById('mixedSeasonsContainer');
    container.innerHTML = '';
}

document.addEventListener('DOMContentLoaded', () => {
    // Checkbox MixedSeasons
    const enableMixedSeasonsCheckbox = document.getElementById('enableMixedSeasons');
    if (enableMixedSeasonsCheckbox) {
        enableMixedSeasonsCheckbox.addEventListener('change', function () {
            if (this.checked) {
                addMix();
            } else {
                clearMixContainer();
            }
        });
    } else {
        (window.AppYacht?.error || console.error)("El elemento con ID 'enableMixedSeasons' no se encontró en el DOM.");
    }

    // Delegación de eventos en #mixedSeasonsContainer
    const mixedSeasonsContainer = document.getElementById('mixedSeasonsContainer');
    if (mixedSeasonsContainer) {
        mixedSeasonsContainer.addEventListener('click', (event) => {
            if (event.target && event.target.id === 'applymixButton') {
                applyMix();
            }
        });
    }

    // Manejo de cambio de currency
    const currencySelector = document.getElementById('currency');
    if (currencySelector) {
        currencySelector.addEventListener('change', updateCurrencySymbols);
    }
});

//===============================autocomplete Nights===============================||
// Manejo en tiempo real de Low/HighSeason
document.addEventListener("DOMContentLoaded", function () {
    const mixedSeasonsContainer = document.getElementById("mixedSeasonsContainer");
    if (!mixedSeasonsContainer) return;

    mixedSeasonsContainer.addEventListener("input", function (event) {
        if (event.target && (event.target.id === "lowSeasonNights" || event.target.id === "highSeasonNights")) {
            updateValues();
        }
    });

    mixedSeasonsContainer.addEventListener("keydown", function (event) {
        if (event.target && (event.target.id === "lowSeasonNights" || event.target.id === "highSeasonNights")) {
            handleArrowKeys(event, event.target);
        }
    });
});

// Nueva función para manejar el input de Mix Nights
function handleMixNightsInput(input) {
    formatNumber(input);
    const mixNights = parseInt(input.value) || 0;
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

// Nueva función para manejar el input de las temporadas
function handleSeasonInput(input) {
    formatNumber(input);
    const mixNightsInput = document.querySelector("input[name='mixnights']");
    const mixNights = parseInt(mixNightsInput?.value) || 0;
    
    if (mixNights > 0) {
        const otherSeasonInput = input.id === "lowSeasonNights" 
            ? document.getElementById("highSeasonNights")
            : document.getElementById("lowSeasonNights");
            
        if (otherSeasonInput) {
            const currentValue = parseInt(input.value) || 0;
            const otherValue = mixNights - currentValue;
            otherSeasonInput.value = Math.max(1, otherValue);
        }
    }
}

function updateValues() {
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

function handleArrowKeys(event, inputElement) {
    const mixNightsInput = document.querySelector("input[name='mixnights']");
    const lowSeasonInput = document.getElementById("lowSeasonNights");
    const highSeasonInput = document.getElementById("highSeasonNights");

    if (!mixNightsInput || !lowSeasonInput || !highSeasonInput) return;

    const mixNights = parseInt(mixNightsInput.value) || 0;

    if (inputElement.id === "lowSeasonNights") {
        if (event.key === "ArrowUp") {
            lowSeasonInput.stepUp();
            lowSeasonInput.value = Math.min(parseInt(lowSeasonInput.value), mixNights - 1);
            highSeasonInput.value = mixNights - parseInt(lowSeasonInput.value);
        } else if (event.key === "ArrowDown") {
            lowSeasonInput.stepDown();
            lowSeasonInput.value = Math.max(parseInt(lowSeasonInput.value), 1);
            highSeasonInput.value = mixNights - parseInt(lowSeasonInput.value);
        }
    } else if (inputElement.id === "highSeasonNights") {
        if (event.key === "ArrowUp") {
            highSeasonInput.stepUp();
            highSeasonInput.value = Math.min(parseInt(highSeasonInput.value), mixNights - 1);
            lowSeasonInput.value = mixNights - parseInt(highSeasonInput.value);
        } else if (event.key === "ArrowDown") {
            highSeasonInput.stepDown();
            highSeasonInput.value = Math.max(parseInt(highSeasonInput.value), 1);
            lowSeasonInput.value = mixNights - parseInt(highSeasonInput.value);
        }
    }
}

//===============================calculate the mix result===============================||

function applyMix() {
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
        alert('Por favor, completa todos los campos de la calculadora mixta.');
        return;
    }

    // Crear FormData
    const formData = new FormData();
    formData.append('action', 'calculate_mix');
    formData.append('nonce', ajaxData?.nonce || ''); // Añadir nonce para seguridad CSRF
    formData.append('mixnights', mixNights);
    formData.append('lowSeasonRate', lowSeasonRate);
    formData.append('lowSeasonNights', lowSeasonNights);
    formData.append('highSeasonRate', highSeasonRate);
    formData.append('highSeasonNights', highSeasonNights);
    formData.append('currency', currency);

    fetch(ajaxData.ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData)
    })
    .then(response => {
        if (!response.ok) throw new Error('Server error in calculatemix.php');
        return response.json();
    })
    .then(result => {
        if (result.success) {
            const { lowSeasonResult, highSeasonResult, mixedResult: totalMixedResult } = result.data;

            // Imprimir en pantalla
            const lowMixedResult = document.getElementById('lowmixedResult');
            const highMixedResult = document.getElementById('highmixedResult');
            const mixedResult = document.getElementById('mixedResult');
            
            if (lowMixedResult) lowMixedResult.textContent = lowSeasonResult;
            if (highMixedResult) highMixedResult.textContent = highSeasonResult;
            if (mixedResult) mixedResult.textContent = totalMixedResult;

            // Mostrar los signos
            document.querySelectorAll('#mixedResultcontainer .hiddenmixresult')
                .forEach(span => span.classList.remove('hiddenmixresult'));

            // Actualizar la calculadora principal
            const nightsInput = document.querySelector('input[name="nights"]');
            const mixNightsField = document.getElementById('mix-nights');
            if (nightsInput && mixNightsField) {
                nightsInput.value = mixNightsField.value;
                nightsInput.dispatchEvent(new Event('input'));
            }
            const baseRateInput = document.querySelector('input[name="baseRate"]');
            if (baseRateInput) {
                const numericMatch = totalMixedResult.match(/([\d,]+\.\d{2})/);
                if (numericMatch) {
                    baseRateInput.value = numericMatch[1].replace(/,/g, '');
                } else {
                    baseRateInput.value = totalMixedResult.replace(/[^\d.-]/g, '');
                }
                baseRateInput.dispatchEvent(new Event('input'));
            }

            // Cambiar el botón a "Recalculate"
            const calculateBtn = document.getElementById('calculateButton');
            if (calculateBtn) calculateBtn.textContent = 'Recalculate';

        } else {
            (window.AppYacht?.error || console.error)('Calculation error:', result.data);
            const mixedResultError = document.getElementById('mixedResult');
            if (mixedResultError) {
                mixedResultError.textContent = 'Calculation error.';
                mixedResultError.classList.add('text-danger');
            }
        }
    })
    .catch(err => {
        (window.AppYacht?.error || console.error)("Error in applyMix:", err);
        const mixedResultError = document.getElementById('mixedResult');
        if (mixedResultError) {
            mixedResultError.textContent = 'Error durante el procesamiento.';
            mixedResultError.classList.add('text-danger');
        }
    });
}

// Utilizamos la función updateCurrencySymbols de shared/js/currency.js
// La función ha sido centralizada para evitar duplicación de código

// Remove debounced listeners for rates
// if (lowRateInputEl) lowRateInputEl.addEventListener('input', debounce((event) => formatNumber(event.target), debounceDelay));
// if (highRateInputEl) highRateInputEl.addEventListener('input', debounce((event) => formatNumber(event.target), debounceDelay));
