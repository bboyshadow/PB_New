<?php
/**
 * Backend handler for charter rate calculations via AJAX for AppYacht.
 *
 * @author Mr ShadoW <mrshadow@probroker.com>
 * @copyright 2024 Pro Broker
 * @link /DOC/modules/calc/php/calculate.php.md
 * @version 3.0
 */
// Archivo: modules/calc/php/calculate.php

// IMPORTANTE: La lógica de propina varía según la moneda:
// EUR => 10%-15%
// Otras (USD, AUD, etc.) => 15%-20%

require_once __DIR__ . '/../../../shared/php/currency-functions.php'; // Para formatCurrency

// Registrar acciones AJAX
add_action( 'wp_ajax_calculate_charter', 'handle_calculate_charter' );
add_action( 'wp_ajax_nopriv_calculate_charter', 'handle_calculate_charter' );

/**
 * Handles AJAX requests for charter rate calculations (`calculate_charter` action).
 *
 * This function serves as the main AJAX handler. It performs security checks (nonce),
 * collects and sanitizes input data from `$_POST`, prepares data for calculation,
 * invokes the `calculate()` function for core computation, formats the results
 * using `textResult()`, and then sends a JSON response (success or error) back to the client.
 *
 * @since 1.0.0
 *
 * @global array $_POST Expected superglobal. Expected keys include:
 *  'nonce' (string) Security nonce.
 *  'currency' (string) Currency symbol (e.g., '€', '$USD').
 *  'vatRate' (string) VAT rate percentage.
 *  'apaAmount' (string) Fixed APA amount.
 *  'apaPercentage' (string) APA percentage.
 *  'relocationFee' (string) Relocation fee.
 *  'securityFee' (string) Security deposit fee.
 *  'charterRates' (array) Array of charter rate blocks.
 *  'extras' (array) Array of extras.
 *  'enableMixedSeasons' (string) '1' if mixed seasons mode is enabled.
 *  'enableOneDayCharter' (string) '1' if one-day charter mode is enabled.
 *  'enableExpenses' (string) '1' if '+ Expenses' label should be shown.
 *  'hideVAT' (string) '1' to hide VAT details.
 *  'hideAPA' (string) '1' to hide APA details.
 *  'hideRelocation' (string) '1' to hide relocation fee details.
 *  'hideSecurity' (string) '1' to hide security deposit details.
 *  'hideExtras' (string) '1' to hide extras details.
 *  'hideGratuity' (string) '1' to hide gratuity details.
 *
 * @uses wp_verify_nonce() For security validation.
 * @uses sanitize_text_field() For input sanitization.
 * @uses calculate() For performing the core calculations.
 * @uses textResult() For formatting the calculation results into text.
 * @uses wp_send_json_error() For sending error responses.
 * @uses wp_send_json_success() For sending success responses.
 *
 * @return void Outputs JSON and terminates execution.
 */
