<?php
/**
 * Interface para el servicio de información de yates
 * Define el contrato para obtener datos de yates desde URLs
 *
 * @package AppYacht\Interfaces
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface para servicios de información de yates
 */
interface YachtInfoServiceInterface {

	/**
	 * Extrae información de un yate desde una URL
	 *
	 * @param string $url URL del yate
	 * @return array|WP_Error Datos del yate o error
	 */
	public function extractYachtInfo( $url);

	/**
	 * Valida si una URL es de un dominio permitido
	 *
	 * @param string $url URL a validar
	 * @return bool
	 */
	public function isValidDomain( $url);

	/**
	 * Obtiene datos en caché si existen
	 *
	 * @param string $url URL del yate
	 * @return array|null
	 */
	public function getCachedData( $url);

	/**
	 * Guarda datos en caché
	 *
	 * @param string $url URL del yate
	 * @param array  $data Datos a cachear
	 */
	public function setCachedData( $url, array $data);

	/**
	 * Limpia la caché de datos de yates
	 */
	public function clearCache();
}
