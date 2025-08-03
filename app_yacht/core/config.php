<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class AppYachtConfig {
	
	
	private static $config = null;
	
	
	public static function get( $key = null ) {
		if ( self::$config === null ) {
			self::$config = self::loadConfig();
		}
		
		if ( $key === null ) {
			return self::$config;
		}
		
		return isset( self::$config[ $key ] ) ? self::$config[ $key ] : null;
	}
	
	
	private static function loadConfig() {
		return array(
			'app' => array(
				'version' => '2.0.0',
				'name'    => 'App Yacht',
				'debug'   => defined( 'WP_DEBUG' ) ? WP_DEBUG : false,
			),
			
			'scraping' => array(
				'allowed_domains' => array(
					'charterworld.com',
					'yachtcharterfleet.com',
					'burgessyachts.com',
					'northropandjohnson.com',
					'fraseryachts.com',
					'edmistoncompany.com',
					'imperial-yachts.com',
					'carolineyacht.com',
					'camperandnicholsons.com',
					'bluewatery.com',
				),
				'timeout'         => 30,
				'user_agent'      => 'Mozilla/5.0 (compatible; AppYachtBot/2.0)',
				'cache_duration'  => 3600, 
				'max_redirects'   => 3,
			),
			
			'calculation' => array(
				'default_currency'       => '€',
				'supported_currencies'   => array( '€', '$USD', '$AUD' ),
				'vat_rates'              => array(
					'default'   => 21.0,
					'countries' => array(
						'ES' => 21.0,
						'FR' => 20.0,
						'IT' => 22.0,
						'GR' => 24.0,
						'HR' => 25.0,
						'MN' => 20.0,
						'MT' => 18.0,
						'CY' => 19.0,
					),
				),
				'apa_default_percentage' => 30.0,
				'precision'              => 2,
				'rounding_mode'          => 'half_up',
			),
			
			'templates' => array(
				'default_template'    => 'default-template',
				'available_templates' => array(
					'default-template' => 'Default Template',
					'template-01'      => 'Template 01',
					'template-02'      => 'Template 02',
				),
				'templates_path'      => get_template_directory() . '/app_yacht/modules/template/templates/',
				'cache_enabled'       => true,
				'cache_duration'      => 1800, 
			),
			
			'mail' => array(
				'default_sender'           => get_option( 'admin_email' ),
				'outlook_enabled'          => true,
				'signature_enabled'        => true,
				'max_recipients'           => 50,
				'attachment_max_size'      => 5 * 1024 * 1024, 
				'allowed_attachment_types' => array( 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png' ),
			),
			
			'security' => array(
				'nonce_lifetime'       => 12 * HOUR_IN_SECONDS,
				'rate_limit'           => array(
					'enabled'      => true,
					'max_requests' => 100,
					'time_window'  => 3600, 
				),
				'allowed_capabilities' => array(
					'edit_yacht_templates',
					'send_yacht_emails',
				),
			),
			
			'cache' => array(
				'enabled'          => true,
				'default_duration' => 3600,
				'prefix'           => 'app_yacht_',
				'cleanup_interval' => 24 * HOUR_IN_SECONDS,
			),
			
			'logging' => array(
				'enabled'       => true,
				'level'         => defined( 'WP_DEBUG' ) && WP_DEBUG ? 'debug' : 'error',
				'max_file_size' => 5 * 1024 * 1024, 
				'max_files'     => 5,
			),
		);
	}
	
	
	public static function set( $key, $value ) {
		if ( self::$config === null ) {
			self::$config = self::loadConfig();
		}
		
		self::$config[ $key ] = $value;
	}
	
	
	public static function getModuleConfig( $module ) {
		return self::get( $module );
	}
}
