<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scope_Map {

	const MCP_READ  = 'mcp:read';
	const MCP_WRITE = 'mcp:write';

	public static function get_all() {
		return array(
			self::MCP_READ  => __( 'Read access to content', 'immens-mcp-fortress' ),
			self::MCP_WRITE => __( 'Write access to content', 'immens-mcp-fortress' ),
		);
	}

	public static function default_scopes() {
		return array( self::MCP_READ, self::MCP_WRITE );
	}
}
