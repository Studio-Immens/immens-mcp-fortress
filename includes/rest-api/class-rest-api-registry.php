<?php
namespace Immens_MCP_Fortress\REST_API;

use Immens_MCP_Fortress\Access_Points\Access_Point_Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API_Registry {

	private $routes_cache       = null;
	private $namespace_cache    = null;

	public function get_all_namespaces() {
		if ( null !== $this->namespace_cache ) {
			return $this->namespace_cache;
		}

		$server = rest_get_server();
		$namespaces = $server->get_namespaces();

		if ( empty( $namespaces ) ) {
			$live_routes = $server->get_routes();
			$fallback    = $this->get_registered_routes_fallback();

			$routes = $live_routes;
			foreach ( $fallback as $ns => $dummy ) {
				if ( ! isset( $routes[ $ns ] ) ) {
					$routes[ $ns ] = array( array() );
				}
			}

			$seen = array();
			foreach ( $routes as $route => $handlers ) {
				$route    = ltrim( $route, '/' );
				$segments = explode( '/', $route );

				if ( count( $segments ) >= 3 ) {
					$namespace = $segments[0] . '/' . $segments[1];
				} elseif ( count( $segments ) === 2 ) {
					$namespace = $segments[0] . '/' . $segments[1];
				} else {
					$namespace = $route;
				}

				if ( ! isset( $seen[ $namespace ] ) ) {
					$seen[ $namespace ] = true;
					$namespaces[] = $namespace;
				}
			}
		}

		sort( $namespaces );
		$this->namespace_cache = $namespaces;

		return $namespaces;
	}

	public function get_namespaces_by_category() {
		$namespaces  = $this->get_all_namespaces();
		$categorized = array();

		foreach ( $namespaces as $ns ) {
			if ( ! REST_API_Schema::should_show_namespace( $ns ) ) {
				continue;
			}

			$category = REST_API_Schema::get_namespace_category( $ns );
			$active   = REST_API_Schema::is_namespace_plugin_active( $ns );

			if ( ! isset( $categorized[ $category ] ) ) {
				$categorized[ $category ] = array(
					'active'     => $active,
					'label'      => self::get_category_label( $category ),
					'namespaces' => array(),
				);
			}

			$categorized[ $category ]['namespaces'][] = $ns;
		}

		uksort( $categorized, function ( $a, $b ) {
			$order = array( 'core' => 0, 'woocommerce' => 1, 'yoast' => 2, 'rank-math' => 3 );
			$a_pos = isset( $order[ $a ] ) ? $order[ $a ] : 100;
			$b_pos = isset( $order[ $b ] ) ? $order[ $b ] : 100;
			return $a_pos - $b_pos;
		} );

		return $categorized;
	}

	public function get_namespace_permissions( $access_point_id ) {
		$access_point_manager = new \Immens_MCP_Fortress\Access_Points\Access_Point_Manager();
		$access_point         = $access_point_manager->get_access_point( $access_point_id );

		if ( ! $access_point || empty( $access_point['tool_permissions'] ) ) {
			return null;
		}

		$all_permissions = json_decode( $access_point['tool_permissions'], true );
		if ( ! is_array( $all_permissions ) ) {
			return null;
		}

		$permissions = array();
		foreach ( $all_permissions as $key => $value ) {
			if ( 0 === strpos( $key, 'ns:' ) && is_array( $value ) ) {
				$permissions[ substr( $key, 3 ) ] = $value;
			}
		}

		return $permissions;
	}

	public function is_namespace_allowed( $namespace, $access_point ) {
		if ( ! is_array( $access_point ) ) {
			return true;
		}

		$namespace = REST_API_Schema::normalize_namespace( $namespace );

		if ( empty( $access_point['tool_permissions'] ) ) {
			return true;
		}

		$all_permissions = json_decode( $access_point['tool_permissions'], true );
		if ( ! is_array( $all_permissions ) ) {
			return true;
		}

		if ( ! empty( $all_permissions['rest-api'] ) ) {
			if ( empty( $all_permissions['rest-api']['read'] )
				&& empty( $all_permissions['rest-api']['write'] ) ) {
				return false;
			}
		}

		$ns_key = 'ns:' . $namespace;
		if ( isset( $all_permissions[ $ns_key ] ) && is_array( $all_permissions[ $ns_key ] ) ) {
			$ns_perms = $all_permissions[ $ns_key ];
			if ( empty( $ns_perms['read'] ) && empty( $ns_perms['write'] ) ) {
				return false;
			}
		}

		return true;
	}

	public function is_write_allowed( $namespace, $access_point ) {
		if ( ! is_array( $access_point ) ) {
			return true;
		}

		if ( empty( $access_point['tool_permissions'] ) ) {
			return true;
		}

		$all_permissions = json_decode( $access_point['tool_permissions'], true );
		if ( ! is_array( $all_permissions ) ) {
			return true;
		}

		if ( ! empty( $all_permissions['rest-api'] ) ) {
			if ( empty( $all_permissions['rest-api']['write'] ) ) {
				return false;
			}
		}

		$ns_key = 'ns:' . REST_API_Schema::normalize_namespace( $namespace );
		if ( isset( $all_permissions[ $ns_key ] ) && is_array( $all_permissions[ $ns_key ] ) ) {
			if ( empty( $all_permissions[ $ns_key ]['write'] ) ) {
				return false;
			}
		}

		return true;
	}

	private function get_registered_routes_fallback() {
		$namespaces = REST_API_Schema::get_core_namespaces();

		$status = Access_Point_Schema::get_plugin_status();

		if ( ! empty( $status['woocommerce'] ) ) {
			$namespaces[] = 'wc/v3';
			$namespaces[] = 'wc/v2';
			$namespaces[] = 'wc/v1';
			$namespaces[] = 'wc-admin';
		}

		if ( ! empty( $status['yoast'] ) ) {
			$namespaces[] = 'yoast/v1';
		}

		if ( ! empty( $status['rank-math'] ) ) {
			$namespaces[] = 'rankmath/v1';
		}

		if ( ! empty( $status['acf'] ) ) {
			$namespaces[] = 'acf/v3';
		}

		if ( ! empty( $status['elementor'] ) ) {
			$namespaces[] = 'elementor/v1';
		}

		if ( ! empty( $status['greenshift'] ) ) {
			$namespaces[] = 'greenshift/v1';
		}

		if ( ! empty( $status['stackable'] ) ) {
			$namespaces[] = 'stackable/v3';
		}

		if ( ! empty( $status['translatepress'] ) ) {
			$namespaces[] = 'trp/v1';
		}

		if ( ! empty( $status['seo-framework'] ) ) {
			$namespaces[] = 'tsf/v1';
		}

		if ( ! empty( $status['classyblocks'] ) ) {
			$namespaces[] = 'cb/v1';
		}

		if ( ! empty( $status['primary-source'] ) ) {
			$namespaces[] = 'ps/v1';
		}

		if ( ! empty( $status['immens-integration'] ) ) {
			$namespaces[] = 'imi/v1';
		}

		if ( ! empty( $status['immens-crm'] ) ) {
			$namespaces[] = 'crm/v1';
		}

		if ( ! empty( $status['loco-translate'] ) ) {
			$namespaces[] = 'loco/v1';
		}

		if ( ! empty( $status['contact-form-7'] ) ) {
			$namespaces[] = 'contact-form-7/v1';
		}

		if ( ! empty( $status['polylang'] ) ) {
			$namespaces[] = 'wp/v2/polylang';
		}

		if ( ! empty( $status['code-snippets'] ) ) {
			$namespaces[] = 'code-snippets/v2';
		}

		$routes = array();
		foreach ( $namespaces as $ns ) {
			$routes[ $ns ] = array();
		}

		return $routes;
	}

	private static function get_category_label( $category ) {
		$labels = array(
			'core'         => __( 'WordPress Core', 'immens-mcp-fortress' ),
			'woocommerce'  => __( 'WooCommerce', 'immens-mcp-fortress' ),
			'yoast'        => __( 'Yoast SEO', 'immens-mcp-fortress' ),
			'rank-math'    => __( 'Rank Math SEO', 'immens-mcp-fortress' ),
			'loco-translate' => __( 'Loco Translate', 'immens-mcp-fortress' ),
			'contact-form-7' => __( 'Contact Form 7', 'immens-mcp-fortress' ),
			'polylang'     => __( 'Polylang', 'immens-mcp-fortress' ),
			'acf'          => __( 'ACF', 'immens-mcp-fortress' ),
			'elementor'    => __( 'Elementor', 'immens-mcp-fortress' ),
			'greenshift'   => __( 'Greenshift', 'immens-mcp-fortress' ),
			'stackable'    => __( 'Stackable', 'immens-mcp-fortress' ),
			'translatepress' => __( 'TranslatePress', 'immens-mcp-fortress' ),
			'classyblocks' => __( 'ClassyBlocks', 'immens-mcp-fortress' ),
			'primary-source' => __( 'Primary Source', 'immens-mcp-fortress' ),
			'immens-integration' => __( 'Immens Integration', 'immens-mcp-fortress' ),
			'immens-crm'   => __( 'Immens CRM', 'immens-mcp-fortress' ),
			'seo-framework' => __( 'The SEO Framework', 'immens-mcp-fortress' ),
			'code-snippets' => __( 'Code Snippets', 'immens-mcp-fortress' ),
			'other'        => __( 'Other Plugins', 'immens-mcp-fortress' ),
		);

		return isset( $labels[ $category ] ) ? $labels[ $category ] : $category;
	}
}
