<?php
namespace Immens_MCP_Fortress\Tools\LocoTranslate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Export extends Base_Tool {

	public function get_name() {
		return 'loco_export';
	}

	public function get_description() {
		return 'Export .po and .mo translation files for a project. Returns file contents or triggers compilation.';
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
			),
			'required'   => array( 'text_domain', 'type' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'text_domain', 'type' ) );

		$text_domain = $arguments['text_domain'];
		$locale      = isset( $arguments['locale'] ) ? $arguments['locale'] : 'it_IT';
		$type        = $arguments['type'];

		$base_dir = ( 'theme' === $type ) ? WP_LANG_DIR . '/themes/' : WP_LANG_DIR . '/plugins/';
		$po_file  = $base_dir . $text_domain . '-' . $locale . '.po';
		$mo_file  = $base_dir . $text_domain . '-' . $locale . '.mo';

		if ( ! file_exists( $po_file ) ) {
			throw new \RuntimeException( sprintf( 'PO file not found: %s', $po_file ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$mo_compiled = false;
		if ( file_exists( $mo_file ) ) {
			$mo_compiled = true;
		} else {
			$mo_compiled = $this->compile_mo( $po_file, $mo_file );
		}

		$po_content = file_get_contents( $po_file );
		$mo_content = file_exists( $mo_file ) ? base64_encode( file_get_contents( $mo_file ) ) : null;

		return array(
			'text_domain'  => $text_domain,
			'locale'       => $locale,
			'type'         => $type,
			'po_file'      => $po_file,
			'mo_file'      => file_exists( $mo_file ) ? $mo_file : null,
			'mo_compiled'  => $mo_compiled,
			'po_content'   => $po_content,
			'mo_base64'    => $mo_content,
			'po_size'      => filesize( $po_file ),
			'mo_size'      => file_exists( $mo_file ) ? filesize( $mo_file ) : 0,
		);
	}

	private function compile_mo( $po_file, $mo_file ) {
		if ( function_exists( 'loco_compile' ) ) {
			loco_compile( $po_file, $mo_file );
			return file_exists( $mo_file );
		}

		if ( function_exists( 'exec' ) ) {
			$po_path = escapeshellarg( $po_file );
			$mo_path = escapeshellarg( $mo_file );
			exec( "msgfmt -o {$mo_path} {$po_path} 2>&1", $output, $return_code );
			if ( 0 === $return_code && file_exists( $mo_file ) ) {
				return true;
			}
		}

		if ( ! class_exists( 'MO' ) ) {
			require_once ABSPATH . 'wp-includes/pomo/mo.php';
		}

		return $this->compile_mo_fallback( $po_file, $mo_file );
	}

	private function compile_mo_fallback( $po_file, $mo_file ) {
		$content = file_get_contents( $po_file );
		$lines   = explode( "\n", $content );
		$entries = array();
		$current_msgid  = '';
		$current_msgstr = '';
		$in_msgid  = false;
		$in_msgstr = false;

		foreach ( $lines as $line ) {
			$trimmed = trim( $line );
			if ( preg_match( '/^msgid\s+"(.*)"$/', $trimmed, $m ) ) {
				if ( '' !== $current_msgid ) {
					$entries[ $current_msgid ] = $current_msgstr;
				}
				$current_msgid  = $m[1];
				$current_msgstr = '';
				$in_msgid  = true;
				$in_msgstr = false;
			} elseif ( preg_match( '/^msgstr\s+"(.*)"$/', $trimmed, $m ) ) {
				$current_msgstr = $m[1];
				$in_msgid  = false;
				$in_msgstr = true;
			} elseif ( $in_msgid && preg_match( '/^"(.*)"$/', $trimmed, $m ) ) {
				$current_msgid .= $m[1];
			} elseif ( $in_msgstr && preg_match( '/^"(.*)"$/', $trimmed, $m ) ) {
				$current_msgstr .= $m[1];
			}
		}

		if ( '' !== $current_msgid ) {
			$entries[ $current_msgid ] = $current_msgstr;
		}

		$mo = new \MO();
		foreach ( $entries as $original => $translation ) {
			$mo->add_entry( array( 'singular' => $original, 'translations' => array( $translation ) ) );
		}

		return $mo->export_to_file( $mo_file );
	}
}
