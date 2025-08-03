<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../shared/helpers/validator-helper.php';


class MailService implements MailServiceInterface {
	
	
	private $config;
	
	
	private $renderEngine;
	
	
	public function __construct( array $config, RenderEngineInterface $renderEngine ) {
		$this->config       = $config;
		$this->renderEngine = $renderEngine;
	}
	
	
	public function sendEmail( array $data ) {
		try {
			
			$validation = $this->validateEmailData( $data );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
			
			
			$data = ValidatorHelper::sanitizeInputData( $data );
			
			
			$userId = get_current_user_id();
			
			
			if ( $this->config['outlook_enabled'] && $this->isOutlookConnected( $userId ) ) {
				return $this->sendEmailViaOutlook( $data, $userId );
			}
			
			
			return $this->sendEmailViaWordPress( $data );
			
		} catch ( Exception $e ) {
			error_log( 'MailService Error: ' . $e->getMessage() );
			return new WP_Error( 'mail_error', 'Error enviando email: ' . $e->getMessage() );
		}
	}
	
	
	public function sendEmailViaOutlook( array $data, $userId ) {
		try {
			
			if ( ! function_exists( 'pb_outlook_send_mail' ) ) {
				return new WP_Error( 'outlook_not_available', 'Funciones de Outlook no disponibles' );
			}
			
			
			$outlookData = $this->prepareOutlookData( $data, $userId );
			
			
			$result = pb_outlook_send_mail( $outlookData, $userId );
			
			if ( is_wp_error( $result ) ) {
				
				error_log( 'Outlook Send Error: ' . $result->get_error_message() );
				
				
				if ( $this->config['fallback_to_wp_mail'] ?? true ) {
					return $this->sendEmailViaWordPress( $data );
				}
				
				return $result;
			}
			
			return true;
			
		} catch ( Exception $e ) {
			error_log( 'MailService Outlook Error: ' . $e->getMessage() );
			return new WP_Error( 'outlook_error', 'Error enviando via Outlook: ' . $e->getMessage() );
		}
	}
	
	
	public function isOutlookConnected( $userId ) {
		if ( function_exists( 'pb_outlook_is_connected' ) ) {
			return pb_outlook_is_connected( $userId );
		}
		
		
		$accessToken = get_user_meta( $userId, 'outlook_access_token', true );
		return ! empty( $accessToken );
	}
	
	
	public function getOutlookLoginUrl() {
		if ( function_exists( 'pb_outlook_get_login_url' ) ) {
			return pb_outlook_get_login_url();
		}
		
		return '#'; 
	}
	
	
	public function disconnectOutlook( $userId ) {
		try {
			if ( function_exists( 'pb_outlook_disconnect_user' ) ) {
				return pb_outlook_disconnect_user( $userId );
			}
			
			
			delete_user_meta( $userId, 'outlook_access_token' );
			delete_user_meta( $userId, 'outlook_refresh_token' );
			delete_user_meta( $userId, 'outlook_email' );
			delete_user_meta( $userId, 'outlook_expires_at' );
			
			return true;
			
		} catch ( Exception $e ) {
			error_log( 'MailService Disconnect Error: ' . $e->getMessage() );
			return false;
		}
	}
	
	
	public function validateEmailData( array $data ) {
		$errors = ValidatorHelper::validateEmailData( $data );
		
		
		
		
		if ( isset( $data['to'] ) ) {
			$recipients = explode( ',', $data['to'] );
			if ( count( $recipients ) > $this->config['max_recipients'] ) {
				$errors[] = 'Excede el límite máximo de ' . $this->config['max_recipients'] . ' destinatarios';
			}
		}
		
		
		if ( isset( $data['attachments'] ) && ! empty( $data['attachments'] ) ) {
			$attachmentValidation = $this->validateAttachments( $data['attachments'] );
			if ( is_wp_error( $attachmentValidation ) ) {
				$errors = array_merge( $errors, $attachmentValidation->get_error_messages() );
			}
		}
		
		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', 'Errores de validación', $errors );
		}
		
