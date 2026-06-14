<?php
namespace Immens_MCP_Fortress\Tools\Meta;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Add_Post_Terms extends Base_Tool {

	public function get_name() {
		return 'wp_add_post_terms';
	}

	public function get_description() {
		return 'Assign taxonomy terms to a post.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_category() {
		return 'meta';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id'  => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
				'taxonomy' => array(
					'type'        => 'string',
					'description' => 'Taxonomy name (e.g. category, post_tag)',
				),
				'terms'    => array(
					'type'        => 'array',
					'items'       => array(
						'type' => array( 'integer', 'string' ),
					),
					'description' => 'Array of term IDs or slugs',
				),
			),
			'required'   => array( 'post_id', 'taxonomy', 'terms' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'post_id', 'taxonomy', 'terms' ) );

		$post_id  = $this->parse_required_id( $arguments['post_id'] );
		$taxonomy = $arguments['taxonomy'];
		$terms    = $this->parse_json_param( $arguments['terms'], 'terms' );

		$result = wp_set_object_terms( $post_id, $terms, $taxonomy, false );

		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException(
				$result->get_error_message()
) ; // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$this->invalidate_post_cache( $post_id );

		return array(
			'success'          => true,
			'post_id'          => $post_id,
			'taxonomy'         => $taxonomy,
			'assigned_term_ids' => $result,
		);
	}
}
