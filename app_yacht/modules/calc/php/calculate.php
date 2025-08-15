<?php

















require_once __DIR__ . '/../../../shared/php/currency-functions.php'; 


add_action( 'wp_ajax_calculate_charter', 'handle_calculate_charter' );
add_action( 'wp_ajax_nopriv_calculate_charter', 'handle_calculate_charter' );


/**
 * Handler AJAX para cálculo de chárter estándar
 *
 * Valida el nonce, normaliza y valida entradas, ejecuta el cálculo principal
 * y devuelve un JSON con el resultado formateado para la UI.
 *
 * Espera vía POST:
 * - currency: string
 * - charterRate: float
 * - vatRate: float
 * - apaAmount: float
 * - apaPercentage: float
 * - relocationFee: float
 * - securityFee: float
 * - extras: array
 *
 * @return void Imprime y finaliza con wp_send_json_success/wp_send_json_error
 */
function handle_calculate_charter() {
	
	// Verificación de nonce con helper centralizado
	if ( function_exists( 'pb_verify_ajax_nonce' ) ) {
		pb_verify_ajax_nonce( $_POST['nonce'] ?? null, 'calculate_nonce', array( 'endpoint' => 'calculate_charter' ), 400 );
	} else if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'calculate_nonce' ) ) {
		// Log security failure if enhanced logging enabled
		if ( class_exists( 'Logger' ) ) {
			Logger::warning( 'Yacht calculation: Nonce verification failed', array(
				'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
				'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
			) );
		}
		wp_send_json_error( array( 'error' => 'Invalid nonce.' ), 400 );
		return;
	}

	// Log calculation request start
	if ( class_exists( 'Logger' ) ) {
		Logger::info( 'Yacht calculation request started', array(
			'post_data_size' => count( $_POST ),
			'user_id'        => get_current_user_id(),
		) );
	}

	// Validación de entrada con DataValidator cuando la feature esté activa
	$features = class_exists( 'AppYachtConfig' ) ? ( AppYachtConfig::get()['features'] ?? array() ) : array();
	if ( ! empty( $features['data_validation'] ) && class_exists( 'DataValidator' ) ) {
		$errors = array();

		$currency      = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';
		$vatRateRaw    = isset( $_POST['vatRate'] ) ? $_POST['vatRate'] : '';
		$apaAmount     = isset( $_POST['apaAmount'] ) ? $_POST['apaAmount'] : '';
		$apaPercentage = isset( $_POST['apaPercentage'] ) ? $_POST['apaPercentage'] : '';
		$relocationFee = isset( $_POST['relocationFee'] ) ? $_POST['relocationFee'] : '';
		$securityFee   = isset( $_POST['securityFee'] ) ? $_POST['securityFee'] : '';

		if ( ! DataValidator::required( $currency ) ) {
			$errors['currency'] = 'Currency is required';
		}

		$enableVatRateMixFlag = ( ! empty( $_POST['vatRateMix'] ) && $_POST['vatRateMix'] === '1' );
		if ( $enableVatRateMixFlag ) {
			// Validación cuando el VAT Mix está activo: vatRate puede llegar como array (vatRate[])
			if ( is_array( $vatRateRaw ) ) {
				foreach ( $vatRateRaw as $i => $vr ) {
					$vrNum = str_replace( ',', '', (string) $vr );
					if ( $vrNum !== '' && ! DataValidator::isPercentage( $vrNum ) ) {
						$errors[ "vatRate[$i]" ] = 'VAT must be a percentage between 0 and 100';
					}
				}
			} else {
				$vrNum = str_replace( ',', '', (string) $vatRateRaw );
				if ( $vrNum !== '' && ! DataValidator::isPercentage( $vrNum ) ) {
					$errors['vatRate'] = 'VAT must be a percentage between 0 and 100';
				}
			}
		} else {
			// Validación estándar (no mix): vatRate debe ser porcentaje único
			if ( ! is_array( $vatRateRaw ) ) {
				$vrNum = str_replace( ',', '', (string) $vatRateRaw );
				if ( $vrNum !== '' && ! DataValidator::isPercentage( $vrNum ) ) {
					$errors['vatRate'] = 'VAT must be a percentage between 0 and 100';
				}
			} else {
				// Si llegara como array accidentalmente, validar cada uno para evitar 422
				foreach ( $vatRateRaw as $i => $vr ) {
					$vrNum = str_replace( ',', '', (string) $vr );
					if ( $vrNum !== '' && ! DataValidator::isPercentage( $vrNum ) ) {
						$errors[ "vatRate[$i]" ] = 'VAT must be a percentage between 0 y 100';
					}
				}
			}
		}

		foreach ( array( 'apaAmount' => $apaAmount, 'apaPercentage' => $apaPercentage, 'relocationFee' => $relocationFee, 'securityFee' => $securityFee ) as $key => $val ) {
			$valNum = str_replace( ',', '', $val );
			if ( $valNum !== '' && ! DataValidator::isPositiveNumber( $valNum ) ) {
				$errors[ $key ] = ucfirst( $key ) . ' must be a positive number';
			}
		}

		$charterRates = isset( $_POST['charterRates'] ) ? $_POST['charterRates'] : array();
		if ( ! is_array( $charterRates ) ) {
			$errors['charterRates'] = 'charterRates must be an array';
		} else {
			foreach ( $charterRates as $idx => $rate ) {
				$baseRate = isset( $rate['baseRate'] ) ? str_replace( ',', '', $rate['baseRate'] ) : '';
				if ( $baseRate === '' || ! DataValidator::isPositiveNumber( $baseRate ) ) {
					$errors["charterRates[$idx][baseRate]"] = 'baseRate must be a positive number';
				}
				$discountType   = $rate['discountType'] ?? '';
				$discountAmount = isset( $rate['discountAmount'] ) ? str_replace( ',', '', $rate['discountAmount'] ) : '';
				$discountActive = ! empty( $rate['discountActive'] ) && $rate['discountActive'] === '1';
				if ( $discountActive && $discountType !== '' && $discountAmount !== '' ) {
					if ( $discountType === 'percentage' ) {
						if ( ! DataValidator::isPercentage( $discountAmount ) ) {
							$errors["charterRates[$idx][discountAmount]"] = 'discountAmount must be a percentage between 0 and 100';
						}
					} else {
						if ( ! DataValidator::isPositiveNumber( $discountAmount ) ) {
							$errors["charterRates[$idx][discountAmount]"] = 'discountAmount must be a positive number';
						}
					}
				}
			}
		}

		if ( ! empty( $errors ) ) {
			if ( class_exists( 'Logger' ) ) {
				Logger::warning( 'Yacht calculation: Validation failed', array( 'errors' => $errors ) );
			}
			wp_send_json_error( array( 'error' => 'Validation error', 'fields' => $errors ), 422 );
			return;
		}
	}

	$currency            = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '';
	// vatRate puede ser string o array si está activo el VAT Mix; en este punto, sólo conservar string
	if ( isset( $_POST['vatRate'] ) ) {
		$vatRate = is_array( $_POST['vatRate'] ) ? '' : sanitize_text_field( $_POST['vatRate'] );
	} else {
		$vatRate = '';
	}
	$apaAmount           = isset( $_POST['apaAmount'] ) ? sanitize_text_field( $_POST['apaAmount'] ) : '';
	$apaPercentage       = isset( $_POST['apaPercentage'] ) ? sanitize_text_field( $_POST['apaPercentage'] ) : '';
	$relocationFee       = isset( $_POST['relocationFee'] ) ? sanitize_text_field( $_POST['relocationFee'] ) : '';
	$securityFee         = isset( $_POST['securityFee'] ) ? sanitize_text_field( $_POST['securityFee'] ) : '';
	$charterRates        = isset( $_POST['charterRates'] ) ? $_POST['charterRates'] : array();
	$extras              = isset( $_POST['extras'] ) ? $_POST['extras'] : array();
	$enableMixedSeasons  = ! empty( $_POST['enableMixedSeasons'] ) && $_POST['enableMixedSeasons'] === '1';
	$enableOneDayCharter = ! empty( $_POST['enableOneDayCharter'] ) && $_POST['enableOneDayCharter'] === '1';
	$enableExpenses      = ! empty( $_POST['enableExpenses'] ) && $_POST['enableExpenses'] === '1';

	$hideElements = array(
		'hideVAT'        => isset( $_POST['hideVAT'] ) && $_POST['hideVAT'] === '1',
		'hideAPA'        => isset( $_POST['hideAPA'] ) && $_POST['hideAPA'] === '1',
		'hideRelocation' => isset( $_POST['hideRelocation'] ) && $_POST['hideRelocation'] === '1',
		'hideSecurity'   => isset( $_POST['hideSecurity'] ) && $_POST['hideSecurity'] === '1',
		'hideExtras'     => isset( $_POST['hideExtras'] ) && $_POST['hideExtras'] === '1',
		'hideGratuity'   => isset( $_POST['hideGratuity'] ) && $_POST['hideGratuity'] === '1',
	);

	
	if ( ! is_array( $charterRates ) ) {
		wp_send_json_error( array( 'error' => 'charterRates no es un array.' ), 400 );
		return;
	}

	
	$symbolsMap = array(
		'€'    => 'EUR',
		'$USD' => 'USD',
		'$AUD' => 'AUD',
	);
	$symbolCode = isset( $symbolsMap[ $currency ] ) ? $symbolsMap[ $currency ] : 'EUR';

	
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
		if ( class_exists( 'Logger' ) ) {
			Logger::debug( 'VatMix constructed', array( 'vatMix' => $vatMix ) );
		} else {
			// fallback legacy
			// error_log( 'VatMix constructed: ' . print_r( $vatMix, true ) );
		}
	}

	
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

	
	$calcArray = calculate( $data );

	
	$textOutput = textResult( $calcArray, $hideElements, $enableExpenses, $enableMixedSeasons );

	
	wp_send_json_success( $textOutput );
	if ( class_exists( 'Logger' ) ) {
		Logger::info( 'Yacht calculation request completed successfully' );
	}
}

