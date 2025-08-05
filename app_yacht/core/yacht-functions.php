<?php


require_once get_template_directory() . '/app_yacht/shared/php/utils.php';
require_once get_template_directory() . '/app_yacht/shared/php/security.php';


function app_yacht_scripts() {
	if ( is_page_template( 'app-yacht.php' ) ) {
		$dependencies = array( 'jquery' );

		wp_enqueue_script( 'ini-script', get_template_directory_uri() . '/app_yacht/shared/js/ini.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'ini-script';

		wp_enqueue_script( 'currency-script', get_template_directory_uri() . '/app_yacht/shared/js/currency.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'currency-script';

		wp_enqueue_script( 'ui-script', get_template_directory_uri() . '/app_yacht/shared/js/ui.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'ui-script';

		wp_enqueue_script( 'validate-script', get_template_directory_uri() . '/app_yacht/shared/js/validate.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'validate-script';

		wp_enqueue_script( 'storage-script', get_template_directory_uri() . '/app_yacht/shared/js/storage.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'storage-script';

		wp_enqueue_script( 'debounce-script', get_template_directory_uri() . '/app_yacht/shared/js/utils/debounce.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'debounce-script';

		wp_enqueue_script( 'dom-script', get_template_directory_uri() . '/app_yacht/shared/js/utils/dom.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'dom-script';

		wp_enqueue_script( 'resources-script', get_template_directory_uri() . '/app_yacht/shared/js/resources.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'resources-script';

		wp_enqueue_script( 'template-manager-class', get_template_directory_uri() . '/app_yacht/shared/js/classes/TemplateManager.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'template-manager-class';

		wp_enqueue_script( 'mail-composer-class', get_template_directory_uri() . '/app_yacht/shared/js/classes/MailComposer.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'mail-composer-class';
		
		wp_enqueue_script( 'calculator-class', get_template_directory_uri() . '/app_yacht/shared/js/classes/Calculator.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'calculator-class';

		wp_enqueue_script( 'interfaz-script', get_template_directory_uri() . '/app_yacht/modules/calc/js/interfaz.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'interfaz-script';

		wp_enqueue_script( 'mix-script', get_template_directory_uri() . '/app_yacht/modules/calc/js/mix.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'mix-script';

		wp_enqueue_script( 'calculator-script', get_template_directory_uri() . '/app_yacht/modules/calc/js/calculate.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'calculator-script';

		wp_enqueue_script( 'extra-per-person-script', get_template_directory_uri() . '/app_yacht/modules/calc/js/extraPerPerson.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'extra-per-person-script';

		wp_localize_script(
			'mix-script',
			'ajaxData',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'mix_calculate_nonce' ),
			) 
		);
		wp_localize_script(
			'calculator-script',
			'ajaxCalculatorData',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'calculate_nonce' ),
			) 
		);

		wp_enqueue_script( 'template-script', get_template_directory_uri() . '/app_yacht/modules/template/js/template.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'template-script';

		wp_localize_script(
			'template-script',
			'ajaxTemplateData',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'template_nonce' ),
			) 
		);

		wp_enqueue_script( 'yacht-mail-hidden-fields', get_template_directory_uri() . '/app_yacht/modules/mail/mail-hidden-fields.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'yacht-mail-hidden-fields';

		wp_enqueue_script( 'yacht-mail-script', get_template_directory_uri() . '/app_yacht/modules/mail/mail.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'yacht-mail-script';

		wp_enqueue_script( 'yachtinfo-script', get_template_directory_uri() . '/app_yacht/modules/yachtinfo/js/yachtinfo.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'yachtinfo-script';

		wp_localize_script(
			'yachtinfo-script',
			'yachtinfo_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'yachtinfo_nonce' ),
			) 
		);

		wp_enqueue_script( 'yacht-outlook-ajax', get_template_directory_uri() . '/app_yacht/modules/mail/outlook/outlook-ajax.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'yacht-outlook-ajax';

		wp_enqueue_script( 'msp-signature-js', get_template_directory_uri() . '/app_yacht/modules/mail/signature/msp-signature.js', $dependencies, '1.0.0', true );
		$dependencies[] = 'msp-signature-js';

		$outlook_nonce = wp_create_nonce( 'pb_outlook_nonce' );
		wp_localize_script(
			'yacht-outlook-ajax',
			'pbOutlookData',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => $outlook_nonce,
				'timestamp' => time(),
			) 
		);
		wp_localize_script(
			'msp-signature-js',
			'mspData',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'mspNonce' => wp_create_nonce( 'msp_nonce_action' ),
			) 
		);
	}
}
add_action( 'wp_enqueue_scripts', 'app_yacht_scripts' );


function app_yacht_css() {
	if ( is_page_template( 'app-yacht.php' ) ) {
		wp_enqueue_style( 'app-yacht-styles', get_template_directory_uri() . '/app_yacht/shared/css/app_yacht.css', array(), '1.0.0' );
		wp_enqueue_style( 'yacht-mail-styles', get_template_directory_uri() . '/app_yacht/modules/mail/mail.css', array(), '1.0.0' );
		wp_enqueue_style( 'msp-styles', get_template_directory_uri() . '/app_yacht/modules/mail/signature/msp-styles.css', array(), '1.0.0' );
		wp_enqueue_style( 'yacht-info-styles', get_template_directory_uri() . '/app_yacht/modules/yachtinfo/css/yachtinfo.css', array(), '1.0.0' );
	}
}
add_action( 'wp_enqueue_scripts', 'app_yacht_css' );


$load_appyacht_php = false;
if ( is_page_template( 'app-yacht.php' ) ) {
	$load_appyacht_php = true;
} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	$load_appyacht_php = true;
} elseif ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/appyacht/auth' ) !== false ) {
	$load_appyacht_php = true;
}

if ( $load_appyacht_php ) {
	require_once get_template_directory() . '/app_yacht/modules/calc/php/calculatemix.php';
	require_once get_template_directory() . '/app_yacht/modules/calc/php/calculate.php';
	require_once get_template_directory() . '/app_yacht/modules/template/php/load-template.php';
	require_once get_template_directory() . '/app_yacht/modules/template/php/template-data.php';
	require_once get_template_directory() . '/app_yacht/modules/mail/signature/signature-functions.php';
}

require_once get_template_directory() . '/app_yacht/modules/mail/outlook/outlook-loader.php';


add_action( 'wp_ajax_calculate_charter', 'handle_calculate_charter' );
add_action( 'wp_ajax_nopriv_calculate_charter', 'handle_calculate_charter' );
add_action( 'wp_ajax_calculate_mix', 'handle_calculate_mix' );
add_action( 'wp_ajax_nopriv_calculate_mix', 'handle_calculate_mix' );
add_action( 'wp_ajax_load_template_preview', 'handle_load_template_preview' );
add_action( 'wp_ajax_nopriv_load_template_preview', 'handle_load_template_preview' );
// add_action( 'wp_ajax_createTemplate', 'handle_create_template' );
// add_action( 'wp_ajax_nopriv_createTemplate', 'handle_create_template' );
add_action( 'wp_ajax_pb_outlook_send_mail', 'pb_outlook_send_mail_ajax_handler' );
add_action( 'wp_ajax_nopriv_pb_outlook_send_mail', 'pb_outlook_send_mail_ajax_handler' );
add_action( 'wp_ajax_pb_outlook_disconnect', 'pb_outlook_disconnect_ajax_handler' );
add_action( 'wp_ajax_nopriv_pb_outlook_disconnect', 'pb_outlook_disconnect_ajax_handler' );
add_action( 'wp_ajax_msp_save_signature', 'msp_save_signature_callback' );
add_action( 'wp_ajax_msp_delete_signature', 'msp_delete_signature_callback' );


function pb_add_auth_rewrite_endpoint() {
	add_rewrite_endpoint( 'auth', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'pb_add_auth_rewrite_endpoint' );


function pb_handle_auth_endpoint() {
	global $wp_query;

	if ( isset( $wp_query->query_vars['auth'] ) ) {
		if ( isset( $_GET['code'] ) ) {
			try {
				if ( ! function_exists( 'pb_log_security_event' ) || ! function_exists( 'pb_outlook_exchange_code_for_tokens' ) || ! function_exists( 'pb_outlook_save_tokens_to_user' ) ) {
					error_log( 'Error crítico: Funciones de Outlook o seguridad no encontradas durante el callback de Outlook en template_redirect.' );
					wp_die( 'Error interno: Faltan funciones esenciales. Contacta al administrador.' );
				}

				if ( ! is_user_logged_in() ) {
					wp_die( 'Debes iniciar sesión en WP antes de conectar tu cuenta de Outlook.' );
				}

				$user_id = get_current_user_id();
				$code    = sanitize_text_field( $_GET['code'] );
				$tokens  = pb_outlook_exchange_code_for_tokens( $code );
				if ( is_wp_error( $tokens ) ) {
					error_log( 'OAuth Token Exchange Error for user ' . $user_id . ': ' . $tokens->get_error_message() );
					wp_die( 'Error al obtener tokens Outlook: ' . esc_html( $tokens->get_error_message() ) );
				}

				pb_outlook_save_tokens_to_user( $user_id, $tokens );
				wp_redirect( home_url( '/appyacht/?outlook=success' ) );
				exit;
			} catch ( Throwable $e ) {
				error_log( 'Critical Error during Outlook auth endpoint handling: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString() );
				wp_die( 'Se produjo un error inesperado durante la autenticación. Por favor, contacta al administrador. Detalles: ' . esc_html( $e->getMessage() ) );
			}
		} else {
			wp_redirect( home_url( '/appyacht/' ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'pb_handle_auth_endpoint' );


function pb_register_yacht_roles_capabilities() {
	$admin_caps = array(
		'edit_yacht_templates',
		'send_yacht_emails',
	);
	
	$editor_caps = array(
		'edit_yacht_templates',
		'send_yacht_emails',
	);
	
	$author_caps = array(
		'edit_yacht_templates',
		'send_yacht_emails',
	);
	
	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( $admin_caps as $cap ) {
			$admin->add_cap( $cap );
		}
	}
	
	$editor = get_role( 'editor' );
	if ( $editor ) {
		foreach ( $editor_caps as $cap ) {
			$editor->add_cap( $cap );
		}
	}
	
	$author = get_role( 'author' );
	if ( $author ) {
		foreach ( $author_caps as $cap ) {
			$author->add_cap( $cap );
		}
	}
}

add_action( 'after_switch_theme', 'pb_register_yacht_roles_capabilities' );
pb_register_yacht_roles_capabilities();

