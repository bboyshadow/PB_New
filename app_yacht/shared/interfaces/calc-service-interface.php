<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


interface CalcServiceInterface {

	
	public function calculateCharter( array $data);

	
	public function calculateMix( array $data);

	
	public function validateCalculationData( array $data);

	
	public function applyVAT( $amount, array $vatConfig);

	
	public function calculateAPA( $baseAmount, array $apaConfig);

	
	public function formatCurrency( $amount, $currency);
}
