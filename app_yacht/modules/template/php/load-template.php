<?php


require_once __DIR__ . '/calculate-template.php';


$allowedTemplates = array( 'default-template', 'template-01', 'template-02' );


function handle_load_template_preview() {
	
	// Verificación de nonce con helper centralizado
	if ( function_exists( 'pb_verify_ajax_nonce' ) ) {
		pb_verify_ajax_nonce( $_GET['nonce'] ?? null, 'template_nonce', array( 'endpoint' => 'load_template_preview' ), 400 );
	} else if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'template_nonce' ) ) {
		wp_send_json_error( 'Invalid or missing nonce.', 400 );
		return;
	}
	
	if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
		wp_send_json_error( 'Invalid request method.', 400 );
	}

	global $allowedTemplates;
	$template = isset( $_GET['template'] ) ? sanitize_text_field( $_GET['template'] ) : '';
	if ( ! in_array( $template, $allowedTemplates, true ) ) {
		wp_send_json_error( 'Invalid template selected.', 400 );
	}

	$previewPath = get_template_directory() . "/app_yacht/modules/template/templates/{$template}-prev.php";
	if ( ! file_exists( $previewPath ) ) {
		wp_send_json_error( 'Template preview not found.', 404 );
	}

	ob_start();
	include $previewPath;
	$html = ob_get_clean();
	wp_die( $html );
}


