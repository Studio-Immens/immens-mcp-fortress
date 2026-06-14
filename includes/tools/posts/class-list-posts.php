<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Posts extends Base_Tool {

	public function get_name() {
		return 'wp_list_posts';
	}

	public function get_description() {
		return 'List posts with pagination, search, and status filter.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Posts per page',
					'default'     => 10,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Post status',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft', 'inherit', 'any' ),
					'default'     => 'any',
				),
				'orderby'  => array(
					'type'        => 'string',
					'description' => 'Sort field',
					'default'     => 'date',
				),
				'order'    => array(
					'type'        => 'string',
					'description' => 'Sort direction',
					'enum'        => array( 'asc', 'desc' ),
					'default'     => 'desc',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$args = array_merge(
			array(
				'page'     => 1,
				'per_page' => 10,
				'status'   => 'any',
				'orderby'  => 'date',
				'order'    => 'desc',
			),
			$arguments
		);

		$params = array(
			'page'     => (int) $args['page'],
			'per_page' => (int) $args['per_page'],
			'status'   => $args['status'],
			'orderby'  => $args['orderby'],
			'order'    => $args['order'],
			'context'  => 'edit',
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}

		$request  = new \WP_REST_Request( 'GET', '/wp/v2/posts' );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();
			throw new \RuntimeException( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$headers = $response->get_headers();

		return array(
			'posts'       => $response->get_data(),
			'total'       => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'total_pages' => isset( $headers['X-WP-TotalPages'] ) ? (int) $headers['X-WP-TotalPages'] : 0,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
		);
	}
}
