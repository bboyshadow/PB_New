<?php
/**
 * File core/security-headers.php
 * Implements security headers for the app_yacht application.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds security headers to HTTP responses.
 * Includes protections against clickjacking, XSS, MIME sniffing, and more.
 */
function pb_add_security_headers() {
	header( 'X-Frame-Options: SAMEORIGIN' ); // Prevent clickjacking
	header( 'X-XSS-Protection: 1; mode=block' ); // Enable XSS protection
	header( 'X-Content-Type-Options: nosniff' ); // Prevent MIME sniffing
	header( 'Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data: https:; connect-src \'self\' https://api.example.com;' ); // Restrict content sources
	header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' ); // Enforce HTTPS
	header( 'Referrer-Policy: strict-origin-when-cross-origin' ); // Control referrer information
}

add_action( 'send_headers', 'pb_add_security_headers' );

