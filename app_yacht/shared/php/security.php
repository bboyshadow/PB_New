<?php
/**
 * ARCHIVO shared/php/security.php
 * Implementa funciones centralizadas de seguridad para la aplicación App_Yacht
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente
}

/**
 * Sanitiza el contenido HTML para prevenir ataques XSS
 *
 * @param string $content El contenido HTML a sanitizar
 * @return string El contenido HTML sanitizado
 */
function pb_sanitize_html_content( $content ) {
	// Lista de etiquetas y atributos permitidos
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

	// Sanitizar usando wp_kses
	$clean_content = wp_kses( $content, $allowed_html );

	// Sanitización adicional para URLs
	$clean_content = preg_replace_callback(
		'/href=(["\'])([^"\']*)(["\'])/i',
		function( $matches ) {
			$url = esc_url( $matches[2] );
			return "href={$matches[1]}{$url}{$matches[3]}";
		},
		$clean_content
	);

	return $clean_content;
}

/**
 * Encripta datos utilizando openssl_encrypt para proteger información sensible
 *
 * @param mixed  $data Los datos a encriptar
 * @param string $key La clave de encriptación (opcional, usa wp_salt('auth') por defecto)
 * @return string Los datos encriptados en formato base64
 */
function pb_encrypt_data( $data, $key = '' ) {
	if ( empty( $key ) ) {
		$key = wp_salt( 'auth' );
	}
	$method = 'aes-256-cbc';
	$iv     = substr( hash( 'sha256', wp_salt( 'secure_auth' ) ), 0, 16 );

	$encrypted = openssl_encrypt( $data, $method, $key, 0, $iv );
	return base64_encode( $encrypted );
}

/**
 * Desencripta datos encriptados con pb_encrypt_data
 *
 * @param string $encrypted_data Los datos encriptados en formato base64
 * @param string $key La clave de encriptación (opcional, usa wp_salt('auth') por defecto)
 * @return mixed Los datos desencriptados
 */
function pb_decrypt_data( $encrypted_data, $key = '' ) {
	if ( empty( $key ) ) {
		$key = wp_salt( 'auth' );
	}
	$method = 'aes-256-cbc';
	$iv     = substr( hash( 'sha256', wp_salt( 'secure_auth' ) ), 0, 16 );

	$decrypted = openssl_decrypt( base64_decode( $encrypted_data ), $method, $key, 0, $iv );
	return $decrypted;
}

/**
 * Implementa un sistema de límite de velocidad para las solicitudes
 *
 * @param string $key Clave única para identificar el tipo de solicitud
 * @param int    $max_attempts Número máximo de intentos permitidos en el período de tiempo
 * @param int    $time_window Período de tiempo en segundos para el límite
 * @return bool True si está dentro del límite, False si se ha excedido
 */
function pb_check_rate_limit( $key, $max_attempts = 5, $time_window = 300 ) {
	try {
		// Validar parámetros de entrada
		if ( empty( $key ) ) {
			error_log( 'Error en pb_check_rate_limit: Clave vacía' );
			return true; // Permitir la operación en caso de error
		}

		// Sanitizar la clave para evitar caracteres problemáticos
		$safe_key = sanitize_key( 'rate_limit_' . $key );

		// Obtener el valor actual con manejo de errores
		try {
			$attempts = get_transient( $safe_key );
		} catch ( Exception $e ) {
			error_log( 'Error al obtener transient para rate limit: ' . $e->getMessage() );
			return true; // Permitir la operación en caso de error
		}

		// Si no existe el transient, crearlo
		if ( $attempts === false ) {
			try {
				$result = set_transient( $safe_key, 1, $time_window );
				if ( $result === false ) {
					error_log( 'Error al establecer transient para rate limit: ' . $safe_key );
				}
			} catch ( Exception $e ) {
				error_log( 'Excepción al establecer transient: ' . $e->getMessage() );
			}
			return true;
		}

		// Verificar si se ha excedido el límite
		if ( is_numeric( $attempts ) && $attempts >= $max_attempts ) {
			return false; // Límite excedido
		}

		// Incrementar el contador de intentos
		try {
			// Asegurar que attempts sea numérico
			$attempts = is_numeric( $attempts ) ? intval( $attempts ) : 0;
			$result   = set_transient( $safe_key, $attempts + 1, $time_window );
			if ( $result === false ) {
				error_log( 'Error al actualizar transient para rate limit: ' . $safe_key );
			}
		} catch ( Exception $e ) {
			error_log( 'Excepción al actualizar transient: ' . $e->getMessage() );
		}

		return true;
	} catch ( Exception $e ) {
		error_log( 'Error general en pb_check_rate_limit: ' . $e->getMessage() );
		return true; // En caso de error, permitir la operación
	} catch ( Throwable $t ) {
		// Capturar errores fatales en PHP 7+
		error_log( 'Error fatal en pb_check_rate_limit: ' . $t->getMessage() );
		return true; // En caso de error fatal, permitir la operación
	}
}

/**
 * Registra un evento de seguridad.
 * (Implementación simple usando error_log por ahora)
 *
 * @param int    $user_id ID del usuario (0 si no aplica).
 * @param string $event_type Tipo de evento (ej. 'login_failed', 'unauthorized_access').
 * @param array  $details Detalles adicionales del evento.
 */
function pb_log_security_event( $user_id, $event_type, $details = array() ) {
	// Asegurarse de que las funciones de WP estén disponibles si se llama muy temprano
	if ( ! function_exists( 'sanitize_key' ) || ! function_exists( 'wp_json_encode' ) ) {
		// Cargar wp-load.php podría ser demasiado pesado o causar otros problemas.
		// Usar funciones PHP básicas como fallback.
		$event_type   = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $event_type ); // Sanitización básica
		$details_json = json_encode( $details ); // Usar json_encode nativo
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
