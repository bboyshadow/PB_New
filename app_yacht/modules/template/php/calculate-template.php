<?php
// ARCHIVO: modules/template/php/calculate-template.php

require_once get_template_directory() . '/app_yacht/modules/calc/php/calculate.php';
require_once get_template_directory() . '/app_yacht/modules/template/php/template-data.php';
require_once get_template_directory() . '/app_yacht/shared/php/currency-functions.php';

function calcularResultadosTemplate( array $data ): array {
	// Validar datos de entrada
	if ( empty( $data['charterRates'] ) ) {
		error_log( 'Empty charterRates in calcularResultadosTemplate()' );
		return array();
	}

	try {
		$structuredResults = calculate( $data ); // <-- llama a la del módulo 1
		return $structuredResults;
	} catch ( Exception $e ) {
		error_log( 'Error in calcularResultadosTemplate: ' . $e->getMessage() );
		return array();
	}
}
