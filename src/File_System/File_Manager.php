<?php

declare(strict_types=1);

/**
 * The file manager service.
 *
 * This service is responsible for managing files and directories.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\File_System;

/**
 * The file manager service.
 */
class File_Manager {

	/**
	 * The base path for the instance.
	 *
	 * @var string
	 */
	protected $base_path;

	/**
	 * Access to the WP_Filesystem.
	 *
	 * @var \WP_Filesystem_Base
	 */
	protected $filesystem;

	/**
	 * Sets up the file manager.
	 *
	 * @param string $base_path The base path for the instance.
	 */
	public function __construct( string $base_path ) {
		$this->base_path  = \trailingslashit( \untrailingslashit( $base_path ) );
		$this->filesystem = $this->get_filesystem();
	}

	/**
	 * Get WP_Filesystem instance.
	 *
	 * @return \WP_Filesystem_Base
	 */
	private function get_filesystem(): \WP_Filesystem_Base {

		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// Get the filesystem.
		return $wp_filesystem;
	}

	/**
	 * Get the base path.`
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return $this->base_path;
	}

	/**
	 * Creates the base path if it does not exist.
	 *
	 * @return boolean
	 */
	public function create_base_path(): bool {
		// If the base path exists, return true.
		if ( $this->filesystem->is_dir( $this->base_path ) ) {
			return true;
		}
		return $this->filesystem->mkdir( $this->base_path );
	}

	/**
	 * Check if a file exists.
	 *
	 * @param string $file The file to check. Can be a relative or absolute path.
	 *
	 * @return boolean
	 */
	public function file_exists( string $file ): bool {
		return $this->filesystem->is_file( $this->base_path . $file );
	}

	/**
	 * Create file.
	 *
	 * @param string $file    The file to create. Can be a relative or absolute path.
	 * @param string $content The content to write to the file.
	 *
	 * @return boolean
	 */
	public function create_file( string $file, string $content ): bool {
		$this->create_base_path();
		// Create the file.
		return $this->filesystem->put_contents( $this->base_path . $file, $content );
	}

	/**
	 * Get file contents.
	 *
	 * @param string $file The file to get the contents of. Can be a relative or absolute path.
	 *
	 * @return string|null
	 */
	public function get_file_contents( string $file ): ?string {
		if ( ! $this->file_exists( $file ) ) {
			return null;
		}

		// Get the file contents.
		return $this->filesystem->get_contents( $this->base_path . $file ) ?: null; // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
	}

	/**
	 * Delete file.
	 *
	 * @param string $file The file to delete. Can be a relative or absolute path.
	 *
	 * @return boolean
	 */
	public function delete_file( string $file ): bool {
		// Delete the file.
		return $this->filesystem->delete( $this->base_path . $file );
	}

	/**
	 * Generate a unique file name based on the base path.
	 *
	 * @param string $file_name The file name to make unique.
	 *
	 * @return string
	 */
	public function unique_file_name( string $file_name ): string {
		// If the file does not exist, return the original name.
		if ( ! $this->file_exists( $file_name ) ) {
			return $file_name;
		}

		$file_parts = pathinfo( $file_name );

		// If we dont have an extension, return the original name with a random prefix.
		if ( ! isset( $file_parts['extension'] ) ) {
			return $file_parts['filename'] . '_' . wp_generate_password( 6, false );
		}

		// @phpstan ignore-next-line
		$extension          = esc_attr( $file_parts['extension'] );
		$original_file_name = esc_attr( $file_parts['filename'] );

		// Loop until we find a unique name.
		$prefix = 1;
		while ( $this->file_exists( "{$original_file_name}_{$prefix}.{$extension}" ) ) {
			++$prefix;
		}

		return "{$original_file_name}_{$prefix}.{$extension}";
	}

	/**
	 * Moves an uploaded file to the path.
	 *
	 * @param string $file The file to move.
	 * @param string $path The relative path to move the file to.
	 *
	 * @return boolean
	 */
	public function move_uploaded_file( string $file, string $path ): bool {
		// Get the full path.
		$full_path = $this->base_path . $path;

		// Check the file exists in $_FILES.
		if ( ! isset( $_FILES[ $file ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return false;
		}

		return move_uploaded_file( $_FILES[ $file ]['tmp_name'], $full_path ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
