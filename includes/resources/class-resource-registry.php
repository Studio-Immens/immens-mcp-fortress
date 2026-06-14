<?php
namespace Immens_MCP_Fortress\Resources;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resource_Registry {

	private $resources = array();
	private $definitions = array();

	public function register( Base_Resource $resource ) {
		$uri = $resource->get_uri();
		$this->resources[ $uri ] = $resource;
		$this->definitions[ $uri ] = $resource->get_definition();
	}

	public function get_resource( $uri ) {
		return isset( $this->resources[ $uri ] ) ? $this->resources[ $uri ] : null;
	}

	public function get_all_definitions() {
		return array_values( $this->definitions );
	}

	public function auto_discover() {
		$classes = get_declared_classes();
		$namespace_prefix = 'Immens_MCP_Fortress\\Resources\\';

		foreach ( $classes as $class ) {
			if ( 0 === strpos( $class, $namespace_prefix )
				&& is_subclass_of( $class, 'Immens_MCP_Fortress\\Resources\\Base_Resource' )
			) {
				$reflection = new \ReflectionClass( $class );
				if ( ! $reflection->isAbstract() ) {
					$instance = new $class();
					$this->register( $instance );
				}
			}
		}
	}
}
