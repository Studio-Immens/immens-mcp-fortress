<?php
namespace Immens_MCP_Fortress\Tools\Polylang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Untranslated extends Base_Tool {

	public function get_name() {
		return 'polylang_get_untranslated';
	}

	public function get_description() {
		return 'Get posts without a translation in a specific target language.';
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
				'post_type'   => array(
					'type'        => 'string',
					'description' => 'Post type to query',
					'default'     => 'post',
				),
				'target_lang' => array(
					'type'        => 'string',
					'description' => 'Target language slug (e.g. fr, de)',
				),
				'limit'       => array(
					'type'        => 'integer',
					'description' => 'Maximum posts to return',
					'default'     => 20,
				),
			),
			'required'   => array( 'target_lang' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'target_lang' ) );

		$post_type   = isset( $arguments['post_type'] ) ? $arguments['post_type'] : 'post';
		$target_lang = $arguments['target_lang'];
		$limit       = isset( $arguments['limit'] ) ? (int) $arguments['limit'] : 20;

		if ( function_exists( 'pll_get_posts_not_translated' ) ) {
			return $this->use_pll_api( $post_type, $target_lang, $limit );
		}

		return $this->use_custom_query( $post_type, $target_lang, $limit );
	}

	private function use_pll_api( $post_type, $target_lang, $limit ) {
		$excluded = pll_get_posts_not_translated( $post_type, $target_lang );

		if ( empty( $excluded ) ) {
			return array(
				'post_type'   => $post_type,
				'target_lang' => $target_lang,
				'total'       => 0,
				'posts'       => array(),
			);
		}

		$posts = get_posts( array(
			'post_type'      => $post_type,
			'post__in'       => $excluded,
			'posts_per_page' => $limit,
			'post_status'    => 'any',
		) );

		$result = array();
		foreach ( $posts as $post ) {
			$source_lang = pll_get_post_language( $post->ID, 'slug' );
			$result[] = array(
				'id'            => $post->ID,
				'title'         => $post->post_title,
				'status'        => $post->post_status,
				'source_lang'   => $source_lang,
				'url'           => get_permalink( $post->ID ),
			);
		}

		return array(
			'post_type'   => $post_type,
			'target_lang' => $target_lang,
			'total'       => count( $excluded ),
			'posts'       => $result,
		);
	}

	private function use_custom_query( $post_type, $target_lang, $limit ) {
		$all_posts = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		) );

		$untranslated = array();
		foreach ( $all_posts as $post ) {
			$post_lang = get_post_meta( $post->ID, '_pll_language', true );
			if ( $post_lang === $target_lang ) {
				continue;
			}

			$translations = get_post_meta( $post->ID, '_pll_translations', true );
			if ( ! is_array( $translations ) ) {
				$translations = array();
			}

			if ( ! isset( $translations[ $target_lang ] ) ) {
				$untranslated[] = array(
					'id'          => $post->ID,
					'title'       => $post->post_title,
					'status'      => $post->post_status,
					'source_lang' => $post_lang,
					'url'         => get_permalink( $post->ID ),
				);
			}

			if ( count( $untranslated ) >= $limit ) {
				break;
			}
		}

		return array(
			'post_type'   => $post_type,
			'target_lang' => $target_lang,
			'total'       => count( $untranslated ),
			'posts'       => $untranslated,
		);
	}
}
