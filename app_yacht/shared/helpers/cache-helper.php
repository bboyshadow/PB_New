
<?php
/**
 * Helper para gestión de caché
 * Funciones auxiliares para manejo de caché en App_Yacht
 * 
 * @package AppYacht\Helpers
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase helper para gestión de caché
 */
class CacheHelper {
    
    /**
     * Prefijo para claves de caché
     */
    const CACHE_PREFIX = 'app_yacht_';
    
    /**
     * Obtiene un valor de la caché
     * 
     * @param string $key Clave
     * @return mixed|false Valor o false si no existe
     */
    public static function get($key) {
        $full_key = self::CACHE_PREFIX . $key;
        return wp_cache_get($full_key, 'app_yacht');
    }
    
    /**
     * Guarda un valor en la caché
     * 
     * @param string $key Clave
     * @param mixed $value Valor
     * @param int $expiration Tiempo de expiración en segundos
     * @return bool
     */
    public static function set($key, $value, $expiration = 3600) {
        $full_key = self::CACHE_PREFIX . $key;
        return wp_cache_set($full_key, $value, 'app_yacht', $expiration);
    }
    
    /**
     * Elimina un valor de la caché
     * 
     * @param string $key Clave
     * @return bool
     */
    public static function delete($key) {
        $full_key = self::CACHE_PREFIX . $key;
        return wp_cache_delete($full_key, 'app_yacht');
    }
    
    /**
     * Elimina múltiples valores de la caché
     * 
     * @param array $keys Array de claves
     * @return bool
     */
    public static function deleteMultiple(array $keys) {
        $success = true;
        foreach ($keys as $key) {
            if (!self::delete($key)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Limpia toda la caché de App_Yacht
     * 
     * @return bool
     */
    public static function flush() {
        return wp_cache_flush_group('app_yacht');
    }
    
    /**
     * Genera una clave de caché para URL
     * 
     * @param string $url URL
     * @return string Clave de caché
     */
    public static function generateUrlKey($url) {
        return 'url_' . md5($url);
    }
    
    /**
     * Genera una clave de caché para cálculo
     * 
     * @param array $data Datos del cálculo
     * @return string Clave de caché
     */
    public static function generateCalcKey(array $data) {
        return 'calc_' . md5(json_encode($data));
    }
    
    /**
     * Genera una clave de caché para template
     * 
     * @param string $template Nombre del template
     * @param array $data Datos del template
     * @return string Clave de caché
     */
    public static function generateTemplateKey($template, array $data = []) {
        $key = 'template_' . $template;
        if (!empty($data)) {
            $key .= '_' . md5(json_encode($data));
        }
        return $key;
    }
}
