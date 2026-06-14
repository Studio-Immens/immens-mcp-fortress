<?php
namespace Immens_MCP_Fortress\Tools\LocoTranslate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Translate_String extends Base_Tool {

	public function get_name() {
		return 'loco_translate_string';
	}

	public function get_description() {
		return 'Add or update a translation string in a .po file and regenerate the .mo file.';
	}

	public function get_required_capability() {
		return 'manage_options';
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
				'original'    => array(
					'type'        => 'string',
					'description' => 'Original string (msgid)',
				),
				'translation' => array(
					'type'        => 'string',
					'description' => 'Translated string (msgstr)',
				),
				'locale'      => array(
					'type'        => 'string',
					'description' => 'Locale code (e.g. it_IT)',
				),
				'type'        => array(
					'type'        => 'string',
					'description' => 'Project type: plugin or theme',
					'enum'        => array( 'plugin', 'theme' ),
				),
			),
			'required'   => array( 'text_domain', 'original', 'translation', 'locale', 'type' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'text_domain', 'original', 'translation', 'locale', 'type' ) );

		$text_domain = $arguments['text_domain'];
		$original    = $arguments['original'];
		$translation = $arguments['translation'];
		$locale      = $arguments['locale'];
		$type        = $arguments['type'];

		$base_dir  = ( 'theme' === $type ) ? WP_LANG_DIR . '/themes/' : WP_LANG_DIR . '/plugins/';

		if ( ! is_dir( $base_dir ) ) {
			wp_mkdir_p( $base_dir );
		}

		$po_file = $base_dir . $text_domain . '-' . $locale . '.po';
		$mo_file = $base_dir . $text_domain . '-' . $locale . '.mo';

		if ( ! file_exists( $po_file ) ) {
			$this->create_po_file( $po_file, $text_domain, $locale );
		}

		$this->update_translation_in_po( $po_file, $original, $translation );
		$this->compile_mo( $po_file, $mo_file );

		return array(
			'success'     => true,
			'text_domain' => $text_domain,
			'locale'      => $locale,
			'original'    => $original,
			'translation' => $translation,
			'po_file'     => $po_file,
			'mo_file'     => $mo_file,
		);
	}

	private function create_po_file( $po_file, $text_domain, $locale ) {
		$content = <<<PO
msgid ""
msgstr ""
"Project-Id-Version: {$text_domain}\\n"
"Language: {$locale}\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"

PO;
		file_put_contents( $po_file, $content );
	}

	private function update_translation_in_po( $po_file, $original, $translation ) {
		$content = file_get_contents( $po_file );
		$escaped_original    = $this->escape_po_string( $original );
		$escaped_translation = $this->escape_po_string( $translation );

		$pattern = '/msgid "' . preg_quote( $escaped_original, '/' ) . '"(\s*msgstr "[^"]*")/';
		$replacement = 'msgid "' . $escaped_original . '"' . "\n" . 'msgstr "' . $escaped_translation . '"';

		if ( preg_match( $pattern, $content ) ) {
			$new_content = preg_replace( $pattern, $replacement, $content, 1 );
		} else {
			$new_entry = "\nmsgid \"{$escaped_original}\"\nmsgstr \"{$escaped_translation}\"\n";
			$new_content = $content . $new_entry;
		}

		file_put_contents( $po_file, $new_content );
	}

	private function compile_mo( $po_file, $mo_file ) {
		if ( function_exists( 'loco_compile' ) ) {
			loco_compile( $po_file, $mo_file );
		} elseif ( function_exists( 'exec' ) ) {
			$po_path = escapeshellarg( $po_file );
			$mo_path = escapeshellarg( $mo_file );
			exec( "msgfmt -o {$mo_path} {$po_path} 2>&1", $output, $return_code );
		} else {
			if ( ! class_exists( 'MO' ) ) {
				require_once ABSPATH . 'wp-includes/pomo/mo.php';
			}
			$this->fallback_mo_compile( $po_file, $mo_file );
		}
	}

	private function fallback_mo_compile( $po_file, $mo_file ) {
		$content = file_get_contents( $po_file );
		$lines   = explode( "\n", $content );
		$entries = array();
		$current_msgid  = '';
		$current_msgstr = '';
		$in_msgid  = false;
		$in_msgstr = false;

		foreach ( $lines as $line ) {
			$trimmed = trim( $line );

			if ( 0 === strpos( $trimmed, 'msgid "' ) ) {
				if ( $current_msgid && '' !== $current_msgid ) {
					$entries[ $current_msgid ] = $current_msgstr;
				}
				$current_msgid  = $this->extract_po_string( $trimmed );
				$current_msgstr = '';
				$in_msgid  = true;
				$in_msgstr = false;
				continue;
			}

			if ( 0 === strpos( $trimmed, 'msgstr "' ) ) {
				$current_msgstr = $this->extract_po_string( $trimmed );
				$in_msgid  = false;
				$in_msgstr = true;
				continue;
			}

			if ( $in_msgid && preg_match( '/^"[^"]*"$/', $trimmed ) ) {
				$current_msgid .= $this->extract_po_string( $trimmed );
			}

			if ( $in_msgstr && preg_match( '/^"[^"]*"$/', $trimmed ) ) {
				$current_msgstr .= $this->extract_po_string( $trimmed );
			}
		}

		if ( $current_msgid && '' !== $current_msgid ) {
			$entries[ $current_msgid ] = $current_msgstr;
		}

		$mo = new \MO();
		foreach ( $entries as $original => $translation ) {
			$mo->add_entry( array( 'singular' => $original, 'translations' => array( $translation ) ) );
		}
		$mo->export_to_file( $mo_file );
	}

	private function escape_po_string( $str ) {
		return str_replace( array( '\\', '"', "\n", "\r", "\t" ), array( '\\\\', '\\"', '\\n', '\\r', '\\t' ), $str );
	}

	private function extract_po_string( $line ) {
		preg_match( '/"((?:[^"\\\\]|\\\\.)*)"/', $line, $matches );
		$str = isset( $matches[1] ) ? $matches[1] : '';
		return str_replace( array( '\\n', '\\r', '\\t', '\\\\', '\\"' ), array( "\n", "\r", "\t", '\\', '"' ), $str );
	}
}
