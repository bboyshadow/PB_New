<?php



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function pb_verify_user_capability( $capability, $error_message = '' ) {
	if ( ! current_user_can( $capability ) ) {
		
		$user_id          = get_current_user_id();
		$backtrace        = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 );
		$calling_function = isset( $backtrace[1]['function'] ) ? $backtrace[1]['function'] : 'unknown';

		
		if ( function_exists( 'pb_log_security_event' ) ) {
			pb_log_security_event(
				$user_id,
				'unauthorized_access',
				array(
					'capability'  => $capability,
					'action'      => $calling_function,
					'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
				)
			);
		} else {
			
			error_log(
				sprintf(
					'Unauthorized access attempt: User %d, Capability %s, Function %s',
					$user_id,
					$capability,
					$calling_function
				)
			);
		}

		
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json_error(
				array(
					'error' => $error_message ?: 'You do not have permission to perform this action.',
				),
				403
			);
			exit;
		} else {
			wp_die(
				$error_message ?: 'You do not have permission to perform this action.',
				'Access Denied',
				array(
					'response'  => 403,
					'back_link' => true,
				)
			);
		}

		return false;
	}

	return true;
}
