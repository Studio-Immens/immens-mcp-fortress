<?php
namespace Immens_MCP_Fortress\Tools\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Orders extends Base_Tool {

	public function get_name() {
		return 'wc_list_orders';
	}

	public function get_description() {
		return 'List WooCommerce orders with pagination, date range, and status filtering.';
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
					'description' => 'Orders per page',
					'default'     => 10,
				),
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'status'   => array(
					'type'        => 'array',
					'description' => 'Order statuses to filter (e.g. processing, completed)',
					'items'       => array(
						'type' => 'string',
					),
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'date_min' => array(
					'type'        => 'string',
					'description' => 'Minimum order date (YYYY-MM-DD)',
				),
				'date_max' => array(
					'type'        => 'string',
					'description' => 'Maximum order date (YYYY-MM-DD)',
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

		if ( ! empty( $args['status'] ) ) {
			$statuses = $this->parse_json_param( $args['status'], 'status' );
			$params['status'] = $statuses;
		}
		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}
		if ( ! empty( $args['date_min'] ) ) {
			$params['after'] = $args['date_min'];
		}
		if ( ! empty( $args['date_max'] ) ) {
			$params['before'] = $args['date_max'];
		}

		return $this->rest_request( 'GET', '/wc/v3/orders', $params );
	}
}
