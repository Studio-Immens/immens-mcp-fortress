<?php
namespace Immens_MCP_Fortress\Tools\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Template extends Base_Tool {

	public function get_name() {
		return 'wp_get_template';
	}

	public function get_description() {
		return 'Get a single template by ID with edit context.';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'string',
					'description' => 'Template ID (e.g. "twentytwentyfour//home")',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		if ( ! wp_is_block_theme() ) {
			throw new \RuntimeException( 'This tool requires a block theme to be active.' );
		}

		$id     = $arguments['id'];
		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/templates/' . $id, $params );
	}
}
