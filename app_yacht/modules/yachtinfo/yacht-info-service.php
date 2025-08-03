
<?php
/**
 * Servicio de información de yates
 * Maneja la extracción de datos desde URLs de yates
 * 
 * @package AppYacht\Modules\YachtInfo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../../shared/helpers/cache-helper.php';
require_once __DIR__ . '/../../shared/helpers/validator-helper.php';

/**
 * Servicio para obtener información de yates
 */
class YachtInfoService implements YachtInfoServiceInterface {
    
    /**
     * @var array Configuración del servicio
     */
    private $config;
    
    /**
     * Constructor
     * 
     * @param array $config Configuración del scraping
     */
    public function __construct(array $config) {
        $this->config = $config;
    }
    
    /**
     * Extrae información de un yate desde una URL
     * 
     * @param string $url URL del yate
     * @return array|WP_Error Datos del yate o error
     */
    public function extractYachtInfo($url) {
        try {
            // Validar URL
            if (!ValidatorHelper::isValidUrl($url)) {
                return new WP_Error('invalid_url', 'URL inválida proporcionada');
            }
            
            // Verificar dominio permitido
            if (!$this->isValidDomain($url)) {
                return new WP_Error('domain_not_allowed', 'Dominio no permitido para scraping');
            }
            
            // Intentar obtener de caché primero
            $cached = $this->getCachedData($url);
            if ($cached !== null) {
                return $cached;
            }
            
            // Hacer scraping
            $data = $this->performScraping($url);
            
            if (is_wp_error($data)) {
                return $data;
            }
            
            // Cachear resultado
            $this->setCachedData($url, $data);
            
            return $data;
            
        } catch (Exception $e) {
            error_log('YachtInfoService Error: ' . $e->getMessage());
            return new WP_Error('scraping_error', 'Error obteniendo información del yate: ' . $e->getMessage());
        }
    }
    
    /**
     * Valida si una URL es de un dominio permitido
     * 
     * @param string $url URL a validar
     * @return bool
     */
    public function isValidDomain($url) {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }
        
        $host = strtolower($parsed['host']);
        
