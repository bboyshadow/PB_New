// ARCHIVO modules\calc\js\extraPerPerson.js

/**
 * Funciones para manejar los guest fees en la calculadora
 */

// Función para agregar un campo de guest fee
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

// Función para eliminar un campo de guest fee
function removeExtraPerPersonField(button) {
    const extraGroup = button.closest('.extra-per-person-group');
    if (extraGroup) {
        extraGroup.remove();
    }
}

// Función para calcular el total del guest fee
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