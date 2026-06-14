<?php
namespace Immens_MCP_Fortress\Tools\Polylang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Set_Post_Translation extends Base_Tool {

	public function get_name() {
		return 'polylang_set_post_translation';
	}

	public function get_description() {
		return 'Set the language for a post and link it as a translation of another post.';
	}

	public function get_required_capability() {
		return 'edit_posts';
	}

	public function get_category() {
		return 'polylang';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id'             => array(
					'type'        => 'integer',
					'description' => 'Post ID to set the language for',
				),
				'lang'                => array(
					'type'        => 'string',
					'description' => 'Language slug (e.g. en, fr, de)',
				),
				'translation_post_id' => array(
					'type'        => 'integer',
					'description' => 'Post ID of the translation in another language',
				),
			),
			'required'   => array( 'post_id', 'lang', 'translation_post_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'post_id', 'lang', 'translation_post_id' ) );

		$post_id             = $this->parse_required_id( $arguments['post_id'], 'Post ID' );
		$lang                = $arguments['lang'];
		$translation_post_id = $this->parse_required_id( $arguments['translation_post_id'], 'Translation Post ID' );

		$post = get_post( $post_id );
		if ( ! $post ) {
			throw new \RuntimeException( sprintf( 'Post not found: %d', $post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$translation_post = get_post( $translation_post_id );
		if ( ! $translation_post ) {
			throw new \RuntimeException( sprintf( 'Translation post not found: %d', $translation_post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $post_id, $lang );
		} else {
			update_post_meta( $post_id, '_pll_language', $lang );
		}

		if ( function_exists( 'pll_save_post_translations' ) ) {
			$existing = function_exists( 'pll_get_post_translations' )
				? pll_get_post_translations( $post_id )
				: array();

			$target_lang = '';
			if ( function_exists( 'pll_get_post_language' ) ) {
				$target_lang = pll_get_post_language( $translation_post_id, 'slug' );
			} else {
				$target_lang = get_post_meta( $translation_post_id, '_pll_language', true );
			}

			$translations = array_merge(
				$existing,
				array( $lang => $post_id )
			);

			if ( $target_lang ) {
				$translations[ $target_lang ] = $translation_post_id;
			}

			pll_save_post_translations( $translations );
		} else {
			$existing_translations = get_post_meta( $post_id, '_pll_translations', true );
			if ( ! is_array( $existing_translations ) ) {
				$existing_translations = array();
			}
			$existing_translations[ $lang ] = $translation_post_id;
			update_post_meta( $post_id, '_pll_translations', $existing_translations );
		}

		$this->invalidate_post_cache( $post_id );

		return array(
			'success'             => true,
			'post_id'             => $post_id,
			'lang'                => $lang,
			'translation_post_id' => $translation_post_id,
		);
	}
}
