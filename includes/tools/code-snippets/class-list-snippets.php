<?php
namespace Immens_MCP_Fortress\Tools\CodeSnippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Snippets extends Base_Tool {

	public function get_name() {
		return 'cs_list_snippets';
	}

	public function get_description() {
		return 'List all code snippets with optional search, scope, and status filters.';
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
				'search'      => array(
					'type'        => 'string',
					'description' => 'Search term to filter snippets by name',
				),
				'scope'       => array(
					'type'        => 'string',
					'description' => 'Filter by scope (global, admin, front-end, content, etc.)',
				),
				'active_only' => array(
					'type'        => 'boolean',
					'description' => 'Only return active snippets',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		global $wpdb;

		$snippets = array();

		$search    = isset( $arguments['search'] ) ? $arguments['search'] : '';
		$scope     = isset( $arguments['scope'] ) ? $arguments['scope'] : '';
		$active    = isset( $arguments['active_only'] ) && $arguments['active_only'];

		$table_name = $wpdb->prefix . 'snippets';
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

		if ( $table_exists ) {
			$where = array( '1=1' );
			$params = array();

			if ( '' !== $search ) {
				$where[] = 'name LIKE %s';
				$params[] = '%' . $wpdb->esc_like( $search ) . '%';
			}
			if ( '' !== $scope ) {
				$where[] = 'scope = %s';
				$params[] = $scope;
			}
			if ( $active ) {
				$where[] = 'active = 1';
			}

			$table = esc_sql( $table_name );
			$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY id DESC';
			if ( ! empty( $params ) ) {
				$results = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			} else {
				$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			}

			foreach ( $results as $row ) {
				$snippets[] = array(
					'id'          => (int) $row['id'],
					'name'        => $row['name'],
					'description' => $row['description'],
					'scope'       => $row['scope'],
					'priority'    => (int) $row['priority'],
					'active'      => (int) $row['active'] === 1,
					'tags'        => array_filter( explode( ',', $row['tags'] ) ),
					'modified'    => $row['modified'],
				);
			}
		} elseif ( post_type_exists( 'code-snippets' ) ) {
			$args = array(
				'post_type'      => 'code-snippets',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'orderby'        => 'date',
				'order'          => 'DESC',
			);

			if ( '' !== $search ) {
				$args['s'] = $search;
			}

			if ( '' !== $scope ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_snippet_scope',
						'value' => $scope,
					),
				);
			}

			if ( $active ) {
				$args['meta_key']   = '_snippet_active';
				$args['meta_value'] = '1';
			}

			$posts = get_posts( $args );

			foreach ( $posts as $post ) {
				$snippets[] = array(
					'id'          => $post->ID,
					'name'        => $post->post_title,
					'description' => get_post_meta( $post->ID, '_snippet_desc', true ),
					'scope'       => get_post_meta( $post->ID, '_snippet_scope', true ),
					'priority'    => (int) get_post_meta( $post->ID, '_snippet_priority', true ),
					'active'      => get_post_meta( $post->ID, '_snippet_active', true ) === '1',
					'tags'        => array_filter( explode( ',', get_post_meta( $post->ID, '_snippet_tags', true ) ) ),
					'modified'    => $post->post_modified,
				);
			}
		}

		return array(
			'snippets' => $snippets,
			'total'    => count( $snippets ),
		);
	}
}
