<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


function pb_validate_email( $email ) {
	return filter_var( $email, FILTER_VALIDATE_EMAIL ) !== false;
}


function pb_validate_url( $url ) {
	return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
}


function pb_validate_int( $value, $options = array() ) {
	if ( ! is_numeric( $value ) || intval( $value ) != $value ) {
		return false;
	}

	$value = intval( $value );

	
	if ( isset( $options['min'] ) && $value < $options['min'] ) {
		return false;
	}

	if ( isset( $options['max'] ) && $value > $options['max'] ) {
		return false;
	}

	return true;
}


function pb_validate_float( $value, $options = array() ) {
	if ( ! is_numeric( $value ) ) {
		return false;
	}

	$value = floatval( $value );

	
	if ( isset( $options['min'] ) && $value < $options['min'] ) {
		return false;
	}

	if ( isset( $options['max'] ) && $value > $options['max'] ) {
		return false;
	}

	
	if ( isset( $options['decimals'] ) ) {
		$parts = explode( '.', (string) $value );
		if ( isset( $parts[1] ) && strlen( $parts[1] ) > $options['decimals'] ) {
			return false;
		}
	}

	return true;
}


function pb_validate_in_array( $value, $allowed_values ) {
	return in_array( $value, $allowed_values, true );
}


function pb_validate_length( $value, $options = array() ) {
	$length = strlen( $value );

	if ( isset( $options['min_length'] ) && $length < $options['min_length'] ) {
		return false;
	}

	if ( isset( $options['max_length'] ) && $length > $options['max_length'] ) {
		return false;
	}

	return true;
}


function pb_validate_regex( $value, $pattern ) {
	return preg_match( $pattern, $value ) === 1;
}


function pb_validate_date( $date, $format = 'Y-m-d' ) {
	$d = DateTime::createFromFormat( $format, $date );
	return $d && $d->format( $format ) === $date;
}


function pb_validate_data( $data, $schema ) {
	$errors = array();

	foreach ( $schema as $field => $rules ) {
		
		if ( isset( $rules['required'] ) && $rules['required'] && ( ! isset( $data[ $field ] ) || $data[ $field ] === '' ) ) {
			$errors[ $field ] = isset( $rules['error_messages']['required'] )
				? $rules['error_messages']['required']
				: "El campo {$field} es obligatorio.";
			continue;
		}

		
		if ( ! isset( $data[ $field ] ) ) {
			continue;
		}

		
		if ( isset( $rules['type'] ) ) {
			$value = $data[ $field ];
			$valid = true;

			switch ( $rules['type'] ) {
				case 'email':
					$valid = pb_validate_email( $value );
					break;
				case 'url':
					$valid = pb_validate_url( $value );
					break;
				case 'int':
					$options = isset( $rules['options'] ) ? $rules['options'] : array();
					$valid   = pb_validate_int( $value, $options );
					break;
				case 'float':
					$options = isset( $rules['options'] ) ? $rules['options'] : array();
					$valid   = pb_validate_float( $value, $options );
					break;
				case 'in':
					$valid = pb_validate_in_array( $value, $rules['allowed_values'] );
					break;
				case 'length':
					$options = isset( $rules['options'] ) ? $rules['options'] : array();
					$valid   = pb_validate_length( $value, $options );
					break;
				case 'regex':
					$valid = pb_validate_regex( $value, $rules['pattern'] );
					break;
				case 'date':
					$format = isset( $rules['format'] ) ? $rules['format'] : 'Y-m-d';
					$valid  = pb_validate_date( $value, $format );
					break;
			}

			if ( ! $valid ) {
				$error_key        = 'invalid_' . $rules['type'];
				$errors[ $field ] = isset( $rules['error_messages'][ $error_key ] )
					? $rules['error_messages'][ $error_key ]
					: "The field {$field} is not valid.";
			}
		}

		
		if ( isset( $rules['custom_validator'] ) && is_callable( $rules['custom_validator'] ) ) {
			$result = call_user_func( $rules['custom_validator'], $data[ $field ], $data );
			if ( $result !== true ) {
				$errors[ $field ] = $result;
			}
		}
	}

	return $errors;
}
