<?php



global $yachtInfoGlobal;
$yachtInfoGlobal = array();

function initYachtScan( $url ) {
	global $yachtInfoGlobal;
	$yachtInfoGlobal = extraerInformacionYate( $url );
}

function buildYachtInfoArray( array $yachtInfo = array() ): array {
	return array(
		'yachtName'          => $yachtInfo['yachtName'] ?? '--',
		'length'             => $yachtInfo['length'] ?? '--',
		'type'               => $yachtInfo['type'] ?? '--',
		'builder'            => $yachtInfo['builder'] ?? '--',
		'yearBuilt'          => $yachtInfo['yearBuilt'] ?? '--',
		'crew'               => $yachtInfo['crew'] ?? '--',
		'cabins'             => $yachtInfo['cabins'] ?? '--',
		'guest'              => $yachtInfo['guest'] ?? '--',
		'cabinConfiguration' => $yachtInfo['cabinConfiguration'] ?? '--',
		'imageUrl'           => $yachtInfo['imageUrl'] ?? '',
		'yachtUrl'           => $yachtInfo['yachtUrl'] ?? '',
	);
}

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
	error_log( 'YachtScan: Fetched HTML length: ' . strlen( $html ) );
	if ( curl_errno( $ch ) ) {
		error_log( 'YachtScan: cURL error: ' . curl_error( $ch ) );
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

	$yachtNameNode = $xpath->query( '//title' )->item( 0 );
	
	error_log( 'YachtScan: HTML snippet: ' . substr( $html, 0, 500 ) . '...' );

	
	 $lengthNode    = $xpath->query( "//span[contains(strong, 'Length :')]" )->item( 0 );
	 $builderNode   = $xpath->query( "//span[contains(strong, 'Builder :')]" )->item( 0 );
	 $yearBuiltNode = $xpath->query( "//span[contains(strong, 'Year Built :')]" )->item( 0 );
	 $crewNode      = $xpath->query( "//span[contains(strong, 'Crew :') and not(contains(., '#Crew'))]" )->item( 0 );
	 $cabinsNode    = $xpath->query( "//span[contains(strong, 'Cabins :')]" )->item( 0 );
	 $guestNode     = $xpath->query( "//span[contains(strong, 'Guest :')]" )->item( 0 );
	 $kingNode      = $xpath->query( "//span[contains(strong, 'King :')]" )->item( 0 );
	 $queenNode     = $xpath->query( "//span[contains(strong, 'Queen :')]" )->item( 0 );

	 error_log( 'YachtScan: lengthNode content: ' . ( $lengthNode ? $lengthNode->textContent : 'null' ) );
	 error_log( 'YachtScan: builderNode content: ' . ( $builderNode ? $builderNode->textContent : 'null' ) );
	 error_log( 'YachtScan: yearBuiltNode content: ' . ( $yearBuiltNode ? $yearBuiltNode->textContent : 'null' ) );
	 error_log( 'YachtScan: crewNode content: ' . ( $crewNode ? $crewNode->textContent : 'null' ) );
	 error_log( 'YachtScan: cabinsNode content: ' . ( $cabinsNode ? $cabinsNode->textContent : 'null' ) );
	 error_log( 'YachtScan: guestNode content: ' . ( $guestNode ? $guestNode->textContent : 'null' ) );
	 error_log( 'YachtScan: kingNode content: ' . ( $kingNode ? $kingNode->textContent : 'null' ) );
	 error_log( 'YachtScan: queenNode content: ' . ( $queenNode ? $queenNode->textContent : 'null' ) );

	
	error_log(
		'YachtScan: XPath results - Length: ' . ( $lengthNode ? 'found' : 'not found' ) .
			  ', Builder: ' . ( $builderNode ? 'found' : 'not found' ) .
			  ', Year: ' . ( $yearBuiltNode ? 'found' : 'not found' )
	);
	$imgNode  = $xpath->query( "//div[contains(@class, 'header') and @data-background]/@data-background" )->item( 0 );
	$typeNode = $xpath->query( "//p[contains(@class, 'yacht-description')]" )->item( 0 );
	$type     = '--';
	if ( $typeNode ) {
		preg_match( '/\d+ Feet (.*?), built/', $typeNode->textContent, $matches );
		$type = $matches[1] ?? '--';
	}
	$cabinConfig = '';
	if ( $kingNode && $queenNode ) {
		$kingNum     = trim( preg_replace( '/.*King\s*:\s*(\d+).*/i', '$1', $kingNode->textContent ) );
		$queenNum    = trim( preg_replace( '/.*Queen\s*:\s*(\d+).*/i', '$1', $queenNode->textContent ) );
		$cabinConfig = $kingNum . ' King, ' . $queenNum . ' Queen';
	}
	$imgUrl = $imgNode ? $imgNode->nodeValue : '';

	$result = array(
		'yachtName'          => $yachtNameNode ? trim( explode( ' Yacht Charters', $yachtNameNode->textContent )[0] ) : 'Yacht Name',
		'length'             => $lengthNode ? trim( preg_replace( '/^.*Length\s*:\s*/i', '', $lengthNode->textContent ) ) : '--',
		'type'               => $type,
		'builder'            => $builderNode ? trim( preg_replace( '/^.*Builder\s*:\s*/i', '', $builderNode->textContent ) ) : '--',
		'yearBuilt'          => $yearBuiltNode ? trim( preg_replace( '/^.*Year Built\s*:\s*/i', '', $yearBuiltNode->textContent ) ) : '--',
		'crew'               => $crewNode ? trim( preg_replace( '/^.*Crew\s*:\s*/i', '', $crewNode->textContent ) ) : '--',
		'cabins'             => $cabinsNode ? trim( preg_replace( '/^.*Cabins\s*:\s*/i', '', $cabinsNode->textContent ) ) : '--',
		'guest'              => $guestNode ? trim( preg_replace( '/^.*Guest\s*:\s*/i', '', $guestNode->textContent ) ) : '--',
		'cabinConfiguration' => $cabinConfig ?: '--',
		'description'        => $typeNode ? trim( $typeNode->textContent ) : '--',
		'imageUrl'           => $imgUrl,
		'yachtUrl'           => $url,
	);
	error_log( 'YachtScan: Extracted info: ' . json_encode( $result ) );
	return $result;
}


