<?php
namespace Immens_MCP_Fortress\Tools\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Site_Settings extends Base_Tool {

	public function get_name() {
		return 'wp_get_site_settings';
	}

	public function get_description() {
		return 'Get site settings (title, description, timezone, etc.).';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		return $this->rest_request( 'GET', '/wp/v2/settings' );
	}
}
