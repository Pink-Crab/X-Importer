<?php

declare(strict_types=1);

/**
 * JSON File Handler
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\File_System;

use PinkCrab\X_Importer\File_System\File_Manager;

/**
 * Json Importer.
 */
class JSON_File_Handler {

	/**
	 * Holds the file manager.
	 *
	 * @var File_Manager
	 */
	protected $file_manager;

	/**
	 * Creates a new instance of the JSON_File_Handler.
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
	public function create_from_filename( string $file_name ): string {
		// Get the filename from the path.
		$file_name = basename( $file_name );

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
	 * Stream the file contents to the file system.
	 *
	 * @param string $file_name The file name.
	 *
	 * @return string The filepath
	 *
	 * @throws \Exception If the file does not exist in the global $_FILES.
	 * @throws \Exception If the file is not a JSON file.
	 * @throws \Exception If the file cannot be moved to the file system.
	 * @throws \Exception If the file does not exist in the file system.
	 */
	public function create_from_upload( string $file_name ): string {
		// Check if the file exists in the global $_FILES.
		if ( ! $this->file_exists_in_global( $file_name ) ) {
			throw new \Exception( 'File not found in global $_FILES' );
		}

		if ( ! $this->is_json_file( $file_name ) ) {
			throw new \Exception( 'Only JSON files can be supplied' );
		}

		// Create the base path if it does not exist.
		$this->file_manager->create_base_path();

		$unique_file_name = $this->file_manager->unique_file_name( $_FILES[ $file_name ]['name'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$target_file      = $this->file_manager->get_base_path() . $unique_file_name; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		// Move the file to the file system.
		// $created = move_uploaded_file( $_FILES[ $file_name ]['tmp_name'], $target_file ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$created = $this->file_manager->move_uploaded_file( $file_name, $unique_file_name );
		if ( ! $created || ! $this->file_manager->file_exists( $unique_file_name ) ) {
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