		return true;
	}
	
	
	public function processAttachments( array $attachments ) {
		$processed = array();
		
		foreach ( $attachments as $attachment ) {
			
			if ( $attachment['size'] > $this->config['attachment_max_size'] ) {
				return new WP_Error( 'attachment_too_large', 'Archivo muy grande: ' . $attachment['name'] );
			}
			
			
			$extension = pathinfo( $attachment['name'], PATHINFO_EXTENSION );
			if ( ! in_array( strtolower( $extension ), $this->config['allowed_attachment_types'] ) ) {
				return new WP_Error( 'attachment_type_not_allowed', 'Tipo de archivo no permitido: ' . $extension );
			}
			
			$processed[] = $attachment;
		}
		
		return $processed;
	}
	
	
	public function generateSignature( $userId ) {
		if ( ! $this->config['signature_enabled'] ) {
			return '';
		}
		
		
		$customSignature = get_user_meta( $userId, 'yacht_email_signature', true );
		if ( ! empty( $customSignature ) ) {
			return $customSignature;
		}
		
		
		$user = get_user_by( 'id', $userId );
		if ( ! $user ) {
			return '';
		}
		
		$signature = $this->renderEngine->render(
			'email-signature',
			array(
				'user_name'    => $user->display_name,
				'user_email'   => $user->user_email,
				'company_name' => get_bloginfo( 'name' ),
				'website'      => home_url(),
			),
			'email'
		);
		
		return $signature;
	}
	
	
	private function sendEmailViaWordPress( array $data ) {
		try {
			$to          = $this->parseEmailRecipients( $data['to'] );
			$subject     = $data['subject'];
			$message     = $this->prepareEmailContent( $data );
			$headers     = $this->prepareEmailHeaders( $data );
			$attachments = $this->prepareAttachments( $data );
			
			$sent = wp_mail( $to, $subject, $message, $headers, $attachments );
			
			if ( ! $sent ) {
				return new WP_Error( 'wp_mail_failed', 'Error enviando email con wp_mail' );
			}
			
			return true;
			
		} catch ( Exception $e ) {
			error_log( 'wp_mail Error: ' . $e->getMessage() );
			return new WP_Error( 'wp_mail_error', 'Error en wp_mail: ' . $e->getMessage() );
		}
	}
	
	
	private function prepareOutlookData( array $data, $userId ) {
		$outlookData = array(
			'to'      => $data['to'],
			'subject' => $data['subject'],
			'body'    => $this->prepareEmailContent( $data ),
			'is_html' => true,
		);
		
		
		if ( ! empty( $data['cc'] ) ) {
			$outlookData['cc'] = $data['cc'];
		}
		
		
		if ( ! empty( $data['bcc'] ) ) {
			$outlookData['bcc'] = $data['bcc'];
		}
		
		
		if ( ! empty( $data['attachments'] ) ) {
			$outlookData['attachments'] = $this->processAttachments( $data['attachments'] );
		}
		
		return $outlookData;
	}
	
	
	private function prepareEmailContent( array $data ) {
		$content = $data['message'];
		
		
		if ( $this->config['signature_enabled'] ) {
			$userId    = get_current_user_id();
			$signature = $this->generateSignature( $userId );
			
			if ( ! empty( $signature ) ) {
				$content .= "\n\n" . $signature;
			}
		}
		
		return $content;
	}
	
	
	private function prepareEmailHeaders( array $data ) {
		$headers = array();
		
		
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		
		
		if ( ! empty( $data['from'] ) ) {
			$headers[] = 'From: ' . $data['from'];
		} else {
			$headers[] = 'From: ' . $this->config['default_sender'];
		}
		
		
		if ( ! empty( $data['reply_to'] ) ) {
			$headers[] = 'Reply-To: ' . $data['reply_to'];
		}
		
		
		if ( ! empty( $data['cc'] ) ) {
			$headers[] = 'Cc: ' . $data['cc'];
		}
		
		
		if ( ! empty( $data['bcc'] ) ) {
			$headers[] = 'Bcc: ' . $data['bcc'];
		}
		
		return $headers;
	}
	
	
	private function prepareAttachments( array $data ) {
		if ( empty( $data['attachments'] ) ) {
			return array();
		}
		
		$attachments = array();
		
		foreach ( $data['attachments'] as $attachment ) {
			if ( isset( $attachment['path'] ) && file_exists( $attachment['path'] ) ) {
				$attachments[] = $attachment['path'];
			}
		}
		
		return $attachments;
	}
	
	
	private function parseEmailRecipients( $to ) {
		if ( is_string( $to ) ) {
			return array_map( 'trim', explode( ',', $to ) );
		}
		
		return is_array( $to ) ? $to : array( $to );
	}
	
	
	private function validateAttachments( array $attachments ) {
		foreach ( $attachments as $attachment ) {
			
			if ( ! isset( $attachment['name'] ) || ! isset( $attachment['size'] ) ) {
				return new WP_Error( 'invalid_attachment', 'Estructura de archivo adjunto inválida' );
			}
			
			
			if ( $attachment['size'] > $this->config['attachment_max_size'] ) {
				return new WP_Error( 'attachment_too_large', 'Archivo muy grande: ' . $attachment['name'] );
			}
			
			
			$extension = pathinfo( $attachment['name'], PATHINFO_EXTENSION );
			if ( ! in_array( strtolower( $extension ), $this->config['allowed_attachment_types'] ) ) {
				return new WP_Error( 'attachment_type_not_allowed', 'Tipo no permitido: ' . $extension );
			}
		}
		
		return true;
	}
}
