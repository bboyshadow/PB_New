<?php


function formatCurrency( $value, $currencyCode, $round = false ) {
	$value    = $round ? ceil( $value ) : $value;
	$decimals = $round ? 0 : 2;

	
	$symbols = array(
		'EUR'  => '€',
		'USD'  => '$',
		'AUD'  => 'A$',
		'€'    => '€',
		'$USD' => '$',
		'$AUD' => 'A$',
	);

	$symbol = $symbols[ $currencyCode ] ?? '€';

	return $symbol . ' ' . number_format( $value, $decimals, '.', ',' );
}
