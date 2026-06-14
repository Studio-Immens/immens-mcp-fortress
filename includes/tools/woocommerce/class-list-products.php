<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Products extends Base_Tool {

	public function get_name() {
		return 'wc_list_products';
	}

	public function get_description() {
		return 'List WooCommerce products with pagination, search, and filtering.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Products per page',
					'default'     => 10,
				),
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Product status (publish, draft, pending, private, etc.)',
				),
				'category' => array(
					'type'        => 'integer',
					'description' => 'Filter by category ID',
				),
				'type'     => array(
					'type'        => 'string',
					'description' => 'Product type (simple, grouped, external, variable)',
				),
			),
		);
	}

	public function get_category() {
		return 'woocommerce';
	}

	public function execute( array $arguments ) {
		$args = array_merge(
			array(
				'per_page' => 10,
				'page'     => 1,
			),
			$arguments
		);

		$params = array(
			'per_page' => (int) $args['per_page'],
			'page'     => (int) $args['page'],
			'context'  => 'edit',
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}
		if ( ! empty( $args['status'] ) ) {
			$params['status'] = $args['status'];
		}
		if ( ! empty( $args['category'] ) ) {
			$params['category'] = (int) $args['category'];
		}
		if ( ! empty( $args['type'] ) ) {
			$params['type'] = $args['type'];
		}

		return $this->rest_request( 'GET', '/wc/v3/products', $params );
	}
}
