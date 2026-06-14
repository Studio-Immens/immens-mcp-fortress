<?php
namespace Immens_MCP_Fortress\Tools\Media;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Immens_MCP_Fortress\Tools\Base_Tool;

class Upload_Media extends Base_Tool {

	public function get_name() {
		return 'wp_upload_media';
	}

	public function get_description() {
		return 'Upload a media file via base64 data or a file path.';
	}

	public function get_required_capability() {
		return 'upload_files';
	}

	public function get_annotations() {
		return array(
			'title'           => $this->get_title(),
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'openWorldHint'   => true,
		);
	}

	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'file'      => array(
					'type'        => 'string',
					'description' => 'Base64-encoded file content or absolute file path on server',
				),
				'filename'  => array(
					'type'        => 'string',
					'description' => 'Desired filename (e.g. image.jpg)',
				),
				'title'     => array(
					'type'        => 'string',
					'description' => 'Media title',
				),
				'alt_text'  => array(
					'type'        => 'string',
					'description' => 'Alt text for the media item',
				),
				'caption'   => array(
					'type'        => 'string',
					'description' => 'Media caption',
				),
			),
			'required'   => array( 'file', 'filename' ),
		);
	}

	public function execute( array $arguments ) {
		$this->validate_required( $arguments, array( 'file', 'filename' ) );

		$tmp_file = null;

		if ( @file_exists( $arguments['file'] ) && is_readable( $arguments['file'] ) ) {
			$file_path = $arguments['file'];
		} else {
			$decoded = base64_decode( $arguments['file'], true );
			if ( false === $decoded ) {
				throw new \InvalidArgumentException( 'Invalid base64 file data.' );
			}
			$tmp_file = wp_tempnam( $arguments['filename'] );
			file_put_contents( $tmp_file, $decoded );
			$file_path = $tmp_file;
		}

		$filename = $arguments['filename'];
		$mime     = wp_check_filetype( $filename );
		$tmp_dir  = get_temp_dir();

		$tmp_path = $tmp_dir . $filename;

		if ( ! copy( $file_path, $tmp_path ) ) {
			$tmp_path = $tmp_dir . wp_generate_uuid4() . '-' . $filename;
			if ( ! copy( $file_path, $tmp_path ) ) {
				throw new \RuntimeException( 'Failed to copy file to temp directory.' );
			}
		}

		$file = array(
			'tmp_name' => $tmp_path,
			'name'     => $filename,
			'type'     => $mime['type'] ?: 'application/octet-stream',
			'size'     => filesize( $tmp_path ),
		);

		$overrides = array( 'test_form' => false, 'test_type' => true );
		$movefile  = wp_handle_sideload( $file, $overrides );

		if ( $tmp_file && file_exists( $tmp_file ) ) {
			@unlink( $tmp_file );
		}

		if ( isset( $movefile['error'] ) ) {
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
			throw new \RuntimeException( $error->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return $response->get_data();
	}
}
