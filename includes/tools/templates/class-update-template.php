<?php
namespace Immens_MCP_Fortress\Tools\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Template extends Base_Tool {

	public function get_name() {
		return 'wp_update_template';
	}

	public function get_description() {
		return 'Update a template\'s content.';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id'      => array(
					'type'        => 'string',
					'description' => 'Template ID (e.g. "twentytwentyfour//home")',
				),
				'content' => array(
					'type'        => 'string',
					'description' => 'New template content (block markup)',
				),
			),
			'required'   => array( 'id', 'content' ),
		);
	}

	public function execute( array $arguments ) {
		if ( ! wp_is_block_theme() ) {
			throw new \RuntimeException( 'This tool requires a block theme to be active.' );
		}

		$this->validate_required( $arguments, array( 'id', 'content' ) );

		$id     = $arguments['id'];
		$params = array( 'content' => $arguments['content'] );
		return $this->rest_request( 'POST', '/wp/v2/templates/' . $id, $params );
	}
}
