<?php



add_action( 'wp_ajax_calculate_mix', 'handle_calculate_mix' );
add_action( 'wp_ajax_nopriv_calculate_mix', 'handle_calculate_mix' );

function handle_calculate_mix() {
	
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mix_calculate_nonce' ) ) {
		wp_send_json_error( array( 'error' => 'Nonce inválido.' ), 400 );
		return;
	}
	
	
	$data = $_POST;

	if ( ! isset( $data['mixnights'], $data['lowSeasonRate'], $data['lowSeasonNights'], $data['highSeasonRate'], $data['highSeasonNights'], $data['currency'] ) ) {
		wp_send_json_error( array( 'error' => 'Datos insuficientes para realizar el cálculo.' ), 400 );
		return;
	}

	
	$mixNights        = (float) str_replace( ',', '', $data['mixnights'] );
	$lowSeasonRate    = (float) str_replace( ',', '', $data['lowSeasonRate'] );
	$lowSeasonNights  = (float) str_replace( ',', '', $data['lowSeasonNights'] );
	$highSeasonRate   = (float) str_replace( ',', '', $data['highSeasonRate'] );
	$highSeasonNights = (float) str_replace( ',', '', $data['highSeasonNights'] );
	$currency         = htmlspecialchars( $data['currency'] );

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
}
