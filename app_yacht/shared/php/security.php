<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


function pb_sanitize_html_content( $content ) {
	
	$allowed_html = array(
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'target' => true,
		),
		'p'      => array( 'style' => true ),
		'br'     => array(),
		'strong' => array(),
		'em'     => array(),
		'h1'     => array( 'style' => true ),
		'h2'     => array( 'style' => true ),
		'h3'     => array( 'style' => true ),
		'ul'     => array( 'style' => true ),
		'ol'     => array( 'style' => true ),
		'li'     => array( 'style' => true ),
		'span'   => array( 'style' => true ),
		'div'    => array( 'style' => true ),
		'table'  => array(
			'style'       => true,
			'border'      => true,
			'cellpadding' => true,
			'cellspacing' => true,
		),
		'tbody'  => array(),
		'tr'     => array( 'style' => true ),
		'td'     => array(
			'style'   => true,
			'colspan' => true,
			'rowspan' => true,
		),
		'img'    => array(
			'src'    => true,
			'alt'    => true,
			'style'  => true,
			'width'  => true,
			'height' => true,
		),
	);

	
	$clean_content = wp_kses( $content, $allowed_html );

	
	$clean_content = preg_replace_callback(
		'/href=((["\']))([^"\']*)(["\'])/i',
		function( $matches ) {
			$url = esc_url( $matches[3] );
			return "href={$matches[1]}{$url}{$matches[4]}";
		},
		$clean_content
	);

	return $clean_content;
}


function pb_encrypt_data( $data, $key = '' ) {
	if ( empty( $key ) ) {
		$key = wp_salt( 'auth' );
	}
	$method = 'aes-256-cbc';
	$iv     = substr( hash( 'sha256', wp_salt( 'secure_auth' ) ), 0, 16 );

	$encrypted = openssl_encrypt( $data, $method, $key, 0, $iv );
	return base64_encode( $encrypted );
}


function pb_decrypt_data( $encrypted_data, $key = '' ) {
	if ( empty( $key ) ) {
		$key = wp_salt( 'auth' );
	}
	$method = 'aes-256-cbc';
	$iv     = substr( hash( 'sha256', wp_salt( 'secure_auth' ) ), 0, 16 );

	$decrypted = openssl_decrypt( base64_decode( $encrypted_data ), $method, $key, 0, $iv );
	return $decrypted;
}


function pb_check_rate_limit( $key, $max_attempts = 5, $time_window = 300 ) {
	try {
		
		if ( empty( $key ) ) {
			error_log( 'Error in pb_check_rate_limit: Empty key' );
			return true; 
		}

		
		$safe_key = sanitize_key( 'rate_limit_' . $key );

		
		try {
			$attempts = get_transient( $safe_key );
		} catch ( Exception $e ) {
			error_log( 'Error getting transient for rate limit: ' . $e->getMessage() );
			return true; 
		}

		
		if ( $attempts === false ) {
			try {
				$result = set_transient( $safe_key, 1, $time_window );
				if ( $result === false ) {
					error_log( 'Error al establecer transient para rate limit: ' . $safe_key );
				}
			} catch ( Exception $e ) {
				error_log( 'Exception when updating transient: ' . $e->getMessage() );
			}
			return true;
		}

		
		if ( is_numeric( $attempts ) && $attempts >= $max_attempts ) {
			return false; 
		}

		
		try {
			
			$attempts = is_numeric( $attempts ) ? intval( $attempts ) : 0;
			$result   = set_transient( $safe_key, $attempts + 1, $time_window );
			if ( $result === false ) {
				error_log( 'Error updating transient for rate limit: ' . $safe_key );
			}
		} catch ( Exception $e ) {
			error_log( 'Exception when updating transient: ' . $e->getMessage() );
		}

		return true;
	} catch ( Exception $e ) {
		error_log( 'General error in pb_check_rate_limit: ' . $e->getMessage() );
		return true; 
	} catch ( Throwable $t ) {
		
		error_log( 'Fatal error in pb_check_rate_limit: ' . $t->getMessage() );
		return true; 
	}
}


function pb_log_security_event( $user_id, $event_type, $details = array() ) {
	
	if ( ! function_exists( 'sanitize_key' ) || ! function_exists( 'wp_json_encode' ) ) {
		
		
		$event_type   = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $event_type ); 
		$details_json = json_encode( $details ); 
	} else {
		$event_type   = sanitize_key( $event_type );
		$details_json = wp_json_encode( $details );
	}

	$log_message = sprintf(
		'[App_Yacht Security Event] User: %d | Event: %s | Details: %s | IP: %s | URI: %s',
		intval( $user_id ),
		$event_type,
		$details_json,
		$_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
		$_SERVER['REQUEST_URI'] ?? 'UNKNOWN'
	);
	error_log( $log_message );
}


/**
 * Verifica un nonce AJAX de forma centralizada.
 * - Loguea el evento de seguridad cuando falla (pb_log_security_event y Logger si existe)
 * - Responde con wp_send_json_error y termina la ejecución si es inválido
 * - Devuelve true si es válido
 *
 * @param mixed  $nonce        Valor del nonce recibido (string o null)
 * @param string $action       Acción/handle del nonce
 * @param array  $context      Datos extra para logs (endpoint, etc.)
 * @param int    $http_status  Código HTTP para la respuesta de error
 * @return bool
 */
function pb_verify_ajax_nonce( $nonce, $action, $context = array(), $http_status = 400 ) {
	if ( isset( $nonce ) && wp_verify_nonce( $nonce, $action ) ) {
		return true;
	}

	$details = array_merge(
		array(
			'reason'     => 'invalid_or_missing_nonce',
			'action'     => $action,
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
		),
		is_array( $context ) ? $context : array( 'context' => (string) $context )
	);

	if ( function_exists( 'pb_log_security_event' ) ) {
		pb_log_security_event( get_current_user_id(), 'ajax_nonce_failed', $details );
	}
	if ( class_exists( 'Logger' ) ) {
		Logger::warning( 'AJAX nonce verification failed', $details );
	}

	wp_send_json_error( array( 'error' => 'Invalid or missing nonce.' ), $http_status );
}
