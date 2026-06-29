<?php
namespace Immens_MCP_Fortress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activator {

	public static function activate() {
		self::create_access_points_table();
		self::create_audit_log_table();
		self::create_sessions_table();
		self::create_change_log_table();
		self::create_oauth_tables();
		self::set_default_options();
		self::schedule_cron_jobs();
		flush_rewrite_rules();
	}

	private static function create_access_points_table() {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_access_points';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$table}` (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255) NOT NULL,
			api_key_hash CHAR(64) NOT NULL,
			api_key_prefix VARCHAR(20) NOT NULL,
			is_enabled TINYINT(1) DEFAULT 1,
			ip_whitelist TEXT,
			tool_permissions LONGTEXT,
			wp_user_id BIGINT UNSIGNED DEFAULT 0,
			rate_limit INT DEFAULT 60,
			is_pro TINYINT(1) DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			last_used_at DATETIME NULL,
			UNIQUE KEY api_key_hash (api_key_hash),
			KEY is_enabled (is_enabled)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	private static function create_audit_log_table() {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_audit_log';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$table}` (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			access_point_id BIGINT UNSIGNED DEFAULT 0,
			tool_name VARCHAR(255) NOT NULL,
			arguments LONGTEXT,
			result_status VARCHAR(50) DEFAULT 'pending',
			ip_address VARCHAR(45),
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY access_point_id (access_point_id),
			KEY tool_name (tool_name),
			KEY created_at (created_at),
			KEY result_status (result_status)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	private static function create_sessions_table() {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_sessions';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$table}` (
			session_id CHAR(64) PRIMARY KEY,
			access_point_id BIGINT UNSIGNED NOT NULL,
			wp_user_id BIGINT UNSIGNED NOT NULL,
			protocol_version VARCHAR(20) DEFAULT '2025-11-25',
			auth_source VARCHAR(20) DEFAULT 'bearer',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			expires_at DATETIME NOT NULL,
			KEY access_point_id (access_point_id),
			KEY expires_at (expires_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	private static function create_change_log_table() {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_change_log';
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$table}` (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			access_point_id BIGINT UNSIGNED DEFAULT 0,
			tool_name VARCHAR(255),
			object_type VARCHAR(50),
			object_id BIGINT UNSIGNED,
			before_data LONGTEXT,
			after_data LONGTEXT,
			wp_user_id BIGINT UNSIGNED,
			ip_address VARCHAR(45),
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY access_point_id (access_point_id),
			KEY object_type_id (object_type, object_id),
			KEY created_at (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	private static function create_oauth_tables() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$clients_table = $wpdb->prefix . 'immens_mcp_oauth_clients';
		$sql1 = "CREATE TABLE `{$clients_table}` (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			client_id VARCHAR(255) UNIQUE NOT NULL,
			client_secret_hash CHAR(64),
			client_name VARCHAR(255),
			redirect_uris TEXT,
			grant_types VARCHAR(255) DEFAULT 'authorization_code',
			is_public TINYINT(1) DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		) {$charset};";

		$codes_table = $wpdb->prefix . 'immens_mcp_oauth_codes';
		$sql2 = "CREATE TABLE `{$codes_table}` (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			code_hash CHAR(64) UNIQUE NOT NULL,
			client_id VARCHAR(255) NOT NULL,
			wp_user_id BIGINT UNSIGNED NOT NULL,
			scopes TEXT,
			code_challenge VARCHAR(255),
			code_challenge_method VARCHAR(10) DEFAULT 'S256',
			redirect_uri TEXT,
			expires_at DATETIME NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY expires_at (expires_at)
		) {$charset};";

		$tokens_table = $wpdb->prefix . 'immens_mcp_oauth_tokens';
		$sql3 = "CREATE TABLE `{$tokens_table}` (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			access_token_hash CHAR(64) UNIQUE NOT NULL,
			refresh_token_hash CHAR(64),
			client_id VARCHAR(255) NOT NULL,
			wp_user_id BIGINT UNSIGNED NOT NULL,
			scopes TEXT,
			is_active TINYINT(1) DEFAULT 1,
			access_token_prefix VARCHAR(20),
			expires_at DATETIME NOT NULL,
			refresh_expires_at DATETIME,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY client_id (client_id),
			KEY refresh_token_hash (refresh_token_hash),
			KEY expires_at (expires_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql1 );
		dbDelta( $sql2 );
		dbDelta( $sql3 );
	}

	private static function set_default_options() {
		add_option( 'immens_mcp_fortress_audit_log_enabled', true );
		add_option( 'immens_mcp_fortress_audit_log_retention', 30 );
		add_option( 'immens_mcp_fortress_force_draft_on_create', false );
		add_option( 'immens_mcp_fortress_disabled_tools', array() );
		add_option( 'immens_mcp_fortress_max_title_length', 0 );
	}

	private static function schedule_cron_jobs() {
		if ( ! wp_next_scheduled( 'immens_mcp_fortress_cleanup_audit_log' ) ) {
			wp_schedule_event( time(), 'daily', 'immens_mcp_fortress_cleanup_audit_log' );
		}
		if ( ! wp_next_scheduled( 'immens_mcp_fortress_cleanup_sessions' ) ) {
			wp_schedule_event( time(), 'hourly', 'immens_mcp_fortress_cleanup_sessions' );
		}
		if ( ! wp_next_scheduled( 'immens_mcp_fortress_cleanup_oauth' ) ) {
			wp_schedule_event( time(), 'daily', 'immens_mcp_fortress_cleanup_oauth' );
		}
	}

	public static function maybe_upgrade() {
		$installed_version = get_option( 'immens_mcp_fortress_version', '0' );
		if ( version_compare( $installed_version, IMMENS_MCP_FORTRESS_VERSION, '<' ) ) {
			self::create_access_points_table();
			self::create_audit_log_table();
			self::create_sessions_table();
			self::create_change_log_table();
			self::create_oauth_tables();
			self::set_default_options();
			self::schedule_cron_jobs();

			if ( version_compare( $installed_version, '1.2.0', '<' ) ) {
				self::migrate_access_point_users();
			}

			update_option( 'immens_mcp_fortress_version', IMMENS_MCP_FORTRESS_VERSION );
			flush_rewrite_rules();
		}
	}

	private static function migrate_access_point_users() {
		$manager = new Access_Points\Access_Point_Manager();
		$all     = $manager->get_all_access_points( 9999, 0 );
		$admins  = \get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
		if ( empty( $admins ) ) {
			return;
		}
		$admin_id = (int) $admins[0];
		foreach ( $all as $ap ) {
			if ( empty( (int) $ap['wp_user_id'] ) ) {
				$manager->update_access_point( (int) $ap['id'], array( 'wp_user_id' => $admin_id ) );
			}
		}
	}
}
