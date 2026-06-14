<?php
namespace Immens_MCP_Fortress\Tools\Yoast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Post_Seo extends Base_Tool {

	public function get_name() {
		return 'yoast_get_post_seo';
	}

	public function get_description() {
		return 'Get Yoast SEO meta for a post. Returns title, meta description, focus keyphrase, OG/Twitter data, canonical URL, and robots settings.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'yoast';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'Post ID to retrieve Yoast SEO meta for',
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
			'_yoast_wpseo_title',
			'_yoast_wpseo_metadesc',
			'_yoast_wpseo_focuskw',
			'_yoast_wpseo_opengraph-title',
			'_yoast_wpseo_opengraph-description',
			'_yoast_wpseo_opengraph-image-id',
			'_yoast_wpseo_twitter-title',
			'_yoast_wpseo_twitter-description',
			'_yoast_wpseo_twitter-image-id',
			'_yoast_wpseo_canonical',
			'_yoast_wpseo_robots-noindex',
			'_yoast_wpseo_robots-nofollow',
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
