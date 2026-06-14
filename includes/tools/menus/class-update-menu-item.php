<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Menu_Item extends Base_Tool {

	public function get_name() {
		return 'wp_update_menu_item';
	}

	public function get_description() {
		return 'Update a menu item.';
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
				'item_id'   => array(
					'type'        => 'integer',
					'description' => 'Menu item ID',
				),
				'title'     => array(
					'type'        => 'string',
					'description' => 'Menu item title',
				),
				'url'       => array(
					'type'        => 'string',
					'description' => 'Menu item URL',
				),
				'type'      => array(
					'type'        => 'string',
					'description' => 'Menu item type',
					'enum'        => array( 'custom', 'post_type', 'taxonomy' ),
				),
				'object_id' => array(
					'type'        => 'integer',
					'description' => 'Object ID',
				),
				'parent'    => array(
					'type'        => 'integer',
					'description' => 'Parent menu item ID',
				),
				'menu_id'   => array(
					'type'        => 'integer',
					'description' => 'Move to a different menu ID',
				),
				'order'     => array(
					'type'        => 'integer',
					'description' => 'Menu item order',
				),
			),
			'required'   => array( 'item_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'item_id' ) );

		$item_id = $this->parse_required_id( $arguments['item_id'] );
		$params  = array();

		if ( isset( $arguments['title'] ) ) {
			$params['title'] = $arguments['title'];
		}
		if ( isset( $arguments['url'] ) ) {
			$params['url'] = $arguments['url'];
		}
		if ( isset( $arguments['type'] ) ) {
			$params['type'] = $arguments['type'];
		}
		if ( isset( $arguments['object_id'] ) ) {
			$params['object_id'] = (int) $arguments['object_id'];
		}
		if ( isset( $arguments['parent'] ) ) {
			$params['parent'] = (int) $arguments['parent'];
		}
		if ( isset( $arguments['menu_id'] ) ) {
			$params['menu'] = (int) $arguments['menu_id'];
		}
		if ( isset( $arguments['order'] ) ) {
			$params['menu_order'] = (int) $arguments['order'];
		}

		return $this->rest_request( 'POST', '/wp/v2/menu-items/' . $item_id, $params );
	}
}
