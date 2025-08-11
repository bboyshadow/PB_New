// ARCHIVO: app_yacht/template/js/template.js

// Variable global para la instancia de TemplateManager
let templateManager;

// --- Funciones específicas de Template ---

/**
 * Añade un nuevo grupo de tarifa charter, gestionando el foco y listeners.
 * @param {boolean} isFirst - Indica si es el primer grupo (para el botón +/-)
 * @param {Object|null} initialData - Datos opcionales para pre-rellenar el grupo
 */
function addCharterRateGroup(isFirst = false, initialData = null) {
    const container = document.getElementById('charterRatesContainer');
    if (!container) return;

    // Usar la utilidad createElement si está disponible globalmente, sino crear manualmente
    const createElement = (typeof window.createElement === 'function') ? window.createElement : document.createElement.bind(document);
    const createWithFragment = (typeof window.createWithFragment === 'function') ? window.createWithFragment : (cb, cont, pos) => { const frag = document.createDocumentFragment(); cb(frag); if (pos === 'append') cont.appendChild(frag); }; // Basic fallback

    createWithFragment(fragment => {
        const newGroup = createElement('div');
        const isOneDay = document.getElementById('enableOneDayCharter')?.checked;
        const nightsOrHoursName = isOneDay ? 'hours' : 'nights';
        const nightsOrHoursLabel = isOneDay ? 'Hours:' : 'Nights:';
        const nightsOrHoursValue = initialData ? (initialData[nightsOrHoursName] || '') : '';
        const guestsValue = initialData?.guests || '';
        const baseRateValue = initialData?.baseRate || '';
        const discountTypeValue = initialData?.discountType || 'percentage';
        const discountAmountValue = initialData?.discountAmount || '';
        const showDiscount = !!(initialData?.discountAmount || initialData?.discountType);

        newGroup.className = 'charter-rate-group row mb-3';
        // Usar innerHTML para la estructura compleja, pero añadir listeners después
        newGroup.innerHTML = `
            <div class="col-6 col-sm-3">
                <label>Guests:</label>
                <input type="text" class="form-control" name="guests" placeholder="Guests" required inputmode="numeric" pattern="[0-9]*" value="${guestsValue}">
            </div>
            <div class="col-6 col-sm-3 ${isOneDay ? 'onedaycharter' : 'onedayNights'}">
                <label>${nightsOrHoursLabel}</label>
                <input type="text" class="form-control" name="${nightsOrHoursName}" placeholder="${nightsOrHoursLabel.replace(':','')}" required inputmode="numeric" pattern="[0-9]*" value="${nightsOrHoursValue}">
            </div>
            <div class="col-12 col-sm-6 mt-2 mt-sm-0 d-flex align-items-end">
                <div class="flex-grow-1">
                    <label>Base Charter Rate:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="baseRate" placeholder="Enter base rate..." required inputmode="numeric" pattern="[0-9]*" value="${baseRateValue}">
                        <span class="input-group-text currency-symbol">€</span>
                    </div>
                </div>
                <div class="ms-2">
                    <button type="button" class="btn btn-secondary toggle-discount-btn">Discount</button>
                </div>
                <div class="ms-2">
                    ${isFirst ? '<button type="button" class="btn btn-secondary add-rate-btn" aria-label="Add charter rate">+</button>' : '<button type="button" class="btn btn-danger remove-rate-btn" aria-label="Remove charter rate">-</button>'}
                </div>
            </div>
            <div class="row discount-container mt-2" style="display: ${showDiscount ? 'flex' : 'none'};">
                 <div class="col-6">
                     <label class="visually-hidden">Discount Type</label>
                     <select class="form-control" name="discountType" required>
                         <option value="percentage" ${discountTypeValue === 'percentage' ? 'selected' : ''}>Percentage</option>
                         <option value="fixed" ${discountTypeValue === 'fixed' ? 'selected' : ''}>Fixed Amount</option>
                     </select>
                 </div>
                 <div class="col-6">
                      <label class="visually-hidden">Discount Amount</label>
                     <input type="text" class="form-control" name="discountAmount" placeholder="Discount" required inputmode="numeric" pattern="[0-9]*" value="${discountAmountValue}">
                    </div>
                </div>
            </div>
        `;
        fragment.appendChild(newGroup);

        // Añadir listeners DESPUÉS de que el HTML esté en el fragmento
        const toggleDiscountBtn = newGroup.querySelector('.toggle-discount-btn');
        if (toggleDiscountBtn && typeof window.toggleDiscountField === 'function') {
            toggleDiscountBtn.addEventListener('click', () => window.toggleDiscountField(toggleDiscountBtn));
        } else if (toggleDiscountBtn) {
             (window.AppYacht?.warn || console.warn)('Función global toggleDiscountField no encontrada.');
        }

        const removeBtn = newGroup.querySelector('.remove-rate-btn');
        if (removeBtn && typeof window.removeCharterRate === 'function') {
            removeBtn.addEventListener('click', () => window.removeCharterRate(removeBtn));
        } else if (removeBtn) {
             (window.AppYacht?.warn || console.warn)('Función global removeCharterRate no encontrada.');
        }
        
        const addBtn = newGroup.querySelector('.add-rate-btn');
        if (addBtn) { // addCharterRateGroup está definida localmente
            addBtn.addEventListener('click', () => addCharterRateGroup(true));
        }

        // Añadir listener para formatNumber
        newGroup.querySelectorAll('input[oninput]').forEach(input => {
             if (input.getAttribute('oninput') === 'formatNumber(this)') {
                 input.addEventListener('input', (event) => {
                     if(typeof window.formatNumber === 'function') window.formatNumber(event.target);
                 });
                 // Opcional: quitar el inline
                 // input.removeAttribute('oninput'); 
             }
         });

        // Mover foco al primer input si no es una restauración
        if (!initialData) {
            const firstInput = newGroup.querySelector('input[name="guests"]');
            if (firstInput) setTimeout(() => firstInput.focus(), 0);
        }

    }, container, 'append');

     // Actualizar símbolos de moneda después de añadir al DOM real
     if (typeof updateCurrencySymbols === 'function') {
         setTimeout(updateCurrencySymbols, 0); // Delay pequeño
     }
}

