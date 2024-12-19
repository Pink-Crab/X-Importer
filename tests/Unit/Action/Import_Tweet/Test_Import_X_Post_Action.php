<?php

declare(strict_types=1);

/**
 * Unit Tests for the Import_X_Post_Action class.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Action\Import_Tweet;

use WP_UnitTestCase;
use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\X_Importer\File_System\JSON_File_Handler;
use PinkCrab\X_Importer\Processor\Processor_Factory;
use PinkCrab\X_Importer\Processor\Processor;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Action;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Config;

/**
 * Tweet
 *
 * @group Unit
 * @group Action
 * @group Import_Tweet
 */
class Test_Import_X_Post_Action extends WP_UnitTestCase {

	/**
	 * Get the noop processor.
	 *
	 * @return Processor
	 */
	protected function get_noop_processor(): Processor {
		return new class() implements \PinkCrab\X_Importer\Processor\Processor {
			public $count = 0;
			public function process( Tweet $tweet, array $thread, string $on_duplicate ): void {
				++$this->count;
			}
			public function get_status(): string {
				return 'success';
			}
			public function get_messages(): array {
				return array();
			}
		};
	}

	/**
	 * @testdox The batch size defined in the args object should be how many times the actions loop is run
	 */
	public function test_batch_size_is_used_to_loop_actions(): void {
		$json_importer = $this->createMock( JSON_File_Handler::class );
		$json_importer->method( 'create_from_filename' )->willReturn( \file_get_contents( PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json' ) );

		$logging_processor = $this->get_noop_processor();

		$processors = $this->createMock( Processor_Factory::class );
		$processors->method( 'create' )->willReturn( $logging_processor );

		$config = new Import_X_Post_Config(
			PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json',
			null,
			'processor',
			'new',
			null,
			5
		);

		$action   = new Import_X_Post_Action( $json_importer, $processors );
		$response = $action->execute( $config );

		$this->assertEquals( 5, $logging_processor->count );
		$this->assertEquals( '50000', $response->last_tweet_id );
	}

	/**
 * @testdox If a tweet id is passed, this should be skipped and the next tweet as the starting point
*/
	public function test_tweet_id_is_used_as_starting_point(): void {
		$json_importer = $this->createMock( JSON_File_Handler::class );
		$json_importer->method( 'create_from_filename' )->willReturn( \file_get_contents( PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json' ) );

		$logging_processor = $this->get_noop_processor();

		$processors = $this->createMock( Processor_Factory::class );
		$processors->method( 'create' )->willReturn( $logging_processor );

		$config = new Import_X_Post_Config(
			PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json',
			null,
			'processor',
			'new',
			'50000',
			2
		);

		$action   = new Import_X_Post_Action( $json_importer, $processors );
		$response = $action->execute( $config );

		$this->assertEquals( 2, $logging_processor->count );
		$this->assertEquals( '70000', $response->last_tweet_id );
	}

	/**
 * @testdox If a no tweet can be found, the loop should break and return whatever we have
*/
	public function test_no_tweet_found_should_break_loop(): void {
		$json_importer = $this->createMock( JSON_File_Handler::class );
		$json_importer->method( 'create_from_filename' )->willReturn( \file_get_contents( PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json' ) );

		$logging_processor = $this->get_noop_processor();

		$processors = $this->createMock( Processor_Factory::class );
		$processors->method( 'create' )->willReturn( $logging_processor );

		$config = new Import_X_Post_Config(
			PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json',
			null,
			'processor',
			'new',
			'110000',
			10
		);

		$action   = new Import_X_Post_Action( $json_importer, $processors );
		$response = $action->execute( $config );
		$this->assertEquals( 0, $logging_processor->count );
		$this->assertNull( $response->last_tweet_id );
	}

	/**
	 * @testdox If an exception is thrown when processing a tweet, it should be logged as a failed tweet but also an message containing the exception should be logged.
	 */
	public function test_exception_thrown_on_tweet_processing(): void {
		$json_importer = $this->createMock( JSON_File_Handler::class );
		$json_importer->method( 'create_from_filename' )->willReturn( \file_get_contents( PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json' ) );

		$exception_processor = new class() implements \PinkCrab\X_Importer\Processor\Processor {
			public function process( Tweet $tweet, array $thread, string $on_duplicate ): void {
				$errors = array( '20000', '30000', '40000' );
				if ( in_array( $tweet->id(), $errors, true ) ) {
					throw new \Exception( 'Test Exception' );
				}
			}
			public function get_status(): string {
				return 'success';
			}
			public function get_messages(): array {
				return array();
			}
		};

		$processors = $this->createMock( Processor_Factory::class );
		$processors->method( 'create' )->willReturn( $exception_processor );

		$config = new Import_X_Post_Config(
			PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json',
			null,
			'processor',
			'new',
			null,
			10
		);

		$action   = new Import_X_Post_Action( $json_importer, $processors );
		$response = $action->execute( $config );

		$this->assertEquals( 3, count( $response->failed_tweet_ids() ) );
		$this->assertEquals( 7, count( $response->processed_tweet_ids() ) );

		// The error messages should contain the tweet ids.
		$this->assertStringContainsString( '20000', $response->messages[0] );
		$this->assertStringContainsString( '30000', $response->messages[1] );
		$this->assertStringContainsString( '40000', $response->messages[2] );
	}

	/**
 * @testdox Any messages from the processor should be padded to the results/response message array.
*/
	public function test_processor_messages_are_logged(): void {
		$json_importer = $this->createMock( JSON_File_Handler::class );
		$json_importer->method( 'create_from_filename' )->willReturn( \file_get_contents( PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json' ) );

		$message_processor = new class() implements \PinkCrab\X_Importer\Processor\Processor {
			public function process( Tweet $tweet, array $thread, string $on_duplicate ): void {
				// noop
			}
			public function get_status(): string {
				return 'success';
			}
			public function get_messages(): array {
				return array( 'Test Message' );
			}
		};

		$processors = $this->createMock( Processor_Factory::class );
		$processors->method( 'create' )->willReturn( $message_processor );

		$config = new Import_X_Post_Config(
			PC_X_IMPORTER_FIXTURES . 'data/tweets-valid.json',
			null,
			'processor',
			'new',
			null,
			1
		);

		$action   = new Import_X_Post_Action( $json_importer, $processors );
		$response = $action->execute( $config );

		$this->assertCount( 1, $response->messages );
		$this->assertContains( 'Test Message', $response->messages );
	}
}
