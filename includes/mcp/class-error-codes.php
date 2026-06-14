<?php
namespace Immens_MCP_Fortress\MCP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Error_Codes {
	const PARSE_ERROR      = -32700;
	const INVALID_REQUEST  = -32600;
	const METHOD_NOT_FOUND = -32601;
	const INVALID_PARAMS   = -32602;
	const INTERNAL_ERROR   = -32603;
	const UNAUTHORIZED     = -32001;
	const FORBIDDEN        = -32002;
	const RESOURCE_NOT_FOUND = -32003;
	const RATE_LIMITED     = -32004;

	public static function get_message( $code ) {
		switch ( $code ) {
			case self::PARSE_ERROR:
				return 'Parse error';
			case self::INVALID_REQUEST:
				return 'Invalid request';
			case self::METHOD_NOT_FOUND:
				return 'Method not found';
			case self::INVALID_PARAMS:
				return 'Invalid params';
			case self::INTERNAL_ERROR:
				return 'Internal error';
			case self::UNAUTHORIZED:
				return 'Unauthorized';
			case self::FORBIDDEN:
				return 'Forbidden';
			case self::RESOURCE_NOT_FOUND:
				return 'Resource not found';
			case self::RATE_LIMITED:
				return 'Rate limited';
			default:
				return 'Unknown error';
		}
	}
}
