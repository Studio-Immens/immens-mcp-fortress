<?php
namespace Immens_MCP_Fortress\Tools\LocoTranslate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Projects extends Base_Tool {

	public function get_name() {
		return 'loco_list_projects';
	}

	public function get_description() {
		return 'List Loco Translate translation projects, scanning languages directories for .po files.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'loco-translate';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'type' => array(
					'type'        => 'string',
					'description' => 'Project type to list: plugin, theme, or all',
					'enum'        => array( 'plugin', 'theme', 'all' ),
					'default'     => 'all',
				),
			),
		);
	}

	public function execute( array $arguments ) {
		$type = isset( $arguments['type'] ) ? $arguments['type'] : 'all';

		$projects = array();

		if ( in_array( $type, array( 'plugin', 'all' ), true ) ) {
			$projects['plugins'] = $this->scan_directory( WP_LANG_DIR . '/plugins/' );
		}

		if ( in_array( $type, array( 'theme', 'all' ), true ) ) {
			$projects['themes'] = $this->scan_directory( WP_LANG_DIR . '/themes/' );
		}

		return $projects;
	}

	private function scan_directory( $dir ) {
		$projects = array();

		if ( ! is_dir( $dir ) ) {
			return $projects;
		}

		$files = glob( $dir . '*.po' );
		if ( ! $files ) {
			return $projects;
		}

		foreach ( $files as $file ) {
			$basename  = basename( $file, '.po' );
			$parts     = explode( '-', $basename );
			$locale    = array_pop( $parts );
			$text_domain = implode( '-', $parts );

			$mo_file = $dir . $basename . '.mo';
			$json_files = glob( $dir . $basename . '-*.json' );

			if ( ! isset( $projects[ $text_domain ] ) ) {
				$projects[ $text_domain ] = array(
					'text_domain' => $text_domain,
					'locales'     => array(),
				);
			}

			$projects[ $text_domain ]['locales'][] = array(
				'locale'      => $locale,
				'po_file'     => $file,
				'mo_file'     => file_exists( $mo_file ) ? $mo_file : null,
				'json_files'  => ! empty( $json_files ) ? $json_files : array(),
				'po_size'     => filesize( $file ),
			);
		}

		return array_values( $projects );
	}
}
