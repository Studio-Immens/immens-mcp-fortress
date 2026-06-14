<?php
namespace Immens_MCP_Fortress\Tools\Yoast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Schema_Settings extends Base_Tool {

	public function get_name() {
		return 'yoast_get_schema_settings';
	}

	public function get_description() {
		return 'Get Yoast schema.org settings from the wpseo option. Returns company_or_person, company_name, person_logo, organization_logo, and related schema configuration.';
	}

	public function get_required_capability() {
		return 'manage_options';
	}

	public function get_category() {
		return 'yoast';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
		);
	}

	public function execute( array $arguments ) {
		$wpseo = get_option( 'wpseo', array() );

		$schema = array(
			'company_or_person' => isset( $wpseo['company_or_person'] ) ? $wpseo['company_or_person'] : null,
			'company_name'      => isset( $wpseo['company_name'] ) ? $wpseo['company_name'] : null,
			'person_logo'       => isset( $wpseo['person_logo'] ) ? $wpseo['person_logo'] : null,
			'person_logo_id'    => isset( $wpseo['person_logo_id'] ) ? $wpseo['person_logo_id'] : null,
			'organization_logo' => isset( $wpseo['organization_logo'] ) ? $wpseo['organization_logo'] : null,
			'organization_logo_id' => isset( $wpseo['organization_logo_id'] ) ? $wpseo['organization_logo_id'] : null,
			'logo_url'          => null,
		);

		if ( 'company' === $schema['company_or_person'] && ! empty( $schema['organization_logo'] ) ) {
			$schema['logo_url'] = $schema['organization_logo'];
		} elseif ( 'person' === $schema['company_or_person'] && ! empty( $schema['person_logo'] ) ) {
			$schema['logo_url'] = $schema['person_logo'];
		}

		return $schema;
	}
}