/**
 * Añade un nuevo extra, gestionando el foco y listeners.
 * @param {Object|null} initialData - Datos opcionales para pre-rellenar el grupo
 */
function addExtraGroup(initialData = null) {
    const container = document.getElementById('extrasContainer');
    if (!container) return;

    const createElement = (typeof window.createElement === 'function') ? window.createElement : document.createElement.bind(document);
    const createWithFragment = (typeof window.createWithFragment === 'function') ? window.createWithFragment : (cb, cont, pos) => { const frag = document.createDocumentFragment(); cb(frag); if (pos === 'append') cont.appendChild(frag); }; // Basic fallback

    createWithFragment(fragment => {
        const newGroup = createElement('div');
        const extraNameValue = initialData?.extraName || '';
        const extraCostValue = initialData?.extraCost || '';

        newGroup.className = 'col-12 col-md-6 col-lg-4 mb-3 extra-group my-2';
        newGroup.innerHTML = `
            <div class="row align-items-center">
                <div class="w-50 pe-1">
                     <label class="visually-hidden">Extra Name</label> 
                    <input type="text" class="form-control" name="extraName" placeholder="Extra name" required value="${extraNameValue}">
                </div>
                <div class="w-50 ps-0 d-flex align-items-center">
                    <div class="input-group">
                         <label class="visually-hidden">Extra Cost</label> 
                        <input type="text" class="form-control" name="extraCost" placeholder="Extra cost" required inputmode="numeric" pattern="[0-9]*" value="${extraCostValue}">
                        <span class="input-group-text extra-currency-symbol currency-symbol">€</span> 
                    </div>
                    <button type="button" class="btn btn-danger btn remove-extra-btn" aria-label="Remove extra">-</button>
                </div>
            </div>
        `;
        fragment.appendChild(newGroup);

        // Añadir listeners DESPUÉS de que el HTML esté en el fragmento
        const removeBtn = newGroup.querySelector('.remove-extra-btn');
        if (removeBtn && typeof window.removeExtraField === 'function') {
            removeBtn.addEventListener('click', () => window.removeExtraField(removeBtn));
        } else if (removeBtn) {
             (window.AppYacht?.warn || console.warn)('Función global removeExtraField no encontrada.');
        }

        // Añadir listener formatNumber
        newGroup.querySelectorAll('input[oninput]').forEach(input => {
             if (input.getAttribute('oninput') === 'formatNumber(this)') {
                 input.addEventListener('input', (event) => {
                     if(typeof window.formatNumber === 'function') window.formatNumber(event.target);
                 });
             }
         });

        // Mover foco al primer input si no es una restauración
        if (!initialData) {
            const firstInput = newGroup.querySelector('input[name="extraName"]');
            if (firstInput) setTimeout(() => firstInput.focus(), 0);
        }

    }, container, 'append');

     // Actualizar símbolos de moneda después de añadir al DOM real
     if (typeof updateCurrencySymbols === 'function') {
         setTimeout(updateCurrencySymbols, 0); // Delay pequeño
     }
}

