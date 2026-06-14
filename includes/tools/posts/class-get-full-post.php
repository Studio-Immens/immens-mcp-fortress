<?php
namespace Immens_MCP_Fortress\Tools\Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Full_Post extends Base_Tool {

	public function get_name() {
		return 'wp_get_full_post';
	}

	public function get_description() {
		return 'Get a post with all meta fields and taxonomy terms in a single call.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
			),
			'required'   => array( 'id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'id' ) );
		$id = $this->parse_required_id( $arguments['id'], 'Post ID' );

		$post = $this->rest_request( 'GET', '/wp/v2/posts/' . $id, array( 'context' => 'edit' ) );

		$meta = get_post_meta( $id );

		$taxonomies = get_object_taxonomies( 'post', 'names' );
		$terms      = array();
		foreach ( $taxonomies as $taxonomy ) {
			$term_objects = wp_get_post_terms( $id, $taxonomy, array( 'fields' => 'all' ) );
			if ( ! is_wp_error( $term_objects ) && ! empty( $term_objects ) ) {
				$terms[ $taxonomy ] = array_map(
					function ( $term ) {
						return array(
							'id'          => $term->term_id,
							'name'        => $term->name,
							'slug'        => $term->slug,
							'description' => $term->description,
							'parent'      => $term->parent,
							'count'       => $term->count,
							'taxonomy'    => $term->taxonomy,
						);
					},
					$term_objects
				);
			}
		}

		return array(
			'post'  => $post,
			'meta'  => $meta,
			'terms' => $terms,
		);
	}
}
