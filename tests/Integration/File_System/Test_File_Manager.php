<?php

declare(strict_types=1);

/**
 * Integration for the File Manager
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\File_System;

use WP_UnitTestCase;
use PinkCrab\X_Importer\File_System\File_Manager;

/**
 * Tweet
 *
 * @group Integration
 * @group File_Manager
 */
class Test_File_Manager extends WP_UnitTestCase {

	private $base_path = PC_X_IMPORTER_FIXTURES . 'FS/';

	/**
	 * On setup, unset the gloabl wp_filesystem and clear the FS.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		parent::tear_down();

		// Iterate over all files and sub directories and remove them, but keep the readme.md
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $this->base_path, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $file ) {
			if ( $file->getRealPath() === $this->base_path . 'readme.md' ) {
				continue;
			}

			if ( $file->isDir() ) {
				rmdir( $file->getRealPath() );
			} else {
				unlink( $file->getRealPath() );
			}
		}

		unset( $GLOBALS['wp_filesystem'] );
	}

	/**
	 * @testdox When an instance of the File_Manger is created, the global WP_Filesytem should be populated on first call.
	 */
	public function test_file_manager_global_filesystem(): void {
		// If the global is set, unset it.
		if ( isset( $GLOBALS['wp_filesystem'] ) ) {
			unset( $GLOBALS['wp_filesystem'] );
		}

		$this->assertArrayNotHasKey( 'wp_filesystem', $GLOBALS );

		new File_Manager( $this->base_path );

		// Check the global is now set.
		$this->assertArrayHasKey( 'wp_filesystem', $GLOBALS );
		$this->assertInstanceOf( 'WP_Filesystem_Base', $GLOBALS['wp_filesystem'] );
	}

	/**
 * @testdox It should be possible to get access to the base path
*/
	public function test_file_manager_base_path(): void {
		$file_manager = new File_Manager( $this->base_path );

		$this->assertEquals( $this->base_path, $file_manager->get_base_path() );
	}

	/**
 * @testdox It should be possible to set a base dir and have it created if it doesn't exist.
*/
	public function test_file_manager_set_base_path(): void {
		// Path exists, so will just return true.
		$file_manager = new File_Manager( $this->base_path );
		$this->assertTrue( $file_manager->create_base_path() );

		// Path doesn't exist, so will create it.
		$file_manager = new File_Manager( $this->base_path . 'new_dir/' );
		$this->assertTrue( $file_manager->create_base_path() );
		$this->assertTrue( is_dir( $this->base_path . 'new_dir/' ) );
	}

	/**
 * @testdox It should be possible to check if a file exists by relative path.
*/
	public function test_file_manager_file_exists(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Check a file that doesn't exist.
		$this->assertFalse( $file_manager->file_exists( $this->base_path . 'not_a_file.txt' ) );

		// Create a file and check it exists.
		\file_put_contents( $this->base_path . 'test_file.txt', 'test' );

		$this->assertTrue( $file_manager->file_exists( 'test_file.txt' ) );
	}

	/**
 * @testdox It should be possible to create a file with a relative filename and its contents.
*/
	public function test_file_manager_create_file(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Create a file.
		$this->assertTrue( $file_manager->create_file( 'test_file.txt', 'test content' ) );

		// Check the file exists and has the correct content.
		$this->assertTrue( \file_exists( $this->base_path . 'test_file.txt' ) );
		$this->assertEquals( 'test content', \file_get_contents( $this->base_path . 'test_file.txt' ) );
	}

	/**
 * @testdox It should be possible to get the contents of a file based on its relative path.
*/
	public function test_file_manager_get_file_contents(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Create a file.
		\file_put_contents( $this->base_path . 'test_read_file.txt', 'test content' );

		// Check the file exists and has the correct content.
		$this->assertEquals( 'test content', $file_manager->get_file_contents( 'test_read_file.txt' ) );
	}

	/**
 * @testdox Attempting to get the contents of a file that doesnt exist, will return null
*/
	public function test_file_manager_get_file_contents_null(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Check the file exists and has the correct content.
		$this->assertNull( $file_manager->get_file_contents( 'not_a_file.txt' ) );
	}

	/**
 * @testdox It should be possible to delete a file based on its relative path
*/
	public function test_file_manager_delete_file(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Create a file.
		\file_put_contents( $this->base_path . 'test_delete_file.txt', 'test content' );

		// Check the file exists.
		$this->assertTrue( \file_exists( $this->base_path . 'test_delete_file.txt' ) );

		// Delete the file.
		$this->assertTrue( $file_manager->delete_file( 'test_delete_file.txt' ) );

		// Check the file has been removed.
		$this->assertFalse( \file_exists( $this->base_path . 'test_delete_file.txt' ) );
	}

	/**
 * @testdox It should be possible to get a unique filename even if a file already exist with the same name
*/
	public function test_file_manager_unique_file_name(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Create a file.
		\file_put_contents( $this->base_path . 'test_file.txt', 'test content' );

		// Check the file exists and has the correct content.
		$this->assertEquals( 'test_file_1.txt', $file_manager->unique_file_name( 'test_file.txt' ) );

		// Create the new file.
		\file_put_contents( $this->base_path . 'test_file_1.txt', 'test content' );

		// Check the file exists and has the correct content.
		$this->assertEquals( 'test_file_2.txt', $file_manager->unique_file_name( 'test_file.txt' ) );

		// If the file doesnt exist, it should return the same name.
		$this->assertEquals( 'other_test_file.txt', $file_manager->unique_file_name( 'other_test_file.txt' ) );
	}

	/**
* @testdox When generating a unique filename, if there is no extension, add a random string at the end.
*/
	public function test_file_manager_unique_file_name_no_extension(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Create a file.
		\file_put_contents( $this->base_path . 'test_file', 'test content' );

		$name = $file_manager->unique_file_name( 'test_file' );

		// The name should be test_file_ with 6 random characters.
		$this->assertMatchesRegularExpression( '/^test_file_[a-zA-Z0-9]{6}$/', $name );
	}

	/**
     * @testdox It should be possible to move an uploaded file from temp to file system using core PHP functions
     * @runInSeparateProcess
	 * @preserveGlobalState disabled
     */
	public function test_file_manager_move_uploaded_file(): void {
		$file_manager = new File_Manager( $this->base_path );

		// Create a temp file.
		$temp_file = tempnam( sys_get_temp_dir(), 'php' );
		file_put_contents( $temp_file, 'This is some test content.' );

		// Mock the global $_FILES array.
		$_FILES['uploaded_file'] = array(
			'name'     => 'example.txt', 
			'type'     => 'text/plain',  
			'tmp_name' => $temp_file,     
			'error'    => UPLOAD_ERR_OK,     
			'size'     => filesize( $temp_file ),  
		);

		// Mock core php move_uploaded_file so it works for testing.
		require_once PC_X_IMPORTER_TEST_ROOT . 'Tools/Overrides/File_System__move_uploaded_file.php';

		// Move the file.
		$result = $file_manager->move_uploaded_file( 'uploaded_file', 'example.txt' );

		$this->assertTrue($result);
		$this->assertFileExists($this->base_path . '/example.txt');
		$this->assertEquals('This is some test content.', file_get_contents($this->base_path . '/example.txt'));

		unlink($temp_file);
	}

	/** @testdox Attempting to move an upload when it doesnt exist in $_FILES should return early as a fail before attempting */
	public function test_fails_if_file_not_in_global(): void{
		$file_manager = new File_Manager( $this->base_path );
		$result = $file_manager->move_uploaded_file( 'uploaded_file', 'example.txt' );
		$this->assertFalse($result);

	}
}