/**
 * Restaura los datos estáticos (excepto yachtUrl) y dinámicos del formulario de plantillas desde localStorage.
 */
function restoreTemplateFormData() {
    const staticFieldsToRestore = ['templateSelector', 'enableOneDayCharter']; // Excluir 'yachtUrl'
    const staticSelectors = {
        // 'yachtUrl': '#yachtUrl', // Excluir 'yachtUrl'
        'templateSelector': '#templateSelector',
        'enableOneDayCharter': '#enableOneDayCharter' 
    };
    // Definir qué funciones usar para recrear los grupos (ahora locales)
    // Configuración de grupos dinámicos (solo tarifas, no extras)
    const dynamicGroupsConfig = [
        { key: 'rates', addFunction: addCharterRateGroup, containerId: 'charterRatesContainer' }
        // { key: 'extras', addFunction: addExtraGroup, containerId: 'extrasContainer' } // EXTRAS NO SE RESTAURAN
    ];

    const savedData = restoreFormData('template_form', {
        fields: staticFieldsToRestore,
        selectors: staticSelectors,
        dynamicGroups: dynamicGroupsConfig.map(g => ({ key: g.key })), // Solo pide restaurar datos de tarifas
        restoreCheckbox: true 
    });
    
    if (savedData) {
        
        // Limpiar contenedor de extras explícitamente al restaurar
        const extrasContainer = document.getElementById('extrasContainer');
        if (extrasContainer) extrasContainer.innerHTML = '';

        // Restaurar solo grupos de tarifas
        dynamicGroupsConfig.forEach(groupConfig => {
            if (groupConfig.key === 'rates') { // Asegurarse de que solo procesamos tarifas
                const container = document.getElementById(groupConfig.containerId);
                if (!container) return;
                container.innerHTML = ''; 

                if (Array.isArray(savedData[groupConfig.key]) && savedData[groupConfig.key].length > 0) {
                    savedData[groupConfig.key].forEach((itemData, index) => {
                        if (typeof groupConfig.addFunction === 'function') {
                            groupConfig.addFunction(index === 0, itemData); // isFirst siempre true para el primer elemento restaurado
                        }
                    });
                } else {
                    // Si no hay tarifas guardadas, añadir una por defecto
                    addCharterRateGroup(true); 
                }
            }
        });

        toggleCreateTemplateButton(); 
        const oneDayCheckbox = document.getElementById('enableOneDayCharter');
        if (oneDayCheckbox && templateManager && typeof templateManager.toggleOneDayCharter === 'function') {
             templateManager.toggleOneDayCharter(oneDayCheckbox.checked);
        }
         if (typeof updateCurrencySymbols === 'function') {
             updateCurrencySymbols();
         }
    } else {
         const ratesContainer = document.getElementById('charterRatesContainer');
         if (ratesContainer && ratesContainer.children.length === 0) {
             addCharterRateGroup(true);
         }
    }
}

