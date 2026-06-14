<?php
namespace Immens_MCP_Fortress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Deactivator {

	public static function deactivate() {
		wp_clear_scheduled_hook( 'immens_mcp_fortress_cleanup_audit_log' );
		wp_clear_scheduled_hook( 'immens_mcp_fortress_cleanup_sessions' );
		wp_clear_scheduled_hook( 'immens_mcp_fortress_cleanup_oauth' );
		wp_clear_scheduled_hook( 'immens_mcp_fortress_cleanup_change_log' );
		flush_rewrite_rules();
	}
}
