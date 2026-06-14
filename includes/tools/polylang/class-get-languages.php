<?php
namespace Immens_MCP_Fortress\Tools\Polylang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Languages extends Base_Tool {

	public function get_name() {
		return 'polylang_get_languages';
	}

	public function get_description() {
		return 'Get configured Polylang languages with slug, name, locale, and flag.';
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
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		if ( function_exists( 'PLL' ) && PLL() && method_exists( PLL()->model, 'get_languages_list' ) ) {
			return $this->from_pll_api();
		}

		return $this->from_option();
	}

	private function from_pll_api() {
		$languages = PLL()->model->get_languages_list();
		$result    = array();

		foreach ( $languages as $lang ) {
			$result[] = array(
				'slug'     => $lang->slug,
				'name'     => $lang->name,
				'locale'   => $lang->locale,
				'flag'     => $lang->flag,
				'rtl'      => $lang->is_rtl,
				'active'   => true,
			);
		}

		return array(
			'source'    => 'pll_api',
			'languages' => $result,
		);
	}

	private function from_option() {
		$polylang_options = get_option( 'polylang', array() );
		$languages = array();

		if ( isset( $polylang_options['browser'] ) ) {
			unset( $polylang_options['browser'] );
		}

		foreach ( $polylang_options as $key => $value ) {
			if ( is_array( $value ) && isset( $value['slug'] ) ) {
				$languages[] = array(
					'slug'   => $value['slug'],
					'name'   => isset( $value['name'] ) ? $value['name'] : '',
					'locale' => isset( $value['locale'] ) ? $value['locale'] : '',
					'flag'   => isset( $value['flag'] ) ? $value['flag'] : '',
					'rtl'    => isset( $value['rtl'] ) ? (bool) $value['rtl'] : false,
					'active' => true,
				);
			}
		}

		return array(
			'source'    => 'option',
			'languages' => $languages,
		);
	}
}
