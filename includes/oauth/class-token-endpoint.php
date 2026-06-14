<?php
namespace Immens_MCP_Fortress\OAuth;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Token_Endpoint {

	public function handle( \WP_REST_Request $request ) {
		$grant_type = $request->get_param( 'grant_type' );

		switch ( $grant_type ) {
			case 'authorization_code':
				return $this->handle_authorization_code( $request );
			case 'refresh_token':
				return $this->handle_refresh_token( $request );
			default:
				return new \WP_Error( 'unsupported_grant_type', 'Unsupported grant type', array( 'status' => 400 ) );
		}
	}

	private function handle_authorization_code( \WP_REST_Request $request ) {
		$code          = $request->get_param( 'code' );
		$client_id     = $request->get_param( 'client_id' );
		$code_verifier = $request->get_param( 'code_verifier' );
		$redirect_uri  = $request->get_param( 'redirect_uri' );

		if ( empty( $code ) || empty( $client_id ) ) {
			return new \WP_Error( 'invalid_request', 'Missing required parameters', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'immens_mcp_oauth_codes'; // phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
		$code_hash = hash( 'sha256', $code );

		$row = $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT * FROM `{$table}` WHERE code_hash = %s AND client_id = %s AND expires_at > UTC_TIMESTAMP()", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$code_hash,
			$client_id
		), ARRAY_A );

		if ( ! $row ) {
			return new \WP_Error( 'invalid_grant', 'Invalid or expired authorization code', array( 'status' => 400 ) );
		}

		if ( ! empty( $row['code_challenge'] ) ) {
			if ( empty( $code_verifier ) ) {
				return new \WP_Error( 'invalid_grant', 'PKCE code_verifier required', array( 'status' => 400 ) );
			}
			$challenge_method = ! empty( $row['code_challenge_method'] ) ? $row['code_challenge_method'] : 'S256';
			$expected = 'S256' === $challenge_method
				? rtrim( strtr( base64_encode( hash( 'sha256', $code_verifier, true ) ), '+/', '-_' ), '=' )
				: $code_verifier;
			if ( ! hash_equals( $row['code_challenge'], $expected ) ) {
				return new \WP_Error( 'invalid_grant', 'PKCE verification failed', array( 'status' => 400 ) );
			}
		}

		$wpdb->delete( $table, array( 'id' => $row['id'] ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$scopes = json_decode( $row['scopes'], true );
		if ( ! is_array( $scopes ) ) {
			$scopes = array();
		}

		$tm     = new OAuth_Token_Manager();
		$tokens = $tm->create_token( $client_id, $row['wp_user_id'], $scopes );

		return array(
			'access_token'  => $tokens['access_token'],
			'token_type'    => 'Bearer',
			'expires_in'    => $tokens['expires_in'],
			'refresh_token' => $tokens['refresh_token'],
			'scope'         => implode( ' ', $scopes ),
		);
	}

	private function handle_refresh_token( \WP_REST_Request $request ) {
		$refresh_token = $request->get_param( 'refresh_token' );
		$client_id     = $request->get_param( 'client_id' );

		if ( empty( $refresh_token ) ) {
			return new \WP_Error( 'invalid_request', 'Missing refresh_token', array( 'status' => 400 ) );
		}

		$tm    = new OAuth_Token_Manager();
		$token = $tm->validate_refresh_token( $refresh_token );

		if ( ! $token ) {
			return new \WP_Error( 'invalid_grant', 'Invalid or expired refresh token', array( 'status' => 400 ) );
		}

		$tm->revoke_token( $token['id'] );

		$scopes = json_decode( $token['scopes'], true ) ?: array();
		$new_tokens = $tm->create_token( $client_id ?: $token['client_id'], $token['wp_user_id'], $scopes );

		return array(
			'access_token'  => $new_tokens['access_token'],
			'token_type'    => 'Bearer',
			'expires_in'    => $new_tokens['expires_in'],
			'refresh_token' => $new_tokens['refresh_token'],
			'scope'         => implode( ' ', $scopes ),
		);
	}
}
