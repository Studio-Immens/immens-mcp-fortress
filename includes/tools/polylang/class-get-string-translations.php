<?php
namespace Immens_MCP_Fortress\Tools\Polylang;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_String_Translations extends Base_Tool {

	public function get_name() {
		return 'polylang_get_string_translations';
	}

	public function get_description() {
		return 'Get Polylang string translations registered by themes and plugins.';
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
				'search'   => array(
					'type'        => 'string',
					'description' => 'Search term to filter strings by name or value',
				),
				'per_page' => array(
					'type'        => 'integer',
					'description' => 'Results per page',
					'default'     => 20,
				),
				'page'     => array(
					'type'        => 'integer',
					'description' => 'Page number',
					'default'     => 1,
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$search   = isset( $arguments['search'] ) ? $arguments['search'] : '';
		$per_page = isset( $arguments['per_page'] ) ? (int) $arguments['per_page'] : 20;
		$page     = isset( $arguments['page'] ) ? (int) $arguments['page'] : 1;

		if ( class_exists( 'PLL_Admin_Strings' ) && method_exists( 'PLL_Admin_Strings', 'get_strings' ) ) {
			$strings = \PLL_Admin_Strings::get_strings();
		} else {
			$strings = $this->get_strings_from_option();
		}

		if ( ! empty( $search ) ) {
			$search_lower = strtolower( $search );
			$strings = array_filter( $strings, function ( $s ) use ( $search_lower ) {
				$name  = isset( $s['name'] ) ? strtolower( $s['name'] ) : '';
				$value = isset( $s['string'] ) ? strtolower( $s['string'] ) : '';
				return false !== strpos( $name, $search_lower )
					|| false !== strpos( $value, $search_lower );
			} );
		}

		$strings = array_values( $strings );
		$total   = count( $strings );
		$offset  = ( $page - 1 ) * $per_page;
		$paged   = array_slice( $strings, $offset, $per_page );

		$translations_per_string = array();
		foreach ( $paged as $string ) {
			$mo_name = isset( $string['name'] ) ? $string['name'] : '';
			$translations = $this->get_string_translations( $mo_name );
			$translations_per_string[] = array(
				'name'         => isset( $string['name'] ) ? $string['name'] : '',
				'string'       => isset( $string['string'] ) ? $string['string'] : '',
				'context'      => isset( $string['context'] ) ? $string['context'] : '',
				'translations' => $translations,
			);
		}

		return array(
			'total'   => $total,
			'page'    => $page,
			'per_page' => $per_page,
			'strings' => $translations_per_string,
		);
	}

	private function get_strings_from_option() {
		$option  = get_option( 'polylang_strings', array() );
		$strings = array();

		foreach ( $option as $name => $value ) {
			$strings[] = array(
				'name'   => $name,
				'string' => $value,
				'context' => '',
			);
		}

		return $strings;
	}

	private function get_string_translations( $name ) {
		global $polylang;

		$translations = array();

		if ( function_exists( 'PLL' ) && PLL() && method_exists( PLL()->model, 'get_languages_list' ) ) {
			$languages = PLL()->model->get_languages_list();
			foreach ( $languages as $lang ) {
				$translation = pll_translate_string( $name, $lang->slug );
				$translations[] = array(
					'locale'      => $lang->locale,
					'language'    => $lang->slug,
					'translation' => $translation,
				);
			}
		} else {
			$option = get_option( 'polylang_strings_translations', array() );
			foreach ( $option as $locale => $strings ) {
				if ( is_array( $strings ) && isset( $strings[ $name ] ) ) {
					$translations[] = array(
						'locale'      => $locale,
						'translation' => $strings[ $name ],
					);
				}
			}
		}

		return $translations;
	}
}
