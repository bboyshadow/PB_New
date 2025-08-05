// ARCHIVO modules\calc\js\interfaz.js

// --- Definiciones de Funciones ---

/**
 * Alterna la visibilidad de un campo opcional de la calculadora,
 * maneja la exclusividad APA, actualiza ARIA y mueve el foco.
 * @param {string} fieldId - El ID del contenedor del campo a mostrar/ocultar.
 */
function toggleCalcOptionalField(fieldId) {
    // Callback para manejar la exclusividad entre APA y APA Percentage, y entre VAT y VAT Mix
    const handleExclusivity = (isVisible, fieldElement) => {
        if (!isVisible || !fieldElement) return; 
        
        const apaCheck = document.getElementById('apaCheck');
        const apaPercentageCheck = document.getElementById('apaPercentageCheck');
        const vatCheck = document.getElementById('vatCheck');
        const vatMixCheck = document.getElementById('vatRateMix');
        
        // Lógica para APA y APA%
        if (apaCheck && apaPercentageCheck) {
            if (fieldId === 'apaField' && apaCheck.checked) {
                apaPercentageCheck.checked = false;
                const apaPercentageField = document.getElementById('apaPercentageField');
                if (apaPercentageField) {
                    apaPercentageField.style.display = 'none';
                    const percCheckbox = document.querySelector('input[aria-controls="apaPercentageField"]');
                    if(percCheckbox) percCheckbox.setAttribute('aria-expanded', 'false');
                }
            } else if (fieldId === 'apaPercentageField' && apaPercentageCheck.checked) {
                apaCheck.checked = false;
                const apaField = document.getElementById('apaField');
                if (apaField) {
                    apaField.style.display = 'none';
                    const apaFixedCheckbox = document.querySelector('input[aria-controls="apaField"]');
                    if(apaFixedCheckbox) apaFixedCheckbox.setAttribute('aria-expanded', 'false');
                }
            }
        }
        
        // Lógica para VAT y VAT Mix
        if (vatCheck && vatMixCheck) {
            if (fieldId === 'vatField' && vatCheck.checked) {
                vatMixCheck.checked = false;
                const vatMixField = document.getElementById('vatCountriesContainer');
                if (vatMixField) {
                    vatMixField.style.display = 'none';
                    const vatMixCheckbox = document.querySelector('input[aria-controls="vatCountriesContainer"]');
                    if(vatMixCheckbox) vatMixCheckbox.setAttribute('aria-expanded', 'false');
                }
            } else if (fieldId === 'vatCountriesContainer' && vatMixCheck.checked) {
                vatCheck.checked = false;
                const vatField = document.getElementById('vatField');
                if (vatField) {
                    vatField.style.display = 'none';
                    const vatCheckbox = document.querySelector('input[aria-controls="vatField"]');
                    if(vatCheckbox) vatCheckbox.setAttribute('aria-expanded', 'false');
                }
            }
        }
    };
    
    const field = document.getElementById(fieldId);
    if (!field) {
        console.error(`Elemento con ID ${fieldId} no encontrado.`);
        return false;
    }
    
    // Determinar el checkbox que controla este fieldId
    const controlCheckbox = document.querySelector(`input[aria-controls="${fieldId}"]`);
    if (!controlCheckbox) {
         console.warn(`Checkbox de control para ${fieldId} no encontrado.`);
    }

    // Alternar visibilidad
    const isVisible = field.style.display === 'none' || field.style.display === '';
    const displayType = field.classList.contains('row') || field.classList.contains('d-flex') ? 'flex' : 'block'; 
    field.style.display = isVisible ? displayType : 'none'; 

    // Ejecutar lógica de exclusividad
    handleExclusivity(isVisible, field);

    // Agregar automáticamente dos campos prellenados si se muestra vatCountriesContainer y está vacío
    if (isVisible && fieldId === 'vatCountriesContainer' && field.children.length === 0) {
        VatRateMix.addCountryField();
        VatRateMix.addCountryField();
    }

    // Actualizar aria-expanded en el checkbox de control
    if (controlCheckbox) {
        controlCheckbox.setAttribute('aria-expanded', isVisible);
    }

    // Mover el foco al primer input dentro del contenedor si se hace visible
    if (isVisible) {
        const firstInput = field.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 0); 
        }
    }
    
    // Manejar visibilidad del botón Add Country para VAT Mix basado en el estado de vatCountriesContainer
    const vatMixField = document.getElementById('vatCountriesContainer');
    const addBtn = document.getElementById('addVatCountryBtn');
    if (vatMixField && addBtn) {
        const isContainerVisible = vatMixField.style.display !== 'none';
        addBtn.style.display = isContainerVisible ? 'inline-block' : 'none';
    }
    
    return isVisible;
}


