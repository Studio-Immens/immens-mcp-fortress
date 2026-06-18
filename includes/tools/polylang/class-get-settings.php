<?php
namespace Immens_MCP_Fortress\Tools\Polylang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Settings extends Base_Tool {

	public function get_name() {
		return 'polylang_get_settings';
	}

	public function get_description() {
		return 'Get Polylang plugin settings including URL mode, browser detection, and media support.';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_category() {
		return 'polylang';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$polylang_options = get_option( 'polylang', array() );

		return array(
			'default_lang'   => isset( $polylang_options['default_lang'] ) ? $polylang_options['default_lang'] : '',
			'force_lang'     => isset( $polylang_options['force_lang'] ) ? (int) $polylang_options['force_lang'] : 0,
			'rewrite'        => isset( $polylang_options['rewrite'] ) ? (int) $polylang_options['rewrite'] : 0,
			'hide_default'   => isset( $polylang_options['hide_default'] ) ? (int) $polylang_options['hide_default'] : 0,
			'browser_detect' => isset( $polylang_options['browser'] ) ? (int) $polylang_options['browser'] : 1,
			'media_support'  => isset( $polylang_options['medias'] ) ? (int) $polylang_options['medias'] : 1,
			'domains'        => isset( $polylang_options['domains'] ) ? $polylang_options['domains'] : array(),
			'post_types'     => isset( $polylang_options['post_types'] ) ? $polylang_options['post_types'] : array(),
			'taxonomies'     => isset( $polylang_options['taxonomies'] ) ? $polylang_options['taxonomies'] : array(),
			'nav_menus'      => isset( $polylang_options['nav_menus'] ) ? (int) $polylang_options['nav_menus'] : 0,
		);
	}
}
