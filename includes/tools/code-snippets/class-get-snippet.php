<?php
namespace Immens_MCP_Fortress\Tools\CodeSnippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Snippet extends Base_Tool {

	public function get_name() {
		return 'cs_get_snippet';
	}

	public function get_description() {
		return 'Get a single code snippet by ID with full details including code content.';
	}

	public function get_category() {
		return 'code-snippets';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => true,
			'destructiveHint' => false,
			'openWorldHint'   => false,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'integer',
					'description' => 'Snippet ID',
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
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $id ), ARRAY_A );

			if ( ! $row ) {
				throw new \RuntimeException( sprintf( 'Snippet with ID %d not found.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			return array(
				'id'          => (int) $row['id'],
				'name'        => $row['name'],
				'description' => $row['description'],
				'code'        => $row['code'],
				'scope'       => $row['scope'],
				'priority'    => (int) $row['priority'],
				'active'      => (int) $row['active'] === 1,
				'tags'        => array_filter( explode( ',', $row['tags'] ) ),
				'modified'    => $row['modified'],
				'revision'    => (int) $row['revision'],
			);
		} elseif ( post_type_exists( 'code-snippets' ) ) {
			$post = get_post( $id );

			if ( ! $post || 'code-snippets' !== $post->post_type ) {
				throw new \RuntimeException( sprintf( 'Snippet with ID %d not found.', $id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			return array(
				'id'          => $post->ID,
				'name'        => $post->post_title,
				'description' => get_post_meta( $post->ID, '_snippet_desc', true ),
				'code'        => $post->post_content,
				'scope'       => get_post_meta( $post->ID, '_snippet_scope', true ),
				'priority'    => (int) get_post_meta( $post->ID, '_snippet_priority', true ),
				'active'      => get_post_meta( $post->ID, '_snippet_active', true ) === '1',
				'tags'        => array_filter( explode( ',', get_post_meta( $post->ID, '_snippet_tags', true ) ) ),
				'modified'    => $post->post_modified,
			);
		}

		throw new \RuntimeException( 'Code Snippets plugin is not active or no snippets found.' );
	}
}
