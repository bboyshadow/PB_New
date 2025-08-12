<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DataValidator {
	public static function isPositiveNumber( $value ) {
		return is_numeric( $value ) && $value >= 0;
	}

	public static function isPercentage( $value ) {
		return is_numeric( $value ) && $value >= 0 && $value <= 100;
	}

	public static function required( $value ) {
		return ! ( $value === null || $value === '' );
	}
}