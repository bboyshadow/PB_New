<?php

/*
Restauramos el archivo yacht-info-service.php porque las modificaciones anteriores no resolvieron el problema y la caché estaba dañando el módulo.
Hemos implementado la normalización de URL, que funcionó.
Hemos implementado la verificación de captcha, que funcionó.
Hemos implementado la selección aleatoria de user-agent, que funcionó.
Hemos implementado el retraso aleatorio entre solicitudes, que funcionó.
Hemos implementado headers adicionales para simular un navegador real, que funcionó.
Hemos implementado la verificación del código de estado HTTP, que funcionó.
Nota: Hubo un error al agregar timeout a las solicitudes cURL, por lo que anotamos hasta este paso. Ahora, agregamos la lógica para extraer datos faltantes (descripción, king, queen, detalles de tripulación) para tener todo funcional antes de continuar con las medidas de seguridad restantes.
*/

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
		$url = $this->normalizeUrl( $url );
		try {
			error_log('YachtInfo: Starting extraction for URL: ' . $url);
			// Validar formato de URL
			if ( ! ValidatorHelper::isValidUrl( $url ) ) {
				error_log('YachtInfo: Invalid URL format');
				return new WP_Error( 'invalid_url', 'Invalid URL provided. Please enter a valid yacht URL.' );
			}
			
			// Verificar dominio permitido
			if ( ! $this->isValidDomain( $url ) ) {
				$host = parse_url( $url, PHP_URL_HOST );
				error_log('YachtInfo: Domain not allowed: ' . $host);
				return new WP_Error( 'domain_not_allowed', "Domain {$host} is not allowed for scraping. Please use a supported yacht website." );
			}

			// Limitación de tasa
			$rateLimitKey = 'yacht_scrape_' . md5( $_SERVER['REMOTE_ADDR'] );
			if ( ! pb_check_rate_limit( $rateLimitKey, 5, 300 ) ) {
				error_log('YachtInfo: Rate limit exceeded for IP: ' . $_SERVER['REMOTE_ADDR']);
				pb_log_security_event( get_current_user_id(), 'rate_limit_exceeded', [ 'ip' => $_SERVER['REMOTE_ADDR'], 'url' => $url ] );
				return new WP_Error( 'rate_limit_exceeded', 'Límite de solicitudes excedido. Intente de nuevo más tarde.' );
			}
			
			// Realizar scraping
			$cachedData = $this->getCachedData( $url );
			if ( $cachedData ) {
				return $cachedData;
			}

			$data = $this->performScraping( $url );

			if ( is_wp_error( $data ) ) {
				error_log('YachtInfo: Scraping error: ' . $data->get_error_message());
				return $data;
			}
			
			// Verificar si se obtuvo algún dato útil
			if (empty($data['name']) && empty($data['length']) && empty($data['guest'])) {
				error_log('YachtInfo: Insufficient data extracted');
				return new WP_Error( 'insufficient_data', 'Could not extract yacht information from this page. Please try a different URL.' );
			}
			
			error_log('YachtInfo: Extraction successful, data: ' . print_r($data, true));
			pb_log_security_event( get_current_user_id(), 'yacht_info_extracted', [ 'url' => $url, 'extracted_fields' => count( $data ) ] );
			$this->setCachedData( $url, $data );
			return $data;
			
		} catch ( Exception $e ) {
			error_log( 'YachtInfoService Error: ' . $e->getMessage() );
			return new WP_Error( 'scraping_error', 'Error obtaining yacht information: ' . $e->getMessage() );
		}
	}
	
	private function performScraping( $url ) {
		$data = $this->extraerInformacionYate( $url );
		$mapped = [
			'name' => $data['yachtName'] ?? 'Yacht Name',
			'length' => $data['length'] ?? '--',
			'draft' => $data['draft'] ?? '--',
			'beam' => $data['beam'] ?? '--',
			'grossTonnage' => $data['grossTonnage'] ?? '--',
			'type' => $data['type'] ?? '--',
			'builder' => $data['builder'] ?? '--',
			'year' => $data['yearBuilt'] ?? '--',
			'crew' => $data['crew'] ?? '--',
			'cabins' => $data['cabins'] ?? '--',
			'guests' => $data['guest'] ?? '--',
			'cabinConfiguration' => $data['cabinConfiguration'] ?? '--',
			'king' => $data['king'] ?? '--',
			'queen' => $data['queen'] ?? '--',
			'wifi' => $data['wifi'] ?? '--',
			'jacuzzi' => $data['jacuzzi'] ?? '--',
			'waterToys' => $data['waterToys'] ?? '--',
			'stabilizers' => $data['stabilizers'] ?? '--',
			'cruisingSpeed' => $data['cruisingSpeed'] ?? '--',
			'maxSpeed' => $data['maxSpeed'] ?? '--',
			'fuelConsumption' => $data['fuelConsumption'] ?? '--',
			'range' => $data['range'] ?? '--',
			'image' => $data['imageUrl'] ?? '',
			'description' => $data['description'] ?? '',
			'crewDetails' => $data['crewDetails'] ?? [],
		];
		return $mapped;
	}
	
	private function extraerInformacionYate( $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return array(
				'yachtName'          => 'Invalid URL',
				'length'             => '--',
				'type'               => '--',
				'builder'            => '--',
				'yearBuilt'          => '--',
				'crew'               => '--',
				'cabins'             => '--',
				'guest'              => '--',
				'cabinConfiguration' => '--',
				'imageUrl'           => '',
				'yachtUrl'           => $url,
			);
		}
		sleep(rand(1, 3));
