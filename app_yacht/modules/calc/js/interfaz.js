// ARCHIVO modules\calc\js\interfaz.js

// --- Definiciones de Funciones ---

/**
 * Alterna la visibilidad de un campo opcional de la calculadora,
 * maneja la exclusividad APA, actualiza ARIA y mueve el foco.
 * @param {string} fieldId - El ID del contenedor del campo a mostrar/ocultar.
 */
/**
 * Alterna la visibilidad de un campo opcional de la calculadora y gestiona exclusividades (APA vs APA%, VAT vs VAT Mix).
 * Actualiza atributos ARIA, sincroniza estados de checkboxes relacionados y mueve el foco al primer input visible.
 * 
 * Efectos colaterales:
 * - Muestra/oculta contenedores en el DOM.
 * - Activa/desactiva checkboxes de exclusividad.
 * - Puede crear dinámicamente países para VAT Mix si el contenedor está vacío.
 * - Actualiza el estado de aria-expanded en el checkbox controlador.
 * 
 * @function toggleCalcOptionalField
 * @param {string} fieldId - ID del contenedor a mostrar/ocultar.
 * @returns {boolean} true si el contenedor queda visible; false en caso contrario.
 */
function toggleCalcOptionalField(fieldId) {
    // Obtener referencias de checkboxes globales para VAT y VAT Mix
    const vatCheck = document.getElementById('vatCheck');
    const vatMixCheck = document.getElementById('vatRateMix');
    // Callback para manejar la exclusividad entre APA y APA Percentage, y entre VAT y VAT Mix
    /**
     * Callback interno que maneja la exclusividad entre campos APA/APA% y VAT/VAT Mix cuando se muestra/oculta un bloque.
     * Actualiza checkboxes relacionados, visibilidad de contenedores y atributos ARIA según corresponda.
     * @param {boolean} isVisible - Indica si el contenedor objetivo quedó visible tras el toggle.
     * @param {HTMLElement} fieldElement - Elemento del contenedor que se está mostrando/ocultando.
     * @returns {void}
     */
    const handleExclusivity = (isVisible, fieldElement) => {
        if (!isVisible || !fieldElement) return; 
        
        const apaCheck = document.getElementById('apaCheck');
        const apaPercentageCheck = document.getElementById('apaPercentageCheck');
        
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
            const vatMixField = document.getElementById('vatCountriesContainer');
            const vatField = document.getElementById('vatField');
            const vatInputGroup = vatField ? vatField.querySelector('.input-group') : null;
    
            if (fieldId === 'vatField' && vatCheck.checked) {
                // Activamos VAT fijo, desactivamos Mix
                vatMixCheck.checked = false;
                // Mostrar únicamente el switch Mix cuando VAT está activo; el botón "+" aparecerá solo si Mix se activa
                const mixSwitchContainer = document.querySelector('.vat-mix-controls input#vatRateMix')?.closest('.vat-mix-controls');
                if (mixSwitchContainer) mixSwitchContainer.style.display = 'inline-block';
                // Asegurarnos de ocultar el botón + hasta que Mix esté activo
                const addBtnInit = document.getElementById('addVatCountryBtn');
                if (addBtnInit) addBtnInit.style.display = 'none';
                
                if (vatMixField) {
                    vatMixField.style.display = 'none';
                    const vatMixCheckbox = document.querySelector('input[aria-controls="vatCountriesContainer"]');
                    if (vatMixCheckbox) vatMixCheckbox.setAttribute('aria-expanded', 'false');
                }
                if (vatInputGroup) {
                    vatInputGroup.style.display = '';
                }
                // Restaurar etiqueta "VAT Rate:" cuando Mix se desactiva
                const vatLabel = vatField.querySelector('label');
                if (vatLabel) {
                    vatLabel.style.display = '';
                }
            } else if (fieldId === 'vatField' && !vatCheck.checked && !vatMixCheck.checked) {
                // Ocultar el switch Mix y el botón + cuando VAT se desactiva y Mix no está activo
                document.querySelectorAll('.vat-mix-controls').forEach(el => el.style.display = 'none');
    
            } else if (fieldId === 'vatCountriesContainer' && vatMixCheck.checked) {
                // Activamos Mix: ocultamos solo el input de tasa fija pero mantenemos visible el contenedor para mostrar el switch y el botón +
                // Mantenemos VAT Rate activo para que los controles Mix permanezcan visibles
                vatCheck.checked = true;
                // Mostrar botón + al activar Mix
                const addBtn = document.getElementById('addVatCountryBtn');
                if (addBtn) addBtn.style.display = 'inline-block';
                // Ocultar el contenedor vatField cuando Mix está activo
                if (vatField) {
                    vatField.style.display = 'none';
                }
            } else if (fieldId === 'vatCountriesContainer' && !vatMixCheck.checked) {
                // Se desactiva Mix: restaurar input fijo y contenedor VAT completo
                // Ocultamos botón +
                const addBtn = document.getElementById('addVatCountryBtn');
                if (addBtn) addBtn.style.display = 'none';
                if (vatField) {
                    vatField.style.display = 'flex';
                }
                if (vatInputGroup) {
                    vatInputGroup.style.display = '';                   
                }
            }
        }
    };
    
    const field = document.getElementById(fieldId);
    if (!field) {
        (window.AppYacht?.error || console.error)(`Element with ID ${fieldId} not found.`);
        return false;
    }
    
    // Determinar el checkbox que controla este fieldId
    const controlCheckbox = document.querySelector(`input[aria-controls="${fieldId}"]`);
    if (!controlCheckbox) {
         (window.AppYacht?.warn || console.warn)(`Checkbox de control para ${fieldId} no encontrado.`);
    }

    // Verificar estado del checkbox y actualizar visibilidad en consecuencia
    let isVisible = false;
    if (controlCheckbox) {
        isVisible = controlCheckbox.checked;
    } else {
        // Fallback a la lógica anterior si no hay checkbox de control
        isVisible = field.style.display === 'none' || field.style.display === '';
    }
    
    const displayType = field.classList.contains('row') || field.classList.contains('d-flex') ? 'flex' : 'block'; 
    field.style.display = isVisible ? displayType : 'none'; 

    // Ejecutar lógica de exclusividad
    handleExclusivity(isVisible, field);

    // Caso especial: vatCountriesContainer y relocationAutoContainer
    if (fieldId === 'vatCountriesContainer') {
        // Agregar automáticamente dos campos prellenados si se muestra vatCountriesContainer y está vacío
        if (isVisible && field.children.length === 0) {
            VatRateMix.addCountryField();
            VatRateMix.addCountryField();
        }
        
        // Manejar visibilidad del botón Add Country para VAT Mix
        const addBtn = document.getElementById('addVatCountryBtn');
        if (addBtn) {
            addBtn.style.display = isVisible ? 'inline-block' : 'none';
        }
    } else if (fieldId === 'relocationAutoContainer') {
        // Verificar si el relocationAutoCheck está marcado
        const relocationAutoCheck = document.getElementById('relocationAutoCheck');
        if (relocationAutoCheck) {
            isVisible = relocationAutoCheck.checked;
            field.style.display = isVisible ? displayType : 'none';
        }
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
    
    // Sincronizar visibilidad de los controles Mix según el estado actual de VAT
    if (vatCheck && vatMixCheck) {
        document.querySelectorAll('.vat-mix-controls').forEach(el => {
            if (el.id === 'addVatCountryBtn') {
                // Botón + solo visible si Mix está activo
                el.style.display = vatMixCheck.checked ? 'inline-block' : 'none';
            } else {
                // Switch Mix visible cuando VAT está activo
                el.style.display = vatCheck.checked ? 'inline-block' : 'none';
            }
        });
    }

    return isVisible;
}


