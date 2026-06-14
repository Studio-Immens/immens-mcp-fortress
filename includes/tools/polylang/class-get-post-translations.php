<?php
namespace Immens_MCP_Fortress\Tools\Polylang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Post_Translations extends Base_Tool {

	public function get_name() {
		return 'polylang_get_post_translations';
	}

	public function get_description() {
		return 'Get all language translations for a given post.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'polylang';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'Post ID to get translations for',
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

		$translations = array();

		if ( function_exists( 'pll_get_post_translations' ) ) {
			$translations = pll_get_post_translations( $post_id );
		} else {
			$meta = get_post_meta( $post_id, '_pll_translations', true );
			if ( is_array( $meta ) ) {
				$translations = $meta;
			}
		}

		$post_language = '';
		if ( function_exists( 'pll_get_post_language' ) ) {
			$post_language = pll_get_post_language( $post_id, 'slug' );
		}

		$result = array();
		foreach ( $translations as $lang => $trans_post_id ) {
			$trans_post = get_post( $trans_post_id );
			$result[] = array(
				'language_slug'    => $lang,
				'translated_post_id' => (int) $trans_post_id,
				'title'            => $trans_post ? $trans_post->post_title : '',
				'status'           => $trans_post ? $trans_post->post_status : '',
				'url'              => $trans_post ? get_permalink( $trans_post_id ) : '',
			);
		}

		return array(
			'post_id'          => $post_id,
			'post_title'       => $post->post_title,
			'post_language'    => $post_language,
			'translations'     => $result,
		);
	}
}