$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		$userAgents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36'
];
curl_setopt( $ch, CURLOPT_USERAGENT, $userAgents[array_rand($userAgents)] );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.5',
    'Connection: keep-alive',
    'Upgrade-Insecure-Requests: 1'
] );
		$html = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			curl_close( $ch );
			return array( /* error array */ );
		}
		// Verificar si hay captcha
		if ( stripos( $html, 'captcha' ) !== false || stripos( $html, 'recaptcha' ) !== false || stripos( $html, 'blocked' ) !== false ) {
			curl_close( $ch );
			return array(
				'yachtName' => 'Access Blocked',
				'length' => '--',
				// ... resto de campos con '--'
				'yachtUrl' => $url,
			);
		}
		curl_close( $ch );

		$dom = new DOMDocument();
		@$dom->loadHTML( $html );
		$xpath = new DOMXPath( $dom );
		
		$yachtNameNode = $xpath->query( "//div[contains(@class, 'logo-wrapper')]/a/h2" )->item( 0 );
		$lengthP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Length:')]]" )->item( 0 );
		$length = '--';
		if ($lengthP) {
			$strong = $xpath->query("strong", $lengthP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $lengthP->textContent;
				$length = trim(str_replace($strongText, '', $pText));
			}
		}
		$draftP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Draft:')]]" )->item( 0 );
		$draft = '--';
		if ($draftP) {
			$strong = $xpath->query("strong", $draftP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $draftP->textContent;
				$draft = trim(str_replace($strongText, '', $pText));
			}
		}
		$beamP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Beam:')]]" )->item( 0 );
		$beam = '--';
		if ($beamP) {
			$strong = $xpath->query("strong", $beamP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $beamP->textContent;
				$beam = trim(str_replace($strongText, '', $pText));
			}
		}
		$grossTonnageP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Gross Tonnage:')]]" )->item( 0 );
		$grossTonnage = '--';
		if ($grossTonnageP) {
			$strong = $xpath->query("strong", $grossTonnageP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $grossTonnageP->textContent;
				$grossTonnage = trim(str_replace($strongText, '', $pText));
			}
		}
		$builderP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Make:')]]" )->item( 0 );
		$builder = '--';
		if ($builderP) {
			$strong = $xpath->query("strong", $builderP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $builderP->textContent;
				$builder = trim(str_replace($strongText, '', $pText));
			}
		}
		$yearBuiltP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Year Built:')]]" )->item( 0 );
		$yearBuilt = '--';
		if ($yearBuiltP) {
			$strong = $xpath->query("strong", $yearBuiltP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $yearBuiltP->textContent;
				$yearBuilt = trim(str_replace($strongText, '', $pText));
			}
		}
		$crewP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Crew:')]]" )->item( 0 );
		$crew = '--';
		if ($crewP) {
			$strong = $xpath->query("strong", $crewP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $crewP->textContent;
				$crew = trim(str_replace($strongText, '', $pText));
			}
		}
		if ($crew === '--') {
			$crewNode = $xpath->query( "//ul[contains(@class, 'yacht-summary-counts')]/li/span[contains(., 'Crew : ')]" )->item( 0 );
			$crew = $crewNode ? trim( str_replace( 'Crew :', '', $crewNode->textContent ) ) : '--';
		}
		$cabinsP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), '# Cabins:')]]" )->item( 0 );
		$cabins = '--';
		if ($cabinsP) {
			$strong = $xpath->query("strong", $cabinsP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $cabinsP->textContent;
				$cabins = trim(str_replace($strongText, '', $pText));
			}
		}
		$guestP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), '#Pax:')]]" )->item( 0 );
		$guest = '--';
		if ($guestP) {
			$strong = $xpath->query("strong", $guestP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $guestP->textContent;
				$guest = trim(str_replace($strongText, '', $pText));
			}
		}
		$cabinConfigP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Cabin Configuration:')]]" )->item( 0 );
		$cabinConfig = '--';
		if ($cabinConfigP) {
			$strong = $xpath->query("strong", $cabinConfigP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $cabinConfigP->textContent;
				$cabinConfig = trim(str_replace($strongText, '', $pText));
			}
		}
		$king = '--';
		$queen = '--';
		if (preg_match('/(\d+) King/', $cabinConfig, $matches)) {
			$king = $matches[1];
		}
		if (preg_match('/(\d+) Queen/', $cabinConfig, $matches)) {
			$queen = $matches[1];
		}

		$imgNode       = $xpath->query( "//div[contains(@class, 'header')]/@data-background" )->item( 0 );

		// Extracción de datos adicionales
		
		
		$wifiP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'WiFi:')]]" )->item( 0 );
		$wifi = '--';
		if ($wifiP) {
			$strong = $xpath->query("strong", $wifiP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $wifiP->textContent;
				$wifi = trim(str_replace($strongText, '', $pText));
			}
		}
		$jacuzziP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Jacuzzi:')]]" )->item( 0 );
		$jacuzzi = '--';
		if ($jacuzziP) {
			$strong = $xpath->query("strong", $jacuzziP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $jacuzziP->textContent;
				$jacuzzi = trim(str_replace($strongText, '', $pText));
			}
		}
		$waterToysP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Water Toys:')]]" )->item( 0 );
		$waterToys = '--';
		if ($waterToysP) {
			$strong = $xpath->query("strong", $waterToysP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $waterToysP->textContent;
				$waterToys = trim(str_replace($strongText, '', $pText));
			}
		}
		$stabilizersP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Stabilizers:')]]" )->item( 0 );
		$stabilizers = '--';
		if ($stabilizersP) {
			$strong = $xpath->query("strong", $stabilizersP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $stabilizersP->textContent;
				$stabilizers = trim(str_replace($strongText, '', $pText));
			}
		}
		$cruisingSpeedP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Cruising Speed:')]]" )->item( 0 );
		$cruisingSpeed = '--';
		if ($cruisingSpeedP) {
			$strong = $xpath->query("strong", $cruisingSpeedP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $cruisingSpeedP->textContent;
				$cruisingSpeed = trim(str_replace($strongText, '', $pText));
			}
		}
		$maxSpeedP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Max Speed:')]]" )->item( 0 );
		$maxSpeed = '--';
		if ($maxSpeedP) {
			$strong = $xpath->query("strong", $maxSpeedP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $maxSpeedP->textContent;
				$maxSpeed = trim(str_replace($strongText, '', $pText));
			}
		}
		$fuelConsumptionP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Fuel Consumption:')]]" )->item( 0 );
		$fuelConsumption = '--';
		if ($fuelConsumptionP) {
			$strong = $xpath->query("strong", $fuelConsumptionP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $fuelConsumptionP->textContent;
				$fuelConsumption = trim(str_replace($strongText, '', $pText));
			}
		}
		$rangeP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Range:')]]" )->item( 0 );
		$range = '--';
		if ($rangeP) {
			$strong = $xpath->query("strong", $rangeP)->item(0);
			if ($strong) {
				$strongText = $strong->textContent;
				$pText = $rangeP->textContent;
				$range = trim(str_replace($strongText, '', $pText));
			}
		}

		// Fallback parsing from description for missing fields - Improved version
		// Primero obtenemos la descripción para los fallbacks
		$descriptionNodes = $xpath->query( "//div[contains(@class, 'yacht-description')]//p | //*[contains(text(), 'is the perfect vessel')]" );
		$description = '';
		foreach ( $descriptionNodes as $node ) {
			$description .= trim( $node->textContent ) . " ";
		}
		$description = trim( $description );
		$description = pb_sanitize_html_content( $description );

		// Ahora procesamos los fallbacks para los campos faltantes
		if ($grossTonnage === '--') {
			// Intentar extraer de párrafos específicos que tengan este dato
			$grossTonnageP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Gross Tonnage')]]" )->item( 0 );
			if ($grossTonnageP) {
				$strong = $xpath->query("strong", $grossTonnageP)->item(0);
				if ($strong) {
					$strongText = $strong->textContent;
					$pText = $grossTonnageP->textContent;
					$grossTonnage = trim(str_replace($strongText, '', $pText));
				}
			}
			// Fallback a descripción
			if ($grossTonnage === '--' && preg_match('/gross tonnage of (\d+(?:,\d+)?)/i', $description, $matches)) {
				$grossTonnage = $matches[1];
			}
		}
		
		if ($wifi === '--') {
			// Intentar extraer de párrafos específicos para WiFi
			$wifiP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Internet') or contains(text(), 'WiFi')]]" )->item( 0 );
			if ($wifiP) {
				$strong = $xpath->query("strong", $wifiP)->item(0);
				if ($strong) {
					$strongText = $strong->textContent;
					$pText = $wifiP->textContent;
					$wifi = trim(str_replace($strongText, '', $pText));
					if (empty($wifi) || $wifi === '--') $wifi = 'Yes'; // Si existe la etiqueta pero está vacía, asumimos que tiene
				}
			}
			// Fallback a descripción
			if ($wifi === '--') {
				if (strpos($description, 'Wi-Fi') !== false || strpos($description, 'wifi') !== false || 
				    strpos($description, 'WiFi') !== false || strpos($description, 'internet') !== false) {
					$wifi = 'Yes';
				} else {
					$wifi = 'No';
				}
			}
		}
		
		if ($waterToys === '--') {
			// Buscamos primero en el div específico de Other Toys
			$otherToysP = $xpath->query( "//div[@id='specifications-tab']//*[contains(text(), 'Other Toys')]" )->item( 0 );
			if ($otherToysP) {
				$waterToys = 'Yes - See details';
			} else {
				// Intentamos en descripción
				if (preg_match('/water toys: (.*?)(?:\.|$)/i', $description, $matches)) {
					$waterToys = trim($matches[1]);
				}
			}
		}
		
		if ($fuelConsumption === '--') {
			// Buscamos en specification-tab
			$fuelConsumptionP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Fuel Consumption')]]" )->item( 0 );
			if ($fuelConsumptionP) {
				$strong = $xpath->query("strong", $fuelConsumptionP)->item(0);
				if ($strong) {
					$strongText = $strong->textContent;
					$pText = $fuelConsumptionP->textContent;
					$fuelConsumption = trim(str_replace($strongText, '', $pText));
				}
			}
			// Fallback a engines/generators que puede contener info de consumo
			if ($fuelConsumption === '--') {
				$enginesP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Engines/Generators')]]" )->item( 0 );
				if ($enginesP) {
					$strong = $xpath->query("strong", $enginesP)->item(0);
					if ($strong) {
						$strongText = $strong->textContent;
						$pText = $enginesP->textContent;
						$enginesText = trim(str_replace($strongText, '', $pText));
						if (preg_match('/(\d+(?:,\d+)?)\s*(?:liters|lph|L\/h)/i', $enginesText, $matches)) {
							$fuelConsumption = $matches[1] . ' lph';
						}
					}
				}
			}
			// Fallback a descripción
			if ($fuelConsumption === '--' && preg_match('/fuel consumption of (\d+(?:,\d+)?) lph/i', $description, $matches)) {
				$fuelConsumption = $matches[1] . ' lph';
			}
		}
		
		if ($range === '--') {
			// Buscamos en specification-tab
			$rangeP = $xpath->query( "//div[@id='specifications-tab']//p[contains(@class, 'text-dark')][strong[contains(text(), 'Range')]]" )->item( 0 );
			if ($rangeP) {
				$strong = $xpath->query("strong", $rangeP)->item(0);
				if ($strong) {
					$strongText = $strong->textContent;
					$pText = $rangeP->textContent;
					$range = trim(str_replace($strongText, '', $pText));
				}
			}
			// Fallback a descripción
			if ($range === '--' && preg_match('/range of up to (\d+(?:,\d+)?) nautical miles/i', $description, $matches)) {
				$range = $matches[1] . ' nm';
			}
		}

		// Detalles de tripulación
		$crewDetails = [];
		$crewNodes = $xpath->query( "//div[contains(@class, 'crew-member')] | //div[contains(., 'Captain:')]" );
		foreach ( $crewNodes as $node ) {
				$name = $xpath->query( ".//*[contains(., 'Captain:') or contains(., 'Chef:')]", $node )->item(0) ? trim( str_replace( [ 'Captain: ', 'Chef: ' ], '', $xpath->query( ".//*[contains(., 'Captain:') or contains(., 'Chef:')]", $node )->item(0)->textContent ) ) : '';
				$nation = $xpath->query( ".//*[contains(., 'Nation:')]", $node )->item(0) ? trim( str_replace( 'Nation: ', '', $xpath->query( ".//*[contains(., 'Nation:')]", $node )->item(0)->textContent ) ) : '';
				$licenses = $xpath->query( ".//*[contains(., 'Licenses:')]", $node )->item(0) ? trim( str_replace( 'Licenses: ', '', $xpath->query( ".//*[contains(., 'Licenses:')]", $node )->item(0)->textContent ) ) : '';
				$role = $xpath->query( ".//h5/following-sibling::p", $node )->item(0) ? trim( $xpath->query( ".//h5/following-sibling::p", $node )->item(0)->textContent ) : '';
				if ( $name ) {
					$crewDetails[] = [ 'name' => $name, 'role' => $role, 'nation' => $nation, 'licenses' => $licenses ];
				}
			}

			foreach ($crewDetails as &$member) {
				$member['name'] = pb_sanitize_html_content($member['name']);
				$member['role'] = pb_sanitize_html_content($member['role']);
				$member['nation'] = pb_sanitize_html_content($member['nation']);
				$member['licenses'] = pb_sanitize_html_content($member['licenses']);
			}

		$typeNode = $xpath->query( "//div[@id='specifications-tab']//div[contains(@class, 'text-start')]/h4" )->item( 0 );
		$type     = $typeNode ? trim( $typeNode->textContent ) : '--';
		
		$imgUrl = $imgNode ? $imgNode->textContent : '';
		
		return array(
			'yachtName'          => $yachtNameNode ? trim( $yachtNameNode->textContent ) : 'Yacht Name',
			'length'             => $length,
			'draft'              => $draft,
			'beam'               => $beam,
			'grossTonnage'       => $grossTonnage,
			'type'               => $type,
			'builder'            => $builder,
			'yearBuilt'          => $yearBuilt,
			'crew'               => $crew,
			'cabins'             => $cabins,
			'guest'              => $guest,
			'cabinConfiguration' => $cabinConfig,
			'king'               => $king,
			'queen'              => $queen,
			'wifi'               => $wifi,
			'jacuzzi'            => $jacuzzi,
			'waterToys'          => $waterToys,
			'stabilizers'        => $stabilizers,
			'cruisingSpeed'      => $cruisingSpeed,
			'maxSpeed'           => $maxSpeed,
			'fuelConsumption'    => $fuelConsumption,
			'range'              => $range,
			'imageUrl'           => $imgUrl,
			'description'        => $description,
			'crewDetails'        => $crewDetails,
			'yachtUrl'           => $url,
		);
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

	private function normalizeUrl( $url ) {
		$parsed = parse_url( $url );
		if ( ! $parsed ) return $url;
		$query = [];
		if ( isset( $parsed['query'] ) ) {
			parse_str( $parsed['query'], $query );
		}
		$query = array_filter( $query, function( $key ) {
			return ! in_array( $key, [ 'utm_source', 'utm_medium', 'utm_campaign', 'gclid' ] );
		}, ARRAY_FILTER_USE_KEY );
		$parsed['query'] = http_build_query( $query );
		$normalized = ( isset( $parsed['scheme'] ) ? $parsed['scheme'] . '://' : '' ) .
		              ( isset( $parsed['host'] ) ? $parsed['host'] : '' ) .
		              ( isset( $parsed['path'] ) ? $parsed['path'] : '' ) .
		              ( ! empty( $parsed['query'] ) ? '?' . $parsed['query'] : '' );
		return $normalized;
	}

}
