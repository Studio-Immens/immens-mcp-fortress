<?php
namespace Immens_MCP_Fortress\Tools\RankMath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Post_Seo extends Base_Tool {

	public function get_name() {
		return 'rankmath_update_post_seo';
	}

	public function get_description() {
		return 'Update Rank Math SEO meta for a post. Supports title, description, focus_keyword, robots, canonical_url, og_title, og_description, twitter_title, twitter_description.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_category() {
		return 'rank-math';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id'           => array(
					'type'        => 'integer',
					'description' => 'Post ID',
				),
				'title'             => array(
					'type'        => 'string',
					'description' => 'Custom SEO title',
				),
				'description'       => array(
					'type'        => 'string',
					'description' => 'Meta description',
				),
				'focus_keyword'     => array(
					'type'        => 'string',
					'description' => 'Focus keyphrase',
				),
				'robots'            => array(
					'type'        => 'array',
					'items'       => array(
						'type' => 'string',
					),
					'description' => 'Robots meta tags array (e.g. noindex, nofollow, noarchive, noimageindex)',
				),
				'canonical_url'     => array(
					'type'        => 'string',
					'description' => 'Canonical URL',
				),
				'og_title'          => array(
					'type'        => 'string',
					'description' => 'Open Graph title',
				),
				'og_description'    => array(
					'type'        => 'string',
					'description' => 'Open Graph description',
				),
				'twitter_title'     => array(
					'type'        => 'string',
					'description' => 'Twitter title',
				),
				'twitter_description' => array(
					'type'        => 'string',
					'description' => 'Twitter description',
				),
			),
			'required'   => array( 'post_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'post_id' ) );
		$post_id = $this->parse_required_id( $arguments['post_id'], 'Post ID' );

		$post = get_post( $post_id );
		if ( ! $post ) {
			throw new \RuntimeException( sprintf( 'Post not found: %d', $post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$map = array(
			'title'               => 'rank_math_title',
			'description'         => 'rank_math_description',
			'focus_keyword'       => 'rank_math_focus_keyword',
			'canonical_url'       => 'rank_math_canonical_url',
			'og_title'            => 'rank_math_og_title',
			'og_description'      => 'rank_math_og_description',
			'twitter_title'       => 'rank_math_twitter_title',
			'twitter_description' => 'rank_math_twitter_description',
		);

		$updated = array();

		foreach ( $map as $arg_key => $meta_key ) {
			if ( isset( $arguments[ $arg_key ] ) ) {
				$value = $arguments[ $arg_key ];
				if ( '' === $value ) {
					delete_post_meta( $post_id, $meta_key );
				} else {
					update_post_meta( $post_id, $meta_key, $value );
				}
				$updated[] = $meta_key;
			}
		}

		if ( isset( $arguments['robots'] ) ) {
			$robots = $arguments['robots'];
			if ( is_string( $robots ) ) {
				$robots = $this->parse_json_param( $robots, 'robots' );
			}
			if ( empty( $robots ) ) {
				delete_post_meta( $post_id, 'rank_math_robots' );
			} else {
				update_post_meta( $post_id, 'rank_math_robots', $robots );
			}
			$updated[] = 'rank_math_robots';
		}

		if ( empty( $updated ) ) {
			throw new \InvalidArgumentException( 'At least one SEO field must be provided to update.' );
		}

		$this->invalidate_post_cache( $post_id );

		return array(
			'post_id' => $post_id,
			'updated' => $updated,
			'message' => sprintf( 'Updated %d Rank Math SEO field(s) for post %d.', count( $updated ), $post_id ),
		);
	}
}
