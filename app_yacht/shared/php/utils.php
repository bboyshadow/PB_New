<?php
/**
 * Funciones de utilidad general para App_Yacht
 *
 * Este archivo contiene funciones de utilidad general que pueden ser utilizadas
 * en diferentes partes de la aplicación App_Yacht.
 *
 * @package App_Yacht
 * @subpackage Shared
 * @since 1.0.0
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verifica si un usuario tiene una capacidad específica y maneja el error apropiadamente.
 *
 * Esta función verifica si el usuario actual tiene una capacidad específica de WordPress.
 * Si no tiene la capacidad, registra el intento no autorizado y devuelve un error apropiado.
 *
 * @param string $capability La capacidad de WordPress a verificar.
 * @param string $error_message Mensaje de error personalizado (opcional).
 * @return bool True si el usuario tiene la capacidad, false en caso contrario.
 */
function pb_verify_user_capability($capability, $error_message = '') {
    if (!current_user_can($capability)) {
        // Registrar intento no autorizado
        $user_id = get_current_user_id();
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $calling_function = isset($backtrace[1]['function']) ? $backtrace[1]['function'] : 'unknown';
        
        // Usar la función de registro de eventos de seguridad
        if (function_exists('pb_log_security_event')) {
            pb_log_security_event($user_id, 'unauthorized_access', [
                'capability' => $capability,
                'action' => $calling_function,
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
        } else {
            // Fallback si la función de registro no está disponible
            error_log(sprintf('Intento de acceso no autorizado: Usuario %d, Capacidad %s, Función %s', 
                $user_id, 
                $capability,
                $calling_function
            ));
        }
        
        // Devolver error apropiado según el contexto
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_error([
                'error' => $error_message ?: 'No tienes permisos para realizar esta acción.'
            ], 403);
            exit;
        } else {
            wp_die(
                $error_message ?: 'No tienes permisos para realizar esta acción.',
                'Acceso Denegado',
                ['response' => 403, 'back_link' => true]
            );
        }
        
        return false;
    }
    
    return true;
}
