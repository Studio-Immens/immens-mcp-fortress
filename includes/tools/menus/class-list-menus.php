<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Menus extends Base_Tool {

	public function get_name() {
		return 'wp_list_menus';
	}

	public function get_description() {
		return 'List all navigation menus.';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
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
					'description' => 'Menus per page',
					'default'     => 10,
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

		return $this->rest_request( 'GET', '/wp/v2/menus', $params );
	}
}
