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

    // distance u hours pueden ser opcionales pero al menos uno debe existir y ser número positivo
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
$speed           = isset( $_POST['speed'] ) ? floatval( str_replace( ',', '', $_POST['speed'] ) ) : null; // nudos
// Calcular horas a partir de distancia y velocidad si no se proporcionan directamente
if ( is_null( $hours ) && ! is_null( $distance ) && ! is_null( $speed ) && $speed > 0 ) {
    $hours = $distance / $speed;
}
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
// Fórmula principal: (millas náuticas / velocidad) * consumo * precio combustible
if ( ! is_null( $distance ) && ! is_null( $speed ) && $distance > 0 && $speed > 0 && ! is_null( $fuelConsumption ) && ! is_null( $fuelPrice ) ) {
    $hours    = $distance / $speed;
    $fuelCost = $hours * $fuelConsumption * $fuelPrice;
// Fallback: si falta velocidad pero el usuario proporcionó horas manualmente
} elseif ( ! is_null( $hours ) && $hours > 0 && ! is_null( $fuelConsumption ) && ! is_null( $fuelPrice ) ) {
    $fuelCost = $hours * $fuelConsumption * $fuelPrice;
}

// Calcular coste de tripulación (salario diario * días)
$crewCost = 0.0;
if ( ! is_null( $crewCount ) && ! is_null( $crewWage ) && $crewCount > 0 ) {
    // Estimar horas si sólo hay distancia (asumiendo 8 nudos)
    $estimatedHours = 0.0;
    if ( ! is_null( $hours ) && $hours > 0 ) {
        $estimatedHours = $hours;
    } elseif ( ! is_null( $distance ) && $distance > 0 ) {
        if ( ! is_null( $speed ) && $speed > 0 ) {
            $estimatedHours = $distance / $speed;
        } else {
            $estimatedHours = $distance / 8.0; // fallback velocidad default
        }
    }
    $estimatedDays = max( 1, ceil( $estimatedHours / 24.0 ) );
    $crewCost      = $crewCount * $crewWage * $estimatedDays;
}

// Sumar todos los conceptos
$total = $fuelCost + $crewCost + $portFees + $extraCosts;

// Formatear resultado
require_once __DIR__ . '/../../../shared/php/currency-functions.php';
$feeFormatted = formatCurrency( $total, $currency, false );

wp_send_json_success( [ 'fee' => $feeFormatted ] );
if ( class_exists( 'Logger' ) ) {
    Logger::info( 'Relocation calculation request completed successfully' );
}