function buildCalcSnippetArray( array $resultArray, string $lowSeasonText = '', string $highSeasonText = '' ): array {
	
	$lowInfo = '';
	$lowCost = '';
	if ( $lowSeasonText ) {
		$lowParts = explode( ':', $lowSeasonText, 2 );
		$lowInfo  = trim( $lowParts[0] ?? '' );
		$lowCost  = trim( $lowParts[1] ?? '' );
	}

	
	$highInfo = '';
	$highCost = '';
	if ( $highSeasonText ) {
		$highParts = explode( ':', $highSeasonText, 2 );
		$highInfo  = trim( $highParts[0] ?? '' );
		$highCost  = trim( $highParts[1] ?? '' );
	}

	
	$block = $resultArray[0] ?? array();

	
	
	global $templateData;
	if ( isset( $templateData ) && isset( $templateData['enableExpenses'] ) ) {
		$block['enableExpenses'] = $templateData['enableExpenses'];

		
		
		foreach ( $resultArray as $key => $value ) {
			$resultArray[ $key ]['enableExpenses'] = $templateData['enableExpenses'];
		}
	} elseif ( isset( $resultArray[0]['enableExpenses'] ) ) {
		
		$block['enableExpenses'] = $resultArray[0]['enableExpenses'];
	}

	
	return array(
		'lowMixInfo'      => $lowInfo,
		'lowMixCost'      => $lowCost,
		'highMixInfo'     => $highInfo,
		'highMixCost'     => $highCost,
		'structuredBlock' => $block, 
	);
}
