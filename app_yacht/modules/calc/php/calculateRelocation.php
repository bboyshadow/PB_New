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
require_once __DIR__ . '/../../shared/php/currency-functions.php';
$feeFormatted = formatCurrency( $total, $currency, false );

wp_send_json_success( [ 'fee' => $feeFormatted ] );
