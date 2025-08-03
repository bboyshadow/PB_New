
<?php
/**
 * Helper para validación de datos
 * Funciones auxiliares para validación en App_Yacht
 * 
 * @package AppYacht\Helpers
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase helper para validación
 */
class ValidatorHelper {
    
    /**
     * Valida una URL
     * 
     * @param string $url URL a validar
     * @return bool
     */
    public static function isValidUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Valida un email
     * 
     * @param string $email Email a validar
     * @return bool
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valida un número decimal
     * 
     * @param mixed $value Valor a validar
     * @return bool
     */
    public static function isValidDecimal($value) {
        return is_numeric($value) && $value >= 0;
    }
    
    /**
     * Valida un porcentaje (0-100)
     * 
     * @param mixed $value Valor a validar
     * @return bool
     */
    public static function isValidPercentage($value) {
        return is_numeric($value) && $value >= 0 && $value <= 100;
    }
    
    /**
     * Valida una moneda soportada
     * 
     * @param string $currency Moneda a validar
     * @return bool
     */
    public static function isValidCurrency($currency) {
        $config = AppYachtConfig::get('calculation');
        return in_array($currency, $config['supported_currencies']);
    }
    
    /**
     * Valida datos requeridos en array
     * 
     * @param array $data Datos a validar
     * @param array $required Campos requeridos
     * @return array Array de errores (vacío si todo es válido)
     */
    public static function validateRequired(array $data, array $required) {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "Campo requerido faltante: {$field}";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitiza datos de entrada
     * 
     * @param array $data Datos a sanitizar
     * @return array Datos sanitizados
     */
    public static function sanitizeInputData(array $data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeInputData($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                $sanitized[$key] = is_float($value + 0) ? floatval($value) : intval($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Valida estructura de datos de cálculo
     * 
     * @param array $data Datos a validar
     * @return array Errores encontrados
     */
    public static function validateCalculationData(array $data) {
        $errors = [];
        
        // Validar campos requeridos
        $required = ['currency'];
        $errors = array_merge($errors, self::validateRequired($data, $required));
        
        // Validar moneda
        if (isset($data['currency']) && !self::isValidCurrency($data['currency'])) {
            $errors[] = 'Moneda no soportada: ' . $data['currency'];
        }
        
        // Validar rates numéricos
        $numericFields = ['vatRate', 'apaAmount', 'apaPercentage', 'relocationFee', 'securityFee'];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && !empty($data[$field]) && !self::isValidDecimal($data[$field])) {
                $errors[] = "Valor numérico inválido para {$field}: " . $data[$field];
            }
        }
        
        return $errors;
    }
    
    /**
     * Valida datos de email
     * 
     * @param array $data Datos a validar
     * @return array Errores encontrados
     */
    public static function validateEmailData(array $data) {
        $errors = [];
        
        // Validar campos requeridos
        $required = ['to', 'subject', 'message'];
        $errors = array_merge($errors, self::validateRequired($data, $required));
        
        // Validar emails destinatarios
        if (isset($data['to'])) {
            $emails = explode(',', $data['to']);
            foreach ($emails as $email) {
                $email = trim($email);
                if (!empty($email) && !self::isValidEmail($email)) {
                    $errors[] = "Email inválido: {$email}";
                }
            }
        }
        
        return $errors;
    }
}
