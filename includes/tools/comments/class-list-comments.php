<?php
namespace Immens_MCP_Fortress\Tools\Comments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Comments extends Base_Tool {

	public function get_name() {
		return 'wp_list_comments';
	}

	public function get_description() {
		return 'List comments with pagination and filters.';
	}

	public function get_required_capability() {
		return 'moderate_comments';
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
					'description' => 'Comments per page',
					'default'     => 10,
				),
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'post'     => array(
					'type'        => 'integer',
					'description' => 'Filter by post ID',
				),
				'status'   => array(
					'type'        => 'string',
					'description' => 'Comment status',
					'enum'        => array( 'hold', 'approve', 'spam', 'trash', '1', '0' ),
				),
				'orderby'  => array(
					'type'        => 'string',
					'description' => 'Sort field',
					'default'     => 'date_gmt',
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
				'orderby'  => 'date_gmt',
				'order'    => 'desc',
			),
			$arguments
		);

		$params = array(
			'page'     => (int) $args['page'],
			'per_page' => (int) $args['per_page'],
			'orderby'  => $args['orderby'],
			'order'    => $args['order'],
			'context'  => 'edit',
		);

		if ( ! empty( $args['search'] ) ) {
			$params['search'] = $args['search'];
		}
		if ( ! empty( $args['post'] ) ) {
			$params['post'] = (int) $args['post'];
		}
		if ( ! empty( $args['status'] ) ) {
			$params['status'] = $args['status'];
		}

		$request  = new \WP_REST_Request( 'GET', '/wp/v2/comments' );
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
			'comments'    => $response->get_data(),
			'total'       => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'total_pages' => isset( $headers['X-WP-TotalPages'] ) ? (int) $headers['X-WP-TotalPages'] : 0,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
		);
	}
}