function calculate( array $data ): array {
	
	$currency     = $data['currency'] ?? '€';
	$symbolCode   = $data['symbolCode'] ?? 'EUR';
	$vatRateStr   = $data['vatRate'] ?? '0';
	$apaAmountStr = $data['apaAmount'] ?? '0';
	$apaPercStr   = $data['apaPercentage'] ?? '0';
	$relocFeeStr  = $data['relocationFee'] ?? '0';
	$secFeeStr    = $data['securityFee'] ?? '0';

	$enableVatRateMix = ! empty( $data['enableVatRateMix'] );
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
	
	$structuredResults = array();

	
	foreach ( $charterRates as $rate ) {
		$guests = (int) ( $rate['guests'] ?? 0 );
		$nights = (int) ( $rate['nights'] ?? 0 );
		$hours  = (int) ( $rate['hours'] ?? 0 );

		$baseRaw  = isset( $rate['baseRate'] ) ? str_replace( ',', '', $rate['baseRate'] ) : '0';
		$baseRate = floatval( $baseRaw );

		$discountType   = $rate['discountType'] ?? '';
		$discountAmount = isset( $rate['discountAmount'] ) ? floatval( str_replace( ',', '', $rate['discountAmount'] ) ) : 0;
		$discountActive = ( ! empty( $rate['discountActive'] ) && $rate['discountActive'] === '1' );

		
		if ( $oneDayActive ) {
			
			$calculatedBaseRate = ceil( $baseRate );
		} else {
			if ( $mixedActive ) {
				
				$calculatedBaseRate = ceil( ( $baseRate * 7 ) / 7 );
			} else {
				
				$divisor            = ( $nights <= 5 ) ? 6 : 7;
				$calculatedBaseRate = ceil( ( $baseRate * $nights ) / $divisor );
			}
		}

		
		$discountedRate           = $calculatedBaseRate;
		$discountValueDisplay     = '--'; 
		$discountAmountForDisplay = $discountAmount; 

		if ( $discountActive && $discountAmount > 0 && $discountType !== '' ) {
			if ( $discountType === 'percentage' ) {
				$descValue                = $calculatedBaseRate * ( $discountAmount / 100 );
				$discountedRate           = $calculatedBaseRate - $descValue;
				$discountValueDisplay     = formatCurrency( $descValue, $symbolCode, true ); 
				$discountAmountForDisplay = (string) $discountAmount; 
			} else {
				
				$discountedRate           = $calculatedBaseRate - $discountAmount;
				$discountValueDisplay     = formatCurrency( $discountAmount, $symbolCode, true ); 
				$discountAmountForDisplay = formatCurrency( $discountAmount, $symbolCode, true ); 
			}
			
			if ( $discountedRate < 0 ) {
				$discountedRate = 0;
			}
		}

		$promotionNights = (int) ( $rate['promotionNights'] ?? 0 );
		$promotionActive = ( ! empty( $rate['promotionActive'] ) && $rate['promotionActive'] === '1' );

		$promotedRate          = $discountedRate;
		$promotionValueDisplay = '--';

		if ( $promotionActive && $promotionNights > 0 ) {
			// Avoid division by zero when nights is zero (e.g., one-day charter / hours mode)
			if ( $nights > 0 ) {
				$promotedRate = ceil( ( $discountedRate / $nights ) * $promotionNights );
			} else {
				// When nights are not provided, keep discountedRate (skip promotion adjustment)
				$promotedRate = $discountedRate;
			}
			if ( $promotedRate < 0 ) {
				$promotedRate = 0;
			}
			$promotionValueDisplay = formatCurrency( $promotedRate, $symbolCode, true );
		}

		
		$subtotal = $promotedRate;

		
		$vatCalc     = $promotedRate * $vatRate;
		$apaPercCalc = $promotedRate * $apaPercentage;

		$mixed_vat_total = 0;
		$mixed_breakdown = array();

		$vatToApply = $vatCalc;
		$apaToApply = $apaPercCalc;

		
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

		
		$subtotal += $vatToApply;
		$subtotal += $apaToApply;

		
		$subtotal += $relocationFee;
		$subtotal += $securityFee;
		
		
		$apaAmountDisp = '';
		if ( $apaAmount > 0 ) {
			$subtotal     += $apaAmount;
			$apaAmountDisp = formatCurrency( $apaAmount, $symbolCode, false );
		} else {
			$apaAmountDisp = '--';
		}

		
		$relocationDisp = '';
		if ( $relocationFee > 0 ) {
			$relocationDisp = formatCurrency( $relocationFee, $symbolCode, false );
		}

		
		$securityDisp = '';
		if ( $securityFee > 0 ) {
			$securityDisp = formatCurrency( $securityFee, $symbolCode, false );
		}

		
		$subtotalDisp = formatCurrency( $subtotal, $symbolCode, true );

		
		$vatDisplay  = ( $vatToApply > 0 ) ? formatCurrency( $vatToApply, $symbolCode, false ) : '--';
		$vatRateDisp = $vatRate * 100; 

		$apaPercDisplay = ( $apaToApply > 0 ) ? formatCurrency( $apaToApply, $symbolCode, false ) : '--';
		$apaRateDisp    = $apaPercentage * 100; 

		
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

		
		$grandTotalVal  = $subtotal + $extrasTotal;
		$grandTotalDisp = ( ! empty( $extrasArr ) )
							? formatCurrency( $grandTotalVal, $symbolCode, true )
							: null;

		
		
		
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

		
		$structuredResults[] = array(
			'nights'                => ( $hours > 0 ) ? '--' : (string) $nights,
			'hours'                 => ( $hours > 0 ) ? (string) $hours : '--',
			'guests'                => (string) $guests,
			'calculatedBaseRate'    => formatCurrency( $calculatedBaseRate, $symbolCode, true ),
			'enableExpenses'        => $enableExpenses, 

			'discountType'          => $discountType,
			'discountAmount'        => $discountAmountForDisplay, 
			'discountValue'         => $discountValueDisplay, 
			'discountedRate'        => ( $discountedRate !== $calculatedBaseRate && $discountedRate >= 0 )
						   ? formatCurrency( $discountedRate, $symbolCode, true )
						   : '--', 

			'promotionActive'       => $promotionActive ? '1' : '0',
			'promotionNights'       => (string) $promotionNights,
			'promotedRate'          => ( $promotionActive && $promotedRate >= 0 && $hours == 0 )
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
			
			'symbolCode'            => $symbolCode, 
		);
	}

	return $structuredResults;
}


function textResult( array $calcArray, array $hideElements = array(), bool $enableExpenses = false, bool $enableMixedSeasons = false ): array {
	$textBlocks = array();

	foreach ( $calcArray as $block ) {
		$separator = "---------------------------------------\n";
		$str       = $separator;

		

		
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

		
		if ( $block['promotedRate'] !== '--' ) {
			$str .= 'Promotion Rate ' . $block['promotionNights'] . 'x' . $block['nights'] . ' = ' . $block['promotedRate'] . "\n";
		}

		$str .= $separator;

		
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

		
		$str .= "<b>Subtotal for charter: {$block['subtotal']}</b>\n";
		$str .= $separator;

		
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

		
		if ( ! $hideElements['hideGratuity'] && ! empty( $block['gratuityRates'] ) ) {
			foreach ( $block['gratuityRates'] as $gratuity ) {
				$str .= "Suggested gratuity ({$gratuity['rate']}%): {$gratuity['amount']}\n";
			}
		}

		$textBlocks[] = $str;
	}

	return $textBlocks;
}
