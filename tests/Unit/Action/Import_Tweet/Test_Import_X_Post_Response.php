<?php

declare(strict_types=1);

/**
 * Unit Tests for the Test_Import_X_Post_Response class.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Action\Import_Tweet;

use WP_UnitTestCase;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Response;

/**
 * Tweet
 *
 * @group Unit
 * @group Action
 * @group Import_Tweet
 * @group Import_Tweet_Response
 *
 * @covers \PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Response
 */
class Test_Import_X_Post_Response extends WP_UnitTestCase {

	/**
 * @testdox It should be possible to add an array of messages and have them append them to the existing messages
*/
	public function test_can_add_messages(): void {
		$response = new Import_X_Post_Response();
		$response->set_messages( array( 'Message 1', 'Message 2' ) );
		$response->set_messages( array( 'Message 3', 'Message 4' ) );

		$this->assertEquals( array( 'Message 1', 'Message 2', 'Message 3', 'Message 4' ), $response->messages );
	}

	/**
 * @testdox When passing a tweet as processed, its id should be added the list of processed ids and also set the last tweet id
*/
	public function test_can_add_processed_tweet(): void {
		$response = new Import_X_Post_Response();
		$tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$tweet->method( 'id' )->willReturn( '12345' );

		$response->processed_tweet( $tweet );

		$this->assertEquals( array( '12345' ), $response->processed_tweet_ids() );
		$this->assertEquals( '12345', $response->last_tweet_id() );
	}

	/**
 * @testdox When passing a tweet as failed, its id should be added the list of failed ids
*/
	public function test_can_add_failed_tweet(): void {
		$response = new Import_X_Post_Response();
		$tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$tweet->method( 'id' )->willReturn( '6789' );

		$response->failed_tweet( $tweet );

		$this->assertEquals( array( '6789' ), $response->failed_tweet_ids() );
		$this->assertEquals( '6789', $response->last_tweet_id() );
	}

	/**
 * @testdox It should be possible to add an exception with a tweet to have it logged as a message and added as a failed.
*/
	public function test_can_add_exception(): void {
		$response = new Import_X_Post_Response();
		$tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
		$tweet->method( 'id' )->willReturn( '6789' );

		$exception = new \Exception( 'Test Exception' );
		$response->log_exception( $tweet, $exception );

		$this->assertEquals( array( 'Failed to process tweet: 6789. Test Exception' ), $response->messages() );
		$this->assertEquals( array( '6789' ), $response->failed_tweet_ids() );
		$this->assertEquals( '6789', $response->last_tweet_id() );
	}

    /** @testdox It should be possible to get a count of how many tweets were a success or a failure */
    public function test_can_get_tweet_counts(): void {
        $response = new Import_X_Post_Response();
        $tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
        $tweet->method( 'id' )->willReturn( '6789' );

        $response->processed_tweet( $tweet );
        $response->processed_tweet( $tweet );
        $response->processed_tweet( $tweet );
        $response->failed_tweet( $tweet );
        $response->failed_tweet( $tweet );
        $response->failed_tweet( $tweet );

        $this->assertEquals( 3, $response->total_processed() );
        $this->assertEquals( 3, $response->total_failed() );
    }

    /** @testdox It should be possible to get all the ids of the processed tweets. */
    public function test_can_get_processed_tweet_ids(): void {
        $response = new Import_X_Post_Response();
        $tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
        $tweet->method( 'id' )->willReturn( '12' );
        $response->processed_tweet( $tweet );
        
        $tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
        $tweet->method( 'id' )->willReturn( '34' );
        $response->processed_tweet( $tweet );
        
        $tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
        $tweet->method( 'id' )->willReturn( '56' );
        $response->processed_tweet( $tweet );

        $this->assertEquals( array( '12', '34', '56' ), $response->processed_tweet_ids() );
    }

    /** @testdox It should be possible to get all the ids of the failed tweets. */
    public function test_can_get_failed_tweet_ids(): void {
        $response = new Import_X_Post_Response();
        $tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
        $tweet->method( 'id' )->willReturn( '23' );
        $response->failed_tweet( $tweet );
        
        $tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
        $tweet->method( 'id' )->willReturn( '45' );
        $response->failed_tweet( $tweet );
        
        $tweet    = $this->createMock( \PinkCrab\X_Importer\Tweet\Tweet::class );
        $tweet->method( 'id' )->willReturn( '67' );
        $response->failed_tweet( $tweet );

        $this->assertEquals( array( '23', '45', '67' ), $response->failed_tweet_ids() );
    }
}