function handle_calculate_charter() {
	// 1) Verificar nonce para seguridad
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calculate_nonce' ) ) {
		wp_send_json_error( array( 'error' => 'Nonce inválido.' ), 400 );
		return;
	}



	// Recoger y sanitizar datos de $_POST
	$currency            = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';
	$vatRate             = isset( $_POST['vatRate'] ) ? sanitize_text_field( $_POST['vatRate'] ) : '';
	$apaAmount           = isset( $_POST['apaAmount'] ) ? sanitize_text_field( $_POST['apaAmount'] ) : '';
	$apaPercentage       = isset( $_POST['apaPercentage'] ) ? sanitize_text_field( $_POST['apaPercentage'] ) : '';
	$relocationFee       = isset( $_POST['relocationFee'] ) ? sanitize_text_field( $_POST['relocationFee'] ) : '';
	$securityFee         = isset( $_POST['securityFee'] ) ? sanitize_text_field( $_POST['securityFee'] ) : '';
	$charterRates        = isset( $_POST['charterRates'] ) ? $_POST['charterRates'] : array();
	$extras              = isset( $_POST['extras'] ) ? $_POST['extras'] : array();
	$enableMixedSeasons  = ! empty( $_POST['enableMixedSeasons'] ) && $_POST['enableMixedSeasons'] === '1';
	$enableOneDayCharter = ! empty( $_POST['enableOneDayCharter'] ) && $_POST['enableOneDayCharter'] === '1';
	$enableExpenses      = ! empty( $_POST['enableExpenses'] ) && $_POST['enableExpenses'] === '1';
	
	// Recoger preferencias de elementos a ocultar
	$hideElements = array(
		'hideVAT'        => isset( $_POST['hideVAT'] ) && $_POST['hideVAT'] === '1',
		'hideAPA'        => isset( $_POST['hideAPA'] ) && $_POST['hideAPA'] === '1',
		'hideRelocation' => isset( $_POST['hideRelocation'] ) && $_POST['hideRelocation'] === '1',
		'hideSecurity'   => isset( $_POST['hideSecurity'] ) && $_POST['hideSecurity'] === '1',
		'hideExtras'     => isset( $_POST['hideExtras'] ) && $_POST['hideExtras'] === '1',
		'hideGratuity'   => isset( $_POST['hideGratuity'] ) && $_POST['hideGratuity'] === '1',
	);

	// Validar si $charterRates es un array
	if ( ! is_array( $charterRates ) ) {
		wp_send_json_error( array( 'error' => 'charterRates no es un array.' ), 400 );
		return;
	}

	// Mapeo de símbolos
	$symbolsMap = array(
		'€'    => 'EUR',
		'$USD' => 'USD',
		'$AUD' => 'AUD',
	);
	$symbolCode = isset( $symbolsMap[ $currency ] ) ? $symbolsMap[ $currency ] : 'EUR';

	// Recoger enableMixedTaxes y arrays para impuestos mixtos
	$enableVatRateMix = ! empty( $_POST['vatRateMix'] ) && $_POST['vatRateMix'] === '1';
	$vatMix           = array();
	if ( $enableVatRateMix ) {
		$countryNames = $_POST['vatCountryName'] ?? array();
		$nightsArr    = $_POST['vatNights'] ?? array();
		$vats         = $_POST['vatRate'] ?? array();
		for ( $i = 0; $i < count( $countryNames ); $i++ ) {
			$vatMix[] = array(
				'country' => sanitize_text_field( $countryNames[ $i ] ?? '' ),
				'nights'  => (int) ( $nightsArr[ $i ] ?? 0 ),
				'vatRate' => sanitize_text_field( $vats[ $i ] ?? '' ),
			);
		}
		error_log( 'VatMix constructed: ' . print_r( $vatMix, true ) );
	}

	// 3) Construir array de datos a pasar a "calculate"
	$data = array(
		'currency'            => $currency,
		'symbolCode'          => $symbolCode,
		'vatRate'             => $vatRate,
		'apaAmount'           => $apaAmount,
		'apaPercentage'       => $apaPercentage,
		'relocationFee'       => $relocationFee,
		'securityFee'         => $securityFee,
		'charterRates'        => $charterRates,
		'extras'              => $extras,
		'enableMixedSeasons'  => $enableMixedSeasons,
		'enableOneDayCharter' => $enableOneDayCharter,
		'enableExpenses'      => $enableExpenses,
		'vatMix'              => $vatMix,
		'enableVatRateMix'    => $enableVatRateMix,
	);

	// 4) Calcular => array estructurado
	$calcArray = calculate( $data );

	// 5) Generar salida en texto plano
	$textOutput = textResult( $calcArray, $hideElements, $enableExpenses, $enableMixedSeasons );

	// 6) Responder con JSON
	wp_send_json_success( $textOutput );
}

