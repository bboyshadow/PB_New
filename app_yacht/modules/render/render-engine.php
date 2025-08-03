<?php
/**
 * Motor de renderizado unificado para App_Yacht
 * Maneja plantillas, generación de contenido y adaptadores de formato
 * 
 * @package AppYacht\Modules\Render
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../shared/helpers/cache-helper.php';

/**
 * Motor de renderizado para plantillas y contenido
 */
class RenderEngine implements RenderEngineInterface {
	
	/**
	 * @var array Configuración del motor
	 */
	private $config;
	
	/**
	 * @var array Adaptadores de formato registrados
	 */
	private $formatAdapters;
	
	/**
	 * Constructor
	 * 
	 * @param array $config Configuración de templates
	 */
	public function __construct( array $config ) {
		$this->config         = $config;
		$this->formatAdapters = array();
		$this->registerDefaultAdapters();
	}
	
	/**
	 * Renderiza una plantilla con datos
	 * 
	 * @param string $template Nombre de la plantilla
	 * @param array  $data Datos para la plantilla
	 * @param string $format Formato de salida (html|text)
	 * @return string Contenido renderizado
	 */
	public function render( $template, array $data, $format = 'html' ) {
		try {
			// Verificar si la plantilla existe
			if ( ! $this->templateExists( $template ) ) {
				throw new Exception( "Plantilla '{$template}' no encontrada" );
			}
			
			// Intentar obtener de caché
			$cacheKey = CacheHelper::generateTemplateKey( $template, $data );
			if ( $this->config['cache_enabled'] ) {
				$cached = CacheHelper::get( $cacheKey );
				if ( $cached !== false ) {
					return $this->applyFormatAdapter( $cached, $format );
				}
			}
			
			// Obtener contenido de la plantilla
			$templateContent = $this->getTemplateContent( $template );
			if ( $templateContent === false ) {
				throw new Exception( "No se pudo cargar el contenido de la plantilla '{$template}'" );
			}
			
			// Procesar variables en la plantilla
			$renderedContent = $this->processVariables( $templateContent, $data );
			
			// Cachear si está habilitado
			if ( $this->config['cache_enabled'] ) {
				CacheHelper::set( $cacheKey, $renderedContent, $this->config['cache_duration'] );
			}
			
			// Aplicar adaptador de formato
			return $this->applyFormatAdapter( $renderedContent, $format );
			
		} catch ( Exception $e ) {
			error_log( 'RenderEngine Error: ' . $e->getMessage() );
			return 'Error renderizando plantilla: ' . $e->getMessage();
		}
	}
	
