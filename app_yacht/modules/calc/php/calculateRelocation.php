<?php
/**
 * AJAX endpoint to calculate the "Relocation Fee" for a charter
 *
 * Reads parameters sent via POST (distance, hours, speed, consumption, fuel price,
 * crew and additional expenses), calculates an approximate cost and returns the formatted fee
 * according to the currency.
 *
 * Expects via POST:
 * - nonce: string
 * - currency: string (€, $USD, $AUD, ...)
 * - distance: float|null (NM)
 * - hours: float|null
 * - speed: float|null (knots)
 * - fuelConsumption: float|null (liters/hour)
 * - fuelPrice: float|null (currency/liter)
 * - crewCount: int|null
 * - crewWage: float|null (currency/day)
 * - portFees: float|null
 * - extraCosts: float|null
 *
 * @return void Prints and ends with wp_send_json_success(["fee" => string]) or wp_send_json_error
 */
?>
<?php
/**
 * calculateRelocation.php
 *
 * WordPress endpoint to automatically calculate the "Relocation Fee".
 * This script reads parameters sent via POST (distance, hours, consumption,
 * fuel price, crew, wages, port fees and extra expenses),
 * calculates an approximate cost and returns the formatted fee according to the currency.
 *
 * For proper operation, you must register the action handler
 * `calculate_relocation` in your bootstrap file.
 */

// Comprobar nonce de seguridad
if ( function_exists( 'pb_verify_ajax_nonce' ) ) {
    pb_verify_ajax_nonce( $_POST['nonce'] ?? null, 'relocation_calculate_nonce', array( 'endpoint' => 'calculate_relocation' ), 400 );
} else if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'relocation_calculate_nonce' ) ) {
    if ( class_exists( 'Logger' ) ) {
        Logger::warning( 'Relocation calculation: Nonce verification failed', array(
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ) );
    }
    wp_send_json_error( [ 'error' => 'Security check failed' ] );
    return;
}

if ( class_exists( 'Logger' ) ) {
    Logger::info( 'Relocation calculation request started', array(
        'user_id' => get_current_user_id(),
    ) );
}

// Validación de entrada con DataValidator cuando la feature esté activa
$features = class_exists( 'AppYachtConfig' ) ? ( AppYachtConfig::get()['features'] ?? array() ) : array();
if ( ! empty( $features['data_validation'] ) && class_exists( 'DataValidator' ) ) {
    $errors = array();

    $currency         = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';
    $distanceStr      = isset( $_POST['distance'] ) ? str_replace( ',', '', $_POST['distance'] ) : '';
    $hoursStr         = isset( $_POST['hours'] ) ? str_replace( ',', '', $_POST['hours'] ) : '';
    $speedStr         = isset( $_POST['speed'] ) ? str_replace( ',', '', $_POST['speed'] ) : '';
    $fuelConsStr      = isset( $_POST['fuelConsumption'] ) ? str_replace( ',', '', $_POST['fuelConsumption'] ) : '';
    $fuelPriceStr     = isset( $_POST['fuelPrice'] ) ? str_replace( ',', '', $_POST['fuelPrice'] ) : '';
    $crewCountStr     = isset( $_POST['crewCount'] ) ? str_replace( ',', '', $_POST['crewCount'] ) : '';
    $crewWageStr      = isset( $_POST['crewWage'] ) ? str_replace( ',', '', $_POST['crewWage'] ) : '';
    $portFeesStr      = isset( $_POST['portFees'] ) ? str_replace( ',', '', $_POST['portFees'] ) : '0';
    $extraCostsStr    = isset( $_POST['extraCosts'] ) ? str_replace( ',', '', $_POST['extraCosts'] ) : '0';

    if ( ! DataValidator::required( $currency ) ) {
        $errors['currency'] = 'Currency is required';
    }

    // distance or hours can be optional but at least one must exist and be a positive number
    $hasDistance = $distanceStr !== '' && DataValidator::isPositiveNumber( $distanceStr );
    $hasHours    = $hoursStr !== '' && DataValidator::isPositiveNumber( $hoursStr );
    if ( ! $hasDistance && ! $hasHours ) {
        $errors['distance_or_hours'] = 'Provide distance or hours as a positive number';
    }

    foreach ( array(
        'speed'           => $speedStr,
        'fuelConsumption' => $fuelConsStr,
        'fuelPrice'       => $fuelPriceStr,
        'crewCount'       => $crewCountStr,
        'crewWage'        => $crewWageStr,
        'portFees'        => $portFeesStr,
        'extraCosts'      => $extraCostsStr,
    ) as $key => $val ) {
        if ( $val !== '' && ! DataValidator::isPositiveNumber( $val ) ) {
            $errors[ $key ] = ucfirst( $key ) . ' must be a positive number';
        }
    }

    if ( ! empty( $errors ) ) {
        if ( class_exists( 'Logger' ) ) {
            Logger::warning( 'Relocation calculation: Validation failed', array( 'errors' => $errors ) );
        }
        wp_send_json_error( array( 'error' => 'Validation error', 'fields' => $errors ), 422 );
        return;
    }
}