function calculate( array $data ): array {
	// 1) Parsear inputs
	$currency     = $data['currency'] ?? '€';
	$symbolCode   = $data['symbolCode'] ?? 'EUR';
	$vatRateStr   = $data['vatRate'] ?? '0';
	$apaAmountStr = $data['apaAmount'] ?? '0';
	$apaPercStr   = $data['apaPercentage'] ?? '0';
	$relocFeeStr  = $data['relocationFee'] ?? '0';
	$secFeeStr    = $data['securityFee'] ?? '0';

	$vatRate = floatval( str_replace( ',', '', $vatRateStr ) ) / 100;
	if ( $enableVatRateMix ) {
		$vatRate = 0;
	}
	$apaAmount     = floatval( str_replace( ',', '', $apaAmountStr ) );
	$apaPercentage = floatval( str_replace( ',', '', $apaPercStr ) ) / 100;
	$relocationFee = floatval( str_replace( ',', '', $relocFeeStr ) );
	$securityFee   = floatval( str_replace( ',', '', $secFeeStr ) );

	$charterRates     = $data['charterRates'] ?? array();
	$extras           = $data['extras'] ?? array();
	$mixedActive      = ! empty( $data['enableMixedSeasons'] );
	$oneDayActive     = ! empty( $data['enableOneDayCharter'] );
	$enableExpenses   = ! empty( $data['enableExpenses'] );
	$vatMix           = $data['vatMix'] ?? array();
	$enableVatRateMix = ! empty( $data['enableVatRateMix'] );

	$structuredResults = array();

	// 2) Recorrer "charterRates" para calcular cada bloque
	foreach ( $charterRates as $rate ) {
		$guests = (int) ( $rate['guests'] ?? 0 );
		$nights = (int) ( $rate['nights'] ?? 0 );
		$hours  = (int) ( $rate['hours'] ?? 0 );

		$baseRaw  = isset( $rate['baseRate'] ) ? str_replace( ',', '', $rate['baseRate'] ) : '0';
		$baseRate = floatval( $baseRaw );

		$discountType   = $rate['discountType'] ?? '';
		$discountAmount = isset( $rate['discountAmount'] ) ? floatval( str_replace( ',', '', $rate['discountAmount'] ) ) : 0;
		$discountActive = ( ! empty( $rate['discountActive'] ) && $rate['discountActive'] === '1' );

		// 3) Cálculo base:
		if ( $oneDayActive ) {
			// One Day: Se redondea
			$calculatedBaseRate = ceil( $baseRate );
		} else {
			if ( $mixedActive ) {
				// Modo mix (base)
				$calculatedBaseRate = ceil( ( $baseRate * 7 ) / 7 );
			} else {
				// Modo normal
				$divisor            = ( $nights <= 5 ) ? 6 : 7;
				$calculatedBaseRate = ceil( ( $baseRate * $nights ) / $divisor );
			}
		}

		// 4) Descuento
		$discountedRate           = $calculatedBaseRate;
		$discountValueDisplay     = '--'; // Valor a mostrar como descuento aplicado
		$discountAmountForDisplay = $discountAmount; // Valor original (porcentaje o fijo)

		if ( $discountActive && $discountAmount > 0 && $discountType !== '' ) {
			if ( $discountType === 'percentage' ) {
				$descValue                = $calculatedBaseRate * ( $discountAmount / 100 );
				$discountedRate           = $calculatedBaseRate - $descValue;
				$discountValueDisplay     = formatCurrency( $descValue, $symbolCode, true ); // Mostrar el valor calculado del descuento
				$discountAmountForDisplay = (string) $discountAmount; // Mantener el porcentaje para mostrar
			} else {
				// Monto fijo
				$discountedRate           = $calculatedBaseRate - $discountAmount;
				$discountValueDisplay     = formatCurrency( $discountAmount, $symbolCode, true ); // Mostrar el monto fijo
				$discountAmountForDisplay = formatCurrency( $discountAmount, $symbolCode, true ); // Mantener el monto fijo formateado
			}
			// Asegurar que el descuento no resulte en un valor negativo
			if ( $discountedRate < 0 ) {
				$discountedRate = 0;
			}
		}

		$promotionNights = (int) ( $rate['promotionNights'] ?? 0 );
		$promotionActive = ( ! empty( $rate['promotionActive'] ) && $rate['promotionActive'] === '1' );

		$promotedRate          = $discountedRate;
		$promotionValueDisplay = '--';

		if ( $promotionActive && $promotionNights > 0 ) {
			 $promotedRate = ceil( ( $discountedRate / $nights ) * $promotionNights );
			if ( $promotedRate < 0 ) {
				$promotedRate = 0;
			}
			 $promotionValueDisplay = formatCurrency( $promotedRate, $symbolCode, true );
		}

		// 5) Subtotal (base para cálculos de impuestos)
		$subtotal = $promotedRate;

		// 6) Calcular VAT y APA porcentual basados en el discountedRate
		$vatCalc     = $promotedRate * $vatRate;
		$apaPercCalc = $promotedRate * $apaPercentage;

		$mixed_vat_total = 0;
		$mixed_breakdown = array();

		$vatToApply = $vatCalc;
		$apaToApply = $apaPercCalc;

		// 7) Desglose para VAT Rate Mix (si aplica)
		if ( $enableVatRateMix && ! empty( $vatMix ) ) {
			$total_nights_in_post = 0;
			foreach ( $vatMix as $tax ) {
					$total_nights_in_post += (int) ( $tax['nights'] ?? 0 );
			}
				error_log( 'Total nights in vatMix: ' . $total_nights_in_post );
			if ( $total_nights_in_post > 0 ) {
				error_log( 'Processing VAT Rate Mix' );
				foreach ( $vatMix as $tax ) {
					$country_name   = sanitize_text_field( $tax['country'] ?? '' );
					$country_nights = (int) ( $tax['nights'] ?? 0 );
					$night_ratio    = $country_nights / $total_nights_in_post;

					$country_vat_rate = floatval( $tax['vatRate'] ?? $vatRateStr ) / 100;

					$country_vat = $promotedRate * $country_vat_rate * $night_ratio;

					$mixed_vat_total += $country_vat;

					$mixed_breakdown[] = array(
						'country_name'         => $country_name,
						'nights'               => $country_nights,
						'vat_amount_formatted' => formatCurrency( $country_vat, $symbolCode, false ),
						'vat_rate'             => $country_vat_rate * 100,
					);
				}
				$vatToApply = $mixed_vat_total;
			}
		}

		// Sumar VAT y APA porcentual al subtotal
		$subtotal += $vatToApply;
		$subtotal += $apaToApply;

		// Sumar Relocation y Security al subtotal
		$subtotal += $relocationFee;
		$subtotal += $securityFee;
		
		// 8) APA fijo (se suma al final del subtotal)
		$apaAmountDisp = '';
		if ( $apaAmount > 0 ) {
			$subtotal     += $apaAmount;
			$apaAmountDisp = formatCurrency( $apaAmount, $symbolCode, false );
		} else {
			$apaAmountDisp = '--';
		}

		// 9) Relocation Display (ya sumado al subtotal)
		$relocationDisp = '';
		if ( $relocationFee > 0 ) {
			$relocationDisp = formatCurrency( $relocationFee, $symbolCode, false );
		}

		// 10) Security Display (ya sumado al subtotal)
		$securityDisp = '';
		if ( $securityFee > 0 ) {
			$securityDisp = formatCurrency( $securityFee, $symbolCode, false );
		}

		// 11) Subtotal final para display
		$subtotalDisp = formatCurrency( $subtotal, $symbolCode, true );

		// Actualizar variables de display para VAT y APA
		$vatDisplay  = ( $vatToApply > 0 ) ? formatCurrency( $vatToApply, $symbolCode, false ) : '--';
		$vatRateDisp = $vatRate * 100; // Se mantiene el original para display

		$apaPercDisplay = ( $apaToApply > 0 ) ? formatCurrency( $apaToApply, $symbolCode, false ) : '--';
		$apaRateDisp    = $apaPercentage * 100; // Se mantiene el original para display

		// 12) Extras
		$extrasArr   = array();
		$extrasTotal = 0;
		if ( ! empty( $extras ) ) {
			foreach ( $extras as $ex ) {
				$exName       = $ex['extraName'] ?? '';
				$exCost       = floatval( str_replace( ',', '', ( $ex['extraCost'] ?? '0' ) ) );
				$extrasArr[]  = array(
					'name' => $exName,
					'cost' => formatCurrency( $exCost, $symbolCode, false ),
				);
				$extrasTotal += $exCost;
			}
		}

		// 13) Grand total
		$grandTotalVal  = $subtotal + $extrasTotal;
		$grandTotalDisp = ( ! empty( $extrasArr ) )
							? formatCurrency( $grandTotalVal, $symbolCode, true )
							: null;

		// 14) Gratuity:
		// Si es EUR => 10% - 15%.
		// Caso contrario => 15% - 20%.
		if ( $symbolCode === 'EUR' ) {
			$gRate1 = 10;
			$gRate2 = 15;
		} else {
			$gRate1 = 15;
			$gRate2 = 20;
		}

		$g1Calc     = ceil( $calculatedBaseRate * ( $gRate1 / 100 ) );
		$g2Calc     = ceil( $calculatedBaseRate * ( $gRate2 / 100 ) );
		$gratuities = array(
			array(
				'rate'   => $gRate1,
				'amount' => formatCurrency( $g1Calc, $symbolCode, true ),
			),
			array(
				'rate'   => $gRate2,
				'amount' => formatCurrency( $g2Calc, $symbolCode, true ),
			),
		);

		// 15) Armar el bloque final
		$structuredResults[] = array(
			'nights'                => ( $hours > 0 ) ? '--' : (string) $nights,
			'hours'                 => ( $hours > 0 ) ? (string) $hours : '--',
			'guests'                => (string) $guests,
			'calculatedBaseRate'    => formatCurrency( $calculatedBaseRate, $symbolCode, true ),
			'enableExpenses'        => $enableExpenses, // Añadir el flag enableExpenses al array de resultados

			'discountType'          => $discountType,
			'discountAmount'        => $discountAmountForDisplay, // Muestra el % o el valor fijo formateado
			'discountValue'         => $discountValueDisplay, // Muestra el valor calculado del descuento (si aplica)
			'discountedRate'        => ( $discountedRate !== $calculatedBaseRate && $discountedRate >= 0 )
								   ? formatCurrency( $discountedRate, $symbolCode, true )
								   : '--', // Mostrar solo si hubo descuento efectivo

			'promotionActive'       => $promotionActive ? '1' : '0',
			'promotionNights'       => (string) $promotionNights,
			'promotedRate'          => ( $promotionActive && $promotedRate >= 0 )
							   ? formatCurrency( $promotedRate, $symbolCode, true )
							   : '--',
			'promotionValueDisplay' => $promotionValueDisplay,

			'vatRateForDisplay'     => (string) $vatRateDisp,
			'vatDisplay'            => $vatDisplay,
			'mixed_breakdown'       => $mixed_breakdown,

			'apaRateForDisplay'     => (string) $apaRateDisp,
			'apaPercDisplay'        => $apaPercDisplay,
			'apaAmountDisplay'      => $apaAmountDisp,

			'relocationDisplay'     => $relocationDisp,
			'securityDisplay'       => $securityDisp,
			'subtotal'              => $subtotalDisp,
			'extras'                => $extrasArr,
			'grandTotal'            => $grandTotalDisp,
			'gratuityRates'         => $gratuities,
			
			'symbolCode'            => $symbolCode, // Add symbolCode to the block
		);
	}

	return $structuredResults;
}

