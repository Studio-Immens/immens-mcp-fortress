<?php
namespace Immens_MCP_Fortress\Access_Points;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Access_Point_Schema {

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'immens_mcp_access_points';
	}

	public static function get_columns() {
		return array(
			'id',
			'name',
			'api_key_hash',
			'api_key_prefix',
			'is_enabled',
			'ip_whitelist',
			'tool_permissions',
			'wp_user_id',
			'rate_limit',
			'is_pro',
			'created_at',
			'updated_at',
			'last_used_at',
		);
	}

	public static function get_default_tool_permissions() {
		return array(
			'posts'              => array( 'read' => true, 'write' => true ),
			'pages'              => array( 'read' => true, 'write' => true ),
			'media'              => array( 'read' => true, 'write' => true ),
			'comments'           => array( 'read' => true, 'write' => true ),
			'users'              => array( 'read' => true, 'write' => false ),
			'taxonomy'           => array( 'read' => true, 'write' => true ),
			'menus'              => array( 'read' => true, 'write' => false ),
			'blocks'             => array( 'read' => true, 'write' => true ),
			'templates'          => array( 'read' => true, 'write' => false ),
			'styles'             => array( 'read' => true, 'write' => false ),
			'plugins'            => array( 'read' => true, 'write' => false ),
			'themes'             => array( 'read' => true, 'write' => false ),
			'site'               => array( 'read' => true, 'write' => false ),
			'search'             => array( 'read' => true, 'write' => false ),
			'revisions'          => array( 'read' => true, 'write' => false ),
			'meta'               => array( 'read' => true, 'write' => true ),
			'rest-api'           => array( 'read' => true, 'write' => true ),
			'woocommerce'        => array( 'read' => true, 'write' => false ),
			'yoast'              => array( 'read' => true, 'write' => true ),
			'rank-math'          => array( 'read' => true, 'write' => true ),
			'loco-translate'     => array( 'read' => true, 'write' => true ),
			'contact-form-7'     => array( 'read' => true, 'write' => false ),
			'polylang'           => array( 'read' => true, 'write' => true ),
			'primary-source'     => array( 'read' => true, 'write' => true ),
			'immens-integration' => array( 'read' => true, 'write' => true ),
			'immens-crm'         => array( 'read' => true, 'write' => true ),
			'classyblocks'       => array( 'read' => true, 'write' => true ),
			'seo-framework'      => array( 'read' => true, 'write' => true ),
			'greenshift'         => array( 'read' => true, 'write' => true ),
			'stackable'          => array( 'read' => true, 'write' => true ),
			'translatepress'     => array( 'read' => true, 'write' => true ),
			'elementor'          => array( 'read' => true, 'write' => true ),
			'acf'                => array( 'read' => true, 'write' => true ),
			'code-snippets'      => array( 'read' => true, 'write' => true ),
			'w3-total-cache'     => array( 'read' => true, 'write' => false ),
		);
	}

	public static function get_all_tool_categories() {
		return array(
			'posts'     => __( 'Posts', 'immens-mcp-fortress' ),
			'pages'     => __( 'Pages', 'immens-mcp-fortress' ),
			'media'     => __( 'Media', 'immens-mcp-fortress' ),
			'comments'  => __( 'Comments', 'immens-mcp-fortress' ),
			'users'     => __( 'Users', 'immens-mcp-fortress' ),
			'taxonomy'  => __( 'Taxonomy', 'immens-mcp-fortress' ),
			'menus'     => __( 'Menus', 'immens-mcp-fortress' ),
			'blocks'    => __( 'Blocks', 'immens-mcp-fortress' ),
			'templates' => __( 'Templates', 'immens-mcp-fortress' ),
			'styles'    => __( 'Global Styles', 'immens-mcp-fortress' ),
			'plugins'   => __( 'Plugins', 'immens-mcp-fortress' ),
			'themes'    => __( 'Themes', 'immens-mcp-fortress' ),
			'site'      => __( 'Site Settings', 'immens-mcp-fortress' ),
			'search'    => __( 'Search', 'immens-mcp-fortress' ),
			'revisions' => __( 'Revisions', 'immens-mcp-fortress' ),
			'meta'      => __( 'Post Meta', 'immens-mcp-fortress' ),
			'rest-api'  => __( 'REST API Gateway', 'immens-mcp-fortress' ),
			'woocommerce' => __( 'WooCommerce', 'immens-mcp-fortress' ),
			'yoast'     => __( 'Yoast SEO', 'immens-mcp-fortress' ),
			'rank-math' => __( 'Rank Math SEO', 'immens-mcp-fortress' ),
			'loco-translate' => __( 'Loco Translate', 'immens-mcp-fortress' ),
			'contact-form-7' => __( 'Contact Form 7', 'immens-mcp-fortress' ),
			'polylang'  => __( 'Polylang', 'immens-mcp-fortress' ),
			'primary-source'     => __( 'Primary Source (Pro)', 'immens-mcp-fortress' ),
			'immens-integration' => __( 'Immens Integration (Pro)', 'immens-mcp-fortress' ),
			'immens-crm'         => __( 'Immens CRM (Pro)', 'immens-mcp-fortress' ),
			'classyblocks'       => __( 'ClassyBlocks (Pro)', 'immens-mcp-fortress' ),
			'seo-framework'      => __( 'SEO Framework (Pro)', 'immens-mcp-fortress' ),
			'greenshift'         => __( 'Greenshift (Pro)', 'immens-mcp-fortress' ),
			'stackable'          => __( 'Stackable (Pro)', 'immens-mcp-fortress' ),
			'translatepress'     => __( 'TranslatePress (Pro)', 'immens-mcp-fortress' ),
			'elementor'          => __( 'Elementor (Pro)', 'immens-mcp-fortress' ),
			'acf'                => __( 'ACF (Pro)', 'immens-mcp-fortress' ),
			'code-snippets'        => __( 'Code Snippets', 'immens-mcp-fortress' ),
			'w3-total-cache'      => __( 'W3 Total Cache', 'immens-mcp-fortress' ),
			'keyword-explorer'    => __( 'Keyword Explorer', 'immens-mcp-fortress' ),
			'fast-product-importer' => __( 'Fast Product Importer', 'immens-mcp-fortress' ),
			'license-system'      => __( 'License System', 'immens-mcp-fortress' ),
		);
	}

	public static function tool_permissions_to_allowed_tools( $permissions ) {
		if ( empty( $permissions ) || ! is_array( $permissions ) ) {
			return array( '*' );
		}

		$allowed = array();

		$category_to_prefixes = array(
			'posts'     => array( 'wp_list_posts', 'wp_get_post', 'wp_create_post', 'wp_update_post', 'wp_delete_post', 'wp_count_posts', 'wp_get_full_post', 'wp_find_replace_post' ),
			'pages'     => array( 'wp_list_pages', 'wp_get_page', 'wp_create_page', 'wp_update_page', 'wp_delete_page', 'wp_count_pages' ),
			'media'     => array( 'wp_list_media', 'wp_get_media', 'wp_upload_media', 'wp_upload_media_url', 'wp_update_media', 'wp_delete_media', 'wp_count_media' ),
			'comments'  => array( 'wp_list_comments', 'wp_get_comment', 'wp_create_comment', 'wp_update_comment', 'wp_delete_comment', 'wp_approve_comment', 'wp_spam_comment', 'wp_trash_comment' ),
			'users'     => array( 'wp_list_users', 'wp_get_user', 'wp_create_user', 'wp_update_user', 'wp_delete_user' ),
			'taxonomy'  => array( 'wp_list_categories', 'wp_get_category', 'wp_create_category', 'wp_update_category', 'wp_delete_category', 'wp_list_tags', 'wp_get_tag', 'wp_create_tag', 'wp_update_tag', 'wp_delete_tag', 'wp_list_terms', 'wp_get_term', 'wp_create_term', 'wp_update_term', 'wp_delete_term', 'wp_get_term_meta', 'wp_update_term_meta', 'wp_delete_term_meta' ),
			'menus'     => array( 'wp_list_menus', 'wp_get_menu', 'wp_create_menu', 'wp_update_menu', 'wp_delete_menu', 'wp_list_menu_items', 'wp_create_menu_item', 'wp_update_menu_item', 'wp_delete_menu_item' ),
			'blocks'    => array( 'wp_list_blocks', 'wp_get_block', 'wp_create_block', 'wp_update_block', 'wp_delete_block', 'wp_get_block_types' ),
			'templates' => array( 'wp_list_templates', 'wp_get_template', 'wp_update_template' ),
			'styles'    => array( 'wp_get_global_styles', 'wp_update_global_styles' ),
			'plugins'   => array( 'wp_list_plugins' ),
			'themes'    => array( 'wp_list_themes' ),
			'site'      => array( 'wp_get_site_settings', 'wp_update_site_settings' ),
			'search'    => array( 'wp_search' ),
			'revisions' => array( 'wp_list_revisions', 'wp_get_revision' ),
			'meta'      => array( 'wp_get_post_meta', 'wp_update_post_meta', 'wp_delete_post_meta', 'wp_add_post_terms' ),
			'rest-api'  => array( 'wp_rest_api_request' ),
			'woocommerce' => array( 'wc_*' ),
			'yoast'     => array( 'yoast_*' ),
			'rank-math' => array( 'rankmath_*' ),
			'loco-translate' => array( 'loco_*' ),
			'contact-form-7' => array( 'cf7_*' ),
			'polylang'  => array( 'polylang_*' ),
			'primary-source'     => array( 'ps_*' ),
			'immens-integration' => array( 'imi_*' ),
			'immens-crm'         => array( 'crm_*' ),
			'classyblocks'       => array( 'cb_*' ),
			'seo-framework'      => array( 'tsf_*' ),
			'greenshift'         => array( 'gs_*' ),
			'stackable'          => array( 'stk_*' ),
			'translatepress'     => array( 'trp_*' ),
			'elementor'          => array( 'elementor_*' ),
			'acf'                => array( 'acf_*' ),
			'code-snippets'        => array( 'cs_*' ),
			'w3-total-cache'      => array( 'w3tc_*' ),
			'keyword-explorer'    => array( 'ike_*' ),
			'fast-product-importer' => array( 'fi_*' ),
			'license-system'      => array( 'stl_*' ),
		);

		foreach ( $permissions as $category => $perms ) {
			if ( ! isset( $category_to_prefixes[ $category ] ) ) {
				continue;
			}

			$read  = ! empty( $perms['read'] );
			$write = ! empty( $perms['write'] );

			foreach ( $category_to_prefixes[ $category ] as $tool ) {
				if ( false !== strpos( $tool, '*' ) ) {
					if ( $read ) {
						$allowed[] = $tool;
					}
					if ( $write ) {
						$allowed[] = $tool;
					}
				} elseif ( strpos( $tool, 'list' ) !== false || strpos( $tool, 'get' ) !== false || strpos( $tool, 'count' ) !== false || strpos( $tool, 'search' ) !== false ) {
					if ( $read ) {
						$allowed[] = $tool;
					}
				} else {
					if ( $write ) {
						$allowed[] = $tool;
					}
				}
			}
		}

		if ( empty( $allowed ) ) {
			$allowed[] = '*';
		}

		return $allowed;
	}

	public static function get_tool_dir_plugin_map() {
		return array(
			'woocommerce'        => 'woocommerce',
			'yoast'              => 'yoast',
			'rank-math'          => 'rank-math',
			'loco-translate'     => 'loco-translate',
			'contact-form-7'     => 'contact-form-7',
			'polylang'           => 'polylang',
			'primary-source'     => 'primary-source',
			'immens-integration' => 'immens-integration',
			'immens-crm'         => 'immens-crm',
			'classyblocks'       => 'classyblocks',
			'seo-framework'      => 'seo-framework',
			'greenshift'         => 'greenshift',
			'stackable'          => 'stackable',
			'translatepress'     => 'translatepress',
			'elementor'          => 'elementor',
			'acf'                => 'acf',
			'code-snippets'        => 'code-snippets',
			'w3-total-cache'      => 'w3-total-cache',
			'keyword-explorer'    => 'keyword-explorer',
			'fast-product-importer' => 'fast-product-importer',
			'license-system'      => 'license-system',
		);
	}

	public static function is_plugin_active_for_tool_dir( $dir ) {
		$map    = self::get_tool_dir_plugin_map();
		$status = self::get_plugin_status();
		if ( isset( $map[ $dir ] ) ) {
			return ! empty( $status[ $map[ $dir ] ] );
		}
		return true;
	}

	public static function get_plugin_status() {
		$status = array(
			'posts'              => true,
			'pages'              => true,
			'media'              => true,
			'comments'           => true,
			'users'              => true,
			'taxonomy'           => true,
			'menus'              => true,
			'blocks'             => true,
			'templates'          => true,
			'styles'             => true,
			'plugins'            => true,
			'themes'             => true,
			'site'               => true,
			'search'             => true,
			'revisions'          => true,
			'meta'               => true,
		);

		$status['woocommerce']        = class_exists( 'WooCommerce' );
		$status['yoast']              = defined( 'WPSEO_VERSION' );
		$status['rank-math']          = defined( 'RANK_MATH_VERSION' );
		$status['loco-translate']     = function_exists( 'loco_plugin_auto_update' ) || function_exists( 'loco_plugin_' );
		$status['contact-form-7']     = defined( 'WPCF7_VERSION' );
		$status['polylang']           = defined( 'POLYLANG_VERSION' );
		$status['primary-source']     = defined( 'PS_VERSION' ) || class_exists( 'PrimarySource\Plugin' );
		$status['immens-integration'] = defined( 'IMMENS_INTEGRATION_VERSION' );
		$status['immens-crm']         = defined( 'IMMENS_CRM_VERSION' );
		$status['classyblocks']       = defined( 'CB_PRO_VERSION' );
		$status['seo-framework']      = defined( 'THE_SEO_FRAMEWORK_VERSION' ) || function_exists( 'the_seo_framework' );
		$status['greenshift']         = defined( 'GREENSHIFT_DIR_URL' );
		$status['stackable']          = defined( 'STACKABLE_VERSION' );
		$status['translatepress']     = defined( 'TRP_PLUGIN_VERSION' );
		$status['elementor']          = defined( 'ELEMENTOR_VERSION' );
		$status['acf']                = class_exists( 'ACF' );
		$status['rest-api']            = true;
		$status['code-snippets']        = defined( 'CODE_SNIPPETS_VERSION' ) || function_exists( 'code_snippets' ) || post_type_exists( 'code-snippets' );
		$status['w3-total-cache']       = defined( 'W3TC' ) || function_exists( 'w3tc_flush_all' );
		$status['keyword-explorer']     = defined( 'IKE_VERSION' ) || class_exists( 'IKE_REST_API' );
		$status['fast-product-importer'] = defined( 'SIFP_VERSION' ) || class_exists( 'SIFlashProducts\Core\Plugin' );
		$status['license-system']       = defined( 'STL_VERSION' ) || class_exists( 'STL_REST' );
		$status['studio-immens-css']    = defined( 'SICC_VERSION' ) || class_exists( 'SICC_Plugin' );

		return $status;
	}
}
