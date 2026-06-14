<?php
namespace Immens_MCP_Fortress\Tools\RankMath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Sitemap_Status extends Base_Tool {

	public function get_name() {
		return 'rankmath_get_sitemap_status';
	}

	public function get_description() {
		return 'Check Rank Math sitemap status. Reads the sitemap option for enable_sitemap flag and verifies sitemap URL accessibility.';
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
		$sitemap_options = get_option( 'rank-math-options-sitemap', array() );

		$enabled = ! empty( $sitemap_options['items_per_page'] ) || ! isset( $sitemap_options['enable_sitemap'] ) || ! empty( $sitemap_options['enable_sitemap'] );

		$result = array(
			'enabled'    => $enabled,
			'sitemap_option_value' => isset( $sitemap_options['enable_sitemap'] ) ? $sitemap_options['enable_sitemap'] : null,
		);

		if ( $enabled && function_exists( 'home_url' ) ) {
			$sitemap_url = trailingslashit( home_url() ) . 'sitemap_index.xml';
			$result['sitemap_url'] = $sitemap_url;

			$response = wp_remote_get( $sitemap_url, array(
				'timeout'     => 10,
				'redirection' => 2,
			) );

			if ( is_wp_error( $response ) ) {
				$result['accessible'] = false;
				$result['error']      = $response->get_error_message();
			} else {
				$status = wp_remote_retrieve_response_code( $response );
				$result['accessible']    = $status >= 200 && $status < 400;
				$result['response_code'] = $status;
			}
		} elseif ( $enabled ) {
			$result['sitemap_url'] = null;
			$result['accessible']  = false;
			$result['error']       = 'home_url() not available.';
		}

		return $result;
	}
}
