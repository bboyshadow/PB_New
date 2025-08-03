
<?php
/**
 * Interface para el servicio de cálculos
 * Define el contrato para operaciones de cálculo de charter
 * 
 * @package AppYacht\Interfaces
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface para servicios de cálculo
 */
interface CalcServiceInterface {
    
    /**
     * Calcula el charter rate estándar
     * 
     * @param array $data Datos del formulario
     * @return array Resultado del cálculo
     */
    public function calculateCharter(array $data);
    
    /**
     * Calcula charter con temporadas mixtas
     * 
     * @param array $data Datos del formulario
     * @return array Resultado del cálculo mix
     */
    public function calculateMix(array $data);
    
    /**
     * Valida los datos de entrada para cálculos
     * 
     * @param array $data Datos a validar
     * @return bool|WP_Error True si válido, WP_Error si no
     */
    public function validateCalculationData(array $data);
    
    /**
     * Aplica impuestos VAT según configuración
     * 
     * @param float $amount Cantidad base
     * @param array $vatConfig Configuración de VAT
     * @return float Cantidad con VAT aplicado
     */
    public function applyVAT($amount, array $vatConfig);
    
    /**
     * Calcula el APA (Advance Provisioning Allowance)
     * 
     * @param float $baseAmount Cantidad base
     * @param array $apaConfig Configuración de APA
     * @return float Cantidad de APA
     */
    public function calculateAPA($baseAmount, array $apaConfig);
    
    /**
     * Formatea una cantidad según la moneda
     * 
     * @param float $amount Cantidad a formatear
     * @param string $currency Moneda
     * @return string Cantidad formateada
     */
    public function formatCurrency($amount, $currency);
}
