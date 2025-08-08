Guía de integración de la minicalculadora “Relocation Fee auto”
Esta guía explica cómo añadir a tu proyecto PB_New una minicalculadora para calcular automáticamente la tarifa de reubicación (Relocation Fee) sin modificar la lógica existente. La calculadora se activa mediante un checkbox independiente, muestra campos seleccionables, calcula el coste por AJAX y escribe el resultado en el campo relocationFee de la calculadora principal.

1. Archivos añadidos
Archivo	Ubicación sugerida	Función
relocationAuto.js	app_yacht/modules/calc/js/relocationAuto.js	Gestiona la interfaz de la minicalculadora: despliega/oculta el panel al marcar el checkbox, precarga valores de yachtInfoData, recoge datos seleccionados y realiza la llamada AJAX. Actualiza el campo relocationFee.
calculateRelocation.php	app_yacht/modules/calc/php/calculateRelocation.php	Endpoint PHP que calcula el coste. Valida el nonce, lee parámetros (distancia, horas, consumo, precio combustible, tripulación, salarios, tasas, extras), calcula un coste aproximado y devuelve la tarifa formateada.

1.1 Contenido de relocationAuto.js
javascript
Copiar
Editar
/*
 * relocationAuto.js
 *
 * Este módulo implementa una minicalculadora para la "Relocation Fee".
 * Al activar la casilla "Relocation Fee auto", se muestran campos de entrada
 * configurables (distancia, consumo, precio combustible, tripulación, etc.).
 * El módulo toma valores predeterminados del objeto global `yachtInfoData` si existe,
 * permitiendo al usuario modificarlos. Al pulsar "Aplicar", se envía una petición
 * AJAX al endpoint `calculate_relocation` que devuelve la tarifa calculada y
 * la inyecta en el campo `relocationFee` de la calculadora principal.
 */

document.addEventListener('DOMContentLoaded', () => {
    const autoCheckbox = document.getElementById('relocationAutoCheck');
    const autoContainer = document.getElementById('relocationAutoContainer');

    if (autoCheckbox && autoContainer) {
        // Mostrar u ocultar la calculadora según el estado del checkbox
        autoCheckbox.addEventListener('change', () => {
            autoContainer.style.display = autoCheckbox.checked ? 'block' : 'none';
            // Al mostrar el contenedor, precargar valores si están disponibles
            if (autoCheckbox.checked) {
                prefillRelocationFields();
            }
        });
    }

    // Manejar clic en el botón de aplicar
    const applyButton = document.getElementById('applyRelocationButton');
    if (applyButton) {
        applyButton.addEventListener('click', () => {
            calculateRelocation();
        });
    }
});

/**
 * Precarga valores en los campos de la calculadora usando la información
 * disponible en `window.yachtInfoData`. Si no existe, se dejan vacíos.
 */
function prefillRelocationFields() {
    const data = window.yachtInfoData || {};
    const fuelConsumption = document.getElementById('reloc-fuel-consumption');
    const cruisingSpeed   = document.getElementById('reloc-cruising-speed');
    const crewCount       = document.getElementById('reloc-crew-count');
    if (fuelConsumption && data.fuelConsumption && fuelConsumption.value === '') {
        fuelConsumption.value = data.fuelConsumption;
    }
    if (cruisingSpeed && data.cruisingSpeed && cruisingSpeed.value === '') {
        cruisingSpeed.value = data.cruisingSpeed;
    }
    if (crewCount && data.crew && crewCount.value === '') {
        crewCount.value = data.crew;
    }
}

/**
 * Recolecta los valores de los campos seleccionados y realiza una solicitud
 * AJAX al servidor para calcular la Relocation Fee. El resultado se inserta
 * en el campo `relocationFee` de la calculadora principal.
 */
