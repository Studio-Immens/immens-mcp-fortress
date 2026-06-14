<?php
namespace Immens_MCP_Fortress\Tools\RankMath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Post_Seo extends Base_Tool {

	public function get_name() {
		return 'rankmath_get_post_seo';
	}

	public function get_description() {
		return 'Get Rank Math SEO meta for a post. Returns title, meta description, focus keyphrase, OG/Twitter data, canonical URL, and robots settings.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'rank-math';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'Post ID to retrieve Rank Math SEO meta for',
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

		$keys = array(
			'rank_math_title',
			'rank_math_description',
			'rank_math_focus_keyword',
			'rank_math_robots',
			'rank_math_canonical_url',
			'rank_math_og_title',
			'rank_math_og_description',
			'rank_math_facebook_image',
			'rank_math_twitter_title',
			'rank_math_twitter_description',
			'rank_math_twitter_image',
		);

		$meta = array();
		foreach ( $keys as $key ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( '' !== $value ) {
				$meta[ $key ] = $value;
			}
		}

		return array(
			'post_id'  => $post_id,
			'post_url' => get_permalink( $post_id ),
			'seo_meta' => $meta,
		);
	}
}
