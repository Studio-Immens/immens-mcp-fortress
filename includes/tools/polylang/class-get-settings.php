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
		$polylang = get_option( 'polylang', array() );

		return array(
			'default_lang'   => isset( $polylang['default_lang'] ) ? $polylang['default_lang'] : '',
			'force_lang'     => isset( $polylang['force_lang'] ) ? (int) $polylang['force_lang'] : 0,
			'rewrite'        => isset( $polylang['rewrite'] ) ? (int) $polylang['rewrite'] : 0,
			'hide_default'   => isset( $polylang['hide_default'] ) ? (int) $polylang['hide_default'] : 0,
			'browser_detect' => isset( $polylang['browser'] ) ? (int) $polylang['browser'] : 1,
			'media_support'  => isset( $polylang['medias'] ) ? (int) $polylang['medias'] : 1,
			'domains'        => isset( $polylang['domains'] ) ? $polylang['domains'] : array(),
			'post_types'     => isset( $polylang['post_types'] ) ? $polylang['post_types'] : array(),
			'taxonomies'     => isset( $polylang['taxonomies'] ) ? $polylang['taxonomies'] : array(),
			'nav_menus'      => isset( $polylang['nav_menus'] ) ? (int) $polylang['nav_menus'] : 0,
		);
	}
}