// Recoger parámetros opcionales
$distance        = isset( $_POST['distance'] ) ? floatval( str_replace( ',', '', $_POST['distance'] ) ) : null; // NM
$hours           = isset( $_POST['hours'] ) ? floatval( str_replace( ',', '', $_POST['hours'] ) ) : null;
$speed           = isset( $_POST['speed'] ) ? floatval( str_replace( ',', '', $_POST['speed'] ) ) : null; // knots
// Calculate hours from distance and speed if not provided directly
if ( is_null( $hours ) && ! is_null( $distance ) && ! is_null( $speed ) && $speed > 0 ) {
    $hours = $distance / $speed;
}
$fuelConsumption = isset( $_POST['fuelConsumption'] ) ? floatval( str_replace( ',', '', $_POST['fuelConsumption'] ) ) : null; // l/h or l/nm
$fuelPrice       = isset( $_POST['fuelPrice'] ) ? floatval( str_replace( ',', '', $_POST['fuelPrice'] ) ) : null; // currency/L
$crewCount       = isset( $_POST['crewCount'] ) ? floatval( str_replace( ',', '', $_POST['crewCount'] ) ) : null;
$crewWage        = isset( $_POST['crewWage'] ) ? floatval( str_replace( ',', '', $_POST['crewWage'] ) ) : null; // currency/day
$portFees        = isset( $_POST['portFees'] ) ? floatval( str_replace( ',', '', $_POST['portFees'] ) ) : 0.0;
$extraCosts      = isset( $_POST['extraCosts'] ) ? floatval( str_replace( ',', '', $_POST['extraCosts'] ) ) : 0.0;
$currency        = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '€';

// At least distance or hours must be specified
if ( is_null( $distance ) && is_null( $hours ) ) {
    wp_send_json_error( [ 'error' => 'You must provide either distance or hours to calculate fuel cost.' ] );
    return;
}

// Calculate fuel cost
$fuelCost = 0.0;
// Main formula: (nautical miles / speed) * consumption * fuel price
if ( ! is_null( $distance ) && ! is_null( $speed ) && $distance > 0 && $speed > 0 && ! is_null( $fuelConsumption ) && ! is_null( $fuelPrice ) ) {
    $hours    = $distance / $speed;
    $fuelCost = $hours * $fuelConsumption * $fuelPrice;
// Fallback: if speed is missing but the user provided hours manually
} elseif ( ! is_null( $hours ) && $hours > 0 && ! is_null( $fuelConsumption ) && ! is_null( $fuelPrice ) ) {
    $fuelCost = $hours * $fuelConsumption * $fuelPrice;
}

// Calculate crew cost (daily wage * days)
$crewCost = 0.0;
if ( ! is_null( $crewCount ) && ! is_null( $crewWage ) && $crewCount > 0 ) {
    // Estimate hours if only distance is provided (assuming 8 knots)
    $estimatedHours = 0.0;
    if ( ! is_null( $hours ) && $hours > 0 ) {
        $estimatedHours = $hours;
    } elseif ( ! is_null( $distance ) && $distance > 0 ) {
        if ( ! is_null( $speed ) && $speed > 0 ) {
            $estimatedHours = $distance / $speed;
        } else {
            $estimatedHours = $distance / 8.0; // default speed fallback
        }
    }
    $estimatedDays = max( 1, ceil( $estimatedHours / 24.0 ) );
    $crewCost      = $crewCount * $crewWage * $estimatedDays;
}

// Sum all components
$total = $fuelCost + $crewCost + $portFees + $extraCosts;

// Format result
require_once __DIR__ . '/../../../shared/php/currency-functions.php';
$feeFormatted = formatCurrency( $total, $currency, false );

wp_send_json_success( [ 'fee' => $feeFormatted ] );
if ( class_exists( 'Logger' ) ) {
    Logger::info( 'Relocation calculation request completed successfully' );
}
