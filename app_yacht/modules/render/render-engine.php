<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../shared/helpers/cache-helper.php';


class RenderEngine implements RenderEngineInterface {
	
	
	private $config;
	
	
	private $formatAdapters;
	
	
	public function __construct( array $config ) {
		$this->config         = $config;
		$this->formatAdapters = array();
		$this->registerDefaultAdapters();
	}
	
	
	public function render( $template, array $data, $format = 'html' ) {
		try {
			
			if ( ! $this->templateExists( $template ) ) {
				throw new Exception( "Plantilla '{$template}' no encontrada" );
			}
			
			
			$cacheKey = CacheHelper::generateTemplateKey( $template, $data );
			if ( $this->config['cache_enabled'] ) {
				$cached = CacheHelper::get( $cacheKey );
				if ( $cached !== false ) {
					return $this->applyFormatAdapter( $cached, $format );
				}
			}
			
			
			$templateContent = $this->getTemplateContent( $template );
			if ( $templateContent === false ) {
				throw new Exception( "No se pudo cargar el contenido de la plantilla '{$template}'" );
			}
			
			
			$renderedContent = $this->processVariables( $templateContent, $data );
			
			
			if ( $this->config['cache_enabled'] ) {
				CacheHelper::set( $cacheKey, $renderedContent, $this->config['cache_duration'] );
			}
			
			
			return $this->applyFormatAdapter( $renderedContent, $format );
			
		} catch ( Exception $e ) {
			error_log( 'RenderEngine Error: ' . $e->getMessage() );
			return 'Error renderizando plantilla: ' . $e->getMessage();
		}
	}
	
	
	public function loadTemplatePreview( array $data ) {
		try {
			
			if ( empty( $data['template'] ) ) {
				return new WP_Error( 'missing_template', 'Nombre de plantilla requerido' );
			}
			
			$templateName = sanitize_text_field( $data['template'] );
			
			
			$previewData = $this->generatePreviewData( $data );
			
			
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
	
	
	public function createTemplate( array $formData, $yachtData = null ) {
		try {
			
			$requiredFields = array( 'currency' );
			foreach ( $requiredFields as $field ) {
				if ( empty( $formData[ $field ] ) ) {
					return new WP_Error( 'missing_field', "Campo requerido faltante: {$field}" );
				}
			}
			
			
			$templateName = $formData['selectedTemplate'] ?? $this->config['default_template'];
			
			
			$templateData = $this->prepareTemplateData( $formData, $yachtData );
			
			
			$htmlContent = $this->render( $templateName, $templateData, 'html' );
			$textContent = $this->render( $templateName, $templateData, 'text' );
			
			
			$result = array(
				'template_name' => $templateName,
				'html_content'  => $htmlContent,
				'text_content'  => $textContent,
				'yacht_data'    => $yachtData,
				'form_data'     => $formData,
				'created_at'    => current_time( 'mysql' ),
				'success'       => true,
			);
			
			
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
	
	
	public function getAvailableTemplates() {
		return $this->config['available_templates'];
	}
	
	
	public function templateExists( $template ) {
		$templatePath = $this->getTemplatePath( $template );
		return file_exists( $templatePath );
	}
	
	
	public function getTemplateContent( $template ) {
		$templatePath = $this->getTemplatePath( $template );
		
		if ( ! file_exists( $templatePath ) ) {
			return false;
		}
		
		
		ob_start();
		
		try {
			
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
	
	
	public function processVariables( $content, array $variables ) {
		
		$content = preg_replace_callback(
			'/\{\{([^}]+)\}\}/',
			function( $matches ) use ( $variables ) {
				$varName = trim( $matches[1] );
			
				
				if ( strpos( $varName, '.' ) !== false ) {
					$parts = explode( '.', $varName );
					$value = $variables;
				
					foreach ( $parts as $part ) {
						if ( isset( $value[ $part ] ) ) {
							$value = $value[ $part ];
						} else {
							return $matches[0]; 
						}
					}
				
					return is_array( $value ) ? json_encode( $value ) : (string) $value;
				}
			
				
				return isset( $variables[ $varName ] ) ? (string) $variables[ $varName ] : $matches[0];
			},
			$content
		);
		
		
		$content = $this->processConditionalBlocks( $content, $variables );
		
		
		$content = $this->processLoopBlocks( $content, $variables );
		
		return $content;
	}
	
	
	private function registerDefaultAdapters() {
		
		$this->formatAdapters['html'] = function( $content ) {
			return $content;
		};
		
		
		$this->formatAdapters['text'] = function( $content ) {
			
			$text = strip_tags( $content );
			
			
			$text = preg_replace( '/\s+/', ' ', $text );
			
			
			$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
			
			return trim( $text );
		};
		
		
		$this->formatAdapters['email'] = function( $content ) {
			
			return $this->applyEmailStyles( $content );
		};
	}
	
	
	private function applyFormatAdapter( $content, $format ) {
		if ( isset( $this->formatAdapters[ $format ] ) ) {
			return $this->formatAdapters[ $format ]( $content );
		}
		
		return $content; 
	}
	
	
	private function getTemplatePath( $template ) {
		return $this->config['templates_path'] . $template . '.php';
	}
	
	
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
	
	
	private function prepareTemplateData( array $formData, $yachtData = null ) {
		$data = array(
			'currency'     => $formData['currency'],
			'form_data'    => $formData,
			'generated_at' => current_time( 'mysql' ),
		);
		
		
		if ( $yachtData && ! is_wp_error( $yachtData ) ) {
			$data['yacht']        = $yachtData;
			$data['yacht_name']   = $yachtData['name'] ?? 'Yacht';
			$data['yacht_length'] = $yachtData['length'] ?? '';
			$data['yacht_guests'] = $yachtData['guests'] ?? '';
			$data['yacht_cabins'] = $yachtData['cabins'] ?? '';
		}
		
		
		if ( isset( $formData['calculationResult'] ) ) {
			$data['calculation'] = $formData['calculationResult'];
		}
		
		return $data;
	}
	
	
	private function processConditionalBlocks( $content, array $variables ) {
		return preg_replace_callback(
			'/\{\{#if\s+([^}]+)\}\}(.*?)\{\{\/if\}\}/s',
			function( $matches ) use ( $variables ) {
				$condition    = trim( $matches[1] );
				$blockContent = $matches[2];
			
				
				if ( isset( $variables[ $condition ] ) && $variables[ $condition ] ) {
					return $blockContent;
				}
			
				return ''; 
			},
			$content
		);
	}
	
	
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
				
					
					$itemContent = str_replace( '{{this}}', (string) $item, $itemContent );
				
					
					$itemContent = str_replace( '{{@index}}', (string) $index, $itemContent );
				
					
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
	
	
	private function applyEmailStyles( $content ) {
		
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
	
	
	private function saveCustomTemplate( $name, array $data, $content ) {
		try {
			
			
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
