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

	public static function is_namespace_plugin_active( $namespace ) {
		$category = self::get_namespace_category( $namespace );

		if ( 'core' === $category ) {
			return true;
		}

		if ( 'other' === $category ) {
			return true;
		}

		$status = \Immens_MCP_Fortress\Access_Points\Access_Point_Schema::get_plugin_status();
		return isset( $status[ $category ] ) && ! empty( $status[ $category ] );
	}

	public static function get_namespace_description( $namespace ) {
		$descriptions = array(
			'wp/v2' => __( 'Posts, Pages, Media, Users, Comments, Categories, Tags, Settings, Blocks, Templates', 'immens-mcp-fortress' ),
			'wp/v3' => __( 'Patterns, Navigation, Global Styles', 'immens-mcp-fortress' ),
			'oembed/1.0' => __( 'oEmbed proxy endpoint', 'immens-mcp-fortress' ),
			'wp-site-health/v1' => __( 'Site Health checks and tests', 'immens-mcp-fortress' ),
			'wp-block-editor/v1' => __( 'Block editor settings and URL details', 'immens-mcp-fortress' ),
			'wc/v3' => __( 'Products, Orders, Customers, Coupons, Reports, Settings, Shipping', 'immens-mcp-fortress' ),
			'wc/v2' => __( 'WooCommerce legacy v2 API', 'immens-mcp-fortress' ),
			'wc/v1' => __( 'WooCommerce legacy v1 API', 'immens-mcp-fortress' ),
			'wc-admin' => __( 'WooCommerce Admin reports and analytics', 'immens-mcp-fortress' ),
			'wc-telemetry' => __( 'WooCommerce usage tracking', 'immens-mcp-fortress' ),
			'wc-analytics' => __( 'WooCommerce Analytics data', 'immens-mcp-fortress' ),
			'wc/store' => __( 'WooCommerce Store API (cart, checkout)', 'immens-mcp-fortress' ),
			'wc/store/v1' => __( 'WooCommerce Store API v1', 'immens-mcp-fortress' ),
			'wc/private' => __( 'WooCommerce private/internal endpoints', 'immens-mcp-fortress' ),
			'wc/pos/v1/catalog' => __( 'WooCommerce POS catalog', 'immens-mcp-fortress' ),
			'wccom-site/v3' => __( 'WooCommerce.com site connection', 'immens-mcp-fortress' ),
			'yoast/v1' => __( 'Yoast SEO: meta, sitemaps, redirects, social', 'immens-mcp-fortress' ),
			'rankmath/v1' => __( 'Rank Math SEO: meta, sitemap, analytics', 'immens-mcp-fortress' ),
			'loco/v1' => __( 'Loco Translate: translations, PO/MO files', 'immens-mcp-fortress' ),
			'contact-form-7/v1' => __( 'Contact Form 7: forms, submissions', 'immens-mcp-fortress' ),
			'cf7/v1' => __( 'Contact Form 7: forms, submissions', 'immens-mcp-fortress' ),
			'acf/v3' => __( 'Advanced Custom Fields: field groups, fields', 'immens-mcp-fortress' ),
			'elementor/v1' => __( 'Elementor: templates, global settings, fonts', 'immens-mcp-fortress' ),
			'greenshift/v1' => __( 'Greenshift: blocks, stylebook, AI settings', 'immens-mcp-fortress' ),
			'stackable/v3' => __( 'Stackable: blocks, global colors, typography', 'immens-mcp-fortress' ),
			'trp/v1' => __( 'TranslatePress: translations, languages', 'immens-mcp-fortress' ),
			'cb/v1' => __( 'ClassyBlocks: animations, packs', 'immens-mcp-fortress' ),
			'ps/v1' => __( 'Primary Source: RAG content, profiles, logs', 'immens-mcp-fortress' ),
			'imi/v1' => __( 'Immens Integration: modules, notifications, QR', 'immens-mcp-fortress' ),
			'crm/v1' => __( 'Immens CRM: contacts, pipelines, automations', 'immens-mcp-fortress' ),
			'tsf/v1' => __( 'The SEO Framework: meta, sitemap, social', 'immens-mcp-fortress' ),
			'code-snippets/v2' => __( 'Code Snippets: snippets CRUD', 'immens-mcp-fortress' ),
			'stl/v1' => __( 'SI Licenze: license management API', 'immens-mcp-fortress' ),
			'jetpack/v4' => __( 'Jetpack: stats, subscriptions, related posts', 'immens-mcp-fortress' ),
			'wp-abilities/v1' => __( 'WordPress user abilities/capabilities', 'immens-mcp-fortress' ),
		);

		$namespace = self::normalize_namespace( $namespace );

		if ( isset( $descriptions[ $namespace ] ) ) {
			return $descriptions[ $namespace ];
		}

		return '';
	}
}
