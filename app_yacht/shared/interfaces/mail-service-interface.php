<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


interface MailServiceInterface {

	
	public function sendEmail( array $data);

	
	public function sendEmailViaOutlook( array $data, $userId);

	
	public function isOutlookConnected( $userId);

	
	public function getOutlookLoginUrl();

	
	public function disconnectOutlook( $userId);

	
	public function validateEmailData( array $data);

	
	public function processAttachments( array $attachments);

	
	public function generateSignature( $userId);
}