function handle_create_template() {
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
		wp_send_json_error( 'Invalid request method.', 400 );
	}

	// Verificación de nonce con helper centralizado
	if ( function_exists( 'pb_verify_ajax_nonce' ) ) {
		pb_verify_ajax_nonce( $_POST['nonce'] ?? null, 'template_nonce', array( 'endpoint' => 'create_template' ), 400 );
	} else if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'template_nonce' ) ) {
		wp_send_json_error( 'Invalid or missing nonce.', 400 );
	}
	
	
	if ( ! pb_verify_user_capability( 'edit_yacht_templates', 'You do not have permission to create templates.' ) ) {
		return; 
	}

	global $allowedTemplates;
	$template = isset( $_POST['template'] ) ? sanitize_text_field( $_POST['template'] ) : 'default-template';
	if ( ! in_array( $template, $allowedTemplates, true ) ) {
		wp_send_json_error( 'Invalid template selected.', 400 );
	}

	
	$yachtUrl       = isset( $_POST['yachtUrl'] ) ? esc_url_raw( $_POST['yachtUrl'] ) : '';
	$lowSeasonText  = isset( $_POST['lowSeasonText'] ) ? sanitize_text_field( $_POST['lowSeasonText'] ) : '';
	$highSeasonText = isset( $_POST['highSeasonText'] ) ? sanitize_text_field( $_POST['highSeasonText'] ) : '';

	
	$yachtInfo = extraerInformacionYate( $yachtUrl );

	
	$currency   = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : '€';
	$symbolsMap = array(
		'€'     => 'EUR',
		'$USD'  => 'USD',
		'$AUD'  => 'AUD',
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
	}

	
	
	
	$data = array(
		'currency'            => $currency,
		'symbolCode'          => $symbolCode,
		'vatRate'             => ( isset( $_POST['vatRate'] ) && $_POST['vatRate'] !== '' )
							 ? floatval( str_replace( ',', '', $_POST['vatRate'] ) )
							 : 0,
		'apaPercentage'       => ( isset( $_POST['apaPercentage'] ) && $_POST['apaPercentage'] !== '' )
							 ? floatval( str_replace( ',', '', $_POST['apaPercentage'] ) )
							 : 0,
		'apaAmount'           => ( isset( $_POST['apaAmount'] ) && $_POST['apaAmount'] !== '' )
							 ? floatval( str_replace( ',', '', $_POST['apaAmount'] ) )
							 : 0,
		'relocationFee'       => ( isset( $_POST['relocationFee'] ) && $_POST['relocationFee'] !== '' )
							 ? floatval( str_replace( ',', '', $_POST['relocationFee'] ) )
							 : 0,
		'securityFee'         => ( isset( $_POST['securityFee'] ) && $_POST['securityFee'] !== '' )
							 ? floatval( str_replace( ',', '', $_POST['securityFee'] ) )
							 : 0,
		'charterRates'        => isset( $_POST['charterRates'] ) ? $_POST['charterRates'] : array(),
		'promotionNights'     => isset( $_POST['promotionNights'] ) ? intval( $_POST['promotionNights'] ) : 0,
		'promotionActive'     => isset( $_POST['promotionActive'] ) && $_POST['promotionActive'] === '1',
		'extras'              => isset( $_POST['extras'] ) ? $_POST['extras'] : array(),
		'enableOneDayCharter' => ( ! empty( $_POST['enableOneDayCharter'] ) && $_POST['enableOneDayCharter'] === '1' ),
		'enableMixedSeasons'  => ( ! empty( $_POST['enableMixedSeasons'] ) && $_POST['enableMixedSeasons'] === '1' ),
		
		'lowSeasonNights'     => isset( $_POST['lowSeasonNights'] ) ? intval( $_POST['lowSeasonNights'] ) : 0,
		'lowSeasonRate'       => isset( $_POST['lowSeasonRate'] )
								? floatval( str_replace( ',', '', $_POST['lowSeasonRate'] ) )
								: 0,
		'highSeasonNights'    => isset( $_POST['highSeasonNights'] ) ? intval( $_POST['highSeasonNights'] ) : 0,
		'highSeasonRate'      => isset( $_POST['highSeasonRate'] )
								? floatval( str_replace( ',', '', $_POST['highSeasonRate'] ) )
								: 0,
		'vatMix'              => $vatMix,
		'enableVatRateMix'    => $enableVatRateMix,
	);

	
	$resultArray = calcularResultadosTemplate( $data );

	if ( empty( $resultArray ) ) {
		error_log( 'Empty resultArray from calcularResultadosTemplate()' );
		error_log( 'Input data: ' . print_r( $data, true ) );
		wp_send_json_error( 'No calculation results available.', 400 );
	}

	
	$templatePath = get_template_directory() . "/app_yacht/modules/template/templates/{$template}.php";
	if ( ! file_exists( $templatePath ) ) {
		wp_send_json_error( 'Template file not found.', 404 );
	}

	
	$hideElements = array(
		'hideVAT'        => isset( $_POST['hideVAT'] ) && $_POST['hideVAT'] === '1',
		'hideAPA'        => isset( $_POST['hideAPA'] ) && $_POST['hideAPA'] === '1',
		'hideRelocation' => isset( $_POST['hideRelocation'] ) && $_POST['hideRelocation'] === '1',
		'hideSecurity'   => isset( $_POST['hideSecurity'] ) && $_POST['hideSecurity'] === '1',
		'hideExtras'     => isset( $_POST['hideExtras'] ) && $_POST['hideExtras'] === '1',
		'hideGratuity'   => isset( $_POST['hideGratuity'] ) && $_POST['hideGratuity'] === '1',
	);

	ob_start();
	$templateData = array(
		'resultArray'    => $resultArray,
		'lowSeasonText'  => $lowSeasonText,
		'highSeasonText' => $highSeasonText,
		'yachtInfo'      => $yachtInfo,
		'hideElements'   => $hideElements,
		'enableExpenses' => ( ! empty( $_POST['enableExpenses'] ) && $_POST['enableExpenses'] === '1' ),
	);
	include $templatePath;
	$html = ob_get_clean();

	wp_send_json_success( $html );
}
add_action( 'wp_ajax_createTemplate', 'handle_create_template' );
add_action( 'wp_ajax_nopriv_createTemplate', 'handle_create_template' );


