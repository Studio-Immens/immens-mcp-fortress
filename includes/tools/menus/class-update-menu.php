<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Menu extends Base_Tool {

	public function get_name() {
		return 'wp_update_menu';
	}

	public function get_description() {
		return 'Update a navigation menu (rename).';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
	}

	public function get_category() {
		return 'menus';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'menu_id' => array(
					'type'        => 'integer',
					'description' => 'Menu ID',
				),
				'name'    => array(
					'type'        => 'string',
					'description' => 'New menu name',
				),
			),
			'required'   => array( 'menu_id', 'name' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'menu_id', 'name' ) );

		$id     = $this->parse_required_id( $arguments['menu_id'] );
		$params = array( 'name' => $arguments['name'] );

		return $this->rest_request( 'POST', '/wp/v2/menus/' . $id, $params );
	}
}
