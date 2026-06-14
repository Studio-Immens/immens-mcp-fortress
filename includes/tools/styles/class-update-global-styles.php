<?php
namespace Immens_MCP_Fortress\Tools\Styles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Global_Styles extends Base_Tool {

	public function get_name() {
		return 'wp_update_global_styles';
	}

	public function get_description() {
		return 'Update global styles (styles and settings objects) for the active theme.';
	}

	public function get_required_capability() {
		return 'edit_theme_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'styles'   => array(
					'type'        => array( 'object', 'string' ),
					'description' => 'Global styles object (JSON object or string)',
				),
				'settings' => array(
					'type'        => array( 'object', 'string' ),
					'description' => 'Global settings object (JSON object or string)',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$id     = $this->discover_global_styles_id();
		$params = array();

		if ( isset( $arguments['styles'] ) ) {
			$params['styles'] = is_array( $arguments['styles'] ) ? $arguments['styles'] : json_decode( $arguments['styles'], true );
		}

		if ( isset( $arguments['settings'] ) ) {
			$params['settings'] = is_array( $arguments['settings'] ) ? $arguments['settings'] : json_decode( $arguments['settings'], true );
		}

		return $this->rest_request( 'POST', '/wp/v2/global-styles/' . $id, $params );
	}
}
