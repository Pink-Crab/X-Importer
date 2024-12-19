<?php

declare(strict_types=1);

/**
 * Collection of helper functions for fixtures.
 *
 * @package PinkCrab\X_Importer
 */

namespace PinkCrab\X_Importer\Tests\Tools;

use WP_UnitTestCase;

class Fixture_Helpers {

	/**
	 * Clears the fixtures uploads directory.
	 *
	 * @return void
	 */
	public static function clear_uploads() {
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( PC_X_IMPORTER_FIXTURES . 'uploads', \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $file ) {
			// If file is .gitignore, skip
			if ( $file->getRealPath() === PC_X_IMPORTER_FIXTURES . 'uploads/.gitignore' ) {
				continue;
			}

			if ( $file->isDir() ) {
				rmdir( $file->getRealPath() );
			} else {
				unlink( $file->getRealPath() );
			}
		}
	}
}
