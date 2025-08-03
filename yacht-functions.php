<?php
// ==================== ARCHIVO yacht-functions.php ====================\\

// --------------------- JS AND CSS START ---------------------\\

function app_yacht_scripts() {
	// Verificar si estamos en el template correcto
	if ( is_page_template( 'app-yacht.php' ) ) {
		// Array para dependencias comunes
		$dependencies = array();

		// Registrar y encolar ini.js
		wp_enqueue_script(
			'ini-script',
			get_template_directory_uri() . '/app_yacht/shared/js/ini.js',
			array(),
			'1.0.0',
			true
		);
		$dependencies[] = 'ini-script';

		// Registrar y encolar interfaz.js
		wp_enqueue_script(
			'interfaz-script',
			get_template_directory_uri() . '/app_yacht/modules/calc/js/interfaz.js',
			$dependencies,
			'1.0.0',
			true
		);
		$dependencies[] = 'interfaz-script';

		// Registrar y encolar validate.js
		wp_enqueue_script(
			'validate-script',
			get_template_directory_uri() . '/app_yacht/shared/js/validate.js',
			$dependencies,
			'1.0.0',
			true
		);
		$dependencies[] = 'validate-script';

		// Registrar y encolar mix.js
		wp_enqueue_script(
			'mix-script',
			get_template_directory_uri() . '/app_yacht/modules/calc/js/mix.js',
			$dependencies,
			'1.0.0',
			true
		);
		$dependencies[] = 'mix-script';

		// Registrar y encolar calculate.js
		wp_enqueue_script(
			'calculator-script',
			get_template_directory_uri() . '/app_yacht/modules/calc/js/calculate.js',
			$dependencies,
			'1.0.0',
			true
		);
		// Localizar datos para mix.js
		wp_localize_script(
			'mix-script',
			'ajaxData',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mix_calculate_nonce' ), // Nonce para handle_calculate_mix
			)
		);
		// Localizar datos para calculate.js
		wp_localize_script(
			'calculator-script',
			'ajaxCalculatorData',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'calculate_nonce' ), // Nonce para seguridad
			)
		);

		// *********** Se elimina calculate-template.js (ya no se usa) ***********

		// Generador de templates (Módulo Template principal)
		wp_enqueue_script(
			'template-script',
			get_template_directory_uri() . '/app_yacht/modules/template/js/template.js',
			$dependencies,
			'1.0.0',
			true
		);

		// Localizar datos para template.js
		wp_localize_script(
			'template-script',
			'ajaxTemplateData',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'template_nonce' ), // Nonce para seguridad
			)
		);

		// ***************** Módulo de correo (mail.js) ***************** //
		wp_enqueue_script(
			'mail-script',
			get_template_directory_uri() . '/app_yacht/modules/mail/mail.js',
			$dependencies,
			'1.0.0',
			true
		);
		// Localizar datos para mail.js si fuera necesario
		wp_localize_script(
			'mail-script',
			'ajaxMailData',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'send_mail_nonce' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'app_yacht_scripts' );

function app_yacht_css() {
	// Verificar si estamos en el template correcto
	if ( is_page_template( 'app-yacht.php' ) ) {
		wp_enqueue_style(
			'app-yacht-styles',
			get_template_directory_uri() . '/app_yacht/shared/css/app_yacht.css',
			array(),
			'1.0.0'
		);

		// CSS de Mail
		wp_enqueue_style(
			'mail-styles',
			get_template_directory_uri() . '/app_yacht/modules/mail/mail.css',
			array(),
			'1.0.0'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'app_yacht_css' );

// --------------------- JS AND CSS END ---------------------\\


// --------------------- CARGA DINAMICA DE ARCHIVOS START ---------------------\\

// Cargar archivos solo si se usa la plantilla o en AJAX
if ( is_page_template( 'app-yacht.php' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	// Calculadoras
	require_once get_template_directory() . '/app_yacht/modules/calc/php/calculatemix.php';
	require_once get_template_directory() . '/app_yacht/modules/calc/php/calculate.php';

	// Templates
	require_once get_template_directory() . '/app_yacht/modules/template/php/load-template.php';
	require_once get_template_directory() . '/app_yacht/modules/template/php/template-data.php';

	// *********** Incluir el módulo de correo *********** 
	require_once get_template_directory() . '/app_yacht/modules/mail/mail.php'; // esto me daña la app corrigeloooo
}

// Registrar las acciones AJAX globalmente
// ARCHIVO: /app_yacht/modules/calc/php/calculate.php
add_action( 'wp_ajax_calculate_charter', 'handle_calculate_charter' );
add_action( 'wp_ajax_nopriv_calculate_charter', 'handle_calculate_charter' );

// ARCHIVO calculatemix.php
add_action( 'wp_ajax_calculate_mix', 'handle_calculate_mix' );
add_action( 'wp_ajax_nopriv_calculate_mix', 'handle_calculate_mix' );

// ARCHIVO load-template.php
add_action( 'wp_ajax_load_template_preview', 'handle_load_template_preview' );
add_action( 'wp_ajax_nopriv_load_template_preview', 'handle_load_template_preview' );
add_action( 'wp_ajax_createTemplate', 'handle_create_template' );
add_action( 'wp_ajax_nopriv_createTemplate', 'handle_create_template' );

// --------------------- CARGA DINAMICA DE ARCHIVOS END ---------------------\\


// --------------------- REGISTROS DE EMAIL ---------------------\\
add_filter(
	'wp_mail',
	function( $args ) {
		error_log( 'Correo enviado:' );
		error_log( 'Para: ' . implode( ', ', (array) $args['to'] ) );
		error_log( 'Asunto: ' . $args['subject'] );
		error_log( 'Contenido: ' . $args['message'] );
		error_log( 'Headers: ' . implode( ', ', (array) $args['headers'] ) );
		return $args;
	}
);


