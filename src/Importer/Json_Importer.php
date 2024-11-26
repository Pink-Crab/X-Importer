<?php

declare(strict_types=1);

/**
 * Json Importer.
 *
 * This class is responsible for importing JSON files.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Importer;

use PinkCrab\X_Importer\File_Manager\File_Manager;

/**
 * Json Importer.
 */
class Json_Importer {

	/**
	 * Holds the file manager.
	 *
	 * @var File_Manager
	 */
	protected $file_manager;

	/**
	 * Creates a new instance of the Json_Importer.
	 *
	 * @param File_Manager $file_manager The file manager.
	 */
	public function __construct( File_Manager $file_manager ) {
		$this->file_manager = $file_manager;
	}

	/**
	 * Create from global.
	 *
	 * @param string $file_name The file name.
	 *
	 * @return string
	 */
	public function create_from_global( string $file_name ): string {
		// @GLYNN add error handling
		return $this->file_manager->get_file_contents( $file_name ) ?? '';
	}

	/**
	 * Check if the file name supplied exists in the global $_FILES.
	 *
	 * @param string $file_name The file name.
	 *
	 * @return boolean
	 */
	private function file_exists_in_global( string $file_name ): bool {
		return isset( $_FILES[ $file_name ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Get a unique file name.
	 *
	 * @param string $file_name The file name.
	 *
	 * @return string
	 */
	private function unique_file_name( string $file_name ): string {
		// If the file does not exist, return the original name.
		if ( ! $this->file_manager->file_exists( $file_name ) ) {
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
		while ( $this->file_manager->file_exists( "{$original_file_name}_{$prefix}.{$extension}" ) ) {
			++$prefix;
		}

		return "{$original_file_name}_{$prefix}.{$extension}";
	}

	/**
	 * Stream the file contents to the file system.
	 *
	 * @param string $file_name The file name.
	 *
	 * @return string|null The filepath or null if failed.
	 */
	public function create_from_upload( string $file_name ): ?string {
		// Check if the file exists in the global $_FILES.
		if ( ! $this->file_exists_in_global( $file_name ) ) {
			throw new \Exception( 'File not found in global $_FILES' );
		}

		if ( ! $this->is_json_file( $file_name ) ) {
			throw new \Exception( 'Only JSON files can be supplied' );
		}

		$this->file_manager->create_base_path();
		$target_file = $this->file_manager->get_base_path() . $this->unique_file_name( $_FILES[ $file_name ]['name'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Move the file to the file system.
		$created = move_uploaded_file( $_FILES[ $file_name ]['tmp_name'], $target_file ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $created || ! $this->file_manager->file_exists( $target_file ) ) {
			throw new \Exception( 'Failed to move file to file system' );
		}

		return $target_file;
	}

	/**
	 * Verify the file is a JSON file.
	 *
	 * @param string $file_name The file name.
	 *
	 * @return boolean
	 */
	public function is_json_file( string $file_name ): bool {
		if ( ! \array_key_exists( $file_name, $_FILES ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return false;
		}

		return 'application/json' === $_FILES[ $file_name ]['type']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
