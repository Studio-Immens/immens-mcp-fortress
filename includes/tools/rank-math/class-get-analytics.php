<?php
namespace Immens_MCP_Fortress\Tools\RankMath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Analytics extends Base_Tool {

	public function get_name() {
		return 'rankmath_get_analytics';
	}

	public function get_description() {
		return 'Get Rank Math analytics data (if module is active). Reads rank_math_analytics_* options and returns module status and available analytics data.';
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

		$modules = isset( $general['modules'] ) ? $general['modules'] : array();
		$analytics_active = ! empty( $modules['analytics'] ) || in_array( 'analytics', $modules, true );

		if ( is_string( $modules ) ) {
			$decoded = json_decode( $modules, true );
			if ( is_array( $decoded ) ) {
				$analytics_active = ! empty( $decoded['analytics'] ) || in_array( 'analytics', $decoded, true );
			}
		}

		$result = array(
			'module_active' => $analytics_active,
		);

		if ( $analytics_active ) {
			$analytics_options = get_option( 'rank_math_analytics_options', array() );

			$analytics_data = array(
				'search_analytics'  => get_option( 'rank_math_analytics_search_analytics', array() ),
				'summary'           => get_option( 'rank_math_analytics_summary', array() ),
				'options'           => $analytics_options,
			);

			$result['analytics'] = $analytics_data;
			$result['has_data']  = ! empty( $analytics_data['search_analytics'] ) || ! empty( $analytics_data['summary'] );
		} else {
			$result['analytics'] = null;
			$result['has_data']  = false;
			$result['message']   = 'Rank Math Analytics module is not active. Enable it in Rank Math > Dashboard > Modules.';
		}

		return $result;
	}
}