// Function to toggle discount fields and make them required if shown
function toggleDiscountField(button) {
    const charterRateGroup = button.closest('.charter-rate-group');
    if (!charterRateGroup) return;
    const discountContainer = charterRateGroup.querySelector('.discount-container');
    if (!discountContainer) return;
    const clearDiscountValues = (container) => {
        const discountType = container.querySelector('[name="discountType"]');
        const discountAmount = container.querySelector('[name="discountAmount"]');
        if (discountType) discountType.value = '';
        if (discountAmount) discountAmount.value = '';
    };
    const isVisible = typeof toggleContainer === 'function' 
                      ? toggleContainer(discountContainer, clearDiscountValues, 'flex') 
                      : false; 
    if (isVisible) {
         const firstInput = discountContainer.querySelector('select, input');
         if (firstInput) setTimeout(() => firstInput.focus(), 0);
    }
    return isVisible;
}

// Function to toggle promotion fields
function togglePromotionField(button) {
    const charterRateGroup = button.closest('.charter-rate-group');
    if (!charterRateGroup) return;
    const promotionContainer = charterRateGroup.querySelector('.promotion-container');
    if (!promotionContainer) return;
    const clearPromotionValues = (container) => {
        const promotionNights = container.querySelector('[name="promotionNights"]');
        if (promotionNights) promotionNights.value = '';
    };
    const isVisible = typeof toggleContainer === 'function' 
                      ? toggleContainer(promotionContainer, clearPromotionValues, 'flex') 
                      : false; 
    if (isVisible) {
         const firstInput = promotionContainer.querySelector('input');
         if (firstInput) setTimeout(() => firstInput.focus(), 0);
    }
    return isVisible;
}

// Function to toggle VAT field (asociada a un botón, no al checkbox directamente)
function toggleVATField(button) {
    const fieldToToggle = document.getElementById('vatField'); 
    if (!fieldToToggle) return;
    const clearVATValues = (container) => {
        const vatRate = container.querySelector('[name="vatRate"]');
        if (vatRate) vatRate.value = '';
    };
    const isVisible = typeof toggleContainer === 'function' 
                      ? toggleContainer(fieldToToggle, clearVATValues, 'flex') 
                      : false;
     if (isVisible) {
         const firstInput = fieldToToggle.querySelector('input');
         if (firstInput) setTimeout(() => firstInput.focus(), 0);
    }
    const checkbox = document.getElementById('vatCheck');
    if(checkbox) checkbox.checked = isVisible;
    return isVisible;
}

// Function to toggle APA field (asociada a un botón) - NO USADA, se usa checkbox
function toggleAPAField(button) {
     console.warn('toggleAPAField llamada desde botón no implementada, usar checkbox con toggleCalcOptionalField');
}

// Function to toggle Relocation field (asociada a un botón)
function toggleRelocationField(button) {
     const fieldToToggle = document.getElementById('relocationField');
     if (!fieldToToggle) return;
    const clearRelocationValues = (container) => {
        const relocationAmount = container.querySelector('[name="relocationAmount"]');
        if (relocationAmount) relocationAmount.value = '';
    };
    const isVisible = typeof toggleContainer === 'function' 
                      ? toggleContainer(fieldToToggle, clearRelocationValues, 'flex') 
                      : false;
     if (isVisible) {
         const firstInput = fieldToToggle.querySelector('input');
         if (firstInput) setTimeout(() => firstInput.focus(), 0);
    }
     const checkbox = document.getElementById('relocationCheck');
     if(checkbox) checkbox.checked = isVisible;
    return isVisible;
}

// Function to toggle Security field (asociada a un botón)
function toggleSecurityField(button) {
     const fieldToToggle = document.getElementById('securityField');
     if (!fieldToToggle) return;
    const clearSecurityValues = (container) => {
        const securityAmount = container.querySelector('[name="securityAmount"]');
        if (securityAmount) securityAmount.value = '';
    };
    const isVisible = typeof toggleContainer === 'function' 
                      ? toggleContainer(fieldToToggle, clearSecurityValues, 'flex') 
                      : false;
     if (isVisible) {
         const firstInput = fieldToToggle.querySelector('input');
         if (firstInput) setTimeout(() => firstInput.focus(), 0);
    }
     const checkbox = document.getElementById('securityCheck');
     if(checkbox) checkbox.checked = isVisible;
    return isVisible;
}

