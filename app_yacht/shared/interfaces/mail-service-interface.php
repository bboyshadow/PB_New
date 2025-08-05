<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


interface MailServiceInterface {

	
	/**
	 * Send an email using the configured method.
	 *
	 * @param array $data Email data.
	 * @return bool|WP_Error True on success or WP_Error on failure.
	 */
	public function sendEmail( array $data);

	
	/**
	 * Send an email through Outlook integration.
	 *
	 * @param array $data   Email data.
	 * @param int   $userId User ID.
	 * @return bool|WP_Error True on success or WP_Error on failure.
	 */
	public function sendEmailViaOutlook( array $data, $userId);

	
	public function isOutlookConnected( $userId);

	
	public function getOutlookLoginUrl();

	
	public function disconnectOutlook( $userId);

	
	public function validateEmailData( array $data);

	
	/**
	 * Process attachments before sending emails.
	 *
	 * @param array $attachments Attachments data.
	 * @return array|WP_Error Processed attachments or WP_Error on failure.
	 */
	public function processAttachments( array $attachments);

	
	public function generateSignature( $userId);
}
