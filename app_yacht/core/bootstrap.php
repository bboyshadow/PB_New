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
require_once __DIR__ . '/../modules/yachtinfo/yacht-info-container.php';
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
			wp_die( __( 'Error initializing App Yacht: ', 'creativoypunto' ) . esc_html( $e->getMessage() ) );
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

                // Minicalculadora de relocation
                add_action( 'wp_ajax_calculate_relocation', array( __CLASS__, 'handleCalculateRelocation' ) );
                add_action( 'wp_ajax_nopriv_calculate_relocation', array( __CLASS__, 'handleCalculateRelocation' ) );
		
		add_action( 'wp_ajax_load_template_preview', array( __CLASS__, 'handleLoadTemplatePreview' ) );
		add_action( 'wp_ajax_nopriv_load_template_preview', array( __CLASS__, 'handleLoadTemplatePreview' ) );
		
		// add_action( 'wp_ajax_createTemplate', array( __CLASS__, 'handleCreateTemplate' ) );
// add_action( 'wp_ajax_nopriv_createTemplate', array( __CLASS__, 'handleCreateTemplate' ) );
		
		add_action( 'wp_ajax_extract_yacht_info', array( __CLASS__, 'handleExtractYachtInfo' ) );
		add_action( 'wp_ajax_nopriv_extract_yacht_info', array( __CLASS__, 'handleExtractYachtInfo' ) );
	}
	
	
	public static function getContainer() {
		if ( ! self::$initialized ) {
			self::init();
		}
		return self::$container;
	}
	
	
        public static function handleCalculateCharter() {
                try {
                        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
                        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'calculate_nonce' ) ) {
                                wp_send_json_error( array(
                                        'message' => __( 'Security check failed', 'creativoypunto' ),
                                        'code'    => 'security_error',
                                ) );
                                return;
                        }

                        $calcService = self::getContainer()->get( 'calc_service' );
                        $result      = $calcService->calculateCharter( $_POST );
                        wp_send_json_success( $result );
                } catch ( Exception $e ) {
                        error_log( 'Calculate Charter Error: ' . $e->getMessage() );
                        wp_send_json_error( __( 'Calculation error: ', 'creativoypunto' ) . $e->getMessage() );
                }
        }
	
	
        public static function handleCalculateMix() {
                try {
                        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
                        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'mix_calculate_nonce' ) ) {
                                wp_send_json_error( array(
                                        'message' => __( 'Security check failed', 'creativoypunto' ),
                                        'code'    => 'security_error',
                                ) );
                                return;
                        }

                        $calcService = self::getContainer()->get( 'calc_service' );
                        $result      = $calcService->calculateMix( $_POST );
                        wp_send_json_success( $result );
                } catch ( Exception $e ) {
                        error_log( 'Calculate Mix Error: ' . $e->getMessage() );
                        wp_send_json_error( __( 'Mix calculation error: ', 'creativoypunto' ) . $e->getMessage() );
                }
        }

        public static function handleCalculateRelocation() {
                try {
                        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
                        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'relocation_calculate_nonce' ) ) {
                                wp_send_json_error( array( 'message' => __( 'Security check failed', 'creativoypunto' ), 'code' => 'security_error' ) );
                                return;
                        }
                        // Incluir el script de cálculo
                        include_once __DIR__ . '/../modules/calc/php/calculateRelocation.php';
                } catch ( Exception $e ) {
                        error_log( 'Calculate Relocation Error: ' . $e->getMessage() );
                        wp_send_json_error( __( 'Relocation calculation error: ', 'creativoypunto' ) . $e->getMessage() );
                }
        }
	
	
        public static function handleLoadTemplatePreview() {
                try {
                        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
                        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'template_nonce' ) ) {
                                wp_send_json_error( array(
                                        'message' => __( 'Security check failed', 'creativoypunto' ),
                                        'code'    => 'security_error',
                                ) );
                                return;
                        }

                        $renderEngine = self::getContainer()->get( 'render_engine' );
                        $result       = $renderEngine->loadTemplatePreview( $_POST );
                        wp_send_json_success( $result );
                } catch ( Exception $e ) {
                        error_log( 'Load Template Preview Error: ' . $e->getMessage() );
                        wp_send_json_error( __( 'Error loading template: ', 'creativoypunto' ) . $e->getMessage() );
                }
        }
	

        public static function handleCreateTemplate() {
                try {
                        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
                        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'template_nonce' ) ) {
                                wp_send_json_error( array(
                                        'message' => __( 'Security check failed', 'creativoypunto' ),
                                        'code'    => 'security_error',
                                ) );
                                return;
                        }

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
                        wp_send_json_error( __( 'Error creating template: ', 'creativoypunto' ) . $e->getMessage() );
                }
        }
	
	public static function handleExtractYachtInfo() {
		try {
			// Verificar nonce de seguridad
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'yachtinfo_nonce' ) ) {
				wp_send_json_error( array(
					'message' => __( 'Security check failed', 'creativoypunto' ),
					'code' => 'security_error'
				) );
				return;
			}
			
			if ( empty( $_POST['yachtUrl'] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Yacht URL is required. Please enter a yacht URL.', 'creativoypunto' ),
					'code' => 'missing_url'
				) );
				return;
			}
			
			$url = esc_url_raw( wp_unslash( $_POST['yachtUrl'] ) );
			if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
				wp_send_json_error( array(
					'message' => __( 'Please enter a valid URL (e.g., https://www.charterworld.com/yacht/...)', 'creativoypunto' ),
					'code' => 'invalid_url'
				) );
				return;
			}
			$force_refresh = isset( $_POST['force_refresh'] ) && absint( $_POST['force_refresh'] ) === 1;
			$container = self::getContainer();
			$yachtInfoService = $container->get( 'yacht_info_service' );
			
			if ( $force_refresh ) {
				// Clear cache for this specific URL (uses normalized key internally)
				if ( method_exists( $yachtInfoService, 'clearCacheForUrl' ) ) {
					$yachtInfoService->clearCacheForUrl( $url );
				}
			}
 			
 			$yachtData = $yachtInfoService->extractYachtInfo( $url );

			// Check if the result is a WP_Error
			if ( is_wp_error( $yachtData ) ) {
				$error_code = $yachtData->get_error_code();
				$error_message = $yachtData->get_error_message();
				$error_data = $yachtData->get_error_data();

				// Si es rate limiting, devolver 429 y Retry-After
				if ( $error_code === 'rate_limit_exceeded' ) {
					$retry_after = is_array( $error_data ) && isset( $error_data['retry_after'] ) ? intval( $error_data['retry_after'] ) : 300;
					if ( $retry_after < 0 ) { $retry_after = 0; }
					// Establecer cabecera HTTP 429 y Retry-After
					status_header( 429 );
					@header( 'Retry-After: ' . $retry_after );
					wp_send_json_error( array(
						'message' => $error_message,
						'code'    => $error_code,
						'retry_after' => $retry_after,
					), 429 );
					return;
				}

				wp_send_json_error( array(
					'message' => $error_message,
					'code' => $error_code
				) );
				return;
			}

			// Check if we got empty data
			if ( empty( $yachtData ) ) {
				wp_send_json_error( array(
					'message' => __( 'Could not extract yacht information. Please try a different URL.', 'creativoypunto' ),
					'code' => 'extraction_failed'
				) );
				return;
			}

			// Verificar que se obtuvieron datos mínimos necesarios
			if ( empty( $yachtData['name'] ) ) {
				wp_send_json_error( array(
					'message' => __( 'Could not extract yacht name. Please try a different URL.', 'creativoypunto' ),
					'code' => 'missing_data'
				) );
				return;
			}
			
			// Mapear datos para el contenedor
			$mappedData = [
				'yachtName' => $yachtData['name'] ?? '',
				'length' => $yachtData['length'] ?? '',
				'type' => $yachtData['type'] ?? '',
				'builder' => $yachtData['builder'] ?? '',
				'yearBuilt' => $yachtData['year'] ?? '',
				'crew' => $yachtData['crew'] ?? '',
				'cabins' => $yachtData['cabins'] ?? '',
				'guest' => $yachtData['guests'] ?? '', 
				'draft' => $yachtData['draft'] ?? '', 
				'beam' => $yachtData['beam'] ?? '', 
				'grossTonnage' => $yachtData['grossTonnage'] ?? '', 
				'cabinConfiguration' => $yachtData['cabinConfiguration'] ?? '', 
				'king' => $yachtData['king'] ?? '', 
				'queen' => $yachtData['queen'] ?? '', 
				'wifi' => $yachtData['wifi'] ?? '', 
				'jacuzzi' => $yachtData['jacuzzi'] ?? '', 
				'waterToys' => $yachtData['waterToys'] ?? '', 
				'stabilizers' => $yachtData['stabilizers'] ?? '', 
				'cruisingSpeed' => $yachtData['cruisingSpeed'] ?? '', 
				'maxSpeed' => $yachtData['maxSpeed'] ?? '', 
				'fuelConsumption' => $yachtData['fuelConsumption'] ?? '', 
				'range' => $yachtData['range'] ?? '', 
				'imageUrl' => $yachtData['image'] ?? ''
			];
			
			// Renderizar el contenedor con los datos
			ob_start();
			renderYachtInfoContainer( $mappedData );
			$html = ob_get_clean();
			
			wp_send_json_success( [ 'html' => $html, 'data' => $mappedData ] );
			
		} catch ( Exception $e ) {
			error_log( 'Extract Yacht Info Error: ' . $e->getMessage() );
			wp_send_json_error( array(
				'message' => __( 'Error extracting yacht information: ', 'creativoypunto' ) . $e->getMessage(),
				'code' => 'extraction_error'
			) );
		}
	}
}