// Crea un nuevo grupo de tarifas
function addCharterRate(isFirst = false) {
    const newRateGroupHTML = `
        <div class="charter-rate-group row mb-3">
            <div class="col-6 col-sm-3">
                <label>Guests:</label>
                <input type="text" class="form-control" name="guests" placeholder="Guests" required inputmode="decimal" pattern="[0-9]*\.?[0-9]+" oninput="formatNumber(this)">
            </div>
            <div class="col-6 col-sm-3 onedayNights">
                <label>${document.getElementById('enableOneDayCharter')?.checked ? 'Hours:' : 'Nights:'}</label>
                <input type="text" class="form-control" name="${document.getElementById('enableOneDayCharter')?.checked ? 'hours' : 'nights'}" placeholder="${document.getElementById('enableOneDayCharter')?.checked ? 'Hours' : 'Nights'}" required inputmode="decimal" pattern="[0-9]*\.?[0-9]+" oninput="formatNumber(this)">
            </div>
            <div class="col-12 col-sm-6 mt-2 mt-sm-0 d-flex align-items-end">
                <div class="flex-grow-1">
                    <label>Base Charter Rate:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="baseRate" placeholder="Enter base rate..." required inputmode="numeric" pattern="[0-9]*" oninput="formatNumber(this)">
                        <span class="input-group-text currency-symbol">€</span>
                    </div>
                </div>
                <div class="ms-2">
                    <button type="button" class="btn btn-secondary toggle-discount-btn">Discount</button> 
                </div>
                <div class="ms-2">
                    <button type="button" class="btn btn-secondary toggle-promotion-btn">Promotion</button>
                </div>
                <div class="ms-2">
                    ${isFirst ? '<button type="button" class="btn btn-secondary add-rate-btn" aria-label="Add charter rate">+</button>' : '<button type="button" class="btn btn-danger remove-rate-btn" aria-label="Remove charter rate">-</button>'}
                </div>
            </div>
            <div class="row discount-container mt-2" style="display: none;">
                <div class="col-6">
                     <label class="visually-hidden">Discount Type</label>
                    <select class="form-control" name="discountType" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
                <div class="col-6">
                     <label class="visually-hidden">Discount Amount</label>
                    <input type="text" class="form-control" name="discountAmount" placeholder="Discount" required inputmode="numeric" pattern="[0-9,.]*" oninput="formatNumber(this)">
                </div>
            </div>
            <div class="row promotion-container mt-2" style="display: none;">
                <div class="col-12">
                    <label>Promotion Nights:</label>
                    <input type="text" class="form-control" name="promotionNights" placeholder="Promotion Nights" inputmode="decimal" pattern="[0-9]*" oninput="formatNumber(this)">
                </div>
            </div>
        </div>
    `;
    const newField = typeof addDynamicField === 'function' 
                     ? addDynamicField('charterRateContainer', newRateGroupHTML, (addedElement) => {
                         updateUIConstraints();
                         const firstInput = addedElement.querySelector('input[name="guests"]');
                         if (firstInput) setTimeout(() => firstInput.focus(), 0);
                         if (typeof updateCurrencySymbols === 'function') {
                             updateCurrencySymbols(); 
                         }
                         // Añadir listeners a los botones del nuevo grupo
                         const toggleBtn = addedElement.querySelector('.toggle-discount-btn');
                         if (toggleBtn) toggleBtn.addEventListener('click', () => toggleDiscountField(toggleBtn));
                         const promoBtn = addedElement.querySelector('.toggle-promotion-btn');
                         if (promoBtn) promoBtn.addEventListener('click', () => togglePromotionField(promoBtn));
                         const removeBtn = addedElement.querySelector('.remove-rate-btn');
                         if (removeBtn) removeBtn.addEventListener('click', () => removeCharterRate(removeBtn));
                         const addBtn = addedElement.querySelector('.add-rate-btn');
                         if (addBtn) addBtn.addEventListener('click', () => addCharterRate(false)); 
                         // Añadir listener formatNumber
                         addedElement.querySelectorAll('input[oninput="formatNumber(this)"]').forEach(input => {
                             input.addEventListener('input', (event) => {
                                 if(typeof formatNumber === 'function') formatNumber(event.target);
                             });
                         });
                     })
                     : null;
    return newField;
}

