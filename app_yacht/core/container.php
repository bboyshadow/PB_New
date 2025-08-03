<?php
/**
 * Contenedor de Inyección de Dependencias para App_Yacht
 * Implementa un contenedor DI simple y eficiente
 * 
 * @package AppYacht
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contenedor DI simple para servicios de App_Yacht
 */
class AppYachtContainer {
	
	/**
	 * @var array Servicios registrados
	 */
	private $services = array();
	
	/**
	 * @var array Instancias singleton
	 */
	private $instances = array();
	
	/**
	 * @var array Servicios marcados como singleton
	 */
	private $singletons = array();
	
	/**
	 * Registra un servicio en el contenedor
	 * 
	 * @param string   $name Nombre del servicio
	 * @param callable $factory Factory function que crea el servicio
	 * @param bool     $singleton Si debe ser singleton
	 */
	public function register( $name, callable $factory, $singleton = true ) {
		$this->services[ $name ] = $factory;
		
		if ( $singleton ) {
			$this->singletons[ $name ] = true;
		}
	}
	
	/**
	 * Obtiene un servicio del contenedor
	 * 
	 * @param string $name Nombre del servicio
	 * @return mixed
	 * @throws Exception Si el servicio no está registrado
	 */
	public function get( $name ) {
		// Si es singleton y ya existe la instancia
		if ( isset( $this->singletons[ $name ] ) && isset( $this->instances[ $name ] ) ) {
			return $this->instances[ $name ];
		}
		
		// Si el servicio no está registrado
		if ( ! isset( $this->services[ $name ] ) ) {
			throw new Exception( "Servicio '{$name}' no está registrado en el contenedor DI" );
		}
		
		// Crear la instancia
		$factory  = $this->services[ $name ];
		$instance = $factory( $this );
		
		// Guardar como singleton si corresponde
		if ( isset( $this->singletons[ $name ] ) ) {
			$this->instances[ $name ] = $instance;
		}
		
		return $instance;
	}
	
	/**
	 * Verifica si un servicio está registrado
	 * 
	 * @param string $name Nombre del servicio
	 * @return bool
	 */
	public function has( $name ) {
		return isset( $this->services[ $name ] );
	}
	
	/**
	 * Elimina un servicio del contenedor
	 * 
	 * @param string $name Nombre del servicio
	 */
	public function remove( $name ) {
		unset( $this->services[ $name ] );
		unset( $this->instances[ $name ] );
		unset( $this->singletons[ $name ] );
	}
	
	/**
	 * Obtiene todos los servicios registrados
	 * 
	 * @return array
	 */
	public function getRegisteredServices() {
		return array_keys( $this->services );
	}
	
	/**
	 * Limpia todas las instancias singleton (útil para testing)
	 */
	public function clearInstances() {
		$this->instances = array();
	}
}
