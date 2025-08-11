<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class ValidatorHelper {

	
	public static function isValidUrl( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}

	
	public static function isValidEmail( $email ) {
		return filter_var( $email, FILTER_VALIDATE_EMAIL ) !== false;
	}

	
	public static function isValidDecimal( $value ) {
		return is_numeric( $value ) && $value >= 0;
	}

	
	public static function isValidPercentage( $value ) {
		return is_numeric( $value ) && $value >= 0 && $value <= 100;
	}

	
	public static function isValidCurrency( $currency ) {
		$config = AppYachtConfig::get( 'calculation' );
		return in_array( $currency, $config['supported_currencies'] );
	}

	
	public static function validateRequired( array $data, array $required ) {
		$errors = array();

		foreach ( $required as $field ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				$errors[] = "Missing required field: {$field}";
			}
		}

		return $errors;
	}

	
	public static function sanitizeInputData( array $data ) {
		$sanitized = array();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = self::sanitizeInputData( $value );
			} elseif ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			} elseif ( is_numeric( $value ) ) {
				$sanitized[ $key ] = is_float( $value + 0 ) ? floatval( $value ) : intval( $value );
			} else {
				$sanitized[ $key ] = $value;
			}
		}

		return $sanitized;
	}

	
	public static function validateCalculationData( array $data ) {
		$errors = array();

		
		$required = array( 'currency' );
		$errors   = array_merge( $errors, self::validateRequired( $data, $required ) );

		
		if ( isset( $data['currency'] ) && ! self::isValidCurrency( $data['currency'] ) ) {
			$errors[] = 'Unsupported currency: ' . $data['currency'];
		}

		
		$numericFields = array( 'vatRate', 'apaAmount', 'apaPercentage', 'relocationFee', 'securityFee' );
		foreach ( $numericFields as $field ) {
			if ( isset( $data[ $field ] ) && ! empty( $data[ $field ] ) && ! self::isValidDecimal( $data[ $field ] ) ) {
				$errors[] = "Invalid numeric value for {$field}: " . $data[ $field ];
			}
		}

		return $errors;
	}

	
	public static function validateEmailData( array $data ) {
		$errors = array();

		
		$required = array( 'to', 'subject', 'message' );
		$errors   = array_merge( $errors, self::validateRequired( $data, $required ) );

		
		if ( isset( $data['to'] ) ) {
			$emails = explode( ',', $data['to'] );
			foreach ( $emails as $email ) {
				$email = trim( $email );
				if ( ! empty( $email ) && ! self::isValidEmail( $email ) ) {
					$errors[] = "Invalid email: {$email}";
				}
			}
		}

		return $errors;
	}
}
