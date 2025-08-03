<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


interface YachtInfoServiceInterface {

	
	public function extractYachtInfo( $url);

	
	public function isValidDomain( $url);

	
	public function getCachedData( $url);

	
	public function setCachedData( $url, array $data);

	
	public function clearCache();
}
