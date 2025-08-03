<?php
/**
 * Servicio de correo para App_Yacht
 * Maneja envío de emails, integración con Outlook y gestión de firmas
 * 
 * @package AppYacht\Modules\Mail
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../shared/helpers/validator-helper.php';

/**
 * Servicio para gestión de correo electrónico
 */
class MailService implements MailServiceInterface {
	
	/**
	 * @var array Configuración del servicio
	 */
	private $config;
	
	/**
	 * @var RenderEngineInterface Motor de renderizado
	 */
	private $renderEngine;
	
	/**
	 * Constructor
	 * 
	 * @param array                 $config Configuración de mail
	 * @param RenderEngineInterface $renderEngine Motor de renderizado
	 */
	public function __construct( array $config, RenderEngineInterface $renderEngine ) {
		$this->config       = $config;
		$this->renderEngine = $renderEngine;
	}
	
	/**
	 * Envía un email
	 * 
	 * @param array $data Datos del email (to, subject, message, etc.)
	 * @return bool|WP_Error True si se envió, WP_Error si falló
	 */
	public function sendEmail( array $data ) {
		try {
			// Validar datos del email
			$validation = $this->validateEmailData( $data );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
			
			// Sanitizar datos
			$data = ValidatorHelper::sanitizeInputData( $data );
			
			// Determinar método de envío
			$userId = get_current_user_id();
			
			// Si está conectado a Outlook y está habilitado, usar Outlook
			if ( $this->config['outlook_enabled'] && $this->isOutlookConnected( $userId ) ) {
				return $this->sendEmailViaOutlook( $data, $userId );
			}
			
			// Usar wp_mail como fallback
			return $this->sendEmailViaWordPress( $data );
			
		} catch ( Exception $e ) {
			error_log( 'MailService Error: ' . $e->getMessage() );
			return new WP_Error( 'mail_error', 'Error enviando email: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Envía email usando Outlook
	 * 
	 * @param array $data Datos del email
	 * @param int   $userId ID del usuario
	 * @return bool|WP_Error
	 */
	public function sendEmailViaOutlook( array $data, $userId ) {
		try {
			// Verificar si las funciones de Outlook están disponibles
			if ( ! function_exists( 'pb_outlook_send_mail' ) ) {
				return new WP_Error( 'outlook_not_available', 'Funciones de Outlook no disponibles' );
			}
			
			// Preparar datos para Outlook
			$outlookData = $this->prepareOutlookData( $data, $userId );
			
			// Enviar usando la función existente de Outlook
			$result = pb_outlook_send_mail( $outlookData, $userId );
			
			if ( is_wp_error( $result ) ) {
				// Log del error para debugging
				error_log( 'Outlook Send Error: ' . $result->get_error_message() );
				
				// Intentar fallback a wp_mail si está configurado
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
	
	/**
	 * Verifica si un usuario está conectado a Outlook
	 * 
	 * @param int $userId ID del usuario
	 * @return bool
	 */
	public function isOutlookConnected( $userId ) {
		if ( function_exists( 'pb_outlook_is_connected' ) ) {
			return pb_outlook_is_connected( $userId );
		}
		
		// Fallback: verificar si existen tokens en user_meta
		$accessToken = get_user_meta( $userId, 'outlook_access_token', true );
		return ! empty( $accessToken );
	}
	
	/**
	 * Obtiene la URL de autenticación para Outlook
	 * 
	 * @return string URL de login
	 */
	public function getOutlookLoginUrl() {
		if ( function_exists( 'pb_outlook_get_login_url' ) ) {
			return pb_outlook_get_login_url();
		}
		
		return '#'; // Fallback
	}
	
	/**
	 * Desconecta la cuenta de Outlook de un usuario
	 * 
	 * @param int $userId ID del usuario
	 * @return bool
	 */
	public function disconnectOutlook( $userId ) {
		try {
			if ( function_exists( 'pb_outlook_disconnect_user' ) ) {
				return pb_outlook_disconnect_user( $userId );
			}
			
			// Fallback: eliminar tokens manualmente
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
	
	/**
	 * Valida los datos de un email
	 * 
	 * @param array $data Datos a validar
	 * @return bool|WP_Error
	 */
	public function validateEmailData( array $data ) {
		$errors = ValidatorHelper::validateEmailData( $data );
		
		// Validaciones adicionales específicas del servicio
		
		// Verificar límite de destinatarios
		if ( isset( $data['to'] ) ) {
			$recipients = explode( ',', $data['to'] );
			if ( count( $recipients ) > $this->config['max_recipients'] ) {
				$errors[] = 'Excede el límite máximo de ' . $this->config['max_recipients'] . ' destinatarios';
			}
		}
		
		// Validar archivos adjuntos si existen
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
	
	/**
	 * Procesa archivos adjuntos
	 * 
	 * @param array $attachments Archivos adjuntos
	 * @return array|WP_Error Archivos procesados o error
	 */
	public function processAttachments( array $attachments ) {
		$processed = array();
		
		foreach ( $attachments as $attachment ) {
			// Validar tamaño
			if ( $attachment['size'] > $this->config['attachment_max_size'] ) {
				return new WP_Error( 'attachment_too_large', 'Archivo muy grande: ' . $attachment['name'] );
			}
			
			// Validar tipo
			$extension = pathinfo( $attachment['name'], PATHINFO_EXTENSION );
			if ( ! in_array( strtolower( $extension ), $this->config['allowed_attachment_types'] ) ) {
				return new WP_Error( 'attachment_type_not_allowed', 'Tipo de archivo no permitido: ' . $extension );
			}
			
			$processed[] = $attachment;
		}
		
		return $processed;
	}
	
	/**
	 * Genera firma de email
	 * 
	 * @param int $userId ID del usuario
	 * @return string HTML de la firma
	 */
	public function generateSignature( $userId ) {
		if ( ! $this->config['signature_enabled'] ) {
			return '';
		}
		
		// Intentar obtener firma personalizada del usuario
		$customSignature = get_user_meta( $userId, 'yacht_email_signature', true );
		if ( ! empty( $customSignature ) ) {
			return $customSignature;
		}
		
		// Generar firma por defecto
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
	
	/**
	 * Envía email usando wp_mail de WordPress
	 */
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
	
	/**
	 * Prepara datos para envío con Outlook
	 */
	private function prepareOutlookData( array $data, $userId ) {
		$outlookData = array(
			'to'      => $data['to'],
			'subject' => $data['subject'],
			'body'    => $this->prepareEmailContent( $data ),
			'is_html' => true,
		);
		
		// Agregar CC si existe
		if ( ! empty( $data['cc'] ) ) {
			$outlookData['cc'] = $data['cc'];
		}
		
		// Agregar BCC si existe
		if ( ! empty( $data['bcc'] ) ) {
			$outlookData['bcc'] = $data['bcc'];
		}
		
		// Agregar archivos adjuntos
		if ( ! empty( $data['attachments'] ) ) {
			$outlookData['attachments'] = $this->processAttachments( $data['attachments'] );
		}
		
		return $outlookData;
	}
	
	/**
	 * Prepara el contenido del email
	 */
	private function prepareEmailContent( array $data ) {
		$content = $data['message'];
		
		// Agregar firma si está habilitado
		if ( $this->config['signature_enabled'] ) {
			$userId    = get_current_user_id();
			$signature = $this->generateSignature( $userId );
			
			if ( ! empty( $signature ) ) {
				$content .= "\n\n" . $signature;
			}
		}
		
		return $content;
	}
	
	/**
	 * Prepara headers para wp_mail
	 */
	private function prepareEmailHeaders( array $data ) {
		$headers = array();
		
		// Content-Type
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		
		// From
		if ( ! empty( $data['from'] ) ) {
			$headers[] = 'From: ' . $data['from'];
		} else {
			$headers[] = 'From: ' . $this->config['default_sender'];
		}
		
		// Reply-To
		if ( ! empty( $data['reply_to'] ) ) {
			$headers[] = 'Reply-To: ' . $data['reply_to'];
		}
		
		// CC
		if ( ! empty( $data['cc'] ) ) {
			$headers[] = 'Cc: ' . $data['cc'];
		}
		
		// BCC
		if ( ! empty( $data['bcc'] ) ) {
			$headers[] = 'Bcc: ' . $data['bcc'];
		}
		
		return $headers;
	}
	
	/**
	 * Prepara archivos adjuntos para wp_mail
	 */
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
	
	/**
	 * Parsea destinatarios de email
	 */
	private function parseEmailRecipients( $to ) {
		if ( is_string( $to ) ) {
			return array_map( 'trim', explode( ',', $to ) );
		}
		
		return is_array( $to ) ? $to : array( $to );
	}
	
	/**
	 * Valida archivos adjuntos
	 */
	private function validateAttachments( array $attachments ) {
		foreach ( $attachments as $attachment ) {
			// Validar estructura
			if ( ! isset( $attachment['name'] ) || ! isset( $attachment['size'] ) ) {
				return new WP_Error( 'invalid_attachment', 'Estructura de archivo adjunto inválida' );
			}
			
			// Validar tamaño
			if ( $attachment['size'] > $this->config['attachment_max_size'] ) {
				return new WP_Error( 'attachment_too_large', 'Archivo muy grande: ' . $attachment['name'] );
			}
			
			// Validar extensión
			$extension = pathinfo( $attachment['name'], PATHINFO_EXTENSION );
			if ( ! in_array( strtolower( $extension ), $this->config['allowed_attachment_types'] ) ) {
				return new WP_Error( 'attachment_type_not_allowed', 'Tipo no permitido: ' . $extension );
			}
		}
		
		return true;
	}
}
