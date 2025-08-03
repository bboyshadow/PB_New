<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}






define('PB_OUTLOOK_CLIENT_ID', '730ac41d-30c8-446c-b6bc-9470d539fdee');
define('PB_OUTLOOK_CLIENT_SECRET', '.ii8Q~GeEeqre-rBd568TNxt.oOEiREv9_XzLdeB');

define('PB_OUTLOOK_REDIRECT_URI', 'https://www.probroke.com/appyacht/auth');
define('PB_OUTLOOK_SCOPES', 'openid profile offline_access User.Read Mail.Send');


function pb_outlook_get_login_url() {
	$base_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
	$params   = array(
		'client_id'     => PB_OUTLOOK_CLIENT_ID,
		'response_type' => 'code',
		'redirect_uri'  => PB_OUTLOOK_REDIRECT_URI,
		'scope'         => PB_OUTLOOK_SCOPES,
	);
	return $base_url . '?' . http_build_query( $params );
}


function pb_outlook_exchange_code_for_tokens( $code ) {
	
	
	pb_log_security_event(
		get_current_user_id(),
		'oauth_token_exchange',
		array(
			'success' => false,
			'error'   => null,
		)
	);

	$url      = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
	$params   = array(
		'client_id'     => PB_OUTLOOK_CLIENT_ID,
		'client_secret' => PB_OUTLOOK_CLIENT_SECRET,
		'grant_type'    => 'authorization_code',
		'code'          => $code,
		'redirect_uri'  => PB_OUTLOOK_REDIRECT_URI,
	);
	$response = wp_remote_post(
		$url,
		array(
			'body'    => $params,
			'timeout' => 30,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $data['error'] ) ) {
		return new WP_Error( 'token_error', $data['error_description'] ?? 'Error al obtener tokens' );
	}

	
	pb_log_security_event(
		get_current_user_id(),
		'oauth_token_exchange',
		array(
			'success' => true,
			'error'   => null,
		)
	);

	return $data;
}


function pb_outlook_save_tokens_to_user( $user_id, $tokens ) {
	
	$access_token  = $tokens['access_token'] ?? '';
	$refresh_token = $tokens['refresh_token'] ?? '';
	$expires_in    = isset( $tokens['expires_in'] ) ? time() + (int) $tokens['expires_in'] : 0;

	
	$encrypted_access_token  = pb_encrypt_data( $access_token );
	$encrypted_refresh_token = pb_encrypt_data( $refresh_token );

	update_user_meta( $user_id, 'outlook_access_token', $encrypted_access_token );
	update_user_meta( $user_id, 'outlook_refresh_token', $encrypted_refresh_token );
	update_user_meta( $user_id, 'outlook_expires_at', $expires_in );

	
	pb_log_security_event( $user_id, 'oauth_tokens_saved' );

	
	if ( ! empty( $tokens['id_token'] ) ) {
		$parts = explode( '.', $tokens['id_token'] );
		if ( count( $parts ) === 3 ) {
			$payload = json_decode( base64_decode( $parts[1] ), true );
			if ( isset( $payload['preferred_username'] ) ) {
				update_user_meta( $user_id, 'outlook_email', sanitize_email( $payload['preferred_username'] ) );
			}
		}
	}
}


function pb_outlook_get_user_access_token( $user_id ) {
	$encrypted_access_token  = get_user_meta( $user_id, 'outlook_access_token', true );
	$encrypted_refresh_token = get_user_meta( $user_id, 'outlook_refresh_token', true );
	$expires_at              = (int) get_user_meta( $user_id, 'outlook_expires_at', true );

	if ( ! $encrypted_access_token || ! $encrypted_refresh_token ) {
		return new WP_Error( 'missing_tokens', 'El usuario no tiene tokens de Outlook.' );
	}

	
	$access_token  = pb_decrypt_data( $encrypted_access_token );
	$refresh_token = pb_decrypt_data( $encrypted_refresh_token );

	
	if ( ! $access_token || ! $refresh_token ) {
		return new WP_Error( 'missing_tokens', 'El usuario no tiene tokens de Outlook.' );
	}
	
	if ( time() >= $expires_at ) {
		$new_tokens = pb_outlook_refresh_token( $refresh_token );
		if ( is_wp_error( $new_tokens ) ) {
			return $new_tokens;
		}
		pb_outlook_save_tokens_to_user( $user_id, $new_tokens );
		return $new_tokens['access_token'] ?? '';
	}
	return $access_token;
}


