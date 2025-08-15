// ARCHIVO modules\calc\js\extraPerPerson.js

/**
 * Funciones para manejar los guest fees en la calculadora
 */

/**
 * Agrega un bloque de "Guest Fee" al contenedor de extras.
 * El bloque contiene: nombre, número de huéspedes, coste por huésped y total calculado.
 * Actualiza los símbolos de moneda mediante updateCurrencySymbols().
 * 
 * @function addExtraPerPersonField
 * @returns {void}
 * 
 * @description
 * - Crea un nuevo div con estructura completa de Guest Fee
 * - Incluye inputs con validación numérica y formato
 * - El campo Total es readonly y se calcula automáticamente
 * - Conecta eventos oninput para formateo y cálculo
 * - Sincroniza símbolos de moneda mediante updateCurrencySymbols()
 */
function addExtraPerPersonField() {
    const container = document.getElementById('extrasContainer');
    const newExtra = document.createElement('div');
    newExtra.classList.add('col-12', 'mb-3', 'extra-per-person-group', 'my-2');

    newExtra.innerHTML = `
        <div class="row align-items-center">
            <div class="col-12 mb-2">
                <strong>Guest Fee</strong>
            </div>
            <div class="col-md-4 mb-2">
                <label>Name:</label>
                <input type="text" class="form-control" name="extraPerPersonName" placeholder="Extra Name" required>
            </div>
            <div class="col-md-2 mb-2">
                <label>Guests:</label>
                <input type="text" class="form-control" name="extraPerPersonGuests" placeholder="Number of guests" required oninput="formatNumber(this); calculateExtraPerPersonTotal(this)" inputmode="numeric" pattern="[0-9]*">
            </div>
            <div class="col-md-3 mb-2">
                <label>Cost per guest:</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="extraPerPersonCost" placeholder="Cost" required oninput="formatNumber(this); calculateExtraPerPersonTotal(this)" inputmode="numeric" pattern="[0-9]*">
                    <span class="input-group-text extra-currency-symbol">€</span>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <label>Total:</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="extraPerPersonTotal" placeholder="Total" readonly>
                    <span class="input-group-text extra-currency-symbol">€</span>
                </div>
            </div>
            <div class="col-md-1 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger" onclick="removeExtraPerPersonField(this)">-</button>
            </div>
        </div>
    `;
    container.appendChild(newExtra);
    updateCurrencySymbols();
}

/**
 * Elimina un bloque de "Guest Fee".
 * Busca el contenedor padre más cercano con clase .extra-per-person-group y lo remueve completamente.
 * 
 * @function removeExtraPerPersonField
 * @param {HTMLButtonElement} button - Botón "-" dentro del bloque de Guest Fee a eliminar.
 * @returns {void}
 * 
 * @description
 * - Utiliza closest() para localizar el contenedor completo
 * - Elimina todo el bloque incluyendo todos sus campos
 * - Seguro: valida existencia del elemento antes de eliminar
 */
function removeExtraPerPersonField(button) {
    const extraGroup = button.closest('.extra-per-person-group');
    if (extraGroup) {
        extraGroup.remove();
    }
}

/**
 * Calcula automáticamente el total del Guest Fee (huéspedes × coste por huésped).
 * Busca los campos dentro del mismo bloque, realiza el cálculo y formatea el resultado.
 * 
 * @function calculateExtraPerPersonTotal
 * @param {HTMLElement} input - Cualquier input dentro del bloque Guest Fee (gatillo del cálculo).
 * @returns {void}
 * 
 * @description
 * - Localiza el bloque contenedor usando closest()
 * - Extrae valores de huéspedes y coste, limpiando separadores de miles
 * - Calcula: total = huéspedes × coste por huésped
 * - Formatea el resultado con separadores de miles (sin decimales)
 * - Actualiza el campo Total (readonly) automáticamente
 * - Maneja valores no numéricos como 0
 */
function calculateExtraPerPersonTotal(input) {
    const extraGroup = input.closest('.extra-per-person-group');
    if (!extraGroup) return;

    const guestsInput = extraGroup.querySelector('[name="extraPerPersonGuests"]');
    const costInput = extraGroup.querySelector('[name="extraPerPersonCost"]');
    const totalInput = extraGroup.querySelector('[name="extraPerPersonTotal"]');

    if (!guestsInput || !costInput || !totalInput) return;

    const guests = parseFloat(guestsInput.value.replace(/,/g, '')) || 0;
    const cost = parseFloat(costInput.value.replace(/,/g, '')) || 0;
    const total = guests * cost;

    // Formatear el total con separadores de miles
    totalInput.value = total.toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}