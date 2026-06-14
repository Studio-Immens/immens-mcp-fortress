<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Menu_Items extends Base_Tool {

	public function get_name() {
		return 'wp_list_menu_items';
	}

	public function get_description() {
		return 'List items in a navigation menu.';
	}

	public function get_required_capability() {
		return 'read';
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
		$params = array(
			'menus'   => $id,
			'context' => 'edit',
		);

		return $this->rest_request( 'GET', '/wp/v2/menu-items', $params );
	}
}
