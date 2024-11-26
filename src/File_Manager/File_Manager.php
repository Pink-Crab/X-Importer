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

namespace PinkCrab\X_Importer\File_Manager;

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
	 * Get the base path.
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
}
