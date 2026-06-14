<?php
namespace Immens_MCP_Fortress\Tools\Yoast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Update_Post_Seo extends Base_Tool {

	public function get_name() {
		return 'yoast_update_post_seo';
	}

	public function get_description() {
		return 'Update Yoast SEO meta for a post. Supports title, description, focus_keyword, og_title, og_description, twitter_title, twitter_description, canonical, noindex, nofollow.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_category() {
		return 'yoast';
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
				'canonical'         => array(
					'type'        => 'string',
					'description' => 'Canonical URL',
				),
				'noindex'           => array(
					'type'        => 'boolean',
					'description' => 'Set noindex (true to hide from search engines)',
				),
				'nofollow'          => array(
					'type'        => 'boolean',
					'description' => 'Set nofollow (true to disallow link following)',
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
			'title'                => '_yoast_wpseo_title',
			'description'          => '_yoast_wpseo_metadesc',
			'focus_keyword'        => '_yoast_wpseo_focuskw',
			'og_title'             => '_yoast_wpseo_opengraph-title',
			'og_description'       => '_yoast_wpseo_opengraph-description',
			'twitter_title'        => '_yoast_wpseo_twitter-title',
			'twitter_description'  => '_yoast_wpseo_twitter-description',
			'canonical'            => '_yoast_wpseo_canonical',
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

		if ( isset( $arguments['noindex'] ) ) {
			$value = $arguments['noindex'] ? '1' : '0';
			update_post_meta( $post_id, '_yoast_wpseo_robots-noindex', $value );
			$updated[] = '_yoast_wpseo_robots-noindex';
		}

		if ( isset( $arguments['nofollow'] ) ) {
			$value = $arguments['nofollow'] ? '1' : '0';
			update_post_meta( $post_id, '_yoast_wpseo_robots-nofollow', $value );
			$updated[] = '_yoast_wpseo_robots-nofollow';
		}

		if ( empty( $updated ) ) {
			throw new \InvalidArgumentException( 'At least one SEO field must be provided to update.' );
		}

		$this->invalidate_post_cache( $post_id );

		return array(
			'post_id'  => $post_id,
			'updated'  => $updated,
			'message'  => sprintf( 'Updated %d Yoast SEO field(s) for post %d.', count( $updated ), $post_id ),
		);
	}
}
