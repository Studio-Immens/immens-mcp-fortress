<?php
namespace Immens_MCP_Fortress\Tools\LocoTranslate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Project extends Base_Tool {

	public function get_name() {
		return 'loco_get_project';
	}

	public function get_description() {
		return 'Get details and translation statistics for a Loco Translate project.';
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
				'text_domain' => array(
					'type'        => 'string',
					'description' => 'Text domain of the project',
				),
				'type'        => array(
					'type'        => 'string',
					'description' => 'Project type: plugin or theme',
					'enum'        => array( 'plugin', 'theme' ),
				),
			),
			'required'   => array( 'text_domain', 'type' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'text_domain', 'type' ) );

		$text_domain = $arguments['text_domain'];
		$type        = $arguments['type'];

		$base_dir = ( 'theme' === $type ) ? WP_LANG_DIR . '/themes/' : WP_LANG_DIR . '/plugins/';

		if ( ! is_dir( $base_dir ) ) {
			return array(
				'text_domain' => $text_domain,
				'type'        => $type,
				'locales'     => array(),
				'total_po'    => 0,
				'total_mo'    => 0,
			);
		}

		$po_files = glob( $base_dir . $text_domain . '-*.po' );
		$locales  = array();

		foreach ( $po_files as $po_file ) {
			$basename = basename( $po_file, '.po' );
			$parts    = explode( '-', $basename );
			$locale   = array_pop( $parts );

			$mo_file = $base_dir . $basename . '.mo';

			$stats = $this->count_strings( $po_file );

			$locales[] = array(
				'locale'          => $locale,
				'po_file'         => $po_file,
				'mo_file'         => file_exists( $mo_file ) ? $mo_file : null,
				'po_exists'       => file_exists( $po_file ),
				'mo_exists'       => file_exists( $mo_file ),
				'total_strings'   => $stats['total'],
				'translated'      => $stats['translated'],
				'untranslated'    => $stats['untranslated'],
				'fuzzy'           => $stats['fuzzy'],
				'percent'         => $stats['total'] > 0 ? round( ( $stats['translated'] / $stats['total'] ) * 100, 1 ) : 0,
			);
		}

		return array(
			'text_domain' => $text_domain,
			'type'        => $type,
			'locales'     => $locales,
			'total_po'    => count( $po_files ),
			'total_mo'    => count( array_filter( $po_files, function ( $f ) use ( $base_dir ) {
				$mo = $base_dir . basename( $f, '.po' ) . '.mo';
				return file_exists( $mo );
			} ) ),
		);
	}

	private function count_strings( $po_file ) {
		$total       = 0;
		$translated  = 0;
		$untranslated = 0;
		$fuzzy       = 0;

		if ( ! file_exists( $po_file ) ) {
			return array( 'total' => 0, 'translated' => 0, 'untranslated' => 0, 'fuzzy' => 0 );
		}

		$content = file_get_contents( $po_file );
		$lines   = explode( "\n", $content );

		$in_msgstr  = false;
		$current_translated = false;
		$current_fuzzy      = false;

		foreach ( $lines as $line ) {
			$trimmed = trim( $line );

			if ( 0 === strpos( $trimmed, '#, fuzzy' ) ) {
				$current_fuzzy = true;
			}

			if ( 0 === strpos( $trimmed, 'msgid "' ) && false === strpos( $trimmed, 'msgid ""' ) ) {
				if ( $in_msgstr ) {
					$total++;
					if ( $current_translated ) {
						$translated++;
					} else {
						$untranslated++;
					}
					if ( $current_fuzzy ) {
						$fuzzy++;
					}
				}
				$in_msgstr          = false;
				$current_translated = false;
				$current_fuzzy      = false;
			}

			if ( 0 === strpos( $trimmed, 'msgstr "' ) ) {
				$in_msgstr = true;
				if ( $trimmed !== 'msgstr ""' ) {
					$current_translated = true;
				}
			}
		}

		if ( $in_msgstr ) {
			$total++;
			if ( $current_translated ) {
				$translated++;
			} else {
				$untranslated++;
			}
			if ( $current_fuzzy ) {
				$fuzzy++;
			}
		}

		return array( 'total' => $total, 'translated' => $translated, 'untranslated' => $untranslated, 'fuzzy' => $fuzzy );
	}
}
