<?php
namespace Immens_MCP_Fortress\Tools\CF7;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Get_Form extends Base_Tool {

	public function get_name() {
		return 'cf7_get_form';
	}

	public function get_description() {
		return 'Get Contact Form 7 form details including form HTML, mail settings, and additional configuration.';
	}

	public function get_required_capability() {
		return 'read';
	}

	public function get_category() {
		return 'contact-form-7';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'form_id' => array(
					'type'        => 'integer',
					'description' => 'Form ID to retrieve details for',
				),
			),
			'required'   => array( 'form_id' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'form_id' ) );
		$form_id = $this->parse_required_id( $arguments['form_id'], 'Form ID' );

		$form = get_post( $form_id );
		if ( ! $form || 'wpcf7_contact_form' !== $form->post_type ) {
			throw new \RuntimeException( sprintf( 'CF7 form not found: %d', $form_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$form_html   = get_post_meta( $form_id, '_form', true );
		$mail        = get_post_meta( $form_id, '_mail', true );
		$mail_2      = get_post_meta( $form_id, '_mail_2', true );
		$messages    = get_post_meta( $form_id, '_messages', true );
		$additional  = get_post_meta( $form_id, '_additional_settings', true );
		$locale      = get_post_meta( $form_id, '_locale', true );
		$form_props  = get_post_meta( $form_id, '_props', true );

		return array(
			'id'                  => $form_id,
			'title'               => $form->post_title,
			'status'              => $form->post_status,
			'locale'              => $locale ? $locale : 'en_US',
			'form_html'           => $form_html,
			'mail'                => $mail,
			'mail_2'              => $mail_2,
			'messages'            => $messages,
			'additional_settings' => $additional,
			'props'               => $form_props,
		);
	}
}
