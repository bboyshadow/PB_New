<?php
/**
 * FILE core/data-validation.php
 * Sanitization and validation functions for the app_yacht application.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Sanitizes input data based on the specified type.
 *
 * @param mixed  $data The data to sanitize.
 * @param string $type The data type (email, url, number, int, html, textarea, array, text).
 * @return mixed Sanitized data.
 */
function pb_sanitize_input( $data, $type = 'text' ) {
	switch ( $type ) {
		case 'email':
			return sanitize_email( $data );
		case 'url':
			return esc_url_raw( $data );
		case 'number':
			return is_numeric( $data ) ? floatval( $data ) : 0;
		case 'int':
			return intval( $data );
		case 'html':
			return wp_kses_post( $data );
		case 'textarea':
			return sanitize_textarea_field( $data );
		case 'array':
			return array_map( 'sanitize_text_field', (array) $data );
		case 'text':
		default:
			return sanitize_text_field( $data );
	}
}

