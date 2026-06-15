<?php
namespace Immens_MCP_Fortress\Tools\CodeSnippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Toggle_Snippet extends Base_Tool {

	public function get_name() {
		return 'cs_toggle_snippet';
	}

	public function get_description() {
		return 'Activate or deactivate a code snippet by ID.';
	}

	public function get_category() {
		return 'code-snippets';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'     => array(
					'type'        => 'integer',
					'description' => 'Snippet ID to toggle',
				),
				'active' => array(
					'type'        => 'boolean',
					'description' => 'True to activate, false to deactivate. If omitted, toggles current state.',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		global $wpdb;

		$this->validate_required( $arguments, array( 'id' ) );
		$id = $this->parse_required_id( $arguments['id'], 'Snippet ID' );

		$table_name = $wpdb->prefix . 'snippets';
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

		if ( $table_exists ) {
			$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ), ARRAY_A );

			if ( ! $existing ) {
				throw new \RuntimeException( sprintf( 'Snippet with ID %d not found.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			$current_active = (int) $existing['active'] === 1;
			$new_active = isset( $arguments['active'] ) ? (bool) $arguments['active'] : ! $current_active;

			$result = $wpdb->update(
				$table_name,
				array(
					'active'   => $new_active ? 1 : 0,
					'modified' => current_time( 'mysql', true ),
				),
				array( 'id' => $id ),
				array( '%d', '%s' ),
				array( '%d' )
			);

			if ( false === $result ) {
				throw new \RuntimeException( 'Failed to toggle snippet status.' );
			}

			return array(
				'id'     => $id,
				'name'   => $existing['name'],
				'active' => $new_active,
				'message' => $new_active ? 'Snippet activated.' : 'Snippet deactivated.',
			);
		} elseif ( post_type_exists( 'code-snippets' ) ) {
			$post = get_post( $id );

			if ( ! $post || 'code-snippets' !== $post->post_type ) {
				throw new \RuntimeException( sprintf( 'Snippet with ID %d not found.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			$current_active = get_post_meta( $id, '_snippet_active', true ) === '1';
			$new_active = isset( $arguments['active'] ) ? (bool) $arguments['active'] : ! $current_active;

			update_post_meta( $id, '_snippet_active', $new_active ? '1' : '0' );

			return array(
				'id'     => $id,
				'name'   => $post->post_title,
				'active' => $new_active,
				'message' => $new_active ? 'Snippet activated.' : 'Snippet deactivated.',
			);
		}

		throw new \RuntimeException( 'Code Snippets plugin is not active.' );
	}
}
