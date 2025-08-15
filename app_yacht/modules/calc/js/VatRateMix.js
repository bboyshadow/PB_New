/**
 * @file VatRateMix.js
 * @description Gestiona la funcionalidad de "VAT Rate Mix" para la calculadora de chárter, enfocada solo en VAT proporcional por país.
 */

const VatRateMix = {
    countryCount: 0,
    currencySymbol: '€', // Símbolo de moneda por defecto

    /**
     * Inicializa la funcionalidad de VAT Rate Mix.
     */
    init: function() {
        // Actualizar símbolo de moneda en el cambio
        const currencySelect = document.getElementById('currency');
        if (currencySelect) {
            this._updateCurrencySymbol(currencySelect.value);
            currencySelect.addEventListener('change', (event) => {
                this._updateCurrencySymbol(event.target.value);
            });
        }

        // Delegación de eventos para botones de eliminar
    const countriesContainer = document.getElementById('vatCountriesContainer');
    if (countriesContainer) {
        countriesContainer.addEventListener('click', (event) => {
            if (event.target.closest('.remove-country-btn')) {
                this.removeCountryField(event.target.closest('.country-vat-item-wrapper'));
            }
        });
        countriesContainer.addEventListener('input', (event) => {
            if (event.target.name === 'vatNights[]') {
                this.handleVatNightsInput(event.target);
            }
        });
    }

    // Listener para el input total de noches
    const totalNightsInput = document.querySelector('input[name="nights"]');
    if (totalNightsInput) {
        totalNightsInput.addEventListener('input', () => {
            this.updateVatNightsFromTotal();
        });
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
     * Alterna la visibilidad de los controles de VAT Rate Mix.
     * @param {HTMLElement} checkbox - El checkbox para habilitar/deshabilitar.
     */
    toggleVisibility: function(checkbox) {
        const addCountryBtn = document.getElementById('addVatCountryBtn');
        const countriesContainer = document.getElementById('vatCountriesContainer');

        if (!addCountryBtn || !countriesContainer) return;

        const isEnabled = checkbox.checked;
        addCountryBtn.style.display = isEnabled ? 'inline-block' : 'none';
        countriesContainer.style.display = isEnabled ? 'flex' : 'none';

        // No ocultar campos globales

        
    },

    /**
     * Añade un nuevo bloque de campos para un país.
     */
    addCountryField: function() {
        this.countryCount++;
        const container = document.getElementById('vatCountriesContainer');
        const uniqueId = this.countryCount;

        const countryFieldHTML = `
            <div class="col-12 col-md-6 country-vat-item-wrapper" id="vat_country_wrapper_${uniqueId}">
                <div class="p-2 border-vat rounded h-100">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="vat_country_${uniqueId}" class="form-label form-label-sm">Country:</label>
                            <input type="text" class="form-control form-control-sm" id="vat_country_${uniqueId}" name="vatCountryName[]" placeholder="Country Name" required>
                        </div>
                        <div class="col-md-3">
                            <label for="vat_nights_${uniqueId}" class="form-label form-label-sm">Nights:</label>
                            <input type="number" class="form-control form-control-sm" id="vat_nights_${uniqueId}" name="vatNights[]" placeholder="Nights" min="1" required>
                        </div>
                        <div class="col-md-4 country-vat-group" id="vat_group_${uniqueId}">
                            <label for="vat_${uniqueId}" class="form-label form-label-sm">VAT Rate:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm tax-input" id="vat_${uniqueId}" name="vatRate[]" placeholder="VAT %" oninput="formatNumber(this)" inputmode="decimal" pattern="[0-9]*\.?[0-9]+" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-auto align-self-end">
                            <button type="button" class="btn btn-sm btn-danger remove-country-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

        container.insertAdjacentHTML('beforeend', countryFieldHTML);
        // Añadir listener con debounce a campos con oninput="formatNumber(this)" dentro del nuevo bloque
        const wrapper = document.getElementById(`vat_country_wrapper_${uniqueId}`);
        if (wrapper) {
            const debounceFunc = typeof window.pbDebounce === 'function' ? window.pbDebounce : 
                                 typeof window.debounce === 'function' ? window.debounce : 
                                 typeof debounce === 'function' ? debounce : (fn) => fn;
            wrapper.querySelectorAll('input[oninput="formatNumber(this)"]').forEach(inp => {
                inp.addEventListener('input', debounceFunc((event) => {
                    if (typeof window.formatNumber === 'function') window.formatNumber(event.target);
                }, 300));
            });
        }
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
     * Maneja cambios en los inputs de noches VAT para sincronizar con el total.
     * Cuando hay exactamente 2 países, ajusta automáticamente el otro campo.
     * 
     * @function handleVatNightsInput
     * @param {HTMLInputElement} input - El elemento input que cambió
     * @returns {void}
     * 
     * @description
     * - Calcula noches restantes basado en el total
     * - Actualiza automáticamente el campo complementario
     * - Solo funciona cuando hay exactamente 2 países configurados
     */
    handleVatNightsInput: function(input) {
        const totalNightsInput = document.querySelector('input[name="nights"]');
        const totalNights = parseFloat(totalNightsInput.value) || 0;
        const nightsInputs = document.querySelectorAll('input[name="vatNights[]"]');
        if (nightsInputs.length !== 2) return;
        const otherInput = Array.from(nightsInputs).find(inp => inp !== input);
        const currentValue = parseFloat(input.value) || 0;
        otherInput.value = Math.max(0, totalNights - currentValue);
    },

    /**
     * Actualiza las noches VAT cuando cambia el total de noches en la calculadora principal.
     * Ajusta automáticamente el segundo país manteniendo el valor del primero.
     * 
     * @function updateVatNightsFromTotal
     * @returns {void}
     * 
     * @description
     * - Lee el total de noches de la calculadora principal
     * - Mantiene el valor del primer país
     * - Calcula automáticamente las noches del segundo país
     * - Solo funciona cuando hay exactamente 2 países configurados
     */
    updateVatNightsFromTotal: function() {
        const totalNights = parseFloat(document.querySelector('input[name="nights"]').value) || 0;
        const nightsInputs = document.querySelectorAll('input[name="vatNights[]"]');
        if (nightsInputs.length !== 2) return;
        const firstValue = parseFloat(nightsInputs[0].value) || 0;
        nightsInputs[1].value = Math.max(0, totalNights - firstValue);
    }

};

// Inicializar el script cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    VatRateMix.init();
});