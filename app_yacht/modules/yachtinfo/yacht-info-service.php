<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../shared/helpers/cache-helper.php';
require_once __DIR__ . '/../../shared/helpers/validator-helper.php';


class YachtInfoService implements YachtInfoServiceInterface {
	
	
	private $config;
	
	
	public function __construct( array $config ) {
		$this->config = $config;
	}
	
	
	public function extractYachtInfo( $url ) {
		try {
			
			if ( ! ValidatorHelper::isValidUrl( $url ) ) {
				return new WP_Error( 'invalid_url', 'URL inválida proporcionada' );
			}
			
			
			if ( ! $this->isValidDomain( $url ) ) {
				return new WP_Error( 'domain_not_allowed', 'Dominio no permitido para scraping' );
			}
			
			
			$cached = $this->getCachedData( $url );
			if ( $cached !== null ) {
				return $cached;
			}
			
			
			$data = $this->performScraping( $url );
			
			if ( is_wp_error( $data ) ) {
				return $data;
			}
			
			
			$this->setCachedData( $url, $data );
			
			return $data;
			
		} catch ( Exception $e ) {
			error_log( 'YachtInfoService Error: ' . $e->getMessage() );
			return new WP_Error( 'scraping_error', 'Error obteniendo información del yate: ' . $e->getMessage() );
		}
	}
	
	
	public function isValidDomain( $url ) {
		$parsed = parse_url( $url );
		if ( ! $parsed || ! isset( $parsed['host'] ) ) {
			return false;
		}
		
		$host = strtolower( $parsed['host'] );
		
		
		if ( strpos( $host, 'www.' ) === 0 ) {
			$host = substr( $host, 4 );
		}
		
		return in_array( $host, $this->config['allowed_domains'] );
	}
	
	
	public function getCachedData( $url ) {
		$cache_key = CacheHelper::generateUrlKey( $url );
		return CacheHelper::get( $cache_key );
	}
	
	
	public function setCachedData( $url, array $data ) {
		$cache_key = CacheHelper::generateUrlKey( $url );
		CacheHelper::set( $cache_key, $data, $this->config['cache_duration'] );
	}
	
	
	public function clearCache() {
		
		
		CacheHelper::flush();
	}
	
	
	private function performScraping( $url ) {
		
		$args = array(
			'timeout'     => $this->config['timeout'],
			'redirection' => $this->config['max_redirects'],
			'user-agent'  => $this->config['user_agent'],
			'headers'     => array(
				'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'en-US,en;q=0.5',
				'Accept-Encoding' => 'gzip, deflate',
				'DNT'             => '1',
				'Connection'      => 'keep-alive',
			),
		);
		
		
		$response = wp_remote_get( $url, $args );
		
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'request_failed', 'Error al acceder a la URL: ' . $response->get_error_message() );
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code !== 200 ) {
			return new WP_Error( 'http_error', "Error HTTP {$status_code}" );
		}
		
		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'empty_response', 'Respuesta vacía del servidor' );
		}
		
		
		return $this->parseHtmlContent( $body, $url );
	}
	
	
	private function parseHtmlContent( $html, $url ) {
		
		$dom = new DOMDocument();
		
		
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();
		
		$xpath = new DOMXPath( $dom );
		
		
		$data = array(
			'name'         => '',
			'length'       => '',
			'guests'       => '',
			'cabins'       => '',
			'crew'         => '',
			'year'         => '',
			'builder'      => '',
			'charter_rate' => '',
			'location'     => '',
			'description'  => '',
			'images'       => array(),
			'url'          => $url,
			'scraped_at'   => current_time( 'mysql' ),
		);
		
		
		$host = parse_url( $url, PHP_URL_HOST );
		
		switch ( true ) {
			case strpos( $host, 'charterworld.com' ) !== false:
				$data = $this->parseCharterworld( $xpath, $data );
				break;
				
			case strpos( $host, 'yachtcharterfleet.com' ) !== false:
				$data = $this->parseYachtCharterFleet( $xpath, $data );
				break;
				
			case strpos( $host, 'burgessyachts.com' ) !== false:
				$data = $this->parseBurgess( $xpath, $data );
				break;
				
			default:
				$data = $this->parseGeneric( $xpath, $data );
				break;
		}
		
		
		$data = $this->cleanExtractedData( $data );
		
		return $data;
	}
	
	
	private function parseCharterworld( $xpath, $data ) {
		
		$titleNodes = $xpath->query( '//h1[@class="yacht-title"] | //h1[contains(@class, "title")]' );
		if ( $titleNodes->length > 0 ) {
			$data['name'] = trim( $titleNodes->item( 0 )->textContent );
		}
		
		
		$detailsNodes = $xpath->query( '//div[contains(@class, "yacht-details")] | //div[contains(@class, "specifications")]' );
		if ( $detailsNodes->length > 0 ) {
			$details = $detailsNodes->item( 0 )->textContent;
			$data    = $this->extractTechnicalDetails( $details, $data );
		}
		
		return $data;
	}
	
	
	private function parseYachtCharterFleet( $xpath, $data ) {
		
		$titleNodes = $xpath->query( '//h1 | //title' );
		if ( $titleNodes->length > 0 ) {
			$data['name'] = trim( $titleNodes->item( 0 )->textContent );
		}
		
		return $data;
	}
	
	
	private function parseBurgess( $xpath, $data ) {
		
		$titleNodes = $xpath->query( '//h1 | //title' );
		if ( $titleNodes->length > 0 ) {
			$data['name'] = trim( $titleNodes->item( 0 )->textContent );
		}
		
		return $data;
	}
	
	
	private function parseGeneric( $xpath, $data ) {
		
		$titleNodes = $xpath->query( '//h1 | //title' );
		if ( $titleNodes->length > 0 ) {
			$data['name'] = trim( $titleNodes->item( 0 )->textContent );
		}
		
		
		$metaNodes = $xpath->query( '//meta[@name="description"]' );
		if ( $metaNodes->length > 0 ) {
			$data['description'] = trim( $metaNodes->item( 0 )->getAttribute( 'content' ) );
		}
		
		return $data;
	}
	
	
	private function extractTechnicalDetails( $text, $data ) {
		
		if ( preg_match( '/(\d+\.?\d*)\s*m\b/i', $text, $matches ) ) {
			$data['length'] = $matches[1] . 'm';
		}
		
		
		if ( preg_match( '/(\d+)\s*guests?/i', $text, $matches ) ) {
			$data['guests'] = $matches[1];
		}
		
		
		if ( preg_match( '/(\d+)\s*cabins?/i', $text, $matches ) ) {
			$data['cabins'] = $matches[1];
		}
		
		
		if ( preg_match( '/(\d+)\s*crew/i', $text, $matches ) ) {
			$data['crew'] = $matches[1];
		}
		
		
		if ( preg_match( '/\b(19|20)\d{2}\b/', $text, $matches ) ) {
			$data['year'] = $matches[0];
		}
		
		return $data;
	}
	
	
	private function cleanExtractedData( $data ) {
		foreach ( $data as $key => $value ) {
			if ( is_string( $value ) ) {
				$data[ $key ] = trim( strip_tags( $value ) );
			}
		}
		
		return $data;
	}
}
