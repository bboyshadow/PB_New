<?php
/**
 * ARCHIVO shared/php/validation.php
 * Implementa funciones centralizadas de validación para la aplicación App_Yacht
 */

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

/**
 * Valida si un valor es un correo electrónico válido
 * 
 * @param string $email El correo electrónico a validar
 * @return bool True si es un correo válido, false en caso contrario
 */
function pb_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida si un valor es una URL válida
 * 
 * @param string $url La URL a validar
 * @return bool True si es una URL válida, false en caso contrario
 */
function pb_validate_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Valida si un valor es un número entero válido
 * 
 * @param mixed $value El valor a validar
 * @param array $options Opciones adicionales (min, max)
 * @return bool True si es un entero válido, false en caso contrario
 */
function pb_validate_int($value, $options = []) {
    if (!is_numeric($value) || intval($value) != $value) {
        return false;
    }
    
    $value = intval($value);
    
    // Validar rango si se especifica
    if (isset($options['min']) && $value < $options['min']) {
        return false;
    }
    
    if (isset($options['max']) && $value > $options['max']) {
        return false;
    }
    
    return true;
}

/**
 * Valida si un valor es un número decimal válido
 * 
 * @param mixed $value El valor a validar
 * @param array $options Opciones adicionales (min, max, decimals)
 * @return bool True si es un decimal válido, false en caso contrario
 */
function pb_validate_float($value, $options = []) {
    if (!is_numeric($value)) {
        return false;
    }
    
    $value = floatval($value);
    
    // Validar rango si se especifica
    if (isset($options['min']) && $value < $options['min']) {
        return false;
    }
    
    if (isset($options['max']) && $value > $options['max']) {
        return false;
    }
    
    // Validar número de decimales si se especifica
    if (isset($options['decimals'])) {
        $parts = explode('.', (string)$value);
        if (isset($parts[1]) && strlen($parts[1]) > $options['decimals']) {
            return false;
        }
    }
    
    return true;
}

/**
 * Valida si un valor está dentro de un conjunto de valores permitidos
 * 
 * @param mixed $value El valor a validar
 * @param array $allowed_values Array de valores permitidos
 * @return bool True si el valor está permitido, false en caso contrario
 */
function pb_validate_in_array($value, $allowed_values) {
    return in_array($value, $allowed_values, true);
}

/**
 * Valida si un valor tiene una longitud dentro del rango especificado
 * 
 * @param string $value El valor a validar
 * @param array $options Opciones (min_length, max_length)
 * @return bool True si la longitud es válida, false en caso contrario
 */
function pb_validate_length($value, $options = []) {
    $length = strlen($value);
    
    if (isset($options['min_length']) && $length < $options['min_length']) {
        return false;
    }
    
    if (isset($options['max_length']) && $length > $options['max_length']) {
        return false;
    }
    
    return true;
}

/**
 * Valida si un valor coincide con un patrón de expresión regular
 * 
 * @param string $value El valor a validar
 * @param string $pattern El patrón de expresión regular
 * @return bool True si coincide con el patrón, false en caso contrario
 */
function pb_validate_regex($value, $pattern) {
    return preg_match($pattern, $value) === 1;
}

/**
 * Valida si un valor es una fecha válida en el formato especificado
 * 
 * @param string $date La fecha a validar
 * @param string $format El formato de fecha (por defecto Y-m-d)
 * @return bool True si es una fecha válida, false en caso contrario
 */
function pb_validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Valida un conjunto de datos contra un esquema de validación
 * 
 * @param array $data Los datos a validar
 * @param array $schema El esquema de validación
 * @return array Array con errores encontrados (vacío si todo es válido)
 */
function pb_validate_data($data, $schema) {
    $errors = [];
    
    foreach ($schema as $field => $rules) {
        // Verificar si el campo es requerido
        if (isset($rules['required']) && $rules['required'] && (!isset($data[$field]) || $data[$field] === '')) {
            $errors[$field] = isset($rules['error_messages']['required']) 
                ? $rules['error_messages']['required'] 
                : "El campo {$field} es obligatorio.";
            continue;
        }
        
        // Si el campo no está presente y no es requerido, continuar
        if (!isset($data[$field])) {
            continue;
        }
        
        // Validar según el tipo
        if (isset($rules['type'])) {
            $value = $data[$field];
            $valid = true;
            
            switch ($rules['type']) {
                case 'email':
                    $valid = pb_validate_email($value);
                    break;
                case 'url':
                    $valid = pb_validate_url($value);
                    break;
                case 'int':
                    $options = isset($rules['options']) ? $rules['options'] : [];
                    $valid = pb_validate_int($value, $options);
                    break;
                case 'float':
                    $options = isset($rules['options']) ? $rules['options'] : [];
                    $valid = pb_validate_float($value, $options);
                    break;
                case 'in':
                    $valid = pb_validate_in_array($value, $rules['allowed_values']);
                    break;
                case 'length':
                    $options = isset($rules['options']) ? $rules['options'] : [];
                    $valid = pb_validate_length($value, $options);
                    break;
                case 'regex':
                    $valid = pb_validate_regex($value, $rules['pattern']);
                    break;
                case 'date':
                    $format = isset($rules['format']) ? $rules['format'] : 'Y-m-d';
                    $valid = pb_validate_date($value, $format);
                    break;
            }
            
            if (!$valid) {
                $error_key = 'invalid_' . $rules['type'];
                $errors[$field] = isset($rules['error_messages'][$error_key]) 
                    ? $rules['error_messages'][$error_key] 
                    : "El campo {$field} no es válido.";
            }
        }
        
        // Validaciones personalizadas
        if (isset($rules['custom_validator']) && is_callable($rules['custom_validator'])) {
            $result = call_user_func($rules['custom_validator'], $data[$field], $data);
            if ($result !== true) {
                $errors[$field] = $result;
            }
        }
    }
    
    return $errors;
}