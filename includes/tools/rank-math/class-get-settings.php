<?php
namespace Immens_MCP_Fortress\Tools\RankMath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Settings extends Base_Tool {

	public function get_name() {
		return 'rankmath_get_settings';
	}

	public function get_description() {
		return 'Read Rank Math global settings from rank-math-options-general, rank-math-options-titles, and rank-math-options-sitemap options.';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_category() {
		return 'rank-math';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$general = get_option( 'rank-math-options-general', array() );
		$titles  = get_option( 'rank-math-options-titles', array() );
		$sitemap = get_option( 'rank-math-options-sitemap', array() );

		return array(
			'general' => $general,
			'titles'  => $titles,
			'sitemap' => $sitemap,
		);
	}
}
