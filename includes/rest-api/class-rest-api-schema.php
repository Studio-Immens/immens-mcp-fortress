<?php
namespace Immens_MCP_Fortress\REST_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API_Schema {

	public static function get_namespace_label( $namespace ) {
		$labels = array(
			'wp/v2'                => __( 'WordPress Core (v2)', 'immens-mcp-fortress' ),
			'wp/v3'                => __( 'WordPress Core (v3)', 'immens-mcp-fortress' ),
			'oembed/1.0'           => __( 'oEmbed', 'immens-mcp-fortress' ),
			'wp-site-health/v1'    => __( 'Site Health', 'immens-mcp-fortress' ),
			'wp-block-editor/v1'   => __( 'Block Editor', 'immens-mcp-fortress' ),
			'immens-mcp-fortress/v1' => __( 'MCP Fortress', 'immens-mcp-fortress' ),
			'wc/v3'                => __( 'WooCommerce (v3)', 'immens-mcp-fortress' ),
			'wc/v2'                => __( 'WooCommerce (v2)', 'immens-mcp-fortress' ),
			'wc/v1'                => __( 'WooCommerce (v1)', 'immens-mcp-fortress' ),
			'wc-admin'             => __( 'WooCommerce Admin', 'immens-mcp-fortress' ),
			'wc-telemetry'         => __( 'WooCommerce Telemetry', 'immens-mcp-fortress' ),
			'yoast/v1'             => __( 'Yoast SEO', 'immens-mcp-fortress' ),
			'rankmath/v1'          => __( 'Rank Math SEO', 'immens-mcp-fortress' ),
			'loco/v1'              => __( 'Loco Translate', 'immens-mcp-fortress' ),
			'contact-form-7/v1'    => __( 'Contact Form 7', 'immens-mcp-fortress' ),
			'wp/v2/polylang'       => __( 'Polylang', 'immens-mcp-fortress' ),
			'cf7/v1'               => __( 'Contact Form 7', 'immens-mcp-fortress' ),
			'acf/v3'               => __( 'ACF', 'immens-mcp-fortress' ),
			'elementor/v1'         => __( 'Elementor', 'immens-mcp-fortress' ),
			'greenshift/v1'        => __( 'Greenshift', 'immens-mcp-fortress' ),
			'stackable/v3'         => __( 'Stackable', 'immens-mcp-fortress' ),
			'trp/v1'               => __( 'TranslatePress', 'immens-mcp-fortress' ),
			'cb/v1'                => __( 'ClassyBlocks', 'immens-mcp-fortress' ),
			'ps/v1'                => __( 'Primary Source', 'immens-mcp-fortress' ),
			'imi/v1'               => __( 'Immens Integration', 'immens-mcp-fortress' ),
			'crm/v1'               => __( 'Immens CRM', 'immens-mcp-fortress' ),
			'tsf/v1'               => __( 'The SEO Framework', 'immens-mcp-fortress' ),
			'code-snippets/v2'     => __( 'Code Snippets', 'immens-mcp-fortress' ),
		);

		$namespace = self::normalize_namespace( $namespace );

		if ( isset( $labels[ $namespace ] ) ) {
			return $labels[ $namespace ];
		}

		$parts = explode( '/', $namespace, 2 );
		if ( count( $parts ) === 2 && isset( $labels[ $parts[0] . '/*' ] ) ) {
			return $labels[ $parts[0] . '/*' ];
		}

		return $namespace;
	}

	public static function get_namespace_category( $namespace ) {
		$namespace = self::normalize_namespace( $namespace );
		$parts     = explode( '/', $namespace, 2 );
		$root      = $parts[0];

		$known = array(
			'wp'                => 'core',
			'oembed'            => 'core',
			'wp-site-health'    => 'core',
			'wp-block-editor'   => 'core',
			'immens-mcp-fortress' => 'core',
			'wc'                => 'woocommerce',
			'yoast'             => 'yoast',
			'rankmath'          => 'rank-math',
			'loco'              => 'loco-translate',
			'contact-form-7'    => 'contact-form-7',
			'cf7'               => 'contact-form-7',
			'polylang'          => 'polylang',
			'acf'               => 'acf',
			'elementor'         => 'elementor',
			'greenshift'        => 'greenshift',
			'stackable'         => 'stackable',
			'trp'               => 'translatepress',
			'cb'                => 'classyblocks',
			'ps'                => 'primary-source',
			'imi'               => 'immens-integration',
			'crm'               => 'immens-crm',
			'tsf'               => 'seo-framework',
			'code-snippets'     => 'code-snippets',
		);

		if ( isset( $known[ $root ] ) ) {
			return $known[ $root ];
		}

		return 'other';
	}

	public static function normalize_namespace( $namespace ) {
		return ltrim( $namespace, '/' );
	}

	public static function get_core_namespaces() {
		return array(
			'wp/v2',
			'wp/v3',
			'oembed/1.0',
			'wp-site-health/v1',
			'wp-block-editor/v1',
		);
	}

	public static function get_default_namespace_permissions() {
		return array();
	}

	public static function should_show_namespace( $namespace ) {
		$namespace = self::normalize_namespace( $namespace );
		$skip      = array(
			'immens-mcp-fortress/v1',
		);
		if ( in_array( $namespace, $skip, true ) ) {
			return false;
		}
		return true;
	}
}
