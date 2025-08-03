<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


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

