<?php
/**
 * Interface para el servicio de correo
 * Define el contrato para envío de emails y gestión de correo
 *
 * @package AppYacht\Interfaces
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface para servicios de correo
 */
interface MailServiceInterface {

	/**
	 * Envía un email
	 *
	 * @param array $data Datos del email (to, subject, message, etc.)
	 * @return bool|WP_Error True si se envió, WP_Error si falló
	 */
	public function sendEmail( array $data);

	/**
	 * Envía email usando Outlook
	 *
	 * @param array $data Datos del email
	 * @param int   $userId ID del usuario
	 * @return bool|WP_Error
	 */
	public function sendEmailViaOutlook( array $data, $userId);

	/**
	 * Verifica si un usuario está conectado a Outlook
	 *
	 * @param int $userId ID del usuario
	 * @return bool
	 */
	public function isOutlookConnected( $userId);

	/**
	 * Obtiene la URL de autenticación para Outlook
	 *
	 * @return string URL de login
	 */
	public function getOutlookLoginUrl();

	/**
	 * Desconecta la cuenta de Outlook de un usuario
	 *
	 * @param int $userId ID del usuario
	 * @return bool
	 */
	public function disconnectOutlook( $userId);

	/**
	 * Valida los datos de un email
	 *
	 * @param array $data Datos a validar
	 * @return bool|WP_Error
	 */
	public function validateEmailData( array $data);

	/**
	 * Procesa archivos adjuntos
	 *
	 * @param array $attachments Archivos adjuntos
	 * @return array|WP_Error Archivos procesados o error
	 */
	public function processAttachments( array $attachments);

	/**
	 * Genera firma de email
	 *
	 * @param int $userId ID del usuario
	 * @return string HTML de la firma
	 */
	public function generateSignature( $userId);
}
