<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'wp_ajax_msp_save_signature', 'msp_save_signature_callback' );
function msp_save_signature_callback() {
	check_ajax_referer( 'msp_nonce_action', 'mspNonce' ); 

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'User not logged in', 401 );
	}
	$signature = isset( $_POST['signature'] ) ? urldecode( $_POST['signature'] ) : '';
	// Sanitize HTML content before saving to prevent XSS
	$signature = wp_kses_post( $signature );
	$user_id   = get_current_user_id();
	update_user_meta( $user_id, 'msp_signature', $signature );
	wp_send_json_success( 'Signature saved' );
}

add_action( 'wp_ajax_msp_delete_signature', 'msp_delete_signature_callback' );
function msp_delete_signature_callback() {
	check_ajax_referer( 'msp_nonce_action', 'mspNonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'User not logged in', 401 );
	}
	$user_id = get_current_user_id();
	delete_user_meta( $user_id, 'msp_signature' );
	wp_send_json_success( 'Signature removed' );
}


add_shortcode( 'outlook_signature', 'msp_signature_shortcode' );
function msp_signature_shortcode() {
	if ( ! is_user_logged_in() ) {
		return "<p style='color:red;'>Please log in to manage your signature.</p>";
	}

	
	$user_id   = get_current_user_id();
	$signature = get_user_meta( $user_id, 'msp_signature', true );
	if ( ! $signature ) {
		$signature = '';
	}

	
	msp_enqueue_scripts();

	
	ob_start(); ?>
	<div class="msp-signature-wrapper" style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
		<h3>Signature Editor</h3>
		<div id="mspEditor" contenteditable="true" style="min-height:100px; border:1px solid #ddd; padding:5px;">
			<?php echo wp_kses_post( $signature ); ?>
		</div>
		<div style="margin-top:10px;">
			<button type="button" id="mspBtnSave" class="button button-primary">Save Signature</button>
			<button type="button" id="mspBtnRemove" class="button button-secondary">Remove Signature</button>
		</div>
		<p style="font-size:0.8em;color:#555;">HTML content is allowed. Paste images / code from your clipboard.</p>
	</div>
	<?php
	return ob_get_clean();
}


function msp_enqueue_scripts() {
	
	static $done = false;
	if ( $done ) {
		return; 
	}
	$done = true;

	
	wp_enqueue_script(
		'msp-signature-js',
		get_template_directory_uri() . '/app_yacht/modules/mail/signature/msp-signature.js',
		array( 'jquery' ),
		'1.0',
		true
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


add_action( 'wp_enqueue_scripts', 'msp_add_styles' );
function msp_add_styles() {
	wp_register_style( 'msp-styles', get_template_directory_uri() . '/app_yacht/modules/mail/signature/msp-styles.css', array(), '1.0', 'all' );
	wp_enqueue_style( 'msp-styles' );
}
