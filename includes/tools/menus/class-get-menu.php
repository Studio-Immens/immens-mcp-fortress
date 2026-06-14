<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Menu extends Base_Tool {

	public function get_name() {
		return 'wp_get_menu';
	}

	public function get_description() {
		return 'Get a single navigation menu by ID with edit context.';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'integer',
					'description' => 'Menu ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$id     = $this->parse_required_id( $arguments['id'] );
		$params = array( 'context' => 'edit' );
		return $this->rest_request( 'GET', '/wp/v2/menus/' . $id, $params );
	}
}
