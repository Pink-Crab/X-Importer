<?php

declare(strict_types=1);

/**
 * Handles the media uploads.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Media;

use PinkCrab\X_Importer\File_System\File_Manager;

/**
 * Media Upload
 */
class Media_Upload {

	/**
	 * Access to the file system.
	 *
	 * @var File_Manager
	 */
	protected $file_manager;

	/**
	 * WP Uploads paths and dirs
	 *
	 * @var array{
	 * path: string,
	 * url: string,
	 * subdir: string,
	 * basedir: string,
	 * baseurl: string,
	 * error: string|false
	 * }
	 */
	protected $wp_uploads;

	/**
	 * Create instance of the Media_Upload.
	 */
	public function __construct() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$this->wp_uploads   = wp_upload_dir();
		$this->file_manager = new File_Manager( $this->wp_uploads['path'] );
	}

	/**
	 * Create from remote path.
	 *
	 * @param string       $remote_path The remote path.
	 * @param string       $file_name   The file name.
	 * @param integer|null $post_id     The optional post ID.
	 *
	 * @return array{
	 *   attachment_id: integer,
	 *   full_path: string,
	 *   full_url: string,
	 *   sizes: array<string, array{name:string, url:string, path:string, width:integer, height:integer, filesize:integer, mime-type:string}>
	 * }
	 *
	 * @throws \Exception If the remote file could not be downloaded.
	 * @throws \Exception If the file could not be created.
	 */
	public function create_from_remote_path( string $remote_path, string $file_name, ?int $post_id = null ): array {
		// Get the file contents.
		$file_contents = $this->get_remote_file_contents( $remote_path );

		// Create the file.
		$file_name = $this->file_manager->unique_file_name( $file_name );
		$file_path = $this->create_file( $file_name, $file_contents );

		// If we have no path, we have an error.
		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			throw new \Exception( 'File could not be created.' );
		}

		$file_url = $this->wp_uploads['url'] . '/' . $file_name;

		// Compile the attachment data.
		$attachment_args = array(
			'guid'           => $file_url,
			'post_mime_type' => wp_check_filetype( basename( $file_name ), null )['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment_args, $file_path, $post_id ?? 0, true );
		if ( \is_wp_error( $attachment_id ) || 0 === $attachment_id ) {
			throw new \Exception( 'Attachment could not be created.' );
		}

		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// Return the attachment data.
		return array(
			'attachment_id' => $attachment_id,
			'full_path'     => $file_path,
			'full_url'      => $file_url,
			'sizes'         => $this->map_sizes( $attachment_data['sizes'] ),
		);
	}

	/**
	 * Map the sizes to full urls and paths.
	 *
	 * @param array<string, array{file:string, width:integer, height:integer, filesize:integer, mime-type:string}> $sizes The sizes to map.
	 *
	 * @return array<string, array{name:string, url:string, path:string, width:integer, height:integer, filesize:integer, mime-type:string}>
	 */
	protected function map_sizes( array $sizes ): array {
		return array_map(
			function ( $size ) {
				return array(
					'name'      => $size['file'],
					'url'       => $this->wp_uploads['url'] . '/' . $size['file'],
					'path'      => $this->wp_uploads['path'] . '/' . $size['file'],
					'width'     => absint( $size['width'] ),
					'height'    => absint( $size['height'] ),
					'filesize'  => absint( $size['filesize'] ),
					'mime-type' => esc_attr( $size['mime-type'] ),
				);
			},
			$sizes
		);
	}

	/**
	 * Get the media mime types allowed.
	 *
	 * @return array<string>
	 */
	protected function get_allowed_media_mime_types(): array {
		// Get all allowed MIME types
		$all_mime_types = get_allowed_mime_types();

		// Define the groups for media types we want to filter
		$media_groups = array(
			'image/',
			'video/',
			'audio/',
		);

		// Filter MIME types based on the groups
		$media_mime_types = array_filter(
			$all_mime_types,
			function ( $mime ) use ( $media_groups ) {
				foreach ( $media_groups as $group ) {
					if ( strpos( $mime, $group ) === 0 ) {
						return true;
					}
				}
				return false;
			}
		);

		return $media_mime_types;
	}

	/**
	 * Get the contents of a remote file.
	 *
	 * @param string $url The remote url.
	 *
	 * @return string
	 *
	 * @throws \Exception If the remote file could not be downloaded.
	 * @throws \Exception If the remote file is not an allowed media type.
	 */
	protected function get_remote_file_contents( string $url ): string {
		// Get the remote contents.
		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );
		if ( is_wp_error( $response ) ) {
			throw new \Exception( 'Remote file could not be downloaded.' );
		}

		$contents = wp_remote_retrieve_body( $response );
		$type     = wp_remote_retrieve_header( $response, 'content-type' );

		if ( is_array( $type ) ) {
			$type = $type[0];
		}

		// If the media type is not allowed, throw an exception.
		if ( ! in_array( $type, $this->get_allowed_media_mime_types(), true ) ) {
			throw new \Exception( 'Remote file is not an allowed media type - ' . esc_html( $type ) . '.' );
		}

		return $contents;
	}

	/**
	 * Create a file.
	 *
	 * @param string $file_name     The file name.
	 * @param string $file_contents The file contents.
	 *
	 * @return string
	 */
	protected function create_file( string $file_name, string $file_contents ): string {
		$result = $this->file_manager->create_file( $file_name, $file_contents );

		return $result
			? $this->file_manager->get_base_path() . $file_name
			: '';
	}
}