function pb_outlook_refresh_token( $refresh_token ) {
	$url      = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
	$params   = array(
		'client_id'     => PB_OUTLOOK_CLIENT_ID,
		'client_secret' => PB_OUTLOOK_CLIENT_SECRET,
		'grant_type'    => 'refresh_token',
		'refresh_token' => $refresh_token,
	);
	$response = wp_remote_post(
		$url,
		array(
			'body'    => $params,
			'timeout' => 30,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $data['error'] ) ) {
		return new WP_Error( 'refresh_error', $data['error_description'] ?? 'Error al refrescar tokens' );
	}
	return $data;
}


function pb_outlook_send_mail_on_behalf( $user_id, $to, $subject, $body_content, $cc = '', $bcc = '' ) {
	
	if ( ! is_ssl() && ! WP_DEBUG ) {
		 return new WP_Error( 'insecure_connection', 'Esta operación requiere una conexión segura (HTTPS).' );
	}

	 
	 
	 $access_token = pb_outlook_get_user_access_token( $user_id );
	if ( is_wp_error( $access_token ) ) {
		
		return $access_token;
	}
	if ( empty( $access_token ) ) { 
		 
		 return new WP_Error( 'token_error', 'No se pudo obtener un token de acceso válido.' );
	}

	
	$clean_body = pb_sanitize_html_content( $body_content );

	
	$wrapped_html = '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0;">
' . $clean_body . '
</body>
 </html>';

	 
	 
	 $sendMailUrl = 'https://graph.microsoft.com/v1.0/me/sendMail';
	$requestBody  = array(
		'message' => array(
			'subject'      => $subject,
			'body'         => array(
				'contentType' => 'html',
				'content'     => $wrapped_html,
			),
			'toRecipients' => array(),
		),
	);

	
	if ( is_string( $to ) && strpos( $to, ',' ) !== false ) {
		$to_array = array_map( 'trim', explode( ',', $to ) );
	} else {
		$to_array = is_array( $to ) ? $to : array( $to );
	}
	
	foreach ( $to_array as $email ) {
		$sanitized_email = sanitize_email( trim( $email ) ); 
		if ( ! empty( $sanitized_email ) && is_email( $sanitized_email ) ) { 
			$requestBody['message']['toRecipients'][] = array(
				'emailAddress' => array( 'address' => $sanitized_email ),
			);
		}
	}

	
	if ( ! empty( $cc ) ) {
		$cc_array = is_string( $cc ) && strpos( $cc, ',' ) !== false ?
			array_map( 'trim', explode( ',', $cc ) ) :
			( is_array( $cc ) ? $cc : array( $cc ) );

		$cc_recipients = array();
		foreach ( $cc_array as $email ) {
			$sanitized_email = sanitize_email( trim( $email ) );
			if ( ! empty( $sanitized_email ) && is_email( $sanitized_email ) ) {
				$cc_recipients[] = array(
					'emailAddress' => array( 'address' => $sanitized_email ),
				);
			}
		}

		if ( ! empty( $cc_recipients ) ) {
			$requestBody['message']['ccRecipients'] = $cc_recipients;
		}
	}

	
	if ( ! empty( $bcc ) ) {
		$bcc_array = is_string( $bcc ) && strpos( $bcc, ',' ) !== false ?
			array_map( 'trim', explode( ',', $bcc ) ) :
			( is_array( $bcc ) ? $bcc : array( $bcc ) );
			
		$bcc_recipients = array();
		foreach ( $bcc_array as $email ) {
			$sanitized_email = sanitize_email( trim( $email ) );
			if ( ! empty( $sanitized_email ) && is_email( $sanitized_email ) ) {
				$bcc_recipients[] = array(
					'emailAddress' => array( 'address' => $sanitized_email ),
				);
			}
		}

		if ( ! empty( $bcc_recipients ) ) {
			$requestBody['message']['bccRecipients'] = $bcc_recipients;
		}
	}

	$response = wp_remote_post(
		$sendMailUrl,
		array(
			'headers'    => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'body'       => json_encode( $requestBody ),
			'timeout'    => 30,
			'sslverify'  => true, 
		)
	);

	 

	if ( is_wp_error( $response ) ) {
		
		error_log( 'pb_outlook_send_mail_on_behalf: Error en wp_remote_post: ' . $response->get_error_message() ); 
		return new WP_Error( 'send_mail_failed', $response->get_error_message() );
	}
	$status_code = wp_remote_retrieve_response_code( $response );
	if ( $status_code >= 200 && $status_code < 300 ) {
		
		pb_log_security_event(
			$user_id,
			'email_sent',
			array(
				'to'      => $to,
				'subject' => $subject,
			) 
		);
		return true;
	}
	
	error_log( 'Error al enviar correo. Respuesta HTTP: ' . $status_code . ' => ' . wp_remote_retrieve_body( $response ) );
	return new WP_Error(
		'send_mail_error',
		'Error al enviar correo. Respuesta HTTP: ' . $status_code . ' => ' . wp_remote_retrieve_body( $response )
	);
}


