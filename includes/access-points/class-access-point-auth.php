<?php
namespace Immens_MCP_Fortress\Access_Points;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Access_Point_Auth {

	private $manager;

	public function __construct( Access_Point_Manager $manager ) {
		$this->manager = $manager;
	}

	public function authenticate( \WP_REST_Request $request ) {
		$auth_header = $request->get_header( 'authorization' );

		if ( empty( $auth_header ) ) {
			return new \WP_Error(
				'no_auth',
				__( 'Missing Authorization header.', 'immens-mcp-fortress' )
			);
		}

		if ( 0 !== stripos( $auth_header, 'Bearer ' ) ) {
			return new \WP_Error(
				'invalid_auth',
				__( 'Authorization header must use Bearer scheme.', 'immens-mcp-fortress' )
			);
		}

		$raw_key = substr( $auth_header, 7 );

		if ( empty( $raw_key ) ) {
			return new \WP_Error(
				'empty_key',
				__( 'Bearer token is empty.', 'immens-mcp-fortress' )
			);
		}

		$access_point = $this->manager->validate_api_key( $raw_key );

		if ( false === $access_point ) {
			return new \WP_Error(
				'invalid_key',
				__( 'Invalid or disabled access point.', 'immens-mcp-fortress' )
			);
		}

		if ( ! $this->is_ip_allowed( $access_point ) ) {
			return new \WP_Error(
				'ip_forbidden',
				__( 'Access denied: your IP address is not whitelisted for this access point.', 'immens-mcp-fortress' )
			);
		}

		$allowed_tools = $this->manager->get_allowed_tools( $access_point['id'] );

		return array(
			'access_point_id' => (int) $access_point['id'],
			'wp_user_id'      => (int) $access_point['wp_user_id'],
			'allowed_tools'   => $allowed_tools,
			'rate_limit'      => (int) $access_point['rate_limit'],
		);
	}

	public function check_ip_allowed( $access_point_id ) {
		$ap = $this->manager->get_access_point( $access_point_id );
		if ( ! $ap ) {
			return false;
		}
		return $this->is_ip_allowed( $ap );
	}

	private function is_ip_allowed( $access_point ) {
		$whitelist_raw = isset( $access_point['ip_whitelist'] ) ? $access_point['ip_whitelist'] : '';

		if ( empty( trim( $whitelist_raw ) ) ) {
			return true;
		}

		$cache_key = 'imf_ipwl_' . substr( md5( $whitelist_raw ), 0, 12 );
		$entries   = wp_cache_get( $cache_key, 'immens_mcp_fortress' );

		if ( false === $entries ) {
			$entries = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $whitelist_raw ) ) );
			wp_cache_set( $cache_key, $entries, 'immens_mcp_fortress', 300 );
		}

		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$remote_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$remote_ip = trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$remote_ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
		} else {
			$remote_ip = isset( $_SERVER['REMOTE_ADDR'] )
				? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
				: '';
		}

		$remote_ip = $this->normalize_ip( $remote_ip );

		if ( ! filter_var( $remote_ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		foreach ( $entries as $entry ) {
			if ( false !== strpos( $entry, '/' ) ) {
				$entry = $this->normalize_cidr_entry( $entry );
				if ( $this->ip_in_cidr( $remote_ip, $entry ) ) {
					return true;
				}
			} else {
				$normalized_entry = $this->normalize_ip( $entry );
				if ( ! filter_var( $normalized_entry, FILTER_VALIDATE_IP ) ) {
					continue;
				}
				if ( inet_pton( $remote_ip ) === inet_pton( $normalized_entry ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private function normalize_ip( $ip ) {
		$lower = strtolower( $ip );
		if ( false !== strpos( $lower, ':' ) ) {
			$bin = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ? inet_pton( $ip ) : false;
			if ( false !== $bin && 16 === strlen( $bin ) ) {
				$prefix = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff";
				if ( 0 === strncmp( $bin, $prefix, 12 ) ) {
					$ipv4 = inet_ntop( substr( $bin, 12 ) );
					if ( false !== $ipv4 && filter_var( $ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
						return $ipv4;
					}
				}
			}
		}
		return $ip;
	}

	private function normalize_cidr_entry( $cidr ) {
		if ( substr_count( $cidr, '/' ) !== 1 ) {
			return $cidr;
		}
		list( $subnet, $prefix ) = explode( '/', $cidr, 2 );
		return $this->normalize_ip( $subnet ) . '/' . $prefix;
	}

	private function ip_in_cidr( $ip, $cidr ) {
		if ( substr_count( $cidr, '/' ) !== 1 ) {
			return false;
		}
		list( $subnet, $raw_prefix ) = explode( '/', $cidr, 2 );

		if ( ! ctype_digit( $raw_prefix ) ) {
			return false;
		}

		$prefix = (int) $raw_prefix;

		if ( false !== strpos( $ip, ':' ) ) {
			if ( $prefix > 128 ) {
				return false;
			}
			if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
				return false;
			}
			if ( ! filter_var( $subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
				return false;
			}
			$ip_bin     = inet_pton( $ip );
			$subnet_bin = inet_pton( $subnet );
			if ( false === $ip_bin || false === $subnet_bin ) {
				return false;
			}
			$mask_bin = $this->build_mask_bin( $prefix, 16 );
			return ( $ip_bin & $mask_bin ) === ( $subnet_bin & $mask_bin );
		}

		if ( $prefix > 32 ) {
			return false;
		}
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return false;
		}
		if ( ! filter_var( $subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return false;
		}
		$ip_bin     = inet_pton( $ip );
		$subnet_bin = inet_pton( $subnet );
		if ( false === $ip_bin || false === $subnet_bin ) {
			return false;
		}
		$mask_bin = $this->build_mask_bin( $prefix, 4 );
		return ( $ip_bin & $mask_bin ) === ( $subnet_bin & $mask_bin );
	}

	private function build_mask_bin( $prefix, $total_bytes ) {
		$full_bytes   = (int) ( $prefix / 8 );
		$partial_bits = $prefix % 8;
		$mask_bin     = str_repeat( "\xFF", $full_bytes );
		if ( $partial_bits > 0 ) {
			$mask_bin .= chr( ( 0xFF << ( 8 - $partial_bits ) ) & 0xFF );
		}
		return str_pad( $mask_bin, $total_bytes, "\x00" );
	}
}
