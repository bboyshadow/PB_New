<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


require_once __DIR__ . '/container.php';
require_once __DIR__ . '/config.php';


require_once __DIR__ . '/../shared/interfaces/yacht-info-service-interface.php';
require_once __DIR__ . '/../shared/interfaces/calc-service-interface.php';
require_once __DIR__ . '/../shared/interfaces/render-engine-interface.php';
require_once __DIR__ . '/../shared/interfaces/mail-service-interface.php';


require_once __DIR__ . '/../modules/yachtinfo/yacht-info-service.php';
require_once __DIR__ . '/../modules/calc/calc-service.php';
require_once __DIR__ . '/../modules/render/render-engine.php';
require_once __DIR__ . '/../modules/mail/mail-service.php';


class AppYachtBootstrap {
	
	
	private static $container = null;
	
	
	private static $initialized = false;
	
	
	public static function init() {
		if ( self::$initialized ) {
			return self::$container;
		}
		
		try {
			
			self::$container = new AppYachtContainer();
			
			
			self::registerServices();
			
			
			self::registerWordPressHooks();
			
			self::$initialized = true;
			
			return self::$container;
			
		} catch ( Exception $e ) {
			error_log( 'AppYacht Bootstrap Error: ' . $e->getMessage() );
			wp_die( 'Error inicializando App Yacht: ' . esc_html( $e->getMessage() ) );
		}
	}
	
	
	private static function registerServices() {
		$container = self::$container;
		$config    = AppYachtConfig::get();
		
		
		$container->register(
			'yacht_info_service',
			function() use ( $config ) {
				return new YachtInfoService( $config['scraping'] );
			}
		);
		
		
		$container->register(
			'calc_service',
			function() use ( $config ) {
				return new CalcService( $config['calculation'] );
			}
		);
		
		
		$container->register(
			'render_engine',
			function() use ( $config ) {
				return new RenderEngine( $config['templates'] );
			}
		);
		
		
		$container->register(
			'mail_service',
			function() use ( $config, $container ) {
				return new MailService(
					$config['mail'],
					$container->get( 'render_engine' )
				);
			}
		);
	}
	
	
	private static function registerWordPressHooks() {
		
		add_action( 'wp_ajax_calculate_charter', array( __CLASS__, 'handleCalculateCharter' ) );
		add_action( 'wp_ajax_nopriv_calculate_charter', array( __CLASS__, 'handleCalculateCharter' ) );
		
		add_action( 'wp_ajax_calculate_mix', array( __CLASS__, 'handleCalculateMix' ) );
		add_action( 'wp_ajax_nopriv_calculate_mix', array( __CLASS__, 'handleCalculateMix' ) );
		
		add_action( 'wp_ajax_load_template_preview', array( __CLASS__, 'handleLoadTemplatePreview' ) );
		add_action( 'wp_ajax_nopriv_load_template_preview', array( __CLASS__, 'handleLoadTemplatePreview' ) );
		
		add_action( 'wp_ajax_createTemplate', array( __CLASS__, 'handleCreateTemplate' ) );
		add_action( 'wp_ajax_nopriv_createTemplate', array( __CLASS__, 'handleCreateTemplate' ) );
	}
	
	
	public static function getContainer() {
		if ( ! self::$initialized ) {
			self::init();
		}
		return self::$container;
	}
	
	
	public static function handleCalculateCharter() {
		try {
			$calcService = self::getContainer()->get( 'calc_service' );
			$result      = $calcService->calculateCharter( $_POST );
			wp_send_json_success( $result );
		} catch ( Exception $e ) {
			error_log( 'Calculate Charter Error: ' . $e->getMessage() );
			wp_send_json_error( 'Error en cálculo: ' . $e->getMessage() );
		}
	}
	
	
	public static function handleCalculateMix() {
		try {
			$calcService = self::getContainer()->get( 'calc_service' );
			$result      = $calcService->calculateMix( $_POST );
			wp_send_json_success( $result );
		} catch ( Exception $e ) {
			error_log( 'Calculate Mix Error: ' . $e->getMessage() );
			wp_send_json_error( 'Error en cálculo mix: ' . $e->getMessage() );
		}
	}
	
	
	public static function handleLoadTemplatePreview() {
		try {
			$renderEngine = self::getContainer()->get( 'render_engine' );
			$result       = $renderEngine->loadTemplatePreview( $_POST );
			wp_send_json_success( $result );
		} catch ( Exception $e ) {
			error_log( 'Load Template Preview Error: ' . $e->getMessage() );
			wp_send_json_error( 'Error cargando template: ' . $e->getMessage() );
		}
	}
	
	
	public static function handleCreateTemplate() {
		try {
			$renderEngine     = self::getContainer()->get( 'render_engine' );
			$yachtInfoService = self::getContainer()->get( 'yacht_info_service' );
			
			
			$yachtData = null;
			if ( ! empty( $_POST['yachtUrl'] ) ) {
				$yachtData = $yachtInfoService->extractYachtInfo( $_POST['yachtUrl'] );
			}
			
			$result = $renderEngine->createTemplate( $_POST, $yachtData );
			wp_send_json_success( $result );
		} catch ( Exception $e ) {
			error_log( 'Create Template Error: ' . $e->getMessage() );
			wp_send_json_error( 'Error creando template: ' . $e->getMessage() );
		}
	}
}
