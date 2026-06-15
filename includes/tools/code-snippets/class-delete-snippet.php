<?php
namespace Immens_MCP_Fortress\Tools\CodeSnippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Snippet extends Base_Tool {

	public function get_name() {
		return 'cs_delete_snippet';
	}

	public function get_description() {
		return 'Permanently delete a code snippet by ID.';
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
				'id' => array(
					'type'        => 'integer',
					'description' => 'Snippet ID to delete',
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

			$result = $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );

			if ( ! $result ) {
				throw new \RuntimeException( 'Failed to delete snippet.' );
			}

			return array(
				'deleted_id' => $id,
				'name'       => $existing['name'],
				'message'    => 'Snippet deleted permanently.',
			);
		} elseif ( post_type_exists( 'code-snippets' ) ) {
			$post = get_post( $id );

			if ( ! $post || 'code-snippets' !== $post->post_type ) {
				throw new \RuntimeException( sprintf( 'Snippet with ID %d not found.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			$result = wp_delete_post( $id, true );

			if ( ! $result ) {
				throw new \RuntimeException( 'Failed to delete snippet.' );
			}

			return array(
				'deleted_id' => $id,
				'name'       => $post->post_title,
				'message'    => 'Snippet deleted permanently.',
			);
		}

		throw new \RuntimeException( 'Code Snippets plugin is not active.' );
	}
}
