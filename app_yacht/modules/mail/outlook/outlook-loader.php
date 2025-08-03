<?php
/**
 * outlook-loader.php
 * Archivo para asegurar que todas las funciones de Outlook estén cargadas correctamente
 */
if (!defined('ABSPATH')) {
    exit;
}

// Cargar funciones principales de Outlook si no están cargadas
if (!function_exists('pb_outlook_get_login_url')) {
    require_once get_template_directory() . '/app_yacht/modules/mail/outlook/outlook-functions.php';
}

// El archivo outlook-disconnect.php ha sido eliminado ya que su contenido fue movido a outlook-functions.php
