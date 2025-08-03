<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


require_once __DIR__ . '/bootstrap.php';


$container = AppYachtBootstrap::init();


$legacy_files = array(
	get_template_directory() . '/app_yacht/core/security-headers.php',
	get_template_directory() . '/app_yacht/core/data-validation.php',
	get_template_directory() . '/app_yacht/core/api-request.php',
);

foreach ( $legacy_files as $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

echo '<div class="container">';


require get_template_directory() . '/app_yacht/modules/calc/calculator.php';
require get_template_directory() . '/app_yacht/modules/template/template.php';
require get_template_directory() . '/app_yacht/modules/mail/mail.php';

echo '</div>';


if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	echo '<!-- App Yacht v2.0.0 - Nueva Arquitectura Cargada -->';
	echo '<!-- Servicios registrados: ' . implode( ', ', $container->getRegisteredServices() ) . ' -->';
}