// Function to toggle discount fields and make them required if shown
/**
 * Muestra u oculta los campos de descuento dentro de un grupo de tarifa
 * y limpia sus valores cuando se ocantan.
 *
 * Efectos colaterales: cambia display del contenedor, puede mover el foco al primer campo del bloque.
 * @param {HTMLButtonElement} button - Botón dentro del grupo .charter-rate-group que dispara el toggle.
 * @returns {boolean} true si el contenedor queda visible; false en caso contrario.
 */
function toggleDiscountField(button) {
    const charterRateGroup = button.closest('.charter-rate-group');
    if (!charterRateGroup) return;
    const discountContainer = charterRateGroup.querySelector('.discount-container');
    if (!discountContainer) return;
    /**
     * Limpia los valores de los campos de descuento dentro del contenedor dado.
     * @param {HTMLElement} container - Contenedor .discount-container cuyo estado cambia.
     * @returns {void}
     */
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
/**
 * Muestra u oculta los campos de promoción dentro de un grupo de tarifa
 * y limpia sus valores cuando se ocantan.
 *
 * Efectos colaterales: cambia display del contenedor, puede mover el foco al primer campo del bloque.
 * @param {HTMLButtonElement} button - Botón dentro del grupo .charter-rate-group que dispara el toggle.
 * @returns {boolean} true si el contenedor queda visible; false en caso contrario.
 */
function togglePromotionField(button) {
    const charterRateGroup = button.closest('.charter-rate-group');
    if (!charterRateGroup) return;
    const promotionContainer = charterRateGroup.querySelector('.promotion-container');
    if (!promotionContainer) return;
    /**
     * Limpia los valores de los campos de promoción dentro del contenedor dado.
     * @param {HTMLElement} container - Contenedor .promotion-container cuyo estado cambia.
     * @returns {void}
     */
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
/**
 * Muestra/oculta el bloque de VAT (tasa fija) desde un botón y sincroniza el checkbox correspondiente.
 * Limpia valores al ocultar, mueve el foco al primer input al mostrar y respeta la exclusividad con VAT Mix.
 *
 * @function toggleVATField
 * @param {HTMLButtonElement} button - Botón que dispara el toggle del bloque VAT.
 * @returns {boolean} true si el bloque queda visible; false en caso contrario.
 */
function toggleVATField(button) {
    const fieldToToggle = document.getElementById('vatField'); 
    if (!fieldToToggle) return;
    /**
     * Limpia los valores del campo de VAT (tasa fija) dentro del contenedor dado.
     * @param {HTMLElement} container - Contenedor del bloque VAT cuyo estado cambia.
     * @returns {void}
     */
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
/**
 * Advertencia de uso: esta función está asociada a un botón para APA fijo y no se utiliza en la UI.
 * El manejo de APA y APA Percentage debe realizarse mediante sus checkboxes con toggleCalcOptionalField.
 *
 * @function toggleAPAField
 * @param {HTMLButtonElement} button - Botón no soportado para alternar el bloque de APA.
 * @returns {void}
 */
function toggleAPAField(button) {
     (window.AppYacht?.warn || console.warn)('toggleAPAField called from button is not implemented, use the checkbox with toggleCalcOptionalField');
}

// Function to toggle Relocation field (asociada a un botón)
/**
 * Muestra/oculta el bloque de Relocation desde un botón y sincroniza el checkbox correspondiente.
 * Al ocultar, limpia los inputs del bloque; al mostrar, mueve el foco al primer input disponible.
 *
 * @function toggleRelocationField
 * @param {HTMLButtonElement} button - Botón que dispara el toggle del bloque de Relocation.
 * @returns {boolean} true si el bloque queda visible; false si queda oculto o falla.
 */
function toggleRelocationField(button) {
     const fieldToToggle = document.getElementById('relocationField');
     if (!fieldToToggle) return;
    /**
     * Limpia los valores del bloque de Relocation cuando se oculta.
     * @param {HTMLElement} container - Contenedor del bloque de Relocation.
     * @returns {void}
     */
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
/**
 * Muestra/oculta el bloque de Security desde un botón y sincroniza el checkbox correspondiente.
 * Al ocultar, limpia los inputs del bloque; al mostrar, mueve el foco al primer input disponible.
 *
 * @function toggleSecurityField
 * @param {HTMLButtonElement} button - Botón que dispara el toggle del bloque de Security.
 * @returns {boolean} true si el bloque queda visible; false si queda oculto o falla.
 */
function toggleSecurityField(button) {
     const fieldToToggle = document.getElementById('securityField');
     if (!fieldToToggle) return;
    /**
     * Limpia los valores del bloque de Security cuando se oculta.
     * @param {HTMLElement} container - Contenedor del bloque de Security.
     * @returns {void}
     */
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
/**
 * Crea y añade dinámicamente un nuevo grupo de "Charter Rate" al contenedor.
 * Configura listeners para formato numérico con debounce y para mostrar/ocultar descuento y promoción.
 * Gestiona el botón "+" inicial y los botones "-" para eliminar grupos existentes.
 *
 * Accesibilidad: al añadir un grupo, se mueve el foco al primer input del nuevo grupo.
 *
 * @function addCharterRate
 * @param {boolean} [isFirst=false] - Indica si es el primer grupo (muestra botón "+" en el bloque principal).
 * @returns {HTMLElement|null} El elemento de grupo añadido o null si no se pudo añadir.
 */
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
                         // Añadir listener formatNumber con debounce
                         addedElement.querySelectorAll('input[oninput="formatNumber(this)"]').forEach(input => {
                             const debounceFunc = typeof window.pbDebounce === 'function' ? window.pbDebounce : 
                                                  typeof window.debounce === 'function' ? window.debounce : 
                                                  typeof debounce === 'function' ? debounce : 
                                                  (fn) => fn;
                             input.addEventListener('input', debounceFunc((event) => {
                                 if (typeof formatNumber === 'function') formatNumber(event.target);
                             }, 300));
                         });
                     })
                     : null;
    return newField;
}

// Yacht info functionality is now handled by the yachtinfo module

/**
 * Remueve un grupo de tarifas del DOM utilizando utilidades de UI compartidas.
 * Si la eliminación tiene éxito, devuelve el foco al botón de añadir tarifa.
 * 
 * @function removeCharterRate
 * @param {HTMLButtonElement} button - Botón que pertenece al grupo de tarifa a eliminar.
 * @returns {boolean} true si se removió el grupo; false en caso contrario.
 */
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
                          // Añadir listener formatNumber con debounce
                         addedElement.querySelectorAll('input[oninput="formatNumber(this)"]').forEach(input => {
                             const debounceFunc = typeof window.pbDebounce === 'function' ? window.pbDebounce : 
                                                  typeof window.debounce === 'function' ? window.debounce : 
                                                  typeof debounce === 'function' ? debounce : 
                                                  (fn) => fn;
                             input.addEventListener('input', debounceFunc((event) => {
                                 if (typeof formatNumber === 'function') formatNumber(event.target);
                             }, 300));
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
        
        // Hide and clear promotion fields when One Day Charter is active
        const promotionContainer = group.querySelector('.promotion-container');
        if (promotionContainer) {
            if (isOneDayActive) {
                // Hide promotion container and clear values
                promotionContainer.style.display = 'none';
                const promotionNights = promotionContainer.querySelector('[name="promotionNights"]');
                if (promotionNights) promotionNights.value = '';
                
                // Also hide the promotion toggle button
                const promotionBtn = group.querySelector('.toggle-promotion-btn');
                if (promotionBtn) {
                    promotionBtn.style.display = 'none';
                }
            } else {
                // Show promotion button again when switching back to Nights mode
                const promotionBtn = group.querySelector('.toggle-promotion-btn');
                if (promotionBtn) {
                    promotionBtn.style.display = '';
                }
                // Note: We don't automatically show the promotion container,
                // the user needs to click the button again if they want it
            }
        }
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
        (window.AppYacht?.warn || console.warn)('Shared UI functions (toggleContainer, addDynamicField, removeDynamicField) are not available.');
    }
    
    // Ocultar los controles de vat-mix-controls al inicio
    document.querySelectorAll('.vat-mix-controls').forEach(el => el.style.display = 'none');

    // Listener dedicado para mantener sincronizados los controles Mix
    const vatCheckEl = document.getElementById('vatCheck');
    const vatMixEl  = document.getElementById('vatRateMix');
    const addVatBtn = document.getElementById('addVatCountryBtn');
    if (vatCheckEl && vatMixEl) {
        vatCheckEl.addEventListener('change', () => {
            // Mostrar/ocultar controles según tipo
            document.querySelectorAll('.vat-mix-controls').forEach(el => {
                // El botón + solo se muestra si Mix está activo
                if (el.id === 'addVatCountryBtn') {
                    el.style.display = (vatCheckEl.checked && vatMixEl.checked) ? 'inline-block' : 'none';
                } else {
                    // El switch Mix se muestra cuando VAT está activo
                    el.style.display = vatCheckEl.checked ? 'inline-block' : 'none';
                }
            });
            // Si se desactiva VAT, desactivar Mix automáticamente
            if (!vatCheckEl.checked) {
                vatMixEl.checked = false;
                // Forzar ocultar contenedor de países
                const container = document.getElementById('vatCountriesContainer');
                if (container) container.style.display = 'none';
            }
        });
        // Garantizar sincronización cuando se cambie el estado de Mix
        vatMixEl.addEventListener('change', () => {
            document.querySelectorAll('.vat-mix-controls').forEach(el => {
                if (el.id === 'addVatCountryBtn') {
                    // Botón + solo visible si VAT y Mix están activos
                    el.style.display = (vatCheckEl.checked && vatMixEl.checked) ? 'inline-block' : 'none';
                }
            });
            // Si VAT está desactivado, ocultar todos los controles
            if (!vatCheckEl.checked) {
                document.querySelectorAll('.vat-mix-controls').forEach(el => el.style.display = 'none');
            }
        });
    }
    
    // Listeners for checkboxes that control constraints
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
        toggleOneDayCharter(oneDayCheckbox.checked); // Initialize
    }
    updateUIConstraints(); // Initial call

    // Listeners for global buttons to add fields
     const addRateBtnInitial = document.querySelector('.add-rate-btn'); // The initial "+" button
     if (addRateBtnInitial) {
         // Do not remove onclick if it still exists in the original HTML
         // addRateBtnInitial.removeAttribute('onclick'); 
         addRateBtnInitial.addEventListener('click', () => addCharterRate(true));
     }
     const addExtraBtnGlobal = document.querySelector('.btn-estras'); // 'Extras' Button
     if (addExtraBtnGlobal) {
         // Do not remove onclick if it still exists in the original HTML
         // addExtraBtnGlobal.removeAttribute('onclick'); 
         addExtraBtnGlobal.addEventListener('click', addExtraField);
     }
     
     // Listener for the Guest Fee button (if onclick hasn't been removed)
     const addGuestFeeBtn = document.querySelector('button[onclick="addExtraPerPersonField()"]');
     if (addGuestFeeBtn && typeof addExtraPerPersonField === 'function') {
         // Do not add an extra listener if it already has onclick
         // addGuestFeeBtn.addEventListener('click', addExtraPerPersonField);
     }
     
}

// --- Initialization Execution ---
// Call initialization after the DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalcInterface);
} else {
    initCalcInterface(); // Call immediately if it's already ready
}


// --- Global Assignments ---
// Assign functions to the window object so they are accessible from HTML onclick
// or from other scripts such as template.js
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
window.formatNumber = typeof formatNumber !== 'undefined' ? formatNumber : (el) => (window.AppYacht?.warn || console.warn)('formatNumber not defined', el); 
// Las funciones de ui.js (toggleContainer, addDynamicField, removeDynamicField) ya deberían ser globales.
