<?php
namespace Immens_MCP_Fortress\Tools\Menus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Create_Menu extends Base_Tool {

	public function get_name() {
		return 'wp_create_menu';
	}

	public function get_description() {
		return 'Create a new navigation menu.';
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
				'name' => array(
					'type'        => 'string',
					'description' => 'Menu name',
				),
			),
			'required'   => array( 'name' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'name' ) );

		$params = array( 'name' => $arguments['name'] );

		return $this->rest_request( 'POST', '/wp/v2/menus', $params );
	}
}
