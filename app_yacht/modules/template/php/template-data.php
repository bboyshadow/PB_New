<?php



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