// Yacht info functionality is now handled by the yachtinfo module

// Remueve un grupo de tarifas
function removeCharterRate(button) {
    const addButton = document.querySelector('.add-rate-btn'); 
    const removed = typeof removeDynamicField === 'function' 
                    ? removeDynamicField(button, '.charter-rate-group', null, true)
                    : false;
    if (removed && addButton) {
         setTimeout(() => addButton.focus(), 0);
    }
    return removed;
}

// Extras
function addExtraField() {
    // console.log('addExtraField called'); // DEBUGGING REMOVED
    const extraTemplate = `
        <div class="col-12 col-md-6 col-lg-4 mb-3 extra-group my-2">
            <div class="row align-items-center">
                <div class="w-50 pe-1">
                     <label class="visually-hidden">Extra Name</label>
                    <input type="text" class="form-control" name="extraName" placeholder="Extra name" required>
                </div>
                <div class="w-50 ps-0 d-flex align-items-center">
                    <div class="input-group">
                         <label class="visually-hidden">Extra Cost</label>
                        <input type="text" class="form-control" name="extraCost" placeholder="Extra cost" required inputmode="decimal" pattern="[0-9]*\.?[0-9]+" oninput="formatNumber(this)">
                        <span class="input-group-text extra-currency-symbol currency-symbol">€</span>
                    </div>
                    <button type="button" class="btn btn-danger btn remove-extra-btn" aria-label="Remove extra">-</button>
                </div>
            </div>
        </div>
    `;
    const newField = typeof addDynamicField === 'function'
                     ? addDynamicField('extrasContainer', extraTemplate, (addedElement) => {
                         if (typeof updateCurrencySymbols === 'function') {
                             updateCurrencySymbols(); 
                         }
                         const firstInput = addedElement.querySelector('input[name="extraName"]');
                         if (firstInput) setTimeout(() => firstInput.focus(), 0);
                         // Añadir listener al botón de borrar
                         const removeBtn = addedElement.querySelector('.remove-extra-btn');
                         if (removeBtn) removeBtn.addEventListener('click', () => removeExtraField(removeBtn));
                          // Añadir listener formatNumber
                         addedElement.querySelectorAll('input[oninput="formatNumber(this)"]').forEach(input => {
                             input.addEventListener('input', (event) => {
                                 if(typeof formatNumber === 'function') formatNumber(event.target);
                             });
                         });
                     })
                     : null;
    return newField;
}

function removeExtraField(button) {
    const addButton = document.querySelector('.btn-estras'); 
    const removed = typeof removeDynamicField === 'function'
                    ? removeDynamicField(button, '.extra-group')
                    : false;
    if (removed && addButton) {
         setTimeout(() => addButton.focus(), 0);
    }
    return removed;
}

// Alterna "Nights" ↔ "Hours"
function toggleOneDayCharter(isOneDayActive) {
    const charterRateGroups = document.querySelectorAll('.charter-rate-group');
    charterRateGroups.forEach(group => {
        const fieldContainer = group.querySelector('.onedayNights, .onedaycharter');
        if (!fieldContainer) return;
        fieldContainer.innerHTML = ''; 
        let labelText, inputName, inputPlaceholder;
        if (isOneDayActive) {
            labelText = 'Hours:'; inputName = 'hours'; inputPlaceholder = 'Hours';
            fieldContainer.classList.add('onedaycharter'); fieldContainer.classList.remove('onedayNights');
        } else {
            labelText = 'Nights:'; inputName = 'nights'; inputPlaceholder = 'Nights';
            fieldContainer.classList.add('onedayNights'); fieldContainer.classList.remove('onedaycharter');
        }
        const label = document.createElement('label'); label.textContent = labelText;
        const input = document.createElement('input');
        input.type = 'text'; input.className = 'form-control'; input.name = inputName;
        input.placeholder = inputPlaceholder; input.required = true; input.inputMode = 'numeric'; input.pattern = '[0-9]*';
        const debounceFunc = typeof debounce === 'function' ? debounce : (fn) => fn; 
        input.addEventListener('input', debounceFunc((event) => {
            if (typeof formatNumber === 'function') { formatNumber(event.target); }
        }, 300));
        fieldContainer.appendChild(label); fieldContainer.appendChild(input);
    });
}

