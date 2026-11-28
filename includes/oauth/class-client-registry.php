<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Client_Registry {

	/**
	 * Registra un client OAuth.
	 *
	 * @param string $client_id
	 * @param string $client_name
	 * @param array  $redirect_uris
	 * @param bool   $is_public
	 * @param string $client_secret Segreto in chiaro: viene salvato solo l'hash SHA-256.
	 */
	public function register_client( $client_id, $client_name, $redirect_uris = array(), $is_public = true, $client_secret = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_clients'; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter

		$wpdb->insert( $table, array( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			'client_id'    => $client_id,
			'client_name'  => $client_name,
			'redirect_uris' => wp_json_encode( $redirect_uris ),
			'is_public'    => $is_public ? 1 : 0,
			// Prima il secret veniva generato ma mai salvato: non era verificabile
			// e il token endpoint lo ignorava del tutto.
			'client_secret_hash' => '' !== $client_secret ? hash( 'sha256', $client_secret ) : null,
			'created_at'   => current_time( 'mysql', true ),
		) );

		return $wpdb->insert_id;
	}

	public function get_client( $client_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_clients'; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
			"SELECT * FROM `{$table}` WHERE client_id = %s",
			$client_id
		), ARRAY_A );
	}

	/**
	 * Verifica il client_secret di un client confidenziale.
	 * Per i client pubblici (PKCE) restituisce true senza richiedere il segreto.
	 *
	 * @param array  $client        Riga client (da get_client()).
	 * @param string $client_secret Segreto fornito dal client.
	 * @return bool
	 */
	public function verify_client_secret( $client, $client_secret ) {
		if ( ! is_array( $client ) ) {
			return false;
		}

		$is_public = ! empty( $client['is_public'] );
		if ( $is_public ) {
			return true;
		}

		$stored = isset( $client['client_secret_hash'] ) ? (string) $client['client_secret_hash'] : '';
		if ( '' === $stored || '' === (string) $client_secret ) {
			return false;
		}

		return hash_equals( $stored, hash( 'sha256', $client_secret ) );
	}
}