/**
 * Formats the calculation results into an array of plain text strings.
 *
 * Takes the structured array produced by `calculate()` and converts it into
 * human-readable text blocks, respecting the user's preferences for hiding
 * certain elements.
 *
 * @since 1.0.0
 *
 * @param array $calcArray    The structured array of calculation results from `calculate()`.
 * @param array $hideElements An associative array indicating which elements should be hidden
 *                            (e.g., `['hideVAT' => true, 'hideAPA' => false]`).
 * @param bool  $enableExpenses Whether expenses are enabled for display.
 *
 * @return array<int, string> An array of strings, where each string is a fully formatted text block
 *                            representing a single charter calculation scenario, ready for display.
 */
function textResult( array $calcArray, array $hideElements = array(), bool $enableExpenses = false, bool $enableMixedSeasons = false ): array {
	$textBlocks = array();

	foreach ( $calcArray as $block ) {
		$separator = "---------------------------------------\n";
		$str       = $separator;

		

		// Hours vs nights (imprime siempre)
		if ( ! empty( $block['hours'] ) && $block['hours'] !== '--' ) {
			$str .= '<b>' . $block['hours'] . ' hours, ' . $block['guests'] . ' Guests: ' . $block['calculatedBaseRate'];
			if ( $enableExpenses ) {
				$str .= ' + Expenses';
			}
			$str .= "</b>\n";
		} else {
			$str .= '<b>' . $block['nights'] . ' nights, ' . $block['guests'] . ' Guests: ' . $block['calculatedBaseRate'];
			if ( $enableExpenses ) {
				$str .= ' + Expenses';
			}
			$str .= "</b>\n";
		}

		// Descuento
		if (
			! empty( $block['discountType'] ) &&
			$block['discountType'] !== '' &&
			$block['discountedRate'] !== '--' &&
			$block['discountAmount'] !== '€ 0' && 
			$block['discountAmount'] !== '0' &&
			$block['discountAmount'] !== '€ 0.00'
		) {
			$str          .= $separator;
			$discountLabel = ( $block['discountType'] === 'percentage' ) ? $block['discountAmount'] . '%' : $block['discountAmount'];
			$str          .= "Discount Rate - {$discountLabel} = {$block['discountedRate']}\n";
		}

		// Promoción
		if ( $block['promotedRate'] !== '--' ) {
			$str .= 'Promotion Rate ' . $block['promotionNights'] . 'x' . $block['nights'] . ' = ' . $block['promotedRate'] . "\n";
		}

		$str .= $separator;

		// Impuestos y tasas
		$taxStr = '';

		if ( ! empty( $block['mixed_breakdown'] ) ) {
			foreach ( $block['mixed_breakdown'] as $country ) {
				if ( ! $hideElements['hideVAT'] && $country['vat_rate'] > 0 ) {
					$taxStr .= 'VAT (' . $country['vat_rate'] . '%): ' . $country['nights'] . 'N ' . strtolower( $country['country_name'] ) . ': ' . $country['vat_amount_formatted'] . "\n";
				}
			}
		} else {
			if ( ! $hideElements['hideVAT'] && $block['vatDisplay'] !== '--' && (float) $block['vatRateForDisplay'] > 0 ) {
				$taxStr .= "VAT ({$block['vatRateForDisplay']}%): {$block['vatDisplay']}\n";
			}
		}

		// Siempre agregar impuestos globales
		if ( ! $hideElements['hideAPA'] ) {
			if ( $block['apaPercDisplay'] !== '--' && (float) str_replace( array( '€', '$', ',', ' ' ), '', $block['apaPercDisplay'] ) > 0 ) {
				$taxStr .= "APA ({$block['apaRateForDisplay']}%): {$block['apaPercDisplay']}\n";
			}
			if ( $block['apaAmountDisplay'] !== '--' ) {
				$taxStr .= "APA (amount): {$block['apaAmountDisplay']}\n";
			}
		}
		if ( ! $hideElements['hideRelocation'] && ! empty( $block['relocationDisplay'] ) ) {
			$taxStr .= "Relocation fee: {$block['relocationDisplay']}\n";
		}
		if ( ! $hideElements['hideSecurity'] && ! empty( $block['securityDisplay'] ) ) {
			$taxStr .= "Security deposit: {$block['securityDisplay']}\n";
		}

		if ( $taxStr !== '' ) {
			$str .= $taxStr . $separator;
		}

		// Subtotal
		$str .= "<b>Subtotal for charter: {$block['subtotal']}</b>\n";
		$str .= $separator;

		// Extras
		if ( ! $hideElements['hideExtras'] && ! empty( $block['extras'] ) ) {
			$str .= "<b>Extras</b>\n";
			$str .= $separator;
			foreach ( $block['extras'] as $extra ) {
				$str .= "{$extra['name']}: {$extra['cost']}\n";
			}
			$str .= $separator;
			$str .= "<b>Grand Total: {$block['grandTotal']}</b>\n";
			$str .= $separator;
		}

		// Propinas
		if ( ! $hideElements['hideGratuity'] && ! empty( $block['gratuityRates'] ) ) {
			foreach ( $block['gratuityRates'] as $gratuity ) {
				$str .= "Suggested gratuity ({$gratuity['rate']}%): {$gratuity['amount']}\n";
			}
		}

		$textBlocks[] = $str;
	}

	return $textBlocks;
}
