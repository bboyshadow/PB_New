/**
 * @file MixedTaxes.js
 * @description Gestiona la funcionalidad de "Mixed Taxes" para la calculadora de chárter con una interfaz unificada.
 * @see {@link ../../../../DOC/modules/calc/js/MixedTaxes.js.md}
 */

const MixedTaxes = {
    countryCount: 0,
    currencySymbol: '€', // Símbolo de moneda por defecto

    /**
     * Inicializa la funcionalidad de Mixed Taxes.
     */
    init: function() {
        // Actualizar símbolo de moneda en el cambio
        const currencySelect = document.getElementById('currency');
        if (currencySelect) {
            this._updateCurrencySymbol(currencySelect.value);
            currencySelect.addEventListener('change', (event) => {
                this._updateCurrencySymbol(event.target.value);
                this._updateVatFields(); // Re-renderizar campos si la moneda cambia
            });
        }

        // Listeners para los checkboxes de tasas para actualizar dinámicamente los campos de país
        const taxCheckboxes = ['vatCheck', 'apaCheck', 'apaPercentageCheck', 'relocationCheck', 'securityCheck'];
        taxCheckboxes.forEach(chkId => {
            const chk = document.getElementById(chkId);
            if (chk) {
                chk.addEventListener('change', () => {
                    const mixedTaxesCheckbox = document.getElementById('enableMixedTaxes');
                    if (mixedTaxesCheckbox && mixedTaxesCheckbox.checked) {
                        this._handleApaExclusivity(chk);
                        this._updateVatFields();
                    }
                });
            }
        });

        // Delegación de eventos para botones de eliminar
        const countriesContainer = document.getElementById('countriesContainer');
        if (countriesContainer) {
            countriesContainer.addEventListener('click', (event) => {
                if (event.target.closest('.remove-country-btn')) {
                    this.removeCountryField(event.target.closest('.country-tax-item-wrapper'));
                }
            });
        }
    },

    /**
     * Gestiona la exclusividad entre APA fijo y porcentual.
     * @param {HTMLElement} checkbox - El checkbox que ha cambiado.
     * @private
     */
    _handleApaExclusivity: function(checkbox) {
        const apaFixedCheck = document.getElementById('apaCheck');
        const apaPercentageCheck = document.getElementById('apaPercentageCheck');

        if (!apaFixedCheck || !apaPercentageCheck) return;

        if (checkbox.checked) {
            if (checkbox.id === 'apaCheck') {
                apaPercentageCheck.checked = false;
            } else if (checkbox.id === 'apaPercentageCheck') {
                apaFixedCheck.checked = false;
            }
        }
    },

    /**
     * Actualiza el símbolo de la moneda en la interfaz.
     * @param {string} currencyValue - El valor de la moneda seleccionada.
     * @private
     */
    _updateCurrencySymbol: function(currencyValue) {
        this.currencySymbol = currencyValue === '$USD' ? '$' : (currencyValue === '$AUD' ? 'A$' : '€');
        document.querySelectorAll('.currency-symbol').forEach(span => {
            span.textContent = this.currencySymbol;
        });
    },

    /**
     * Alterna la visibilidad de los controles de Mixed Taxes.
     * @param {HTMLElement} checkbox - El checkbox para habilitar/deshabilitar.
     */
    toggleVisibility: function(checkbox) {
        const addCountryBtn = document.getElementById('addCountryBtn');
        const countriesContainer = document.getElementById('countriesContainer');
        const optionalFieldsContainer = document.querySelector('.optional-fields');

        if (!addCountryBtn || !countriesContainer || !optionalFieldsContainer) return;

        const isEnabled = checkbox.checked;
        addCountryBtn.style.display = isEnabled ? 'inline-block' : 'none';
        countriesContainer.style.display = isEnabled ? 'flex' : 'none';

        // Ocultar/mostrar TODOS los campos de impuestos base
        optionalFieldsContainer.style.display = isEnabled ? 'none' : 'flex';

        if (isEnabled && countriesContainer.children.length === 0) {
            this.addCountryField();
        } else if (!isEnabled) {
            // Al desactivar, simplemente ocultamos los campos base, pero no limpiamos los de impuestos mixtos
            // para que mantengan su estado.
            document.querySelectorAll('.optional-field-container').forEach(field => {
                const checkId = field.id.replace('Field', 'Check');
                const check = document.getElementById(checkId);
                if(check) {
                    field.style.display = check.checked ? 'block' : 'none';
                }
            });
        }
    },

    /**
     * Añade un nuevo bloque de campos para un país.
     */
    addCountryField: function() {
        this.countryCount++;
        const container = document.getElementById('countriesContainer');
        const uniqueId = this.countryCount;

        const countryFieldHTML = `
            <div class="col-12 col-md-6 mb-3 country-tax-item-wrapper" id="country_wrapper_${uniqueId}">
                <div class="p-3 border rounded h-100">
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="countryName[]" placeholder="Country Name" required>
                        </div>
                        <div class="col-3">
                            <input type="number" class="form-control form-control-sm" name="nights[]" placeholder="Nights" min="1" required>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-sm btn-danger remove-country-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-2 country-tax-group" id="tax_group_${uniqueId}">
                    </div>
                </div>
            </div>`;

        container.insertAdjacentHTML('beforeend', countryFieldHTML);
        this._updateVatFields();
    },

    /**
     * Elimina el bloque de campos de un país.
     * @param {HTMLElement} element - El elemento wrapper del país a eliminar.
     */
    removeCountryField: function(element) {
        if (element) {
            element.remove();
        }
    },

    /**
     * Actualiza los campos de impuestos (VAT, APA) dentro de cada país.
     * @private
     */
    _updateVatFields: function() {
        const countryContainers = document.querySelectorAll('.country-tax-group');
        const taxCheckboxes = {
            'vatCheck': { label: 'VAT Rate', name: 'vat[]', placeholder: 'VAT %', isPercentage: true },
            'apaCheck': { label: 'APA (Fixed)', name: 'mixed_apa_amount[]', placeholder: 'APA Amount', isPercentage: false },
            'apaPercentageCheck': { label: 'APA (%)', name: 'mixed_apa_percentage[]', placeholder: 'APA %', isPercentage: true },
            'relocationCheck': { label: 'Relocation Fee', name: 'mixed_relocation_fee[]', placeholder: 'Relocation Fee', isPercentage: false },
            'securityCheck': { label: 'Security Deposit', name: 'mixed_security_deposit[]', placeholder: 'Security Deposit', isPercentage: false }
        };

        countryContainers.forEach(container => {
            const containerId = container.id.split('_').pop();
            container.innerHTML = ''; // Limpiar campos existentes para redibujar

            for (const chkId in taxCheckboxes) {
                const chk = document.getElementById(chkId);
                if (chk && chk.checked) {
                    const tax = taxCheckboxes[chkId];
                    const taxFieldHTML = `
                        <div class="col-md-4 mb-3">
                            <label for="${chkId}_${containerId}" class="form-label form-label-sm">${tax.label}:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm tax-input" id="${chkId}_${containerId}" name="${tax.name}" placeholder="${tax.placeholder}" oninput="formatNumber(this)" inputmode="decimal" pattern="[0-9]*\.?[0-9]+" required>
                                <span class="input-group-text">${tax.isPercentage ? '%' : this.currencySymbol}</span>
                            </div>
                        </div>`;
                    container.insertAdjacentHTML('beforeend', taxFieldHTML);
                }
            }
            // Añadir listeners con debounce para formatNumber a los inputs recién insertados
            const debounceFunc = typeof window.pbDebounce === 'function' ? window.pbDebounce : 
                                 typeof window.debounce === 'function' ? window.debounce : 
                                 typeof debounce === 'function' ? debounce : (fn) => fn;
            container.querySelectorAll('input[oninput="formatNumber(this)"]').forEach(inp => {
                inp.addEventListener('input', debounceFunc((event) => {
                    if (typeof window.formatNumber === 'function') window.formatNumber(event.target);
                }, 300));
            });
        });
    }
};

// Inicializar el script cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    MixedTaxes.init();
});