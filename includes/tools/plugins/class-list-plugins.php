<?php
namespace Immens_MCP_Fortress\Tools\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Plugins extends Base_Tool {

	public function get_name() {
		return 'wp_list_plugins';
	}

	public function get_description() {
		return 'List all installed plugins with their status.';
	}

	public function get_required_capability() {
		return 'manage_options';
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
					'description' => 'Plugins per page',
					'default'     => 10,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Plugin status filter',
					'enum'        => array( 'active', 'inactive' ),
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$args = array_merge(
			array(
				'page'     => 1,
				'per_page' => 10,
			),
			$arguments
		);

		$params = array(
			'page'     => (int) $args['page'],
			'per_page' => (int) $args['per_page'],
			'context'  => 'edit',
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}
		if ( ! empty( $args['status'] ) ) {
			$params['status'] = $args['status'];
		}

		return $this->rest_request( 'GET', '/wp/v2/plugins', $params );
	}
}