function extraerInformacionYate( $url ) {
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return array(
			'yachtName'          => 'Invalid URL',
			'length'             => '--',
			'type'               => '--',
			'builder'            => '--',
			'yearBuilt'          => '--',
			'crew'               => '--',
			'cabins'             => '--',
			'guest'              => '--',
			'cabinConfiguration' => '--',
			'imageUrl'           => '',
			'yachtUrl'           => $url,
		);
	}
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	$html = curl_exec( $ch );
	if ( curl_errno( $ch ) ) {
		curl_close( $ch );
		return array(
			'yachtName'          => 'Curl Error',
			'length'             => '--',
			'type'               => '--',
			'builder'            => '--',
			'yearBuilt'          => '--',
			'crew'               => '--',
			'cabins'             => '--',
			'guest'              => '--',
			'cabinConfiguration' => '--',
			'imageUrl'           => '',
			'yachtUrl'           => $url,
		);
	}
	curl_close( $ch );

	$dom = new DOMDocument();
	
	@$dom->loadHTML( $html );
	$xpath = new DOMXPath( $dom );
	
	
	$yachtNameNode = $xpath->query( "//div[contains(@class, 'logo-wrapper')]/a/h2" )->item( 0 );
	$lengthNode    = $xpath->query( "//ul[contains(@class, 'yacht-summary-counts')]/li/span[contains(., 'Length : ')]" )->item( 0 );
	$builderNode   = $xpath->query( "//ul[contains(@class, 'yacht-summary-counts')]/li/span[contains(., 'Builder : ')]" )->item( 0 );
	$yearBuiltNode = $xpath->query( "//ul[contains(@class, 'yacht-summary-counts')]/li/span[contains(., 'Year Built : ')]" )->item( 0 );
	$crewNode      = $xpath->query( "//ul[contains(@class, 'yacht-summary-counts')]/li/span[contains(., 'Crew : ')]" )->item( 0 );
	$cabinsNode    = $xpath->query( "//ul[contains(@class, 'yacht-summary-counts')]/li/span[contains(., 'Cabins : ')]" )->item( 0 );
	$guestNode     = $xpath->query( "//ul[contains(@class, 'yacht-summary-counts')]/li/span[contains(., 'Guest : ')]" )->item( 0 );
	$cabinConfig   = $xpath->query( "//p/strong[contains(text(), 'Cabin Configuration')]/following-sibling::text()" )->item( 0 );
	$imgNode       = $xpath->query( "//div[contains(@class, 'header')]/@data-background" )->item( 0 );
	
	
	$typeNode = $xpath->query( "//div[@id='specifications-tab']//div[contains(@class, 'text-start')]/h4" )->item( 0 );
	$type     = $typeNode ? trim( $typeNode->textContent ) : '--';
	
	$imgUrl = $imgNode ? $imgNode->textContent : '';
	
	return array(
		'yachtName'          => $yachtNameNode ? trim( $yachtNameNode->textContent ) : 'Yacht Name',
		'length'             => $lengthNode ? trim( str_replace( 'Length :', '', $lengthNode->textContent ) ) : '--',
		'type'               => $type,
		'builder'            => $builderNode ? trim( str_replace( 'Builder :', '', $builderNode->textContent ) ) : '--',
		'yearBuilt'          => $yearBuiltNode ? trim( str_replace( 'Year Built :', '', $yearBuiltNode->textContent ) ) : '--',
		'crew'               => $crewNode ? trim( str_replace( 'Crew :', '', $crewNode->textContent ) ) : '--',
		'cabins'             => $cabinsNode ? trim( str_replace( 'Cabins :', '', $cabinsNode->textContent ) ) : '--',
		'guest'              => $guestNode ? trim( str_replace( 'Guest :', '', $guestNode->textContent ) ) : '--',
		'cabinConfiguration' => $cabinConfig ? trim( $cabinConfig->textContent ) : '--',
		'imageUrl'           => $imgUrl,
		'yachtUrl'           => $url,
	);
}
