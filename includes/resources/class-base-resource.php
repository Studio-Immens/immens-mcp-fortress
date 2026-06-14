<?php
namespace Immens_MCP_Fortress\Resources;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Base_Resource {

	abstract public function get_uri();
	abstract public function get_name();
	abstract public function get_description();
	abstract public function get_mime_type();
	abstract public function read();

	public function get_definition() {
		return array(
			'uri'         => $this->get_uri(),
			'name'        => $this->get_name(),
			'description' => $this->get_description(),
			'mimeType'    => $this->get_mime_type(),
		);
	}
}