function calculateRelocation() {
    // Crear objeto con los datos seleccionados
    const params = {};
    // Solo incluir campos marcados por el usuario
    const fields = [
        { checkboxId: 'reloc-distance-check', inputId: 'reloc-distance', name: 'distance' },
        { checkboxId: 'reloc-hours-check',    inputId: 'reloc-hours',    name: 'hours' },
        { checkboxId: 'reloc-fuel-consumption-check', inputId: 'reloc-fuel-consumption', name: 'fuelConsumption' },
        { checkboxId: 'reloc-fuel-price-check', inputId: 'reloc-fuel-price', name: 'fuelPrice' },
        { checkboxId: 'reloc-crew-count-check', inputId: 'reloc-crew-count', name: 'crewCount' },
        { checkboxId: 'reloc-crew-wage-check', inputId: 'reloc-crew-wage', name: 'crewWage' },
        { checkboxId: 'reloc-port-fees-check', inputId: 'reloc-port-fees', name: 'portFees' },
        { checkboxId: 'reloc-extra-check', inputId: 'reloc-extra', name: 'extraCosts' }
    ];
    fields.forEach(field => {
        const chk = document.getElementById(field.checkboxId);
        const input = document.getElementById(field.inputId);
        if (chk && chk.checked && input) {
            params[field.name] = input.value;
        }
    });
    // Moneda (para formatear la respuesta)
    const currency = document.getElementById('currency')?.value || '€';
    params.currency = currency;
    // Añadir nonce de seguridad si existe
    params.nonce = ajaxRelocationData?.nonce || '';

    fetch(ajaxRelocationData.ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(Object.assign({ action: 'calculate_relocation' }, params))
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const fee = result.data.fee;
            const relocationField = document.getElementById('relocationFee');
            if (relocationField) {
                relocationField.value = fee;
                relocationField.dispatchEvent(new Event('input'));
            }
            const output = document.getElementById('relocation-auto-result');
            if (output) output.textContent = fee;
        } else {
            alert(result.data.error || 'Error calculando la relocation fee');
        }
    })
    .catch(() => alert('Error de conexión al calcular la relocation fee'));
}
1.2 Contenido de calculateRelocation.php
php
Copiar
Editar
<?php
/**
 * calculateRelocation.php
 *
 * Endpoint de WordPress para calcular automáticamente la "Relocation Fee".
 * Este script lee parámetros enviados por POST (distancia, horas, consumo,
 * precio del combustible, tripulación, salarios, tasas de puerto y gastos extra),
 * calcula un coste aproximado y devuelve la tarifa formateada según la moneda.
 *
 * Para su correcto funcionamiento, debes registrar el manejador de la acción
 * `calculate_relocation` en tu archivo bootstrap.
 */

// Comprobar nonce de seguridad
if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'relocation_calculate_nonce' ) ) {
    wp_send_json_error( [ 'error' => 'Security check failed' ] );
    return;
}

// Recoger parámetros opcionales
$distance        = isset( $_POST['distance'] ) ? floatval( str_replace( ',', '', $_POST['distance'] ) ) : null; // NM
$hours           = isset( $_POST['hours'] ) ? floatval( str_replace( ',', '', $_POST['hours'] ) ) : null;
$fuelConsumption = isset( $_POST['fuelConsumption'] ) ? floatval( str_replace( ',', '', $_POST['fuelConsumption'] ) ) : null; // l/h o l/nm
$fuelPrice       = isset( $_POST['fuelPrice'] ) ? floatval( str_replace( ',', '', $_POST['fuelPrice'] ) ) : null; // €/L
$crewCount       = isset( $_POST['crewCount'] ) ? floatval( str_replace( ',', '', $_POST['crewCount'] ) ) : null;
$crewWage        = isset( $_POST['crewWage'] ) ? floatval( str_replace( ',', '', $_POST['crewWage'] ) ) : null; // €/día
$portFees        = isset( $_POST['portFees'] ) ? floatval( str_replace( ',', '', $_POST['portFees'] ) ) : 0.0;
$extraCosts      = isset( $_POST['extraCosts'] ) ? floatval( str_replace( ',', '', $_POST['extraCosts'] ) ) : 0.0;
$currency        = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '€';

