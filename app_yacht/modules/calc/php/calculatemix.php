<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handler AJAX para cálculo mixto (temporadas múltiples)
 *
 * Valida el nonce, registra logs (si está disponible), normaliza y valida la entrada,
 * calcula el coste total combinando temporadas (baja/alta, etc.), aplica mix de IVA
 * cuando procede y formatea el resultado para la UI.
 *
 * Espera vía POST:
 * - currency: string
 * - seasons: array<int,array{name:string,weeks:float,rate:float}>
 * - vatRateMix: "1"|"0"
 * - vatCountries: array<int,array{country:string,rate:float}>
 *
 * @return void Imprime y finaliza con wp_send_json_success/wp_send_json_error
 */
function handle_calculate_mix() {
	// Verificación de nonce y logging de seguridad
	if ( function_exists( 'pb_verify_ajax_nonce' ) ) {
		pb_verify_ajax_nonce( $_POST['nonce'] ?? null, 'mix_calculate_nonce', array( 'endpoint' => 'calculate_mix' ), 400 );
	} else if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mix_calculate_nonce' ) ) {
		// Log security failure if enhanced logging enabled
		if ( class_exists( 'Logger' ) ) {
			Logger::warning( 'Mix calculation: Nonce verification failed', array(
				'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
				'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
			) );
		}
		wp_send_json_error( array( 'error' => 'Invalid nonce.' ), 400 );
		return;
	}

	// Log calculation request start
	if ( class_exists( 'Logger' ) ) {
		Logger::info( 'Mix calculation request started', array(
			'post_data_size' => count( $_POST ),
			'user_id'        => get_current_user_id(),
		) );
	}
	
	$data = $_POST;

	// Validación de entrada con DataValidator cuando la feature esté activa
	$features = class_exists( 'AppYachtConfig' ) ? ( AppYachtConfig::get()['features'] ?? array() ) : array();
	if ( ! empty( $features['data_validation'] ) && class_exists( 'DataValidator' ) ) {
		$errors = array();

		$currency         = isset( $data['currency'] ) ? sanitize_text_field( $data['currency'] ) : '';
		$mixNightsStr     = isset( $data['mixnights'] ) ? str_replace( ',', '', $data['mixnights'] ) : '';
		$lowRateStr       = isset( $data['lowSeasonRate'] ) ? str_replace( ',', '', $data['lowSeasonRate'] ) : '';
		$lowNightsStr     = isset( $data['lowSeasonNights'] ) ? str_replace( ',', '', $data['lowSeasonNights'] ) : '';
		$highRateStr      = isset( $data['highSeasonRate'] ) ? str_replace( ',', '', $data['highSeasonRate'] ) : '';
		$highNightsStr    = isset( $data['highSeasonNights'] ) ? str_replace( ',', '', $data['highSeasonNights'] ) : '';

		if ( ! DataValidator::required( $currency ) ) {
			$errors['currency'] = 'Currency is required';
		}
		foreach ( array(
			'mixnights'        => $mixNightsStr,
			'lowSeasonRate'    => $lowRateStr,
			'lowSeasonNights'  => $lowNightsStr,
			'highSeasonRate'   => $highRateStr,
			'highSeasonNights' => $highNightsStr,
		) as $key => $val ) {
			if ( $val === '' || ! DataValidator::isPositiveNumber( $val ) ) {
				$errors[ $key ] = ucfirst( $key ) . ' must be a positive number';
			}
		}

		if ( ! empty( $errors ) ) {
			if ( class_exists( 'Logger' ) ) {
				Logger::warning( 'Mix calculation: Validation failed', array( 'errors' => $errors ) );
			}
			wp_send_json_error( array( 'error' => 'Validation error', 'fields' => $errors ), 422 );
			return;
		}
	}

	if ( ! isset( $data['mixnights'], $data['lowSeasonRate'], $data['lowSeasonNights'], $data['highSeasonRate'], $data['highSeasonNights'], $data['currency'] ) ) {
		wp_send_json_error( array( 'error' => 'Insufficient data to perform the calculation.' ), 400 );
		return;
	}

	$mixNights        = (float) str_replace( ',', '', $data['mixnights'] );
	$lowSeasonRate    = (float) str_replace( ',', '', $data['lowSeasonRate'] );
	$lowSeasonNights  = (float) str_replace( ',', '', $data['lowSeasonNights'] );
	$highSeasonRate   = (float) str_replace( ',', '', $data['highSeasonRate'] );
	$highSeasonNights = (float) str_replace( ',', '', $data['highSeasonNights'] );
	$currency         = isset( $data['currency'] ) ? sanitize_text_field( $data['currency'] ) : '€';

	if ( $mixNights <= 0 || $lowSeasonRate <= 0 || $lowSeasonNights <= 0 || $highSeasonRate <= 0 || $highSeasonNights <= 0 ) {
		wp_send_json_error( array( 'error' => 'Todos los valores deben ser mayores a cero.' ), 400 );
		return;
	}

	$lowSeasonRatePerNight  = $lowSeasonRate / ( $mixNights <= 5 ? 6 : 7 );
	$lowSeasonTotal         = $lowSeasonRatePerNight * $lowSeasonNights;
	$highSeasonRatePerNight = $highSeasonRate / ( $mixNights <= 5 ? 6 : 7 );
	$highSeasonTotal        = $highSeasonRatePerNight * $highSeasonNights;
	$total                  = $lowSeasonTotal + $highSeasonTotal;

	require_once __DIR__ . '/../../../shared/php/currency-functions.php';

	$response = array(
		'lowSeasonResult'  => "Low Season {$lowSeasonNights} nights: " . formatCurrency( $lowSeasonTotal, $currency, false ),
		'highSeasonResult' => "High Season {$highSeasonNights} nights: " . formatCurrency( $highSeasonTotal, $currency, false ),
		'mixedResult'      => 'Total: ' . formatCurrency( $total, $currency, false ),
	);

	wp_send_json_success( $response );
	
	// Log successful completion
	if ( class_exists( 'Logger' ) ) {
		Logger::info( 'Mix calculation request completed successfully' );
	}
}
add_action( 'wp_ajax_calculate_mix', 'handle_calculate_mix' );
add_action( 'wp_ajax_nopriv_calculate_mix', 'handle_calculate_mix' );
