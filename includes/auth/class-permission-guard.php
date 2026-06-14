<?php
namespace Immens_MCP_Fortress\Auth;

use Immens_MCP_Fortress\Access_Points\Access_Point_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Permission_Guard {

	private $manager;

	public function __construct( Access_Point_Manager $manager ) {
		$this->manager = $manager;
	}

	public function can_use_tool( $access_point_id, $tool_name ) {
		$allowed = $this->manager->get_allowed_tools( $access_point_id );

		if ( in_array( '*', $allowed, true ) ) {
			return true;
		}

		return $this->matches_allowed( $tool_name, $allowed );
	}

	public function can_use_tool_with_scope( $allowed_tools, $tool_name ) {
		if ( in_array( '*', $allowed_tools, true ) ) {
			return true;
		}

		return $this->matches_allowed( $tool_name, $allowed_tools );
	}

	private function matches_allowed( $tool_name, $allowed ) {
		if ( in_array( $tool_name, $allowed, true ) ) {
			return true;
		}
		foreach ( $allowed as $pattern ) {
			if ( false !== strpos( $pattern, '*' ) && fnmatch( $pattern, $tool_name ) ) {
				return true;
			}
		}
		return false;
	}

	public function get_allowed_tools( $access_point_id ) {
		return $this->manager->get_allowed_tools( $access_point_id );
	}
}
