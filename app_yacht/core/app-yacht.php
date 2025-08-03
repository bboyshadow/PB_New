<?php
/**
 * 
 * ARCHIVO core/app-yacht.php
 * Punto de entrada principal para la aplicación App_Yacht
 * REFACTORIZADO - Versión 2.0.0 con nueva arquitectura
 * 
 * @package AppYacht
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Cargar nueva arquitectura
require_once __DIR__ . '/bootstrap.php';

// Inicializar la aplicación
$container = AppYachtBootstrap::init();

// Cargar archivos de compatibilidad si existen
$legacy_files = [
    get_template_directory() . '/app_yacht/core/security-headers.php',
    get_template_directory() . '/app_yacht/core/data-validation.php',
    get_template_directory() . '/app_yacht/core/api-request.php'
];

foreach ($legacy_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

echo '<div class="container">';

// Incluir las vistas de los módulos (UI mantiene compatibilidad)
include get_template_directory() . '/app_yacht/modules/calc/calculator.php';
include get_template_directory() . '/app_yacht/modules/template/template.php';
include get_template_directory() . '/app_yacht/modules/mail/mail.php';

echo '</div>';

// Agregar información de debug en modo desarrollo
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- App Yacht v2.0.0 - Nueva Arquitectura Cargada -->';
    echo '<!-- Servicios registrados: ' . implode(', ', $container->getRegisteredServices()) . ' -->';
}
?>