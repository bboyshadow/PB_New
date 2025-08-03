<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class CacheHelper {

	
	const CACHE_PREFIX = 'app_yacht_';

	
	public static function get( $key ) {
		$full_key = self::CACHE_PREFIX . $key;
		return wp_cache_get( $full_key, 'app_yacht' );
	}

	
	public static function set( $key, $value, $expiration = 3600 ) {
		$full_key = self::CACHE_PREFIX . $key;
		return wp_cache_set( $full_key, $value, 'app_yacht', $expiration );
	}

	
	public static function delete( $key ) {
		$full_key = self::CACHE_PREFIX . $key;
		return wp_cache_delete( $full_key, 'app_yacht' );
	}

	
	public static function deleteMultiple( array $keys ) {
		$success = true;
		foreach ( $keys as $key ) {
			if ( ! self::delete( $key ) ) {
				$success = false;
			}
		}
		return $success;
	}

	
	public static function flush() {
		return wp_cache_flush_group( 'app_yacht' );
	}

	
	public static function generateUrlKey( $url ) {
		return 'url_' . md5( $url );
	}

	
	public static function generateCalcKey( array $data ) {
		return 'calc_' . md5( json_encode( $data ) );
	}

	
	public static function generateTemplateKey( $template, array $data = array() ) {
		$key = 'template_' . $template;
		if ( ! empty( $data ) ) {
			$key .= '_' . md5( json_encode( $data ) );
		}
		return $key;
	}
}
