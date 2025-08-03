<?php
/**
 * Bootstrap principal para la aplicación App_Yacht
 * Inicializa el contenedor DI y los servicios principales
 * 
 * @package AppYacht
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Incluir el contenedor DI
require_once __DIR__ . '/container.php';
require_once __DIR__ . '/config.php';

// Incluir interfaces
require_once __DIR__ . '/../shared/interfaces/yacht-info-service-interface.php';
require_once __DIR__ . '/../shared/interfaces/calc-service-interface.php';
require_once __DIR__ . '/../shared/interfaces/render-engine-interface.php';
require_once __DIR__ . '/../shared/interfaces/mail-service-interface.php';

// Incluir servicios
require_once __DIR__ . '/../modules/yachtinfo/yacht-info-service.php';
require_once __DIR__ . '/../modules/calc/calc-service.php';
require_once __DIR__ . '/../modules/render/render-engine.php';
require_once __DIR__ . '/../modules/mail/mail-service.php';

/**
 * Clase principal de Bootstrap para App_Yacht
 */
class AppYachtBootstrap {
	
	/**
	 * @var AppYachtContainer
	 */
	private static $container = null;
	
	/**
	 * @var bool
	 */
	private static $initialized = false;
	
	/**
	 * Inicializa la aplicación
	 */
	public static function init() {
		if ( self::$initialized ) {
			return self::$container;
		}
		
		try {
			// Crear contenedor
			self::$container = new AppYachtContainer();
			
			// Registrar servicios
			self::registerServices();
			
			// Registrar hooks de WordPress
			self::registerWordPressHooks();
			
			self::$initialized = true;
			
			return self::$container;
			
		} catch ( Exception $e ) {
			error_log( 'AppYacht Bootstrap Error: ' . $e->getMessage() );
			wp_die( 'Error inicializando App Yacht: ' . esc_html( $e->getMessage() ) );
		}
	}
	
	/**
	 * Registra los servicios en el contenedor DI
	 */
	private static function registerServices() {
		$container = self::$container;
		$config    = AppYachtConfig::get();
		
		// Registrar YachtInfoService
		$container->register(
			'yacht_info_service',
			function() use ( $config ) {
				return new YachtInfoService( $config['scraping'] );
			}
		);
		
		// Registrar CalcService
		$container->register(
			'calc_service',
			function() use ( $config ) {
				return new CalcService( $config['calculation'] );
			}
		);
		
		// Registrar RenderEngine
		$container->register(
			'render_engine',
			function() use ( $config ) {
				return new RenderEngine( $config['templates'] );
			}
		);
		
		// Registrar MailService
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
	
	/**
	 * Registra hooks de WordPress para mantener compatibilidad
	 */
	private static function registerWordPressHooks() {
		// Mantener los hooks AJAX existentes para compatibilidad
		add_action( 'wp_ajax_calculate_charter', array( __CLASS__, 'handleCalculateCharter' ) );
		add_action( 'wp_ajax_nopriv_calculate_charter', array( __CLASS__, 'handleCalculateCharter' ) );
		
		add_action( 'wp_ajax_calculate_mix', array( __CLASS__, 'handleCalculateMix' ) );
		add_action( 'wp_ajax_nopriv_calculate_mix', array( __CLASS__, 'handleCalculateMix' ) );
		
		add_action( 'wp_ajax_load_template_preview', array( __CLASS__, 'handleLoadTemplatePreview' ) );
		add_action( 'wp_ajax_nopriv_load_template_preview', array( __CLASS__, 'handleLoadTemplatePreview' ) );
		
		add_action( 'wp_ajax_createTemplate', array( __CLASS__, 'handleCreateTemplate' ) );
		add_action( 'wp_ajax_nopriv_createTemplate', array( __CLASS__, 'handleCreateTemplate' ) );
	}
	
	/**
	 * Obtiene el contenedor DI
	 */
	public static function getContainer() {
		if ( ! self::$initialized ) {
			self::init();
		}
		return self::$container;
	}
	
	/**
	 * Handler para AJAX calculate_charter (compatibilidad)
	 */
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
	
	/**
	 * Handler para AJAX calculate_mix (compatibilidad)
	 */
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
	
	/**
	 * Handler para AJAX load_template_preview (compatibilidad)
	 */
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
	
	/**
	 * Handler para AJAX createTemplate (compatibilidad)
	 */
	public static function handleCreateTemplate() {
		try {
			$renderEngine     = self::getContainer()->get( 'render_engine' );
			$yachtInfoService = self::getContainer()->get( 'yacht_info_service' );
			
			// Obtener datos del yacht si hay URL
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
