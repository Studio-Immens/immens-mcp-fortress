<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Categories extends Base_Tool {

	public function get_name() {
		return 'wc_list_categories';
	}

	public function get_description() {
		return 'List WooCommerce product categories with pagination and search.';
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
					'description' => 'Categories per page',
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

		return $this->rest_request( 'GET', '/wc/v3/products/categories', $params );
	}
}
