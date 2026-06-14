<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Count_Posts extends Base_Tool {

	public function get_name() {
		return 'wp_count_posts';
	}

	public function get_description() {
		return 'Count posts by type and status.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type to count',
					'default'     => 'post',
				),
				'status'    => array(
					'type'        => 'string',
					'description' => 'Filter by status',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft', 'inherit', 'any' ),
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$post_type = isset( $arguments['post_type'] ) ? $arguments['post_type'] : 'post';

		$params = array(
			'per_page' => 1,
			'context'  => 'edit',
		);

		if ( ! empty( $arguments['status'] ) ) {
			$params['status'] = $arguments['status'];
		}

		$route = '/wp/v2/' . $post_type . 's';
		if ( ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
			$route = '/wp/v2/' . $post_type;
		}

		$request = new \WP_REST_Request( 'GET', $route );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();
			throw new \RuntimeException( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$headers = $response->get_headers();
		$total   = isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0;

		return array(
			'post_type' => $post_type,
			'total'     => $total,
		);
	}
}
