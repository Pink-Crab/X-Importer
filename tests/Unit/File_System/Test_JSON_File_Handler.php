<?php

declare(strict_types=1);

/**
 * Unit Tests for the JSON File Handler class
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\File_System;

use WP_UnitTestCase;
use PinkCrab\X_Importer\File_System\File_Manager;
use PinkCrab\X_Importer\File_System\JSON_File_Handler;

/**
 * Tweet
 *
 * @group Unit
 * @group File_System
 * @group JSON_File_Handler
 */
class Test_JSON_File_Handler extends WP_UnitTestCase {

    /**
     * Clear the $_FILES global on tear down
     * @return void
     */
    public function tear_down(): void {
        unset( $_FILES );
        parent::tear_down();
    }

	/**
 * @testdox When attempting to get the contents of a filename, if it doesnt exist an empty string should be returned.
*/
	public function test_create_from_filename_unknown_file(): void {
		$file_manager = $this->createMock( File_Manager::class );
		$file_manager->method( 'get_file_contents' )->willReturn( null );
		$json = new JSON_File_Handler( $file_manager );

		$this->assertEquals( '', $json->create_from_filename( 'some.json' ) );
	}

	/**
 * @testdox When creating from a filename, the contents of the file should be returned
*/
	public function test_create_from_filename(): void {
		$file_manager = $this->createMock( File_Manager::class );
		$file_manager->method( 'get_file_contents' )->willReturn( '[null, null]' );
		$json = new JSON_File_Handler( $file_manager );

		$this->assertEquals( '[null, null]', $json->create_from_filename( 'some.json' ) );
	}

	/**
 * @testdox When creating a file by filename, any proceeding paths will be removed
*/
	public function test_create_from_filename_remove_path(): void {
		$file_manager = $this->createMock( File_Manager::class );
		$file_manager->method( 'get_file_contents' )->willReturnCallback(
			function ( $filename ) {
				return $filename;
			}
		);

		$json = new JSON_File_Handler( $file_manager );
		$this->assertEquals( 'file.json', $json->create_from_filename( 'some/path/to/file.json' ) );
	}

	/**
 * @testdox When attempting to create a json string from uploads, if the file is not present in the $_FILES global an exception should be thrown
*/
	public function test_create_from_uploads_missing_file(): void {
		$file_manager = $this->createMock( File_Manager::class );
		$json         = new JSON_File_Handler( $file_manager );

		$this->expectException( \Exception::class );
		$json->create_from_upload( 'missing_file' );
	}

	/**
 * @testdox Attempting to create a json string from an upload where the file is not a .json, should result in an exception being thrown
*/
	public function test_create_from_uploads_not_json(): void {
		$file_manager = $this->createMock( File_Manager::class );
		$json         = new JSON_File_Handler( $file_manager );

		$_FILES['not_json'] = array(
			'name'     => 'not_json.txt',
			'type'     => 'text/plain',
			'tmp_name' => 'tmp_name',
			'error'    => 0,
			'size'     => 1000,
		);

		$this->expectException( \Exception::class );
		$json->create_from_upload( 'not_json' );

		unset( $_FILES['not_json'] );
	}

	/**
 * @testdox When creating a json string from an upload, the file should be uploaded to the path as defined in the file manger
*/
	public function test_create_from_uploads(): void {
		$values = array(
			'src'  => null,
			'dest' => null,
		);

		$file_manager = $this->createMock( File_Manager::class );
		$file_manager->method( 'unique_file_name' )->willReturn( 'json_file-10.json' );
		$file_manager->method( 'get_base_path' )->willReturn( '/some/path/' );
		$file_manager->method( 'file_exists' )->willReturn( true );
		$file_manager->method( 'move_uploaded_file' )->willReturnCallback(
			function ( $src, $dest ) use ( &$values ) {
				$values['src']  = $src;
				$values['dest'] = $dest;
				return true;
			}
		);

		$_FILES['json_file'] = array(
			'name'     => 'json_file.json',
			'type'     => 'application/json',
			'tmp_name' => 'tmp_name',
			'error'    => 0,
			'size'     => 1000,
		);

		$json = new JSON_File_Handler( $file_manager );

		$result = $json->create_from_upload( 'json_file' );

		$this->assertEquals( '/some/path/json_file-10.json', $result );
		$this->assertEquals( 'json_file', $values['src'] );
		$this->assertEquals( 'json_file-10.json', $values['dest'] );
	}

    /** @testdox When creating a json string from an upload, if move file fails, an exception should be thrown */
    public function test_create_from_uploads_move_fail(): void {
        $file_manager = $this->createMock( File_Manager::class );
        $file_manager->method( 'unique_file_name' )->willReturn( 'json_file-10.json' );
        $file_manager->method( 'get_base_path' )->willReturn( '/some/path/' );
        $file_manager->method( 'file_exists' )->willReturn( true );
        $file_manager->method( 'move_uploaded_file' )->willReturn( false );

        $_FILES['json_file'] = array(
            'name'     => 'json_file.json',
            'type'     => 'application/json',
            'tmp_name' => 'tmp_name',
            'error'    => 0,
            'size'     => 1000,
        );

        $json = new JSON_File_Handler( $file_manager );

        $this->expectException( \Exception::class );
        $json->create_from_upload( 'json_file' );
    }

    /** @testdox When creating a json string from an upload, if the file isnt found, even if we get a success from the file manager, an exception should be thrown */
    public function test_create_from_uploads_file_not_found(): void {
        $file_manager = $this->createMock( File_Manager::class );
        $file_manager->method( 'unique_file_name' )->willReturn( 'json_file-10.json' );
        $file_manager->method( 'get_base_path' )->willReturn( '/some/path/' );
        $file_manager->method( 'file_exists' )->willReturn( false );

        $_FILES['json_file'] = array(
            'name'     => 'json_file.json',
            'type'     => 'application/json',
            'tmp_name' => 'tmp_name',
            'error'    => 0,
            'size'     => 1000,
        );

        $json = new JSON_File_Handler( $file_manager );

        $this->expectException( \Exception::class );
        $json->create_from_upload( 'json_file' );
    }

}
