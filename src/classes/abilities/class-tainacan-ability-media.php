<?php

namespace Tainacan\Abilities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Media abilities that are not backed by an existing REST route.
 *
 * Every other ability delegates to a Tainacan controller through the bridge. There
 * is no route for accepting an uploaded file, so this class does the work itself
 * and therefore carries its own security burden:
 *
 * - content arrives as base64 or as a URL fetched with wp_safe_remote_get, never
 *   as a caller-supplied filesystem path
 * - the size is capped before anything reaches disk
 * - the real type is verified with wp_check_filetype_and_ext on the written file,
 *   and a rejected file is deleted rather than left behind
 * - nothing is trusted from the extension alone
 *
 * @since 1.5.0
 */
class Ability_Media {

	use \Tainacan\Traits\Singleton_Instance;

	/**
	 * Maximum accepted payload, in bytes.
	 *
	 * Filterable so a site with large documents can raise it deliberately.
	 *
	 * @var int
	 */
	const MAX_BYTES = 52428800; // 50 MB

	/**
	 * Nothing to hook.
	 *
	 * @return void
	 */
	protected function init() {
	}

	/**
	 * Upload a file and return the created attachment.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function upload( $input = [] ) {
		$input = is_array( $input ) ? $input : [];

		$filename = isset( $input['filename'] ) ? sanitize_file_name( (string) $input['filename'] ) : '';

		if ( '' === $filename ) {
			return new \WP_Error(
				'tainacan_media_missing_filename',
				__( 'A filename is required.', 'tainacan' )
			);
		}

		$contents = $this->read_contents( $input );

		if ( is_wp_error( $contents ) ) {
			return $contents;
		}

		$max_bytes = (int) apply_filters( 'tainacan_ability_upload_max_bytes', self::MAX_BYTES );

		if ( strlen( $contents ) > $max_bytes ) {
			return new \WP_Error(
				'tainacan_media_too_large',
				sprintf(
					/* translators: %s: maximum size in bytes */
					__( 'The file exceeds the maximum accepted size of %s bytes.', 'tainacan' ),
					number_format_i18n( $max_bytes )
				)
			);
		}

		$upload = wp_upload_bits( $filename, null, $contents );

		if ( ! empty( $upload['error'] ) ) {
			return new \WP_Error( 'tainacan_media_write_failed', $upload['error'] );
		}

		/*
		 * Verify the real type from the bytes on disk. An extension claiming .png
		 * proves nothing, so a file whose detected type is empty is removed instead
		 * of becoming an attachment.
		 */
		$filetype = wp_check_filetype_and_ext( $upload['file'], $filename );

		if ( empty( $filetype['type'] ) ) {
			wp_delete_file( $upload['file'] );

			return new \WP_Error(
				'tainacan_media_unsupported_type',
				__( 'The file type could not be verified, so the upload was rejected.', 'tainacan' )
			);
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';

		$title = isset( $input['title'] ) && '' !== $input['title']
			? sanitize_text_field( (string) $input['title'] )
			: pathinfo( $filename, PATHINFO_FILENAME );

		$attachment_id = wp_insert_attachment(
			[
				'post_mime_type' => $filetype['type'],
				'post_title'     => $title,
				'post_content'   => isset( $input['description'] ) ? wp_kses_post( (string) $input['description'] ) : '',
				'post_status'    => 'inherit',
			],
			$upload['file']
		);

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $upload['file'] );

			return $attachment_id;
		}

		if ( ! $attachment_id ) {
			wp_delete_file( $upload['file'] );

			return new \WP_Error(
				'tainacan_media_attachment_failed',
				__( 'The attachment record could not be created.', 'tainacan' )
			);
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );

		if ( ! is_wp_error( $metadata ) && ! empty( $metadata ) ) {
			wp_update_attachment_metadata( $attachment_id, $metadata );
		}

		if ( isset( $input['alt_text'] ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( (string) $input['alt_text'] ) );
		}

		return $this->describe( (int) $attachment_id, $filetype['type'], (int) filesize( $upload['file'] ) );
	}

	/**
	 * Read attachment details.
	 *
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error
	 */
	public function get( $input = [] ) {
		$input = is_array( $input ) ? $input : [];
		$id    = isset( $input['attachment_id'] ) ? (int) $input['attachment_id'] : 0;

		if ( $id <= 0 ) {
			return new \WP_Error(
				'tainacan_media_missing_attachment',
				__( 'An attachment_id is required.', 'tainacan' )
			);
		}

		$post = get_post( $id );

		if ( ! $post || 'attachment' !== $post->post_type ) {
			return new \WP_Error(
				'tainacan_media_not_found',
				__( 'No attachment exists with that ID.', 'tainacan' )
			);
		}

		$metadata = wp_get_attachment_metadata( $id );
		$filesize = is_array( $metadata ) && isset( $metadata['filesize'] ) ? (int) $metadata['filesize'] : 0;

		return $this->describe( $id, (string) $post->post_mime_type, $filesize );
	}

	/**
	 * Build the shared attachment description returned by both abilities.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $mime_type     Mime type.
	 * @param int    $filesize      Size in bytes.
	 * @return array
	 */
	private function describe( $attachment_id, $mime_type, $filesize ) {
		return [
			'id'        => (int) $attachment_id,
			'url'       => wp_get_attachment_url( $attachment_id ),
			'mime_type' => $mime_type,
			'title'     => get_the_title( $attachment_id ),
			'alt_text'  => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			'filesize'  => (int) $filesize,
		];
	}

	/**
	 * Turn ability input into file bytes.
	 *
	 * @param array $input Ability input.
	 * @return string|\WP_Error
	 */
	private function read_contents( array $input ) {
		if ( ! empty( $input['content_base64'] ) ) {
			$decoded = base64_decode( (string) $input['content_base64'], true );

			if ( false === $decoded || '' === $decoded ) {
				return new \WP_Error(
					'tainacan_media_invalid_base64',
					__( 'content_base64 is not valid base64 data.', 'tainacan' )
				);
			}

			return $decoded;
		}

		if ( ! empty( $input['url'] ) ) {
			$url = esc_url_raw( (string) $input['url'] );

			if ( '' === $url ) {
				return new \WP_Error(
					'tainacan_media_invalid_url',
					__( 'The provided url is not valid.', 'tainacan' )
				);
			}

			$response = wp_safe_remote_get( $url, [ 'timeout' => 30 ] );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );

			if ( 200 !== $code ) {
				return new \WP_Error(
					'tainacan_media_fetch_failed',
					sprintf(
						/* translators: %d: HTTP status code */
						__( 'Fetching the file returned HTTP %d.', 'tainacan' ),
						$code
					)
				);
			}

			$body = (string) wp_remote_retrieve_body( $response );

			if ( '' === $body ) {
				return new \WP_Error(
					'tainacan_media_empty_body',
					__( 'The fetched file was empty.', 'tainacan' )
				);
			}

			return $body;
		}

		return new \WP_Error(
			'tainacan_media_missing_content',
			__( 'Provide either content_base64 or a url.', 'tainacan' )
		);
	}
}
