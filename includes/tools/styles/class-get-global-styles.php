<?php
namespace Immens_MCP_Fortress\Tools\Styles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Global_Styles extends Base_Tool {

	public function get_name() {
		return 'wp_get_global_styles';
	}

	public function get_description() {
		return 'Get the current global styles for the active theme.';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$id = $this->discover_global_styles_id();
		return $this->rest_request( 'GET', '/wp/v2/global-styles/' . $id );
	}
}
