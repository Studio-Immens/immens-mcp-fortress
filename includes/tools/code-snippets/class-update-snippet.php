<?php
namespace Immens_MCP_Fortress\Tools\CodeSnippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Snippet extends Base_Tool {

	public function get_name() {
		return 'cs_update_snippet';
	}

	public function get_description() {
		return 'Update an existing code snippet by ID. Only provided fields will be updated.';
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
				'id'          => array(
					'type'        => 'integer',
					'description' => 'Snippet ID to update',
				),
				'name'        => array(
					'type'        => 'string',
					'description' => 'New snippet name',
				),
				'code'        => array(
					'type'        => 'string',
					'description' => 'New snippet code content',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'New description',
				),
				'scope'       => array(
					'type'        => 'string',
					'description' => 'New execution scope',
				),
				'tags'        => array(
					'type'        => 'string',
					'description' => 'New comma-separated tags',
				),
				'priority'    => array(
					'type'        => 'integer',
					'description' => 'New execution priority',
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

			$data = array();
			$types = array();

			if ( isset( $arguments['name'] ) ) {
				$data['name'] = $arguments['name'];
				$types[] = '%s';
			}
			if ( isset( $arguments['code'] ) ) {
				$data['code'] = $arguments['code'];
				$types[] = '%s';
			}
			if ( isset( $arguments['description'] ) ) {
				$data['description'] = $arguments['description'];
				$types[] = '%s';
			}
			if ( isset( $arguments['scope'] ) ) {
				$data['scope'] = $arguments['scope'];
				$types[] = '%s';
			}
			if ( isset( $arguments['tags'] ) ) {
				$data['tags'] = $arguments['tags'];
				$types[] = '%s';
			}
			if ( isset( $arguments['priority'] ) ) {
				$data['priority'] = (int) $arguments['priority'];
				$types[] = '%d';
			}

			if ( empty( $data ) ) {
				throw new \InvalidArgumentException( 'No fields to update.' );
			}

			$data['modified'] = current_time( 'mysql', true );
			$types[] = '%s';

			$result = $wpdb->update( $table_name, $data, array( 'id' => $id ), $types, array( '%d' ) );

			if ( false === $result ) {
				throw new \RuntimeException( 'Failed to update snippet.' );
			}

			$updated = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ), ARRAY_A );

			return array(
				'id'          => (int) $updated['id'],
				'name'        => $updated['name'],
				'description' => $updated['description'],
				'code'        => $updated['code'],
				'scope'       => $updated['scope'],
				'priority'    => (int) $updated['priority'],
				'active'      => (int) $updated['active'] === 1,
				'tags'        => array_filter( explode( ',', $updated['tags'] ) ),
				'modified'    => $updated['modified'],
				'message'     => 'Snippet updated successfully.',
			);
		} elseif ( post_type_exists( 'code-snippets' ) ) {
			$post = get_post( $id );

			if ( ! $post || 'code-snippets' !== $post->post_type ) {
				throw new \RuntimeException( sprintf( 'Snippet with ID %d not found.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			$post_data = array( 'ID' => $id );

			if ( isset( $arguments['name'] ) ) {
				$post_data['post_title'] = $arguments['name'];
			}
			if ( isset( $arguments['code'] ) ) {
				$post_data['post_content'] = $arguments['code'];
			}

			if ( count( $post_data ) > 1 ) {
				$result = wp_update_post( $post_data, true );
				if ( is_wp_error( $result ) ) {
					throw new \RuntimeException( esc_html( $result->get_error_message() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				}
			}

			if ( isset( $arguments['description'] ) ) {
				update_post_meta( $id, '_snippet_desc', $arguments['description'] );
			}
			if ( isset( $arguments['scope'] ) ) {
				update_post_meta( $id, '_snippet_scope', $arguments['scope'] );
			}
			if ( isset( $arguments['tags'] ) ) {
				update_post_meta( $id, '_snippet_tags', $arguments['tags'] );
			}
			if ( isset( $arguments['priority'] ) ) {
				update_post_meta( $id, '_snippet_priority', (int) $arguments['priority'] );
			}

			$updated_post = get_post( $id );

			return array(
				'id'          => $updated_post->ID,
				'name'        => $updated_post->post_title,
				'description' => get_post_meta( $id, '_snippet_desc', true ),
				'code'        => $updated_post->post_content,
				'scope'       => get_post_meta( $id, '_snippet_scope', true ),
				'priority'    => (int) get_post_meta( $id, '_snippet_priority', true ),
				'active'      => get_post_meta( $id, '_snippet_active', true ) === '1',
				'tags'        => array_filter( explode( ',', get_post_meta( $id, '_snippet_tags', true ) ) ),
				'modified'    => $updated_post->post_modified,
				'message'     => 'Snippet updated successfully.',
			);
		}

		throw new \RuntimeException( 'Code Snippets plugin is not active.' );
	}
}
