<?php
namespace Immens_MCP_Fortress\Tools\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Count_Pages extends Base_Tool {

	public function get_name() {
		return 'wp_count_pages';
	}

	public function get_description() {
		return 'Get page counts grouped by status.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$request  = new \WP_REST_Request( 'GET', '/wp/v2/pages' );
		$request->set_param( 'per_page', 1 );
		$request->set_param( 'status', 'any' );
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();
			throw new \RuntimeException( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$headers = $response->get_headers();
		$counts  = wp_count_posts( 'page' );

		return array(
			'total'  => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'counts' => $counts,
		);
	}
}
