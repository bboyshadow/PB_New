<?php




if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


function pb_make_api_request( $url, $args = array() ) {
	if ( ! isset( $args['timeout'] ) ) {
		$args['timeout'] = 15;
	}
	
	$args['sslverify'] = true;
	
	$response = wp_remote_request( $url, $args );
	
	if ( is_wp_error( $response ) ) {
		return array(
			'success' => false,
			'error'   => 'connection_error',
			'message' => $response->get_error_message(),
			'code'    => $response->get_error_code(),
		);
	}
	
	$status_code = wp_remote_retrieve_response_code( $response );
	if ( $status_code < 200 || $status_code >= 300 ) {
		return array(
			'success' => false,
			'error'   => 'api_error',
			'message' => 'API error: Code ' . $status_code,
			'code'    => $status_code,
			'body'    => wp_remote_retrieve_body( $response ),
		);
	}
	
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	
	if ( json_last_error() !== JSON_ERROR_NONE && strpos( $args['headers']['Accept'] ?? '', 'application/json' ) !== false ) {
		return array(
			'success'  => false,
			'error'    => 'json_parse_error',
			'message'  => 'Error parsing response: ' . json_last_error_msg(),
			'raw_body' => $body,
		);
	}
	
	return array(
		'success' => true,
		'data'    => $data ?: $body,
		'headers' => wp_remote_retrieve_headers( $response ),
		'status'  => $status_code,
	);
}


function pb_get_request_timeout( $request_type = 'default' ) {
	$timeouts = array(
		'default'      => 15,
		'outlook_api'  => 30,
		'file_upload'  => 60,
		'long_process' => 120,
	);
	
	return $timeouts[ $request_type ] ?? $timeouts['default'];
}

