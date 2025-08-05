<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


/**
 * Generate and return a CSP nonce for the current request.
 *
 * @return string
 */
function pb_get_csp_nonce() {
        static $nonce = null;

        if ( null === $nonce ) {
                $nonce = base64_encode( random_bytes( 16 ) );
        }

        return $nonce;
}

/**
 * Add security related HTTP headers.
 */
function pb_add_security_headers() {
        $nonce = pb_get_csp_nonce();

        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'X-XSS-Protection: 1; mode=block' );
        header( 'X-Content-Type-Options: nosniff' );
        header(
                "Content-Security-Policy: default-src 'self'; " .
                "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net; " .
                "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com; " .
                "font-src 'self' https://fonts.gstatic.com; " .
                "img-src 'self' data: https:; " .
                "connect-src 'self' https://api.example.com;"
        );
        header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' );
        header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}

add_action( 'send_headers', 'pb_add_security_headers' );

/**
 * Inject the CSP nonce into enqueued script tags.
 */
function pb_nonce_script_loader_tag( $tag, $handle, $src ) {
        $nonce = pb_get_csp_nonce();

        return str_replace( '<script', '<script nonce="' . esc_attr( $nonce ) . '"', $tag );
}
add_filter( 'script_loader_tag', 'pb_nonce_script_loader_tag', 10, 3 );

/**
 * Inject the CSP nonce into enqueued style tags.
 */
function pb_nonce_style_loader_tag( $tag, $handle ) {
        $nonce = pb_get_csp_nonce();

        return str_replace( '<link', '<link nonce="' . esc_attr( $nonce ) . '"', $tag );
}
add_filter( 'style_loader_tag', 'pb_nonce_style_loader_tag', 10, 2 );

