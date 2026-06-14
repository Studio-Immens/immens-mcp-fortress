<?php
namespace Immens_MCP_Fortress\MCP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JSON_RPC {

	public static function parse_request( $body ) {
		if ( empty( $body ) ) {
			return new \WP_Error( 'empty_body', 'Request body is empty' );
		}

		$data = json_decode( $body, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return new \WP_Error( 'parse_error', 'Invalid JSON: ' . json_last_error_msg() );
		}

		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'invalid_request', 'Request must be a JSON object or array' );
		}

		if ( isset( $data[0] ) ) {
			return $data;
		}

		if ( ! isset( $data['jsonrpc'] ) || '2.0' !== $data['jsonrpc'] ) {
			return new \WP_Error( 'invalid_request', 'jsonrpc field must be "2.0"' );
		}

		return $data;
	}

	public static function is_notification( $message ) {
		return is_array( $message ) && ! isset( $message['id'] );
	}

	public static function success_response( $id, $result ) {
		$response = array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => $result,
		);

		if ( null === $id ) {
			return $response;
		}

		return $response;
	}

	public static function error_response( $id, $code, $message = null, $data = null ) {
		$error = array(
			'code'    => $code,
			'message' => $message ?? Error_Codes::get_message( $code ),
		);

		if ( null !== $data ) {
			$error['data'] = $data;
		}

		$response = array(
			'jsonrpc' => '2.0',
			'id'      => $id,
			'error'   => $error,
		);

		return $response;
	}
}