function pb_outlook_is_connected( $user_id ) {
	
	$encrypted_access_token  = get_user_meta( $user_id, 'outlook_access_token', true );
	$encrypted_refresh_token = get_user_meta( $user_id, 'outlook_refresh_token', true );
	
	return ( ! empty( $encrypted_access_token ) && ! empty( $encrypted_refresh_token ) );
}




function pb_outlook_send_mail_ajax_handler() {
	
	$user_id   = get_current_user_id();
	$limit_key = 'mail_send_' . $user_id;

	if ( ! pb_check_rate_limit( $limit_key, 10, 300 ) ) {
		wp_send_json_error( 'Has excedido el límite de intentos. Por favor, inténtalo más tarde.', 429 );
		 return;
	}
	 check_ajax_referer( 'pb_outlook_nonce', 'nonce' ); 
	 

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'No estás autenticado en WordPress.', 401 );
	}
	$user_id = get_current_user_id();
	
	
	if ( ! pb_verify_user_capability( 'send_yacht_emails', 'No tienes permisos para enviar correos.' ) ) {
		return; 
	}

	$to      = $_POST['to'] ?? '';
	$cc      = $_POST['cc'] ?? '';
	$bcc     = $_POST['bcc'] ?? '';
	$subject = sanitize_text_field( $_POST['subject'] ?? '' );
	$body    = wp_unslash( $_POST['body'] ?? '' );

	if ( empty( $to ) || empty( $subject ) || empty( $body ) ) {
		wp_send_json_error( 'Faltan campos obligatorios.', 400 );
	}

	
	$signature = get_user_meta( $user_id, 'msp_signature', true );
	if ( ! empty( $signature ) ) {
		 $body .= '<br><br>' . $signature;
	}

	 
	 
	 $result = pb_outlook_send_mail_on_behalf( $user_id, $to, $subject, $body, $cc, $bcc );
	 
	 

	if ( is_wp_error( $result ) ) {
		
		wp_send_json_error( $result->get_error_message(), 500 ); 
	}
	wp_send_json_success( 'Correo enviado exitosamente.' );
}


