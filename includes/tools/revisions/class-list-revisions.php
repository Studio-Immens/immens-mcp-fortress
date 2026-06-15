<?php
namespace Immens_MCP_Fortress\Tools\Revisions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Revisions extends Base_Tool {

	public function get_name() {
		return 'wp_list_revisions';
	}

	public function get_description() {
		return 'List revisions for a given post/page.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'parent'   => array(
					'type'        => 'integer',
					'description' => 'Parent post ID',
				),
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Revisions per page',
					'default'     => 10,
				),
				'post_type' => array(
					'type'        => 'string',
					'description' => 'Post type (post or page)',
					'enum'        => array( 'post', 'page' ),
					'default'     => 'post',
				),
			),
			'required'   => array( 'parent' ),
		);
	}

	public function execute( array $arguments ) {
		$parent_id = $this->parse_required_id( $arguments['parent'], 'Parent ID' );

		$args = array_merge(
			array(
				'page'     => 1,
				'per_page' => 10,
			),
			$arguments
		);

		$params = array(
			'page'     => (int) $args['page'],
			'per_page' => (int) $args['per_page'],
			'context'  => 'edit',
		);

		$post_type = isset( $arguments['post_type'] ) ? trim( $arguments['post_type'] ) : 'post';
		$base      = ( 'page' === $post_type ) ? 'pages' : 'posts';
		$request   = new \WP_REST_Request( 'GET', '/wp/v2/' . $base . '/' . $parent_id . '/revisions' );
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
			'revisions'   => $response->get_data(),
			'total'       => isset( $headers['X-WP-Total'] ) ? (int) $headers['X-WP-Total'] : 0,
			'total_pages' => isset( $headers['X-WP-TotalPages'] ) ? (int) $headers['X-WP-TotalPages'] : 0,
			'page'        => (int) $args['page'],
			'per_page'    => (int) $args['per_page'],
		);
	}
}
