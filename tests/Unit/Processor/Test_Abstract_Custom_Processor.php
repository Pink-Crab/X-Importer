<?php

declare(strict_types=1);

/**
 * Unit Tests for the Abstract Processor and its internal methods
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Processor;

use Exception;
use WP_UnitTestCase;
use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\X_Importer\Processor\Processor;

/**
 * Custom_Processor
 *
 * @group Unit
 * @group Processor
 * @group Custom_Processor
 */
class Test_Abstract_Custom_Processor extends WP_UnitTestCase {

	/**
	 * @testdox A class which extends the processor should have the setup and teardown methods called between the main call
	*/
	public function test_processor_setup_and_teardown_called(): void {
		$processor = new class() extends \PinkCrab\X_Importer\Processor\Custom_Processor {

			public function setup(): void {
				$this->messages[] = 'setup';
			}

			public function teardown(): void {
				$this->messages[] = 'teardown';
			}

			protected function process_tweet( Tweet $tweet, array $thread, string $on_duplicate ): void {
				$this->messages[] = 'process_tweet - ' . $tweet->id();
			}
		};

		$tweet = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$tweet->method( 'id' )->willReturn( '1234' );

		$processor->process( $tweet, array(), 'update' );

		$this->assertEquals( array( 'setup', 'process_tweet - 1234', 'teardown' ), $processor->get_messages() );
		$this->assertEquals( Processor::SUCCESS, $processor->get_status() );
	}

	/**
	 * @testdox A class which extends the processor should be able to catch any exceptions thrown during processing and log these with the status as failed.
	 */
	public function test_processor_exception_handling(): void {
		$processor = new class() extends \PinkCrab\X_Importer\Processor\Custom_Processor {

			protected function process_tweet( Tweet $tweet, array $thread, string $on_duplicate ): void {
				throw new Exception( 'Test Exception' );
			}
		};

		$tweet = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$tweet->method( 'id' )->willReturn( '1234' );

		$processor->process( $tweet, array(), 'update' );

		$this->assertEquals( array( 'Test Exception' ), $processor->get_messages() );
		$this->assertEquals( Processor::ERROR, $processor->get_status() );
	}

	/**
 * @testdox When the extended class is used, the tweet, thread and on duplicate values should be passed down as are.
*/
	public function test_processor_values_passed_to_process_tweet(): void {
		$processor = new class() extends \PinkCrab\X_Importer\Processor\Custom_Processor {

			protected function process_tweet( Tweet $tweet, array $thread, string $on_duplicate ): void {
				$this->messages[] = 'tweet id - ' . $tweet->id();
                $this->messages[] = 'on duplicated - ' . $on_duplicate;

                foreach ( $thread as $thread_tweet ) {
                    $this->messages[] = 'thread tweet id - ' . $thread_tweet->id();
                }
			}
		};

		$tweet = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$tweet->method( 'id' )->willReturn( '1234' );

		$thread1 = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$thread1->method( 'id' )->willReturn( '5678' );
		$thread2 = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$thread2->method( 'id' )->willReturn( '91011' );

		$processor->process( $tweet, array( $thread1, $thread2 ), 'update' );

		$expected = [
            'tweet id - 1234',
            'on duplicated - update',
            'thread tweet id - 5678',
            'thread tweet id - 91011',
        ];
        
        $this->assertEquals( $expected, $processor->get_messages() );
		$this->assertEquals( Processor::SUCCESS, $processor->get_status() );
	}
}