// Al menos se debe especificar distancia u horas
if ( is_null( $distance ) && is_null( $hours ) ) {
    wp_send_json_error( [ 'error' => 'You must provide either distance or hours to calculate fuel cost.' ] );
    return;
}

// Calcular coste de combustible
$fuelCost = 0.0;
if ( ! is_null( $fuelPrice ) && ! is_null( $fuelConsumption ) ) {
    // Si se proporciona distancia y consumo por NM, usar distancia; de lo contrario usar horas
    if ( ! is_null( $distance ) && $distance > 0 ) {
        $fuelCost = $distance * $fuelConsumption * $fuelPrice;
    } elseif ( ! is_null( $hours ) && $hours > 0 ) {
        $fuelCost = $hours * $fuelConsumption * $fuelPrice;
    }
}

// Calcular coste de tripulación (salario diario * días)
$crewCost = 0.0;
if ( ! is_null( $crewCount ) && ! is_null( $crewWage ) && $crewCount > 0 ) {
    // Estimar horas si sólo hay distancia (asumiendo 8 nudos)
    $estimatedHours = 0.0;
    if ( ! is_null( $hours ) && $hours > 0 ) {
        $estimatedHours = $hours;
    } elseif ( ! is_null( $distance ) && $distance > 0 ) {
        $estimatedHours = $distance / 8.0;
    }
    $estimatedDays = max( 1, ceil( $estimatedHours / 24.0 ) );
    $crewCost      = $crewCount * $crewWage * $estimatedDays;
}

// Sumar todos los conceptos
$total = $fuelCost + $crewCost + $portFees + $extraCosts;

// Formatear resultado
require_once __DIR__ . '/../shared/php/currency-functions.php';
$feeFormatted = formatCurrency( $total, $currency, false );

wp_send_json_success( [ 'fee' => $feeFormatted ] );
2. Integración en el proyecto
2.1 Registrar la nueva acción AJAX
En app_yacht/core/bootstrap.php, dentro del método registerWordPressHooks(), añade:

php
Copiar
Editar
// Minicalculadora de relocation
add_action( 'wp_ajax_calculate_relocation', array( __CLASS__, 'handleCalculateRelocation' ) );
add_action( 'wp_ajax_nopriv_calculate_relocation', array( __CLASS__, 'handleCalculateRelocation' ) );
Luego define el método handleCalculateRelocation() en la clase AppYacht (junto a los handlers existentes):

php
Copiar
Editar
public static function handleCalculateRelocation() {
    try {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'relocation_calculate_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed', 'code' => 'security_error' ) );
            return;
        }
        // Incluir el script de cálculo
        include_once __DIR__ . '/../modules/calc/php/calculateRelocation.php';
    } catch ( Exception $e ) {
        error_log( 'Calculate Relocation Error: ' . $e->getMessage() );
        wp_send_json_error( 'Error en cálculo de relocation: ' . $e->getMessage() );
    }
}
2.2 Añadir el checkbox y el contenedor al formulario de la calculadora
En app_yacht/modules/calc/calculator.php, localiza el bloque donde aparecen las opciones de VAT, APA, Relocation Fee y Security Deposit. Después del campo de Relocation Fee, inserta el siguiente código HTML:

html
Copiar
Editar
<!-- Activador de la minicalculadora -->
<div class="form-check form-switch mt-2">
    <input id="relocationAutoCheck" type="checkbox" class="form-check-input" aria-controls="relocationAutoContainer">
    <label class="form-check-label" for="relocationAutoCheck">Relocation Fee auto</label>
</div>