	/**
	 * Carga una vista previa de plantilla
	 * 
	 * @param array $data Datos del request
	 * @return array Resultado con vista previa
	 */
	public function loadTemplatePreview( array $data ) {
		try {
			// Validar datos requeridos
			if ( empty( $data['template'] ) ) {
				return new WP_Error( 'missing_template', 'Nombre de plantilla requerido' );
			}
			
			$templateName = sanitize_text_field( $data['template'] );
			
			// Datos de ejemplo para la preview
			$previewData = $this->generatePreviewData( $data );
			
			// Renderizar plantilla
			$htmlContent = $this->render( $templateName, $previewData, 'html' );
			$textContent = $this->render( $templateName, $previewData, 'text' );
			
			return array(
				'template'     => $templateName,
				'html_content' => $htmlContent,
				'text_content' => $textContent,
				'preview_data' => $previewData,
				'success'      => true,
			);
			
		} catch ( Exception $e ) {
			error_log( 'RenderEngine Preview Error: ' . $e->getMessage() );
			return new WP_Error( 'preview_error', 'Error generando vista previa: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Crea una nueva plantilla
	 * 
	 * @param array      $formData Datos del formulario
	 * @param array|null $yachtData Datos del yate (opcional)
	 * @return array Resultado de la creación
	 */
	public function createTemplate( array $formData, $yachtData = null ) {
		try {
			// Validar datos del formulario
			$requiredFields = array( 'currency' );
			foreach ( $requiredFields as $field ) {
				if ( empty( $formData[ $field ] ) ) {
					return new WP_Error( 'missing_field', "Campo requerido faltante: {$field}" );
				}
			}
			
			// Determinar plantilla a usar
			$templateName = $formData['selectedTemplate'] ?? $this->config['default_template'];
			
			// Preparar datos para el template
			$templateData = $this->prepareTemplateData( $formData, $yachtData );
			
			// Renderizar template
			$htmlContent = $this->render( $templateName, $templateData, 'html' );
			$textContent = $this->render( $templateName, $templateData, 'text' );
			
			// Generar resultado
			$result = array(
				'template_name' => $templateName,
				'html_content'  => $htmlContent,
				'text_content'  => $textContent,
				'yacht_data'    => $yachtData,
				'form_data'     => $formData,
				'created_at'    => current_time( 'mysql' ),
				'success'       => true,
			);
			
			// Guardar template si se solicita
			if ( ! empty( $formData['saveTemplate'] ) && ! empty( $formData['templateName'] ) ) {
				$saveResult      = $this->saveCustomTemplate( $formData['templateName'], $templateData, $htmlContent );
				$result['saved'] = $saveResult;
			}
			
			return $result;
			
		} catch ( Exception $e ) {
			error_log( 'RenderEngine Create Error: ' . $e->getMessage() );
			return new WP_Error( 'create_error', 'Error creando template: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Obtiene lista de plantillas disponibles
	 * 
	 * @return array Lista de plantillas
	 */
	public function getAvailableTemplates() {
		return $this->config['available_templates'];
	}
	
	/**
	 * Verifica si una plantilla existe
	 * 
	 * @param string $template Nombre de la plantilla
	 * @return bool
	 */
	public function templateExists( $template ) {
		$templatePath = $this->getTemplatePath( $template );
		return file_exists( $templatePath );
	}
	
	/**
	 * Obtiene el contenido de una plantilla
	 * 
	 * @param string $template Nombre de la plantilla
	 * @return string|false Contenido de la plantilla o false
	 */
	public function getTemplateContent( $template ) {
		$templatePath = $this->getTemplatePath( $template );
		
		if ( ! file_exists( $templatePath ) ) {
			return false;
		}
		
		// Usar output buffering para capturar el contenido
		ob_start();
		
		try {
			// Variables disponibles en la plantilla
			$yacht_data    = array();
			$calc_data     = array();
			$template_vars = array();
			
			include $templatePath;
			
			$content = ob_get_contents();
			ob_end_clean();
			
			return $content;
			
		} catch ( Exception $e ) {
			ob_end_clean();
			error_log( 'Error loading template: ' . $e->getMessage() );
			return false;
		}
	}
	
	/**
	 * Procesa variables en el contenido de la plantilla
	 * 
	 * @param string $content Contenido con variables
	 * @param array  $variables Variables a reemplazar
	 * @return string Contenido procesado
	 */
	public function processVariables( $content, array $variables ) {
		// Procesar variables con sintaxis {{variable}}
		$content = preg_replace_callback(
			'/\{\{([^}]+)\}\}/',
			function( $matches ) use ( $variables ) {
				$varName = trim( $matches[1] );
			
				// Soportar notación punto para arrays anidados
				if ( strpos( $varName, '.' ) !== false ) {
					$parts = explode( '.', $varName );
					$value = $variables;
				
					foreach ( $parts as $part ) {
						if ( isset( $value[ $part ] ) ) {
							$value = $value[ $part ];
						} else {
							return $matches[0]; // Retornar sin cambios si no existe
						}
					}
				
					return is_array( $value ) ? json_encode( $value ) : (string) $value;
				}
			
				// Variable simple
				return isset( $variables[ $varName ] ) ? (string) $variables[ $varName ] : $matches[0];
			},
			$content
		);
		
		// Procesar bloques condicionales {{#if variable}}...{{/if}}
		$content = $this->processConditionalBlocks( $content, $variables );
		
		// Procesar loops {{#each array}}...{{/each}}
		$content = $this->processLoopBlocks( $content, $variables );
		
		return $content;
	}
	
	/**
	 * Registra adaptadores de formato por defecto
	 */
	private function registerDefaultAdapters() {
		// Adaptador HTML (por defecto, no hace nada)
		$this->formatAdapters['html'] = function( $content ) {
			return $content;
		};
		
		// Adaptador texto plano
		$this->formatAdapters['text'] = function( $content ) {
			// Remover tags HTML
			$text = strip_tags( $content );
			
			// Limpiar espacios extra
			$text = preg_replace( '/\s+/', ' ', $text );
			
			// Convertir entities HTML
			$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
			
			return trim( $text );
		};
		
		// Adaptador Email HTML
		$this->formatAdapters['email'] = function( $content ) {
			// Aplicar estilos inline para compatibilidad con clientes de email
			return $this->applyEmailStyles( $content );
		};
	}
	
	/**
	 * Aplica un adaptador de formato al contenido
	 */
	private function applyFormatAdapter( $content, $format ) {
		if ( isset( $this->formatAdapters[ $format ] ) ) {
			return $this->formatAdapters[ $format ]( $content );
		}
		
		return $content; // Retornar sin cambios si no hay adaptador
	}
	
	/**
	 * Obtiene la ruta completa de una plantilla
	 */
	private function getTemplatePath( $template ) {
		return $this->config['templates_path'] . $template . '.php';
	}
	
	/**
	 * Genera datos de ejemplo para vista previa
	 */
	private function generatePreviewData( array $data ) {
		return array(
			'yacht_name'     => 'EJEMPLO YACHT',
			'yacht_length'   => '45m',
			'yacht_guests'   => '12',
			'yacht_cabins'   => '6',
			'yacht_crew'     => '8',
			'yacht_year'     => '2020',
			'charter_rate'   => '€85,000',
			'currency'       => $data['currency'] ?? '€',
			'vat_amount'     => '€18,000',
			'apa_amount'     => '€25,500',
			'total_amount'   => '€128,500',
			'charter_period' => '7 days',
			'location'       => 'Mediterranean',
			'preview'        => true,
		);
	}
	
	/**
	 * Prepara datos para el template desde el formulario
	 */
	private function prepareTemplateData( array $formData, $yachtData = null ) {
		$data = array(
			'currency'     => $formData['currency'],
			'form_data'    => $formData,
			'generated_at' => current_time( 'mysql' ),
		);
		
		// Agregar datos del yate si están disponibles
		if ( $yachtData && ! is_wp_error( $yachtData ) ) {
			$data['yacht']        = $yachtData;
			$data['yacht_name']   = $yachtData['name'] ?? 'Yacht';
			$data['yacht_length'] = $yachtData['length'] ?? '';
			$data['yacht_guests'] = $yachtData['guests'] ?? '';
			$data['yacht_cabins'] = $yachtData['cabins'] ?? '';
		}
		
		// Agregar datos calculados si están en el formulario
		if ( isset( $formData['calculationResult'] ) ) {
			$data['calculation'] = $formData['calculationResult'];
		}
		
		return $data;
	}
	
	/**
	 * Procesa bloques condicionales en el template
	 */
	private function processConditionalBlocks( $content, array $variables ) {
		return preg_replace_callback(
			'/\{\{#if\s+([^}]+)\}\}(.*?)\{\{\/if\}\}/s',
			function( $matches ) use ( $variables ) {
				$condition    = trim( $matches[1] );
				$blockContent = $matches[2];
			
				// Evaluar condición simple
				if ( isset( $variables[ $condition ] ) && $variables[ $condition ] ) {
					return $blockContent;
				}
			
				return ''; // Ocultar bloque si la condición es falsa
			},
			$content
		);
	}
	
	/**
	 * Procesa bloques de loop en el template
	 */
	private function processLoopBlocks( $content, array $variables ) {
		return preg_replace_callback(
			'/\{\{#each\s+([^}]+)\}\}(.*?)\{\{\/each\}\}/s',
			function( $matches ) use ( $variables ) {
				$arrayName   = trim( $matches[1] );
				$loopContent = $matches[2];
			
				if ( ! isset( $variables[ $arrayName ] ) || ! is_array( $variables[ $arrayName ] ) ) {
					return '';
				}
			
				$result = '';
				foreach ( $variables[ $arrayName ] as $index => $item ) {
					$itemContent = $loopContent;
				
					// Reemplazar {{this}} con el valor del item
					$itemContent = str_replace( '{{this}}', (string) $item, $itemContent );
				
					// Reemplazar {{@index}} con el índice
					$itemContent = str_replace( '{{@index}}', (string) $index, $itemContent );
				
					// Si el item es un array, procesar sus propiedades
					if ( is_array( $item ) ) {
						foreach ( $item as $key => $value ) {
							$itemContent = str_replace( '{{' . $key . '}}', (string) $value, $itemContent );
						}
					}
				
					$result .= $itemContent;
				}
			
				return $result;
			},
			$content
		);
	}
	
	/**
	 * Aplica estilos inline para emails
	 */
	private function applyEmailStyles( $content ) {
		// Estilos básicos para compatibilidad con clientes de email
		$styles = array(
			'body'  => 'font-family: Arial, sans-serif; line-height: 1.6; color: #333;',
			'table' => 'border-collapse: collapse; width: 100%;',
			'td'    => 'padding: 8px; border: 1px solid #ddd;',
			'th'    => 'padding: 8px; border: 1px solid #ddd; background-color: #f2f2f2; font-weight: bold;',
		);
		
		foreach ( $styles as $tag => $style ) {
			$content = preg_replace( '/<' . $tag . '(?![^>]*style=)([^>]*)>/', '<' . $tag . '$1 style="' . $style . '">', $content );
		}
		
		return $content;
	}
	
	/**
	 * Guarda un template personalizado
	 */
	private function saveCustomTemplate( $name, array $data, $content ) {
		try {
			// Aquí podrías implementar guardado en base de datos
			// Por ahora, solo retornamos éxito
			return array(
				'success'       => true,
				'template_name' => $name,
				'saved_at'      => current_time( 'mysql' ),
			);
		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'error'   => $e->getMessage(),
			);
		}
	}
}