// Callbacks para los eventos de TemplateManager
function handleTemplateCreated(data) {
}
function handleTemplateLoaded(data) {
}
function handleTemplateError(error) {
    // Solo mostrar en consola si es un error inesperado (no validación)
    if (!error.message || !error.message.includes('Validation failed')) {
        (window.AppYacht?.error || console.error)('Template error:', error);
    }
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.textContent = error.message || 'Template error';
        errorMessage.style.display = 'block';
    }
}

function toggleCreateTemplateButton() {
    const yachtUrlInput = document.getElementById('yacht-url');
    const createTemplateButton = document.getElementById('createTemplateButton');
    if (!yachtUrlInput || !createTemplateButton) return;
    const urlPattern = /^https?:\/\/(?:[\w-]+\.)+[\w-]{2,}(\/\S*)?$/;
    const yachtUrl = yachtUrlInput.value.trim();
    createTemplateButton.disabled = !(yachtUrl && urlPattern.test(yachtUrl));
}

/**
 * Guarda los datos estáticos (excepto yachtUrl) y dinámicos del formulario de plantillas en localStorage usando storage.js
 */
function saveTemplateFormData() {
    const staticFieldsToSave = ['templateSelector', 'enableOneDayCharter']; // Excluir 'yachtUrl'
    const staticSelectors = {
        // 'yachtUrl': '#yachtUrl', // Excluir 'yachtUrl'
        'templateSelector': '#templateSelector',
        'enableOneDayCharter': '#enableOneDayCharter'
    };
    const dynamicGroupsConfig = [
        {
            groupSelector: '.charter-rate-group',
            key: 'rates', 
            fields: ['guests', 'nights', 'hours', 'baseRate', 'discountType', 'discountAmount'] 
        },
        {
            groupSelector: '.extra-group',
            key: 'extras', 
            fields: ['extraName', 'extraCost']
        }
    ];

    saveFormData('template_form', {
        fields: staticFieldsToSave,
        selectors: staticSelectors,
        dynamicGroups: dynamicGroupsConfig, 
        saveCheckbox: true 
    });
    // console.log('Datos estáticos y dinámicos del formulario de plantilla guardados.'); // Opcional
}

/**
 * Opción para copiar el template HTML
 */
function copyTemplate() {
    let container = document.getElementById('result');
    if (!container || !container.innerHTML.trim()) {
         const fallbackContainer = document.getElementById('yachtInfoContainer');
         if (!fallbackContainer) {
            alert('No template content found to copy.');
            return;
         }
         container = fallbackContainer;
    }
    const htmlContent = container.innerHTML; 
    navigator.clipboard.writeText(htmlContent).then(() => {
        alert('Template content copied to clipboard.');
    }).catch(err => {
            (window.AppYacht?.error || console.error)('Error copying template:', err);
            alert('Unable to copy template.');
        });
}

/**
 * Cambia de template en el selector
 */
function onTemplateChange() {
    const templateSelector = document.getElementById('templateSelector');
    const resultContainer = document.getElementById('result'); 
    if (!templateSelector || !resultContainer) return;

    const selectedTemplate = templateSelector.value;
    if (!selectedTemplate) {
        resultContainer.innerHTML = ''; 
        saveTemplateFormData(); 
        return;
    }

    const yachtUrl = document.getElementById('yacht-url')?.value.trim() || '';
        if (!yachtUrl) {
        const params = new URLSearchParams({
            action: 'load_template_preview',
            template: selectedTemplate,
            nonce: ajaxTemplateData?.nonce || '' 
        });
        fetch(`${ajaxTemplateData.ajaxurl}?${params.toString()}`)
        .then(r => {
            if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
            return r.text();
        })
        .then(previewHtml => {
            resultContainer.innerHTML = previewHtml;
        })
        .catch(err => {
            (window.AppYacht?.error || console.error)('Error loading template preview:', err);
            resultContainer.innerHTML = '<p>Error loading template preview.</p>';
        });
    } else {
        // No hay URL de yate, solo cargar plantilla vacía sin llamar createTemplate
        const params = new URLSearchParams({
            action: 'load_template_preview',
            template: selectedTemplate,
            nonce: ajaxTemplateData?.nonce || '' 
        });
        fetch(`${ajaxTemplateData.ajaxurl}?${params.toString()}`)
        .then(r => {
            if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
            return r.text();
        })
        .then(previewHtml => {
            resultContainer.innerHTML = previewHtml;
        })
        .catch(err => {
            (window.AppYacht?.error || console.error)('Error loading template preview:', err);
            resultContainer.innerHTML = '<p>Error loading template preview.</p>';
        });
    }
     saveTemplateFormData(); 
}

