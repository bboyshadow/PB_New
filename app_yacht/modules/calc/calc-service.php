<?php

/**
 * CalcService class
 * 
 * Servicio principal para realizar cálculos de chárter de yates.
 * Implementa la lógica de negocio para cálculos estándar y mixtos (temporadas múltiples).
 * 
 * @package AppYacht
 * @subpackage Calculator
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../shared/helpers/validator-helper.php';

/**
 * CalcService - Servicio de cálculo de chárteres de yates
 * 
 * Esta clase maneja todos los cálculos relacionados con tarifas de chárter,
 * aplicación de IVA, APA, extras y formateo de resultados.
 * 
 * @implements CalcServiceInterface
 */
class CalcService implements CalcServiceInterface {
	
	/**
	 * Configuración del servicio de cálculo
	 * 
	 * @var array Configuración que incluye precisión de decimales y otros parámetros
	 */
	private $config;
	
	/**
	 * Constructor de CalcService
	 * 
	 * @param array $config Configuración del servicio que debe incluir:
	 *                      - precision: int Número de decimales para formateo de moneda
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}
	
	/**
	 * Calcula una tarifa de chárter estándar
	 * 
	 * Procesa todos los componentes de un chárter: tarifa base, IVA, APA,
	 * tasas de reubicación, depósito de seguridad y extras.
	 * 
	 * @param array $data Datos del cálculo que deben incluir:
	 *                    - currency: string Moneda para el cálculo (€, $USD, $AUD)
	 *                    - charterRate: float Tarifa base del chárter
	 *                    - vatRate: float Porcentaje de IVA
	 *                    - apaAmount: float Cantidad fija de APA
	 *                    - apaPercentage: float Porcentaje de APA sobre tarifa base
	 *                    - relocationFee: float Tarifa de reubicación
	 *                    - securityFee: float Depósito de seguridad
	 *                    - extras: array Lista de extras con nombre y cantidad
	 *                    
	 * @return array|WP_Error Resultado del cálculo con estructura:
	 *                        - base_rate: float Tarifa base calculada
	 *                        - vat_amount: float Cantidad de IVA
	 *                        - apa_amount: float Cantidad de APA
	 *                        - relocation_fee: float Tarifa de reubicación
	 *                        - security_deposit: float Depósito de seguridad
	 *                        - extras: array Lista de extras procesados
	 *                        - extras_total: float Total de extras
	 *                        - subtotal: float Subtotal sin tasas adicionales
	 *                        - total: float Total final
	 *                        - currency: string Moneda utilizada
	 *                        - breakdown: array Desglose detallado
	 *                        - formatted: array Versión formateada para mostrar
	 *                        
	 * @throws Exception Si ocurre un error durante el cálculo
	 */
	public function calculateCharter( array $data ) {
		try {
			
			$validation = $this->validateCalculationData( $data );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
			
			
			$data = ValidatorHelper::sanitizeInputData( $data );
			
			
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
			
			
			$result['base_rate'] = $this->calculateBaseRate( $data );
			
			
			if ( isset( $data['vatCheck'] ) && ! empty( $data['vatRate'] ) ) {
				$result['vat_amount'] = $this->applyVAT(
					$result['base_rate'],
					array(
						'rate' => floatval( $data['vatRate'] ),
						'type' => 'percentage',
					)
				);
			}
			
			
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
			
			
			$result['extras']       = $this->processExtras( $data );
			$result['extras_total'] = array_sum( array_column( $result['extras'], 'amount' ) );
			
			
			$result['subtotal'] = $result['base_rate'] + $result['vat_amount'] + $result['apa_amount'] + $result['extras_total'];
			$result['total']    = $result['subtotal'] + $result['relocation_fee'] + $result['security_deposit'];
			
			
			$result['breakdown'] = $this->generateBreakdown( $result );
			
			
			$result['formatted'] = $this->formatResultForDisplay( $result );
			
			return $result;
			
		} catch ( Exception $e ) {
			error_log( 'CalcService Error: ' . $e->getMessage() );
			return new WP_Error( 'calculation_error', 'Calculation error: ' . $e->getMessage() );
		}
	}
	
	
	/**
	 * Calcula una tarifa mixta combinando varias temporadas
	 *
	 * Recorre las temporadas aportadas, calcula importes por periodo, el promedio
	 * ponderado semanal/noches y aplica el mix de IVA si procede.
	 *
	 * @param array $data Datos con:
	 *                    - currency: string
	 *                    - seasons: array<int,array{name:string,weeks:float,rate:float}>
	 *                    - vatRateMix: bool
	 *                    - vatCountries: array<int,array{country:string,rate:float}>
	 * @return array|WP_Error Resultado con temporadas, promedio ponderado, IVA mix y totales (incluye versión formateada)
	 */
	public function calculateMix( array $data ) {
		try {
			
			$validation = $this->validateCalculationData( $data );
			if ( is_wp_error( $validation ) ) {
				return $validation;
			}
			
			
			$result = array(
				'seasons'          => array(),
				'total_weeks'      => 0,
				'weighted_average' => 0,
				'vat_mix'          => array(),
				'total_amount'     => 0,
				'currency'         => $data['currency'],
				'breakdown'        => array(),
			);
			
			
			if ( isset( $data['seasons'] ) && is_array( $data['seasons'] ) ) {
				foreach ( $data['seasons'] as $season ) {
					$seasonResult           = $this->calculateSeasonPeriod( $season, $data );
					$result['seasons'][]    = $seasonResult;
					$result['total_weeks'] += $seasonResult['weeks'];
				}
			}
			
			
			$result['weighted_average'] = $this->calculateWeightedAverage( $result['seasons'] );
			
			
			if ( isset( $data['vatRateMix'] ) && ! empty( $data['vatCountries'] ) ) {
				$result['vat_mix'] = $this->calculateVATMix( $data['vatCountries'], $result['weighted_average'] );
			}
			
			
			$result['total_amount'] = $this->calculateMixTotal( $result );
			
			
			$result['formatted'] = $this->formatMixResultForDisplay( $result );
			
			return $result;
			
		} catch ( Exception $e ) {
			error_log( 'CalcService Mix Error: ' . $e->getMessage() );
			return new WP_Error( 'mix_calculation_error', 'Mix calculation error: ' . $e->getMessage() );
		}
	}
	
	
	public function validateCalculationData( array $data ) {
		$errors = ValidatorHelper::validateCalculationData( $data );
		
		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', 'Validation errors', $errors );
		}
		
