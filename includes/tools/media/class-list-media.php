<?php
namespace Immens_MCP_Fortress\Tools\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Media extends Base_Tool {

	public function get_name() {
		return 'wp_list_media';
	}

	public function get_description() {
		return 'List media items with pagination, search, and type filter.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'page'       => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page'   => array(
					'type'        => 'integer',
					'description' => 'Items per page',
					'default'     => 10,
				),
				'search'     => array(
					'type'        => 'string',
					'description' => 'Search term',
				),
				'media_type' => array(
					'type'        => 'string',
					'description' => 'Media type filter',
					'enum'        => array( 'image', 'video', 'audio', 'application', 'file' ),
				),
				'mime_type'  => array(
					'type'        => 'string',
					'description' => 'MIME type filter',
				),
				'orderby'    => array(
					'type'        => 'string',
					'description' => 'Sort field',
					'default'     => 'date',
				),
				'order'      => array(
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
				'orderby'  => 'date',
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
		if ( ! empty( $args['media_type'] ) ) {
			$params['media_type'] = $args['media_type'];
		}
		if ( ! empty( $args['mime_type'] ) ) {
			$params['mime_type'] = $args['mime_type'];
		}

		$request  = new \WP_REST_Request( 'GET', '/wp/v2/media' );
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
			'media'       => $response->get_data(),
			'total'       => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'total_pages' => isset( $headers['X-WP-TotalPages'] ) ? (int) $headers['X-WP-TotalPages'] : 0,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
		);
	}
}
