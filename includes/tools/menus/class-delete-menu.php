<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Delete_Menu extends Base_Tool {

	public function get_name() {
		return 'wp_delete_menu';
	}

	public function get_description() {
		return 'Permanently delete a navigation menu.';
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
			),
			'required'   => array( 'menu_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'menu_id' ) );

		$id     = $this->parse_required_id( $arguments['menu_id'] );
		$params = array( 'force' => true );

		return $this->rest_request( 'DELETE', '/wp/v2/menus/' . $id, $params );
	}
}