// --- Inicialización y Listeners Globales ---
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar el gestor de plantillas
    templateManager = new TemplateManager({
        ajaxUrl: ajaxTemplateData?.ajaxurl || '',
        nonce: ajaxTemplateData?.nonce || '',
        onTemplateCreated: handleTemplateCreated,
        onTemplateLoaded: handleTemplateLoaded,
        onError: handleTemplateError
    });
    
    // Configurar event listeners para elementos estáticos
    const createTemplateButton = document.getElementById('createTemplateButton');
    if (createTemplateButton) {
        createTemplateButton.addEventListener('click', () => { templateManager.createTemplate().catch(() => {}); });
    }

    const templateSelector = document.getElementById('templateSelector');
    if (templateSelector) {
        templateSelector.addEventListener('change', onTemplateChange);
    }

    const oneDayCharterCheckbox = document.getElementById('enableOneDayCharter');
    if (oneDayCharterCheckbox) {
        oneDayCharterCheckbox.addEventListener('change', function () {
            // Llamar a la función global de interfaz.js si existe
            if (typeof window.toggleOneDayCharter === 'function') {
                 window.toggleOneDayCharter(this.checked); 
            } else {
                 (window.AppYacht?.warn || console.warn)('toggleOneDayCharter no está definida globalmente');
            }
            // Actualizar la propia clase TemplateManager si es necesario
            templateManager.toggleOneDayCharter(this.checked); 
            
            // Remover la llamada automática a createTemplate para evitar "Validation failed"
            // El usuario puede usar el botón manualmente si lo desea
            saveTemplateFormData(); 
        });
    }

    const yachtUrlInput = document.getElementById('yacht-url');
    if (yachtUrlInput) {
        yachtUrlInput.addEventListener('input', debounce(() => {
            toggleCreateTemplateButton();
            saveTemplateFormData(); 
        }, 300));
        // Remover la llamada automática a createTemplate al cambiar URL
        // para evitar validaciones no deseadas
        yachtUrlInput.addEventListener('change', () => {
            saveTemplateFormData();
        });

        toggleCreateTemplateButton(); 
        
        restoreTemplateFormData(); // Restaurar datos al cargar

        const templateSelectorInput = document.getElementById('templateSelector');
        if (templateSelectorInput) {
            templateSelectorInput.addEventListener('change', saveTemplateFormData);
        }
    } else {
         // Si no hay input de URL, restaurar igualmente (puede haber otros campos guardados)
         restoreTemplateFormData();
    }

     // Listeners delegados para guardar datos al cambiar campos dinámicos
     const ratesContainer = document.getElementById('charterRatesContainer');
     if (ratesContainer) {
         ratesContainer.addEventListener('input', debounce(saveTemplateFormData, 300));
         ratesContainer.addEventListener('change', saveTemplateFormData); 
     }
     const extrasContainer = document.getElementById('extrasContainer');
     if (extrasContainer) {
         extrasContainer.addEventListener('input', debounce(saveTemplateFormData, 300));
     }

     // Listener para el botón global "Add Extra" (si existe en la página)
     const addExtraBtnGlobal = document.querySelector('button[onclick="addExtraField()"]'); 
     if(addExtraBtnGlobal) {
         addExtraBtnGlobal.addEventListener('click', () => {
             if(typeof window.addExtraField === 'function') {
                 window.addExtraField(); // Llama a la función global de interfaz.js
                 setTimeout(saveTemplateFormData, 50);
             } else {
                 (window.AppYacht?.error || console.error)('addExtraField no está definida globalmente');
             }
         });
     }
});

// Asegurar que copyTemplate esté disponible globalmente
window.copyTemplate = copyTemplate;
