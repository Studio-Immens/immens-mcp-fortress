<?php
namespace Immens_MCP_Fortress\Tools\Yoast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Settings extends Base_Tool {

	public function get_name() {
		return 'yoast_get_settings';
	}

	public function get_description() {
		return 'Read Yoast global settings from wpseo, wpseo_titles, and wpseo_social options.';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_category() {
		return 'yoast';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$wpseo        = get_option( 'wpseo', array() );
		$wpseo_titles = get_option( 'wpseo_titles', array() );
		$wpseo_social = get_option( 'wpseo_social', array() );

		return array(
			'wpseo'         => $wpseo,
			'wpseo_titles'  => $wpseo_titles,
			'wpseo_social'  => $wpseo_social,
		);
	}
}
