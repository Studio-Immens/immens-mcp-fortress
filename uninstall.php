<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$tables = array( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	'immens_mcp_access_points',
	'immens_mcp_audit_log',
	'immens_mcp_sessions',
	'immens_mcp_change_log',
	'immens_mcp_oauth_clients',
	'immens_mcp_oauth_codes',
	'immens_mcp_oauth_tokens',
);

foreach ( $tables as $table ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}{$table}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange
}

$options = array( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	'immens_mcp_fortress_rate_limit',
	'immens_mcp_fortress_audit_log_enabled',
	'immens_mcp_fortress_audit_log_retention',
	'immens_mcp_fortress_force_draft_on_create',
	'immens_mcp_fortress_change_log_enabled',
	'immens_mcp_fortress_change_log_retention',
	'immens_mcp_fortress_disabled_tools',
	'immens_mcp_fortress_max_title_length',
	'immens_mcp_fortress_admin_language',
);

foreach ( $options as $option ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	delete_option( $option );
}

wp_cache_flush();
