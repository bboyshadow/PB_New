<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class AppYachtContainer {
	
	
	private $services = array();
	
	
	private $instances = array();
	
	
	private $singletons = array();
	
	
	public function register( $name, callable $factory, $singleton = true ) {
		$this->services[ $name ] = $factory;
		
		if ( $singleton ) {
			$this->singletons[ $name ] = true;
		}
	}
	
	
	public function get( $name ) {
		
		if ( isset( $this->singletons[ $name ] ) && isset( $this->instances[ $name ] ) ) {
			return $this->instances[ $name ];
		}
		
		
		if ( ! isset( $this->services[ $name ] ) ) {
			throw new Exception( "Service '{$name}' is not registered in the DI container" );
		}
		
		
		$factory  = $this->services[ $name ];
		$instance = $factory( $this );
		
		
		if ( isset( $this->singletons[ $name ] ) ) {
			$this->instances[ $name ] = $instance;
		}
		
		return $instance;
	}
	
	
	public function has( $name ) {
		return isset( $this->services[ $name ] );
	}
	
	
	public function remove( $name ) {
		unset( $this->services[ $name ] );
		unset( $this->instances[ $name ] );
		unset( $this->singletons[ $name ] );
	}
	
	
	public function getRegisteredServices() {
		return array_keys( $this->services );
	}
	
	
	public function clearInstances() {
		$this->instances = array();
	}
}
