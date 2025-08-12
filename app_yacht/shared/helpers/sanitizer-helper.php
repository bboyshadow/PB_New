<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class SanitizerHelper {
	public static function text( $value ) {
		return sanitize_text_field( $value );
	}

	public static function float( $value ) {
		$value = is_string( $value ) ? str_replace( ',', '', $value ) : $value;
		return floatval( $value );
	}

	public static function int( $value ) {
		return intval( $value );
	}

	public static function bool( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}
}