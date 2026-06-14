<?php
namespace Immens_MCP_Fortress\Tools\LocoTranslate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class List_Translations extends Base_Tool {

	public function get_name() {
		return 'loco_list_translations';
	}

	public function get_description() {
		return 'List translated/untranslated strings from a .po file for a given text domain and locale.';
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
				'locale'      => array(
					'type'        => 'string',
					'description' => 'Locale code (e.g. it_IT)',
					'default'     => 'it_IT',
				),
				'type'        => array(
					'type'        => 'string',
					'description' => 'Project type: plugin or theme',
					'enum'        => array( 'plugin', 'theme' ),
				),
				'status'      => array(
					'type'        => 'string',
					'description' => 'Filter by translation status',
					'enum'        => array( 'all', 'untranslated', 'translated' ),
					'default'     => 'all',
				),
			),
			'required'   => array( 'text_domain', 'type' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'text_domain', 'type' ) );

		$text_domain = $arguments['text_domain'];
		$locale      = isset( $arguments['locale'] ) ? $arguments['locale'] : 'it_IT';
		$type        = $arguments['type'];
		$status      = isset( $arguments['status'] ) ? $arguments['status'] : 'all';

		$base_dir  = ( 'theme' === $type ) ? WP_LANG_DIR . '/themes/' : WP_LANG_DIR . '/plugins/';
		$po_file   = $base_dir . $text_domain . '-' . $locale . '.po';

		if ( ! file_exists( $po_file ) ) {
			throw new \RuntimeException( sprintf( 'PO file not found: %s', $po_file ) );
		}

		$strings = $this->parse_po_file( $po_file, $status );

		return array(
			'text_domain' => $text_domain,
			'locale'      => $locale,
			'type'        => $type,
			'po_file'     => $po_file,
			'total'       => count( $strings ),
			'strings'     => $strings,
		);
	}

	private function parse_po_file( $po_file, $status_filter ) {
		$content = file_get_contents( $po_file );
		$lines   = explode( "\n", $content );

		$strings     = array();
		$current     = null;
		$in_msgstr   = false;
		$msgid_lines = array();
		$msgstr_lines = array();
		$fuzzy       = false;

		foreach ( $lines as $line ) {
			$trimmed = $line;

			if ( 0 === strpos( $trimmed, '#, fuzzy' ) ) {
				$fuzzy = true;
				continue;
			}

			if ( 0 === strpos( $trimmed, '#~' ) ) {
				continue;
			}

			if ( 0 === strpos( $trimmed, '#' ) ) {
				continue;
			}

			if ( 0 === strpos( $trimmed, 'msgid "' ) ) {
				if ( null !== $current ) {
					$entry = $this->build_entry( $current, $status_filter, $fuzzy );
					if ( $entry ) {
						$strings[] = $entry;
					}
				}
				$current = array(
					'msgid'   => $this->extract_quoted( $trimmed ),
					'msgstr'  => '',
					'fuzzy'   => $fuzzy,
				);
				$in_msgstr = false;
				$fuzzy     = false;
				continue;
			}

			if ( 0 === strpos( $trimmed, 'msgstr "' ) ) {
				$in_msgstr = true;
				$current['msgstr'] = $this->extract_quoted( $trimmed );
				continue;
			}

			if ( $in_msgstr && 0 === strpos( $trimmed, '"' ) ) {
				$current['msgstr'] .= $this->extract_quoted( $trimmed );
				continue;
			}

			if ( ! $in_msgstr && 0 === strpos( $trimmed, '"' ) ) {
				$current['msgid'] .= $this->extract_quoted( $trimmed );
				continue;
			}
		}

		if ( null !== $current ) {
			$entry = $this->build_entry( $current, $status_filter, $fuzzy );
			if ( $entry ) {
				$strings[] = $entry;
			}
		}

		return $strings;
	}

	private function extract_quoted( $line ) {
		preg_match( '/"((?:[^"\\\\]|\\\\.)*)"/', $line, $matches );
		return isset( $matches[1] ) ? $matches[1] : '';
	}

	private function build_entry( $current, $status_filter, $fuzzy ) {
		if ( empty( $current['msgid'] ) ) {
			return null;
		}

		$is_translated = ! empty( $current['msgstr'] );

		if ( 'translated' === $status_filter && ! $is_translated ) {
			return null;
		}
		if ( 'untranslated' === $status_filter && $is_translated ) {
			return null;
		}

		return array(
			'msgid'      => $current['msgid'],
			'msgstr'     => $current['msgstr'],
			'fuzzy'      => $current['fuzzy'],
			'translated' => $is_translated,
			'status'     => $is_translated ? 'translated' : 'untranslated',
		);
	}
}
