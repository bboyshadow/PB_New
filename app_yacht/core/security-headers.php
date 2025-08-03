<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


function pb_add_security_headers() {
	header( 'X-Frame-Options: SAMEORIGIN' ); 
	header( 'X-XSS-Protection: 1; mode=block' ); 
	header( 'X-Content-Type-Options: nosniff' ); 
	header( 'Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data: https:; connect-src \'self\' https://api.example.com;' ); 
	header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' ); 
	header( 'Referrer-Policy: strict-origin-when-cross-origin' ); 
}

add_action( 'send_headers', 'pb_add_security_headers' );