function pb_outlook_disconnect_user( $user_id ) {
	try {
		
		if ( ! get_user_by( 'id', $user_id ) ) {
			
			pb_log_security_event(
				$user_id,
				'outlook_disconnect_failed',
				array(
					'reason'  => 'user_not_found',
					'user_id' => $user_id,
				)
			);
			return false;
		}
		
		
		pb_log_security_event( $user_id, 'outlook_disconnect_started' );
		
		
		$meta_keys = array(
			'outlook_access_token',
			'outlook_refresh_token',
			'outlook_expires_at',
			'outlook_email',
		);
		
		
		$success = true;
		$results = array();
		
		foreach ( $meta_keys as $meta_key ) {
			$result               = delete_user_meta( $user_id, $meta_key );
			$results[ $meta_key ] = $result !== false ? 'success' : 'failed';
			
			if ( $result === false ) {
				$success = false;
			}
		}
		
		
		pb_log_security_event(
			$user_id,
			'outlook_disconnect_completed',
			array(
				'success' => $success,
				'results' => $results,
			)
		);
		
		return $success;
	} catch ( Exception $e ) {
		
		pb_log_security_event(
			$user_id,
			'outlook_disconnect_error',
			array(
				'error_type' => 'exception',
				'message'    => $e->getMessage(),
			)
		);
		error_log( 'Excepción en pb_outlook_disconnect_user: ' . $e->getMessage() );
		return false; 
	} catch ( Throwable $t ) {
		
		pb_log_security_event(
			$user_id,
			'outlook_disconnect_error',
			array(
				'error_type' => 'fatal',
				'message'    => $t->getMessage(),
			)
		);
		error_log( 'Error fatal en pb_outlook_disconnect_user: ' . $t->getMessage() );
		return false; 
	}
}



function pb_outlook_disconnect_ajax_handler() {
	
	try {
		
		pb_log_security_event( 0, 'outlook_disconnect_ajax_started' );
		
		
		if ( ! isset( $_POST['nonce'] ) ) {
			pb_log_security_event( 0, 'outlook_disconnect_ajax_failed', array( 'reason' => 'missing_nonce' ) );
			wp_send_json_error( 'Error de seguridad: Token no proporcionado. Por favor, recarga la página e intenta de nuevo.', 403 );
			return;
		}
		
		
		$nonce_verification = wp_verify_nonce( $_POST['nonce'], 'pb_outlook_nonce' );
		if ( ! $nonce_verification ) {
			pb_log_security_event( 0, 'outlook_disconnect_ajax_failed', array( 'reason' => 'invalid_nonce' ) );
			wp_send_json_error( 'Error de seguridad: Token inválido o expirado. Por favor, recarga la página e intenta de nuevo.', 403 );
			return;
		}
		
		
		if ( ! is_user_logged_in() ) {
			pb_log_security_event( 0, 'outlook_disconnect_ajax_failed', array( 'reason' => 'not_authenticated' ) );
			wp_send_json_error( 'Debes iniciar sesión para realizar esta acción.', 401 );
			return;
		}
		
		
		$user_id = get_current_user_id();
		
		
		
		$result = pb_outlook_disconnect_user( $user_id ); 
		
		
		if ( $result ) {
			pb_log_security_event( $user_id, 'outlook_disconnect_ajax_success' );
			wp_send_json_success( 'Tu cuenta de Outlook ha sido desconectada correctamente.' );
		} else {
			pb_log_security_event( $user_id, 'outlook_disconnect_ajax_failed', array( 'reason' => 'disconnect_failed' ) );
			wp_send_json_error( 'No se pudo desconectar tu cuenta de Outlook. Por favor, intenta de nuevo más tarde.' );
		}
	} catch ( Exception $e ) {
		pb_log_security_event(
			get_current_user_id(),
			'outlook_disconnect_ajax_error',
			array(
				'error_type' => 'exception',
				'message'    => $e->getMessage(),
			) 
		);
		error_log( 'Excepción en pb_outlook_disconnect_ajax_handler: ' . $e->getMessage() );
		wp_send_json_error( 'Error al procesar la solicitud: ' . $e->getMessage() );
	} catch ( Throwable $t ) {
		pb_log_security_event(
			get_current_user_id(),
			'outlook_disconnect_ajax_error',
			array(
				'error_type' => 'fatal',
				'message'    => $t->getMessage(),
			) 
		);
		error_log( 'Error fatal en pb_outlook_disconnect_ajax_handler: ' . $t->getMessage() );
		wp_send_json_error( 'Error interno al procesar la solicitud.' );
	}
}
