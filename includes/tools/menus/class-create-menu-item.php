<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Menu_Item extends Base_Tool {

	public function get_name() {
		return 'wp_create_menu_item';
	}

	public function get_description() {
		return 'Create a menu item in a navigation menu.';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
	}

	public function get_category() {
		return 'menus';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'menu_id'   => array(
					'type'        => 'integer',
					'description' => 'Menu ID to add the item to',
				),
				'title'     => array(
					'type'        => 'string',
					'description' => 'Menu item title',
				),
				'url'       => array(
					'type'        => 'string',
					'description' => 'Menu item URL (required for custom type)',
				),
				'type'      => array(
					'type'        => 'string',
					'description' => 'Menu item type',
					'default'     => 'custom',
					'enum'        => array( 'custom', 'post_type', 'taxonomy' ),
				),
				'object_id' => array(
					'type'        => 'integer',
					'description' => 'Object ID (required for post_type or taxonomy type)',
				),
				'parent'    => array(
					'type'        => 'integer',
					'description' => 'Parent menu item ID',
				),
			),
			'required'   => array( 'menu_id', 'title' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'menu_id', 'title' ) );

		$menu_id = $this->parse_required_id( $arguments['menu_id'] );

		$params = array(
			'title' => $arguments['title'],
			'menu'  => $menu_id,
			'type'  => isset( $arguments['type'] ) ? $arguments['type'] : 'custom',
		);

		if ( isset( $arguments['url'] ) ) {
			$params['url'] = $arguments['url'];
		}
		if ( isset( $arguments['object_id'] ) ) {
			$params['object_id'] = (int) $arguments['object_id'];
		}
		if ( isset( $arguments['parent'] ) ) {
			$params['parent'] = (int) $arguments['parent'];
		}

		return $this->rest_request( 'POST', '/wp/v2/menu-items', $params );
	}
}
