<?php


declare(strict_types=1);

/**
 * Unit Tests for the JSON File Handler class
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Media;

use Exception;
use WP_UnitTestCase;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\X_Importer\Media\Media_Upload;
use PinkCrab\X_Importer\File_System\File_Manager;
use PinkCrab\X_Importer\Tests\Tools\Fixture_Helpers;

/**
 * Tweet
 *
 * @group Unit
 * @group Media
 * @group Media_Upload
 */
class Test_Media_Upload extends WP_UnitTestCase {

	/**
	 * Clear the uploads directory on tear down
	 *
	 * @return void
	 */
	public function tear_down(): void {
		parent::tear_down();
		Fixture_Helpers::clear_uploads();
	}


	/**
 * @testdox Attempting to import an image from a URL that returns HTML should fail.
*/
	public function test_import_image_from_url_html(): void {
		$media_upload = new Media_Upload();
		$this->expectException( \Exception::class );
		$media_upload->create_from_remote_path( 'https://www.google.com', 'file.jpg' );
	}

	/**
 * @testdox When a valid image is used, it should be created locally and added to the media library
*/
	public function test_import_image_from_url(): void {
		$media_upload = new Media_Upload();
		$result       = $media_upload->create_from_remote_path( PC_X_IMPORTER_VALID_IMG_URL, 'bird.jpeg' );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'attachment_id', $result );
		$this->assertIsNumeric( $result['attachment_id'] );
		$this->assertGreaterThan( 0, $result['attachment_id'] );

		// Check the paths.
		$this->assertArrayHasKey( 'full_path', $result );
		$this->assertSame( PC_X_IMPORTER_FIXTURES . 'uploads/bird.jpeg', $result['full_path'] );
		$this->assertArrayHasKey( 'full_url', $result );
		$this->assertSame( 'https://example.org/wp-content/uploads/bird.jpeg', $result['full_url'] );

		// Check we have expected sizes.
		// !!! THIS MIGHT NEED TO BE UPDATED TO REFLECT THE SIZES OF THE IMAGES IN FUTURE.
		$this->assertArrayHasKey( 'sizes', $result );

		$expected = array( 'thumbnail', 'medium', 'medium_large', 'large' );

		// Check all sizes are present.
		foreach ( $expected as $size ) {
			$this->assertArrayHasKey( $size, $result['sizes'] );
			$this->assertMatchesRegularExpression( '/bird[-_]?\d+x\d+\.jpeg/', $result['sizes'][ $size ]['name'] );
		}

		// Check the valid values are set for each size.
		foreach ( $result['sizes'] as $size ) {
			$this->assertArrayHasKey( 'name', $size );
			$this->assertArrayHasKey( 'url', $size );
			$this->assertArrayHasKey( 'path', $size );
			$this->assertArrayHasKey( 'width', $size );
			$this->assertArrayHasKey( 'height', $size );
			$this->assertArrayHasKey( 'filesize', $size );
			$this->assertArrayHasKey( 'mime-type', $size );

            $this->assertSame('image/jpeg', $size['mime-type']);

            $this->assertIsNumeric($size['width']);
            $this->assertGreaterThan(0, $size['width']);

            $this->assertIsNumeric($size['height']);
            $this->assertGreaterThan(0, $size['height']);

			// The file path should contain the width and height.
			$width  = $size['width'];
			$height = $size['height'];
			$this->assertMatchesRegularExpression( "/bird[-_]{$width}x{$height}\.jpeg/", $size['name'] );

			// The file path should be in the uploads directory.
			$this->assertStringContainsString( PC_X_IMPORTER_FIXTURES . 'uploads', $size['path'] );
		}
	}

	/**
 * @testdox When attempting to create the local version of the file, an exception should be thrown if an error is encountered and no path is generated
*/
	public function test_import_image_from_url_error(): void {
		$media_upload = new Media_Upload();
		// Mock the instance of the file manager to return false.
		$file_manager = $this->createMock( File_Manager::class );
		$file_manager->method( 'create_file' )->willReturn( false );

		Objects::set_property( $media_upload, 'file_manager', $file_manager );

		$this->expectException( \Exception::class );
		$media_upload->create_from_remote_path( PC_X_IMPORTER_VALID_IMG_URL, 'bird.jpeg' );
	}

	/**
 * @testdox If the local version of the file cant be found, even if it returns a path should result in an exception being thrown.
*/
	public function test_import_image_from_url_no_file(): void {
		$media_upload = new Media_Upload();
		// Mock the instance of the file manager to return false.
		$file_manager = $this->createMock( File_Manager::class );
		$file_manager->method( 'create_file' )->willReturn( true );

		Objects::set_property( $media_upload, 'file_manager', $file_manager );

		$this->expectException( \Exception::class );
		$media_upload->create_from_remote_path( PC_X_IMPORTER_VALID_IMG_URL, 'bird.jpeg' );
	}

    /** @testdox If its not possible to create teh media wp post, an exception should be thrown */
    public function test_import_image_from_url_no_post(): void {
        // Mock the wp_insert_post to return a WP_Error.
        \add_filter(
            'wp_insert_post_empty_content',
            function( $maybe_empty, $postarr ) {
                return new \WP_Error( 'failed', 'Failed to insert post' );
            },
            10,
            2
        );

        $media_upload = new Media_Upload();
        $this->expectException( \Exception::class );

        $media_upload->create_from_remote_path( PC_X_IMPORTER_VALID_IMG_URL, 'bird.jpeg' );

    }

    /** @testdox When getting the remote file contents, if an error accures, an exception should be thrown */
    public function test_get_remote_file_contents_error(): void {
    
        add_filter('pre_http_request', function( $response, $parsed_args, $url ) {
            return new \WP_Error( 'failed', 'Failed to get remote file contents' );
        }, 10, 3 );
        
        $media_upload = new Media_Upload();
        $this->expectException( \Exception::class );
        $media_upload->create_from_remote_path( PC_X_IMPORTER_VALID_IMG_URL, 'bird.jpeg' );

        // Clear the filter.
        \remove_all_filters( 'pre_http_request' );
    }
}
