<?php
namespace Immens_MCP_Fortress\Tools\CodeSnippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Snippet extends Base_Tool {

	public function get_name() {
		return 'cs_create_snippet';
	}

	public function get_description() {
		return 'Create a new code snippet with name, code, scope, and optional settings.';
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
				'name'        => array(
					'type'        => 'string',
					'description' => 'Snippet name/title',
				),
				'code'        => array(
					'type'        => 'string',
					'description' => 'Snippet code content (without PHP opening tags)',
				),
				'description' => array(
					'type'        => 'string',
					'description' => 'Optional description of what the snippet does',
				),
				'scope'       => array(
					'type'        => 'string',
					'description' => 'Execution scope: global, admin, front-end, content, head-content, footer-content, admin-css, site-css, site-head-js, site-footer-js',
					'default'     => 'global',
				),
				'tags'        => array(
					'type'        => 'string',
					'description' => 'Comma-separated tags',
				),
				'priority'    => array(
					'type'        => 'integer',
					'description' => 'Execution priority (lower runs first, default 10)',
				),
				'active'      => array(
					'type'        => 'boolean',
					'description' => 'Whether the snippet should be active immediately',
					'default'     => true,
				),
			),
			'required'   => array( 'name', 'code' ),
		);
	}

	public function execute( array $arguments ) {
		global $wpdb;

		$this->validate_required( $arguments, array( 'name', 'code' ) );

		$name        = $arguments['name'];
		$code        = $arguments['code'];
		$description = isset( $arguments['description'] ) ? $arguments['description'] : '';
		$scope       = isset( $arguments['scope'] ) ? $arguments['scope'] : 'global';
		$tags        = isset( $arguments['tags'] ) ? $arguments['tags'] : '';
		$priority    = isset( $arguments['priority'] ) ? (int) $arguments['priority'] : 10;
		$active      = isset( $arguments['active'] ) ? (bool) $arguments['active'] : true;

		$table_name = $wpdb->prefix . 'snippets';
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

		if ( $table_exists ) {
			$result = $wpdb->insert(
				$table_name,
				array(
					'name'        => $name,
					'description' => $description,
					'code'        => $code,
					'scope'       => $scope,
					'tags'        => $tags,
					'priority'    => $priority,
					'active'      => $active ? 1 : 0,
					'modified'    => current_time( 'mysql', true ),
				),
				array( '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s' )
			);

			if ( ! $result ) {
				throw new \RuntimeException( 'Failed to create snippet.' );
			}

			$new_id = $wpdb->insert_id;

			return array(
				'id'          => $new_id,
				'name'        => $name,
				'description' => $description,
				'code'        => $code,
				'scope'       => $scope,
				'tags'        => array_filter( explode( ',', $tags ) ),
				'priority'    => $priority,
				'active'      => $active,
				'message'     => 'Snippet created successfully.',
			);
		} elseif ( post_type_exists( 'code-snippets' ) ) {
			$post_data = array(
				'post_title'   => $name,
				'post_content' => $code,
				'post_type'    => 'code-snippets',
				'post_status'  => 'publish',
			);

			$new_id = wp_insert_post( $post_data, true );

			if ( is_wp_error( $new_id ) ) {
				throw new \RuntimeException( esc_html( $new_id->get_error_message() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			update_post_meta( $new_id, '_snippet_desc', $description );
			update_post_meta( $new_id, '_snippet_scope', $scope );
			update_post_meta( $new_id, '_snippet_tags', $tags );
			update_post_meta( $new_id, '_snippet_priority', $priority );
			update_post_meta( $new_id, '_snippet_active', $active ? '1' : '0' );

			return array(
				'id'          => $new_id,
				'name'        => $name,
				'description' => $description,
				'code'        => $code,
				'scope'       => $scope,
				'tags'        => array_filter( explode( ',', $tags ) ),
				'priority'    => $priority,
				'active'      => $active,
				'message'     => 'Snippet created successfully.',
			);
		}

		throw new \RuntimeException( 'Code Snippets plugin is not active.' );
	}
}
