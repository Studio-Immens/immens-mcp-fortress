<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OAuth_Schema {

	public static function create_tables() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$clients = $wpdb->prefix . 'immens_mcp_oauth_clients';
		$sql1 = "CREATE TABLE IF NOT EXISTS `{$clients}` (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			client_id VARCHAR(255) UNIQUE NOT NULL,
			client_secret_hash CHAR(64),
			client_name VARCHAR(255),
			redirect_uris TEXT,
			grant_types VARCHAR(255) DEFAULT 'authorization_code',
			is_public TINYINT(1) DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP
		) {$charset};";

		$codes = $wpdb->prefix . 'immens_mcp_oauth_codes';
		$sql2 = "CREATE TABLE IF NOT EXISTS `{$codes}` (
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

		$tokens = $wpdb->prefix . 'immens_mcp_oauth_tokens';
		$sql3 = "CREATE TABLE IF NOT EXISTS `{$tokens}` (
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

	public static function maybe_upgrade() {
		self::create_tables();
	}
}
