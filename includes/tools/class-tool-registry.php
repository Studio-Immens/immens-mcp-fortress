<?php
namespace Immens_MCP_Fortress\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tool_Registry {

	private $tools = array();
	private $definitions = array();

	public function register( Base_Tool $tool ) {
		$name = $tool->get_name();
		$this->tools[ $name ] = $tool;
		$this->definitions[ $name ] = $tool->get_definition();
	}

	public function get_tool( $name ) {
		return isset( $this->tools[ $name ] ) ? $this->tools[ $name ] : null;
	}

	public function get_definition( $name ) {
		return isset( $this->definitions[ $name ] ) ? $this->definitions[ $name ] : null;
	}

	public function get_all_tools() {
		return $this->tools;
	}

	public function get_all_definitions() {
		return array_values( $this->definitions );
	}

	public function auto_discover() {
		$classes = get_declared_classes();
		$namespace_prefix = 'Immens_MCP_Fortress\\Tools\\';

		foreach ( $classes as $class ) {
			if ( 0 === strpos( $class, $namespace_prefix )
				&& is_subclass_of( $class, 'Immens_MCP_Fortress\\Tools\\Base_Tool' )
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
