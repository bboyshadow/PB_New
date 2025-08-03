<?php
// Archivo: modules/template/php/template-data.php

/**
 * buildYachtInfoArray($yachtInfo)
 * Retorna un array con la info del yate
 */
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

/**
 * buildCalcSnippetArray($resultArray, $lowSeasonText, $highSeasonText)
 * Combina la info de "mix text" + el primer (o varios) bloques de $resultArray
 */
function buildCalcSnippetArray( array $resultArray, string $lowSeasonText = '', string $highSeasonText = '' ): array {
	// 1) Parsear Low Season
	$lowInfo = '';
	$lowCost = '';
	if ( $lowSeasonText ) {
		$lowParts = explode( ':', $lowSeasonText, 2 );
		$lowInfo  = trim( $lowParts[0] ?? '' );
		$lowCost  = trim( $lowParts[1] ?? '' );
	}

	// 2) Parsear High Season
	$highInfo = '';
	$highCost = '';
	if ( $highSeasonText ) {
		$highParts = explode( ':', $highSeasonText, 2 );
		$highInfo  = trim( $highParts[0] ?? '' );
		$highCost  = trim( $highParts[1] ?? '' );
	}

	// 3) Tomar el primer "block"
	$block = $resultArray[0] ?? array();
	
	// Asegurarse de que enableExpenses se mantenga en el bloque estructurado
	// si no está presente en el bloque, pero sí en templateData global
	global $templateData;
	if ( isset( $templateData ) && isset( $templateData['enableExpenses'] ) ) {
		$block['enableExpenses'] = $templateData['enableExpenses'];
		
		// También aplicar enableExpenses a todos los elementos en resultArray
		// para asegurar que esté disponible en cada bloque cuando se itera en el template
		foreach ( $resultArray as $key => $value ) {
			$resultArray[ $key ]['enableExpenses'] = $templateData['enableExpenses'];
		}
	} elseif ( isset( $resultArray[0]['enableExpenses'] ) ) {
		// Si no está en templateData pero sí en el primer resultado, usamos ese valor
		$block['enableExpenses'] = $resultArray[0]['enableExpenses'];
	}

	// 4) Armar array final con nombres consistentes
	return array(
		'lowMixInfo'      => $lowInfo,
		'lowMixCost'      => $lowCost,
		'highMixInfo'     => $highInfo,
		'highMixCost'     => $highCost,
		'structuredBlock' => $block, // nombre consistente con default-template.php
	);
}