<!-- Contenedor de la minicalculadora (oculto por defecto) -->
<div id="relocationAutoContainer" class="mt-3" style="display:none;">
    <p class="fw-bold mb-2">Calculadora de relocation</p>
    <div class="row">
        <div class="col-4">
            <input id="reloc-distance-check" type="checkbox"> Distancia (NM)
            <input id="reloc-distance" type="number" class="form-control mt-1" placeholder="Distancia" />
        </div>
        <div class="col-4">
            <input id="reloc-hours-check" type="checkbox"> Horas
            <input id="reloc-hours" type="number" class="form-control mt-1" placeholder="Horas" />
        </div>
        <div class="col-4">
            <input id="reloc-fuel-consumption-check" type="checkbox"> Consumo (l/h o l/nm)
            <input id="reloc-fuel-consumption" type="number" class="form-control mt-1" placeholder="Consumo" />
        </div>
        <div class="col-4">
            <input id="reloc-fuel-price-check" type="checkbox"> Precio combustible
            <input id="reloc-fuel-price" type="number" step="0.01" class="form-control mt-1" placeholder="€/L" />
        </div>
        <div class="col-4">
            <input id="reloc-crew-count-check" type="checkbox"> Tripulación
            <input id="reloc-crew-count" type="number" class="form-control mt-1" placeholder="N.º tripulantes" />
        </div>
        <div class="col-4">
            <input id="reloc-crew-wage-check" type="checkbox"> Salario diario
            <input id="reloc-crew-wage" type="number" step="0.01" class="form-control mt-1" placeholder="€/día" />
        </div>
        <div class="col-4">
            <input id="reloc-port-fees-check" type="checkbox"> Tasas portuarias
            <input id="reloc-port-fees" type="number" step="0.01" class="form-control mt-1" placeholder="€" />
        </div>
        <div class="col-4">
            <input id="reloc-extra-check" type="checkbox"> Otros gastos
            <input id="reloc-extra" type="number" step="0.01" class="form-control mt-1" placeholder="€" />
        </div>
    </div>
    <div class="mt-2">
        <button id="applyRelocationButton" type="button" class="btn btn-secondary">Aplicar</button>
        <span id="relocation-auto-result" class="ms-3 fw-bold"></span>
    </div>
</div>
El atributo aria-controls="relocationAutoContainer" permite reutilizar la función existente toggleCalcOptionalField() para gestionar accesibilidad si lo deseas. Por defecto el contenedor está oculto (style="display:none;").

2.3 Cargar el script relocationAuto.js
En la misma plantilla, tras incluir los demás scripts de la calculadora (VatRateMix.js, interfaz.js, mix.js, etc.), carga el nuevo archivo y prepara la variable global ajaxRelocationData con el nonce:

php
Copiar
Editar
<script src="<?= get_template_directory_uri(); ?>/app_yacht/modules/calc/js/relocationAuto.js"></script>
<script>
window.ajaxRelocationData = {
    ajaxurl: ajaxCalculatorData.ajaxurl,
    nonce: wp_create_nonce( 'relocation_calculate_nonce' )
};
</script>
2.4 Formulario principal (calculate.js)
No necesitas modificar calculate.js; la minicalculadora escribe el resultado final en el input relocationFee y la lógica existente ya envía ese valor al servidor cuando relocationCheck está marcado.

3. Flujo de uso
El broker marca la casilla Relocation Fee auto. Aparece el panel de la minicalculadora.

Los campos de consumo, velocidad y tripulación se pre‑rellenan con los datos extraídos del yate (yachtInfoData), aunque siguen siendo editables.

El broker activa únicamente los factores relevantes, rellena/corrige los datos y pulsa Aplicar.

Se envía una petición AJAX a calculate_relocation; el importe devuelto se muestra junto al botón y se copia al campo relocationFee.

Al calcular el charter, la lógica principal suma automáticamente la relocationFee al subtotal, igual que con la entrada manual.

Con esta guía puedes integrar de forma limpia y aislada la nueva funcionalidad, respetando el estilo y la lógica de tu proyecto.