<?php

declare(strict_types=1);

/**
 * Unit Tests for the Test_Import_X_Post_Config class.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Action\Import_Tweet;

use WP_UnitTestCase;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Config;

/**
 * Tweet
 *
 * @group Unit
 * @group Action
 * @group Import_Tweet
 * @group Import_Tweet_Config
 *
 * @covers \PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Config
 */
class Test_Import_X_Post_Config extends WP_UnitTestCase {

	/**
 * @testdox It should be possible to pass the file path a constructor param and be able to fetch it
*/
	public function test_can_set_and_get_file_path(): void {
		$file_path = 'path/to/file.json';
		$config    = new Import_X_Post_Config( $file_path, 'img_path', 'processor' );
		$this->assertEquals( $file_path, $config->file_path() );

		// With no path (null)
		$config = new Import_X_Post_Config( null, 'img_path', 'processor' );
		$this->assertNull( $config->file_path() );
	}

	/**
	 * @testdox It should be possible to pass the image path a constructor param and be able to fetch it
	 */
	public function test_can_set_and_get_image_path(): void {
		$img_path = 'path/to/img';
		$config   = new Import_X_Post_Config( 'file_path', $img_path, 'processor' );
		$this->assertEquals( $img_path, $config->media_url() );

		// With no path (null)
		$config = new Import_X_Post_Config( 'file_path', null, 'processor' );
		$this->assertNull( $config->media_url() );
	}

	/**
	 * @testdox It should be possible to pass the processor a constructor param and be able to fetch it
	 */
	public function test_can_set_and_get_processor(): void {
		$processor = 'processor';
		$config    = new Import_X_Post_Config( 'file_path', 'img_path', $processor );
		$this->assertEquals( $processor, $config->processor() );
	}

	/**
	 * The on_duplicate_data provider
	 *
	 * @return array<string, array{0:string, 1:string}>
	 */
	public function on_duplicate_data(): array {
		return array(
			'new'     => array( 'new', 'new' ),
			'update'  => array( 'update', 'update' ),
			'skip'    => array( 'skip', 'skip' ),
			'invalid' => array( 'invalid', 'new' ),
		);
	}

	/**
	 * @testdox It should be possible to pass in the on duplicate action and only have this as one of the valid options. Passing an invalid option will see this cast to new
	 * @dataProvider on_duplicate_data
	 * @param string $value    The value passed.
	 * @param string $expected The expected value
	 *
	 * @return void
	 */
	public function test_can_set_and_get_on_duplicate( string $value, string $expected ): void {
		$config = new Import_X_Post_Config( 'file_path', 'img_path', 'processor', $value );
		$this->assertEquals( $expected, $config->on_duplicate() );
	}

	/**
 * @testdox It should be possible to set and get last tweet id as passed in the constructor. Can be null
*/
	public function test_can_set_and_get_last_tweet_id(): void {
		$last_tweet_id = '12345';
		$config        = new Import_X_Post_Config( 'file_path', 'img_path', 'processor', 'new', $last_tweet_id );
		$this->assertEquals( $last_tweet_id, $config->last_tweet_id() );

		// With no path (null)
		$config = new Import_X_Post_Config( 'file_path', 'img_path', 'processor', 'new', null );
		$this->assertNull( $config->last_tweet_id() );
	}

	/**
 * @testdox It should be possible to set and get the batch size as passed in the constructor. Can be null
*/
	public function test_can_set_and_get_batch_size(): void {
		$batch_size = 15;
		$config     = new Import_X_Post_Config( 'file_path', 'img_path', 'processor', 'new', null, $batch_size );
		$this->assertEquals( $batch_size, $config->batch_size() );

		// With no path (null)
		$config = new Import_X_Post_Config( 'file_path', 'img_path', 'processor', 'new', null, null );
		$this->assertEquals('10', $config->batch_size() );
	}

	/**
 * @testdox It should be possible to set and get the delay as passed in the constructor. Can be null
*/
	public function test_can_set_and_get_delay(): void {
		$delay  = 10;
		$config = new Import_X_Post_Config( 'file_path', 'img_path', 'processor', 'new', null, null, 10 );
		$this->assertEquals( $delay, $config->delay() );

		// With no path (null)
		$config = new Import_X_Post_Config( 'file_path', 'img_path', 'processor', 'new', null, null, null );
		$this->assertEquals( 0, $config->delay() );
	}
}
