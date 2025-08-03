<?php
/**
 * Servicio de cálculos para charter de yates
 * Maneja todos los cálculos relacionados con rates, impuestos y comisiones
 * 
 * @package AppYacht\Modules\Calc
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../shared/helpers/validator-helper.php';

/**
 * Servicio para cálculos de charter
 */
class CalcService implements CalcServiceInterface {
	
	/**
	 * @var array Configuración del servicio
	 */
	private $config;
	
	/**
	 * Constructor
	 * 
	 * @param array $config Configuración de cálculos
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}
	
	/**
	 * Calcula el charter rate estándar
	 * 
	 * @param array $data Datos del formulario
	 * @return array Resultado del cálculo
	 */
	public function calculateCharter( array $data ) {
		try {
			// Validar datos
			$validation = $this->validateCalculationData( $data );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
			
			// Sanitizar datos
			$data = ValidatorHelper::sanitizeInputData( $data );
			
			// Inicializar resultado
			$result = array(
				'base_rate'        => 0,
				'vat_amount'       => 0,
				'apa_amount'       => 0,
				'relocation_fee'   => floatval( $data['relocationFee'] ?? 0 ),
				'security_deposit' => floatval( $data['securityFee'] ?? 0 ),
				'extras'           => array(),
				'extras_total'     => 0,
				'subtotal'         => 0,
				'total'            => 0,
				'currency'         => $data['currency'],
				'breakdown'        => array(),
				'hide_elements'    => $this->getHideElements( $data ),
			);
			
			// Calcular rate base
			$result['base_rate'] = $this->calculateBaseRate( $data );
			
			// Calcular VAT
			if ( isset( $data['vatCheck'] ) && ! empty( $data['vatRate'] ) ) {
				$result['vat_amount'] = $this->applyVAT(
					$result['base_rate'],
					array(
						'rate' => floatval( $data['vatRate'] ),
						'type' => 'percentage',
					)
				);
			}
			
			// Calcular APA
			if ( isset( $data['apaCheck'] ) && ! empty( $data['apaAmount'] ) ) {
				$result['apa_amount'] = floatval( $data['apaAmount'] );
			} elseif ( isset( $data['apaPercentageCheck'] ) && ! empty( $data['apaPercentage'] ) ) {
				$result['apa_amount'] = $this->calculateAPA(
					$result['base_rate'],
					array(
						'percentage' => floatval( $data['apaPercentage'] ),
					)
				);
			}
			
			// Procesar extras
			$result['extras']       = $this->processExtras( $data );
			$result['extras_total'] = array_sum( array_column( $result['extras'], 'amount' ) );
			
			// Calcular totales
			$result['subtotal'] = $result['base_rate'] + $result['vat_amount'] + $result['apa_amount'] + $result['extras_total'];
			$result['total']    = $result['subtotal'] + $result['relocation_fee'] + $result['security_deposit'];
			
			// Generar breakdown detallado
			$result['breakdown'] = $this->generateBreakdown( $result );
			
			// Formatear para presentación
			$result['formatted'] = $this->formatResultForDisplay( $result );
			
			return $result;
			
		} catch ( Exception $e ) {
			error_log( 'CalcService Error: ' . $e->getMessage() );
			return new WP_Error( 'calculation_error', 'Error en cálculo: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Calcula charter con temporadas mixtas
	 * 
	 * @param array $data Datos del formulario
	 * @return array Resultado del cálculo mix
	 */
	public function calculateMix( array $data ) {
		try {
			// Validar datos
			$validation = $this->validateCalculationData( $data );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
			
			// Inicializar resultado
			$result = array(
				'seasons'          => array(),
				'total_weeks'      => 0,
				'weighted_average' => 0,
				'vat_mix'          => array(),
				'total_amount'     => 0,
				'currency'         => $data['currency'],
				'breakdown'        => array(),
			);
			
			// Procesar cada temporada
			if ( isset( $data['seasons'] ) && is_array( $data['seasons'] ) ) {
				foreach ( $data['seasons'] as $season ) {
					$seasonResult           = $this->calculateSeasonPeriod( $season, $data );
					$result['seasons'][]    = $seasonResult;
					$result['total_weeks'] += $seasonResult['weeks'];
				}
			}
			
			// Calcular promedio ponderado
			$result['weighted_average'] = $this->calculateWeightedAverage( $result['seasons'] );
			
			// Procesar VAT mix si está habilitado
			if ( isset( $data['vatRateMix'] ) && ! empty( $data['vatCountries'] ) ) {
				$result['vat_mix'] = $this->calculateVATMix( $data['vatCountries'], $result['weighted_average'] );
			}
			
			// Calcular total final
			$result['total_amount'] = $this->calculateMixTotal( $result );
			
			// Formatear para presentación
			$result['formatted'] = $this->formatMixResultForDisplay( $result );
			
			return $result;
			
		} catch ( Exception $e ) {
			error_log( 'CalcService Mix Error: ' . $e->getMessage() );
			return new WP_Error( 'mix_calculation_error', 'Error en cálculo mix: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Valida los datos de entrada para cálculos
	 * 
	 * @param array $data Datos a validar
	 * @return bool|WP_Error True si válido, WP_Error si no
	 */
	public function validateCalculationData( array $data ) {
		$errors = ValidatorHelper::validateCalculationData( $data );
		
		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', 'Errores de validación', $errors );
		}
		
		return true;
	}
	
	/**
	 * Aplica impuestos VAT según configuración
	 * 
	 * @param float $amount Cantidad base
	 * @param array $vatConfig Configuración de VAT
	 * @return float Cantidad con VAT aplicado
	 */
	public function applyVAT( $amount, array $vatConfig ) {
		if ( ! isset( $vatConfig['rate'] ) || $vatConfig['rate'] <= 0 ) {
			return 0;
		}
		
		$rate = floatval( $vatConfig['rate'] );
		return ( $amount * $rate ) / 100;
	}
	
	/**
	 * Calcula el APA (Advance Provisioning Allowance)
	 * 
	 * @param float $baseAmount Cantidad base
	 * @param array $apaConfig Configuración de APA
	 * @return float Cantidad de APA
	 */
	public function calculateAPA( $baseAmount, array $apaConfig ) {
		if ( ! isset( $apaConfig['percentage'] ) || $apaConfig['percentage'] <= 0 ) {
			return 0;
		}
		
		$percentage = floatval( $apaConfig['percentage'] );
		return ( $baseAmount * $percentage ) / 100;
	}
	
	/**
	 * Formatea una cantidad según la moneda
	 * 
	 * @param float  $amount Cantidad a formatear
	 * @param string $currency Moneda
	 * @return string Cantidad formateada
	 */
	public function formatCurrency( $amount, $currency ) {
		$precision = $this->config['precision'];
		$formatted = number_format( $amount, $precision, '.', ',' );
		
		switch ( $currency ) {
			case '€':
				return '€' . $formatted;
			case '$USD':
				return '$' . $formatted . ' USD';
			case '$AUD':
				return 'A$' . $formatted;
			default:
				return $currency . ' ' . $formatted;
		}
	}
	
	/**
	 * Calcula el rate base según los datos
	 */
	private function calculateBaseRate( array $data ) {
		// Esta lógica dependerá de cómo se definen los rates
		// Por ahora, buscamos campos de rate por temporada
		$baseRate = 0;
		
		if ( isset( $data['charterRate'] ) ) {
			$baseRate = floatval( $data['charterRate'] );
		}
		
		// Si hay temporadas múltiples, calcular diferente
		if ( isset( $data['seasons'] ) && is_array( $data['seasons'] ) ) {
			$totalRate  = 0;
			$totalWeeks = 0;
			
			foreach ( $data['seasons'] as $season ) {
				$weeks       = floatval( $season['weeks'] ?? 0 );
				$rate        = floatval( $season['rate'] ?? 0 );
				$totalRate  += ( $rate * $weeks );
				$totalWeeks += $weeks;
			}
			
			if ( $totalWeeks > 0 ) {
				$baseRate = $totalRate; // Total acumulado
			}
		}
		
		return $baseRate;
	}
	
	/**
	 * Procesa extras del formulario
	 */
	private function processExtras( array $data ) {
		$extras = array();
		
		// Procesar extras dinámicos
		if ( isset( $data['extras'] ) && is_array( $data['extras'] ) ) {
			foreach ( $data['extras'] as $extra ) {
				if ( ! empty( $extra['name'] ) && ! empty( $extra['amount'] ) ) {
					$extras[] = array(
						'name'   => sanitize_text_field( $extra['name'] ),
						'amount' => floatval( $extra['amount'] ),
						'type'   => $extra['type'] ?? 'fixed',
					);
				}
			}
		}
		
		return $extras;
	}
	
	/**
	 * Genera breakdown detallado del cálculo
	 */
	private function generateBreakdown( array $result ) {
		$breakdown = array();
		
		if ( $result['base_rate'] > 0 ) {
			$breakdown[] = array(
				'label'     => 'Charter Rate Base',
				'amount'    => $result['base_rate'],
				'formatted' => $this->formatCurrency( $result['base_rate'], $result['currency'] ),
			);
		}
		
		if ( $result['vat_amount'] > 0 ) {
			$breakdown[] = array(
				'label'     => 'VAT',
				'amount'    => $result['vat_amount'],
				'formatted' => $this->formatCurrency( $result['vat_amount'], $result['currency'] ),
			);
		}
		
		if ( $result['apa_amount'] > 0 ) {
			$breakdown[] = array(
				'label'     => 'APA',
				'amount'    => $result['apa_amount'],
				'formatted' => $this->formatCurrency( $result['apa_amount'], $result['currency'] ),
			);
		}
		
		foreach ( $result['extras'] as $extra ) {
			$breakdown[] = array(
				'label'     => $extra['name'],
				'amount'    => $extra['amount'],
				'formatted' => $this->formatCurrency( $extra['amount'], $result['currency'] ),
			);
		}
		
		if ( $result['relocation_fee'] > 0 ) {
			$breakdown[] = array(
				'label'     => 'Relocation Fee',
				'amount'    => $result['relocation_fee'],
				'formatted' => $this->formatCurrency( $result['relocation_fee'], $result['currency'] ),
			);
		}
		
		if ( $result['security_deposit'] > 0 ) {
			$breakdown[] = array(
				'label'     => 'Security Deposit',
				'amount'    => $result['security_deposit'],
				'formatted' => $this->formatCurrency( $result['security_deposit'], $result['currency'] ),
			);
		}
		
		return $breakdown;
	}
	
	/**
	 * Obtiene elementos a ocultar del resultado
	 */
	private function getHideElements( array $data ) {
		$hide = array();
		
		$hideFields = array( 'hideVAT', 'hideAPA', 'hideRelocation', 'hideSecurity', 'hideExtras', 'hideGratuity' );
		
		foreach ( $hideFields as $field ) {
			if ( isset( $data[ $field ] ) && $data[ $field ] ) {
				$hide[] = str_replace( 'hide', '', $field );
			}
		}
		
		return $hide;
	}
	
	/**
	 * Formatea resultado para presentación
	 */
	private function formatResultForDisplay( array $result ) {
		return array(
			'base_rate'        => $this->formatCurrency( $result['base_rate'], $result['currency'] ),
			'vat_amount'       => $this->formatCurrency( $result['vat_amount'], $result['currency'] ),
			'apa_amount'       => $this->formatCurrency( $result['apa_amount'], $result['currency'] ),
			'relocation_fee'   => $this->formatCurrency( $result['relocation_fee'], $result['currency'] ),
			'security_deposit' => $this->formatCurrency( $result['security_deposit'], $result['currency'] ),
			'extras_total'     => $this->formatCurrency( $result['extras_total'], $result['currency'] ),
			'subtotal'         => $this->formatCurrency( $result['subtotal'], $result['currency'] ),
			'total'            => $this->formatCurrency( $result['total'], $result['currency'] ),
		);
	}
	
	/**
	 * Calcula un periodo de temporada
	 */
	private function calculateSeasonPeriod( array $season, array $data ) {
		return array(
			'name'  => $season['name'] ?? 'Season',
			'weeks' => floatval( $season['weeks'] ?? 0 ),
			'rate'  => floatval( $season['rate'] ?? 0 ),
			'total' => floatval( $season['weeks'] ?? 0 ) * floatval( $season['rate'] ?? 0 ),
		);
	}
	
	/**
	 * Calcula promedio ponderado de temporadas
	 */
	private function calculateWeightedAverage( array $seasons ) {
		$totalAmount = 0;
		$totalWeeks  = 0;
		
		foreach ( $seasons as $season ) {
			$totalAmount += $season['total'];
			$totalWeeks  += $season['weeks'];
		}
		
		return $totalWeeks > 0 ? $totalAmount / $totalWeeks : 0;
	}
	
	/**
	 * Calcula VAT mix por países
	 */
	private function calculateVATMix( array $vatCountries, $baseAmount ) {
		$vatMix = array();
		
		foreach ( $vatCountries as $country ) {
			if ( ! empty( $country['country'] ) && ! empty( $country['rate'] ) ) {
				$vatAmount = $this->applyVAT( $baseAmount, array( 'rate' => floatval( $country['rate'] ) ) );
				$vatMix[]  = array(
					'country' => sanitize_text_field( $country['country'] ),
					'rate'    => floatval( $country['rate'] ),
					'amount'  => $vatAmount,
				);
			}
		}
		
		return $vatMix;
	}
	
	/**
	 * Calcula total del cálculo mix
	 */
	private function calculateMixTotal( array $result ) {
		$total = 0;
		
		foreach ( $result['seasons'] as $season ) {
			$total += $season['total'];
		}
		
		// Agregar VAT mix
		foreach ( $result['vat_mix'] as $vat ) {
			$total += $vat['amount'];
		}
		
		return $total;
	}
	
	/**
	 * Formatea resultado mix para presentación
	 */
	private function formatMixResultForDisplay( array $result ) {
		$formatted = array(
			'seasons'          => array(),
			'vat_mix'          => array(),
			'weighted_average' => $this->formatCurrency( $result['weighted_average'], $result['currency'] ),
			'total_amount'     => $this->formatCurrency( $result['total_amount'], $result['currency'] ),
		);
		
		foreach ( $result['seasons'] as $season ) {
			$formatted['seasons'][] = array(
				'name'  => $season['name'],
				'weeks' => $season['weeks'],
				'rate'  => $this->formatCurrency( $season['rate'], $result['currency'] ),
				'total' => $this->formatCurrency( $season['total'], $result['currency'] ),
			);
		}
		
		foreach ( $result['vat_mix'] as $vat ) {
			$formatted['vat_mix'][] = array(
				'country' => $vat['country'],
				'rate'    => $vat['rate'] . '%',
				'amount'  => $this->formatCurrency( $vat['amount'], $result['currency'] ),
			);
		}
		
		return $formatted;
	}
}
