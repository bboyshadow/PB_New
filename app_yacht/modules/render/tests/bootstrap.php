<?php
define( 'ABSPATH', __DIR__ . '/' );

if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        private $code;
        private $message;
        public function __construct( $code = '', $message = '' ) {
            $this->code = $code;
            $this->message = $message;
        }
        public function get_error_code() {
            return $this->code;
        }
        public function get_error_message() {
            return $this->message;
        }
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return is_string( $str ) ? preg_replace( '/[\x00-\x1F\x7F<>]/', '', $str ) : $str;
    }
}

if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type ) {
        return date( 'Y-m-d H:i:s' );
    }
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return $thing instanceof WP_Error;
    }
}

if ( ! function_exists( 'wp_cache_get' ) ) {
    function wp_cache_get( $key, $group ) { return false; }
    function wp_cache_set( $key, $value, $group, $exp ) { return true; }
    function wp_cache_delete( $key, $group ) { return true; }
    function wp_cache_flush_group( $group ) { return true; }
}

require_once __DIR__ . '/../../../shared/interfaces/render-engine-interface.php';
require_once __DIR__ . '/../render-engine.php';