        // Remover www. si existe
        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }
        
        return in_array($host, $this->config['allowed_domains']);
    }
    
    /**
     * Obtiene datos en caché si existen
     * 
     * @param string $url URL del yate
     * @return array|null
     */
    public function getCachedData($url) {
        $cache_key = CacheHelper::generateUrlKey($url);
        return CacheHelper::get($cache_key);
    }
    
    /**
     * Guarda datos en caché
     * 
     * @param string $url URL del yate
     * @param array $data Datos a cachear
     */
    public function setCachedData($url, array $data) {
        $cache_key = CacheHelper::generateUrlKey($url);
        CacheHelper::set($cache_key, $data, $this->config['cache_duration']);
    }
    
    /**
     * Limpia la caché de datos de yates
     */
    public function clearCache() {
        // Para limpiar toda la caché de URLs necesitaríamos un mecanismo más complejo
        // Por ahora, limpiamos toda la caché de app_yacht
        CacheHelper::flush();
    }
    
    /**
     * Realiza el scraping real de la URL
     * 
     * @param string $url URL a scrapear
     * @return array|WP_Error Datos extraídos o error
     */
    private function performScraping($url) {
        // Configurar argumentos para wp_remote_get
        $args = [
            'timeout' => $this->config['timeout'],
            'redirection' => $this->config['max_redirects'],
            'user-agent' => $this->config['user_agent'],
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'DNT' => '1',
                'Connection' => 'keep-alive',
            ]
        ];
        
        // Realizar request
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return new WP_Error('request_failed', 'Error al acceder a la URL: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return new WP_Error('http_error', "Error HTTP {$status_code}");
        }
        
        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return new WP_Error('empty_response', 'Respuesta vacía del servidor');
        }
        
        // Parsear contenido HTML
        return $this->parseHtmlContent($body, $url);
    }
    
    /**
     * Parsea el contenido HTML para extraer datos del yate
     * 
     * @param string $html Contenido HTML
     * @param string $url URL original
     * @return array Datos extraídos
     */
    private function parseHtmlContent($html, $url) {
        // Usar DOMDocument para parsear HTML
        $dom = new DOMDocument();
        
        // Suprimir errores de HTML mal formado
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Datos por defecto
        $data = [
            'name' => '',
            'length' => '',
            'guests' => '',
            'cabins' => '',
            'crew' => '',
            'year' => '',
            'builder' => '',
            'charter_rate' => '',
            'location' => '',
            'description' => '',
            'images' => [],
            'url' => $url,
            'scraped_at' => current_time('mysql')
        ];
        
        // Estrategias de extracción por dominio
        $host = parse_url($url, PHP_URL_HOST);
        
        switch (true) {
            case strpos($host, 'charterworld.com') !== false:
                $data = $this->parseCharterworld($xpath, $data);
                break;
                
            case strpos($host, 'yachtcharterfleet.com') !== false:
                $data = $this->parseYachtCharterFleet($xpath, $data);
                break;
                
            case strpos($host, 'burgessyachts.com') !== false:
                $data = $this->parseBurgess($xpath, $data);
                break;
                
            default:
                $data = $this->parseGeneric($xpath, $data);
                break;
        }
        
        // Limpieza final de datos
        $data = $this->cleanExtractedData($data);
        
        return $data;
    }
    
    /**
     * Parser específico para CharterWorld
     */
    private function parseCharterworld($xpath, $data) {
        // Título/nombre del yate
        $titleNodes = $xpath->query('//h1[@class="yacht-title"] | //h1[contains(@class, "title")]');
        if ($titleNodes->length > 0) {
            $data['name'] = trim($titleNodes->item(0)->textContent);
        }
        
        // Detalles técnicos
        $detailsNodes = $xpath->query('//div[contains(@class, "yacht-details")] | //div[contains(@class, "specifications")]');
        if ($detailsNodes->length > 0) {
            $details = $detailsNodes->item(0)->textContent;
            $data = $this->extractTechnicalDetails($details, $data);
        }
        
        return $data;
    }
    
    /**
     * Parser específico para YachtCharterFleet
     */
    private function parseYachtCharterFleet($xpath, $data) {
        // Implementar lógica específica para YachtCharterFleet
        $titleNodes = $xpath->query('//h1 | //title');
        if ($titleNodes->length > 0) {
            $data['name'] = trim($titleNodes->item(0)->textContent);
        }
        
        return $data;
    }
    
    /**
     * Parser específico para Burgess
     */
    private function parseBurgess($xpath, $data) {
        // Implementar lógica específica para Burgess
        $titleNodes = $xpath->query('//h1 | //title');
        if ($titleNodes->length > 0) {
            $data['name'] = trim($titleNodes->item(0)->textContent);
        }
        
        return $data;
    }
    
    /**
     * Parser genérico para sitios no específicos
     */
    private function parseGeneric($xpath, $data) {
        // Buscar título
        $titleNodes = $xpath->query('//h1 | //title');
        if ($titleNodes->length > 0) {
            $data['name'] = trim($titleNodes->item(0)->textContent);
        }
        
        // Buscar meta description
        $metaNodes = $xpath->query('//meta[@name="description"]');
        if ($metaNodes->length > 0) {
            $data['description'] = trim($metaNodes->item(0)->getAttribute('content'));
        }
        
        return $data;
    }
    
    /**
     * Extrae detalles técnicos desde texto
     */
    private function extractTechnicalDetails($text, $data) {
        // Longitud
        if (preg_match('/(\d+\.?\d*)\s*m\b/i', $text, $matches)) {
            $data['length'] = $matches[1] . 'm';
        }
        
        // Huéspedes
        if (preg_match('/(\d+)\s*guests?/i', $text, $matches)) {
            $data['guests'] = $matches[1];
        }
        
        // Cabinas
        if (preg_match('/(\d+)\s*cabins?/i', $text, $matches)) {
            $data['cabins'] = $matches[1];
        }
        
        // Tripulación
        if (preg_match('/(\d+)\s*crew/i', $text, $matches)) {
            $data['crew'] = $matches[1];
        }
        
        // Año
        if (preg_match('/\b(19|20)\d{2}\b/', $text, $matches)) {
            $data['year'] = $matches[0];
        }
        
        return $data;
    }
    
    /**
     * Limpia y valida los datos extraídos
     */
    private function cleanExtractedData($data) {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim(strip_tags($value));
            }
        }
        
        return $data;
    }
}