// Actualiza restricciones de UI
function updateUIConstraints() {
    const mixedCheckbox   = document.getElementById('enableMixedSeasons');
    const oneDayCheckbox  = document.getElementById('enableOneDayCharter');
    const plusButtons = document.querySelectorAll('.charter-rate-group button.add-rate-btn'); 
    if (!mixedCheckbox || !oneDayCheckbox) return;
    if (mixedCheckbox.checked) {
        oneDayCheckbox.disabled = true;
        plusButtons.forEach(btn => { btn.disabled = true; });
    } else {
        if (!oneDayCheckbox.checked) {
             oneDayCheckbox.disabled = false;
             plusButtons.forEach(btn => { btn.disabled = false; });
        }
    }
    if (oneDayCheckbox.checked) {
        mixedCheckbox.disabled = true;
         plusButtons.forEach(btn => { btn.disabled = true; });
    } else {
         if (!mixedCheckbox.checked) {
             mixedCheckbox.disabled = false;
         }
    }
}

// --- Función de Inicialización del Módulo ---
function initCalcInterface() {
    // Check si funciones compartidas existen
    if (!window.toggleContainer || !window.addDynamicField || !window.removeDynamicField) {
        console.warn('Las funciones UI compartidas (toggleContainer, addDynamicField, removeDynamicField) no están disponibles.');
    }
    
    // Listeners para checkboxes que controlan restricciones
    const mixedCheckbox  = document.getElementById('enableMixedSeasons');
    const oneDayCheckbox = document.getElementById('enableOneDayCharter');
    if (mixedCheckbox) {
        mixedCheckbox.addEventListener('change', updateUIConstraints);
    }
    if (oneDayCheckbox) {
        oneDayCheckbox.addEventListener('change', updateUIConstraints);
        oneDayCheckbox.addEventListener('change', function () {
            toggleOneDayCharter(this.checked);
        });
        toggleOneDayCharter(oneDayCheckbox.checked); // Inicializar
    }
    updateUIConstraints(); // Llamada inicial

    // Listeners para botones globales de añadir campos
     const addRateBtnInitial = document.querySelector('.add-rate-btn'); // El botón inicial '+'
     if (addRateBtnInitial) {
         // No quitar onclick si todavía existe en el HTML original
         // addRateBtnInitial.removeAttribute('onclick'); 
         addRateBtnInitial.addEventListener('click', () => addCharterRate(true));
     }
     const addExtraBtnGlobal = document.querySelector('.btn-estras'); // Botón 'Extras'
     if (addExtraBtnGlobal) {
         // No quitar onclick si todavía existe en el HTML original
         // addExtraBtnGlobal.removeAttribute('onclick'); 
         addExtraBtnGlobal.addEventListener('click', addExtraField);
     }
     
     // Listener para el botón de Guest Fee (si no se ha quitado el onclick)
     const addGuestFeeBtn = document.querySelector('button[onclick="addExtraPerPersonField()"]');
     if (addGuestFeeBtn && typeof addExtraPerPersonField === 'function') {
         // No añadir listener adicional si ya tiene onclick
         // addGuestFeeBtn.addEventListener('click', addExtraPerPersonField);
     }
     
}

// --- Ejecución de Inicialización ---
// Llamar a la inicialización después de que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalcInterface);
} else {
    initCalcInterface(); // Llamar inmediatamente si ya está listo
}


// --- Asignaciones Globales ---
// Asignar funciones al objeto window para que sean accesibles desde onclick en HTML
// o desde otros scripts como template.js
window.toggleCalcOptionalField = toggleCalcOptionalField; 
window.toggleDiscountField = toggleDiscountField;
window.toggleVATField = toggleVATField;
window.toggleAPAField = toggleAPAField; 
window.toggleRelocationField = toggleRelocationField;
window.toggleSecurityField = toggleSecurityField;
window.addCharterRate = addCharterRate;
window.removeCharterRate = removeCharterRate;
window.addExtraField = addExtraField;
window.removeExtraField = removeExtraField;
window.toggleOneDayCharter = toggleOneDayCharter; 
window.formatNumber = typeof formatNumber !== 'undefined' ? formatNumber : (el) => console.warn('formatNumber not defined', el); 
// Las funciones de ui.js (toggleContainer, addDynamicField, removeDynamicField) ya deberían ser globales.