		return true;
	}
	
	
	public function applyVAT( $amount, array $vatConfig ) {
		if ( ! isset( $vatConfig['rate'] ) || $vatConfig['rate'] <= 0 ) {
			return 0;
		}
		
		$rate = floatval( $vatConfig['rate'] );
		return ( $amount * $rate ) / 100;
	}
	
	
	public function calculateAPA( $baseAmount, array $apaConfig ) {
		if ( ! isset( $apaConfig['percentage'] ) || $apaConfig['percentage'] <= 0 ) {
			return 0;
		}
		
		$percentage = floatval( $apaConfig['percentage'] );
		return ( $baseAmount * $percentage ) / 100;
	}
	
	
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
	
	
	private function calculateBaseRate( array $data ) {
		
		
		$baseRate = 0;
		
		if ( isset( $data['charterRate'] ) ) {
			$baseRate = floatval( $data['charterRate'] );
		}
		
		
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
				$baseRate = $totalRate; 
			}
		}
		
		return $baseRate;
	}
	
	
	private function processExtras( array $data ) {
		$extras = array();
		
		
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
	
	
	private function calculateSeasonPeriod( array $season, array $data ) {
		return array(
			'name'  => $season['name'] ?? 'Season',
			'weeks' => floatval( $season['weeks'] ?? 0 ),
			'rate'  => floatval( $season['rate'] ?? 0 ),
			'total' => floatval( $season['weeks'] ?? 0 ) * floatval( $season['rate'] ?? 0 ),
		);
	}
	
	
	private function calculateWeightedAverage( array $seasons ) {
		$totalAmount = 0;
		$totalWeeks  = 0;
		
		foreach ( $seasons as $season ) {
			$totalAmount += $season['total'];
			$totalWeeks  += $season['weeks'];
		}
		
		return $totalWeeks > 0 ? $totalAmount / $totalWeeks : 0;
	}
	
	
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
	
	
	private function calculateMixTotal( array $result ) {
		$total = 0;
		
		foreach ( $result['seasons'] as $season ) {
			$total += $season['total'];
		}
		
		
		foreach ( $result['vat_mix'] as $vat ) {
			$total += $vat['amount'];
		}
		
		return $total;
	}
	
	
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
