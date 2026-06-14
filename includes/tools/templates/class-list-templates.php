<?php
namespace Immens_MCP_Fortress\Tools\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Templates extends Base_Tool {

	public function get_name() {
		return 'wp_list_templates';
	}

	public function get_description() {
		return 'List all block templates. Only works with block themes.';
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
					'description' => 'Templates per page',
					'default'     => 10,
				),
			),
		);
	}

	public function execute( array $arguments ) {
		if ( ! wp_is_block_theme() ) {
			throw new \RuntimeException( 'This tool requires a block theme to be active.' );
		}

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
		);

		return $this->rest_request( 'GET', '/wp/v2/templates', $params );
	}
}
