<?php
namespace Immens_MCP_Fortress\Tools\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Categories extends Base_Tool {

	public function get_name() {
		return 'wp_list_categories';
	}

	public function get_description() {
		return 'List categories with pagination, search, and hierarchy.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Categories per page',
					'default'     => 10,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'parent'   => array(
					'type'        => 'integer',
					'description' => 'Parent category ID',
				),
				'orderby'  => array(
					'type'        => 'string',
					'description' => 'Sort field',
					'default'     => 'name',
				),
				'order'    => array(
					'type'        => 'string',
					'description' => 'Sort direction',
					'enum'        => array( 'asc', 'desc' ),
					'default'     => 'asc',
				),
				'hide_empty' => array(
					'type'        => 'boolean',
					'description' => 'Hide categories with no posts',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$args = array_merge(
			array(
				'page'     => 1,
				'per_page' => 10,
				'orderby'  => 'name',
				'order'    => 'asc',
			),
			$arguments
		);

		$params = array(
			'page'     => (int) $args['page'],
			'per_page' => (int) $args['per_page'],
			'orderby'  => $args['orderby'],
			'order'    => $args['order'],
			'context'  => 'edit',
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}
		if ( isset( $args['parent'] ) && '' !== $args['parent'] ) {
			$params['parent'] = (int) $args['parent'];
		}
		if ( isset( $args['hide_empty'] ) ) {
			$params['hide_empty'] = (bool) $args['hide_empty'];
		}

		return $this->rest_request( 'GET', '/wp/v2/categories', $params );
	}
}
