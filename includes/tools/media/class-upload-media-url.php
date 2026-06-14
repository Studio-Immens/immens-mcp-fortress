<?php
namespace Immens_MCP_Fortress\Tools\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Upload_Media_Url extends Base_Tool {

	public function get_name() {
		return 'wp_upload_media_url';
	}

	public function get_description() {
		return 'Upload media from a URL.';
	}

	public function get_required_capability() {
		return 'upload_files';
	}

	public function get_category() {
		return 'media';
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'url'      => array(
					'type'        => 'string',
					'description' => 'URL of the media file to upload',
				),
				'title'    => array(
					'type'        => 'string',
					'description' => 'Media title',
				),
				'alt_text' => array(
					'type'        => 'string',
					'description' => 'Alt text for the media item',
				),
				'caption'  => array(
					'type'        => 'string',
					'description' => 'Media caption',
				),
			),
			'required'   => array( 'url' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'url' ) );

		$url = esc_url_raw( $arguments['url'] );

		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			throw new \RuntimeException( $tmp->get_error_message() );
		}

		$filename  = basename( $url );
		$mime      = wp_check_filetype( $filename );
		$overrides = array( 'test_form' => false, 'test_type' => true );
		$file      = array(
			'name'     => $filename,
			'type'     => $mime['type'] ?: 'application/octet-stream',
			'tmp_name' => $tmp,
			'error'    => 0,
			'size'     => filesize( $tmp ),
		);

		$movefile = wp_handle_sideload( $file, $overrides );
		if ( isset( $movefile['error'] ) ) {
			@unlink( $tmp );
			throw new \RuntimeException( $movefile['error'] );
		}

		$attachment = array(
			'post_mime_type' => $movefile['type'],
			'post_title'     => isset( $arguments['title'] ) ? $arguments['title'] : sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment, $movefile['file'] );
		if ( is_wp_error( $attach_id ) ) {
			@unlink( $movefile['file'] );
			throw new \RuntimeException( $attach_id->get_error_message() );
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		if ( isset( $arguments['alt_text'] ) ) {
			update_post_meta( $attach_id, '_wp_attachment_image_alt', $arguments['alt_text'] );
		}
		if ( isset( $arguments['caption'] ) ) {
			wp_update_post( array(
				'ID'           => $attach_id,
				'post_excerpt' => $arguments['caption'],
			) );
		}

		$request  = new \WP_REST_Request( 'GET', '/wp/v2/media/' . $attach_id );
		$request->set_param( 'context', 'edit' );
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			$error = $response->as_error();
			throw new \RuntimeException( $error->get_error_message() );
		}

		return $response->get_data();
	}
}
