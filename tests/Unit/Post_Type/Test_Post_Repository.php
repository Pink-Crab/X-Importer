<?php

declare(strict_types=1);

/**
 * Unit Tests for the Post Repository.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Post_Type;

use Exception;
use WP_UnitTestCase;
use DateTimeImmutable;
use PinkCrab\X_Importer\Post_Type\Post_Repository;

/**
 * Tweet
 *
 * @group Unit
 * @group Post_Type
 * @group Post_Repository
 */
class Test_Post_Repository extends WP_UnitTestCase {

	/**
	 * Tare down
	 *
	 * @return void
	 */
	public function tear_down(): void {
		parent::tear_down();
		\remove_all_filters( 'wp_insert_post_empty_content' );
	}
	/**
 * @testdox It should be possible to get the post type and have this defined by the App Config
*/
	public function test_get_post_type(): void {
		$post_repository = new Post_Repository( 'tweets' );
		$this->assertEquals( 'tweets', $post_repository->get_post_type() );
	}

	/**
 * @testdox It should be possible to find a post using wp query args
*/
	public function test_find_post(): void {
		// Create a post in the `post` post type
		$post_id = self::factory()->post->create( array( 'post_title' => 'Test Post' ) );

		// Create a tweets post
		$tweet_id = self::factory()->post->create(
			array(
				'post_title' => 'Test Tweet',
				'post_type'  => 'tweets',
			)
		);

		$post_repository = new Post_Repository( 'tweets' );

		$result = $post_repository->get_post( array( 'post_type' => 'tweet' ) );

		$this->assertNotNull( $result );
		$this->assertNotInstanceOf( 'WP_Error', $result );
		$this->assertInstanceOf( 'WP_Post', $result );
		$this->assertEquals( 'tweets', $result->post_type );
		$this->assertEquals( $tweet_id, $result->ID );
	}

	/**
 * @testdox It should not be possible to change the post type of a get_post query
*/
	public function test_find_post_invalid_post_type(): void {
		// Create a post in the `post` post type
		$post_id = self::factory()->post->create( array( 'post_title' => 'Test Post' ) );

		// Create a tweets post
		$tweet_id = self::factory()->post->create(
			array(
				'post_title' => 'Test Tweet',
				'post_type'  => 'tweets',
			)
		);

		$post_repository = new Post_Repository( 'tweets' );

		// This will only return the tweet post due to the post type being replaced in the call.
		$result = $post_repository->get_post( array( 'post_type' => 'post' ) );
		$this->assertEquals( $tweet_id, $result->ID );
	}

	/**
 * @testdox It should be possible to create a post for the define post type, using the passed values and get the WP_Post instance back on success
*/
	public function test_create_post(): void {
		$post_repository = new Post_Repository( 'tweets' );

		$post = $post_repository->create_post( 'Test Post', 'Test Content', 24, new DateTimeImmutable( '2000/01/12' ), 'publish' );

		$this->assertNotNull( $post );

		$this->assertInstanceOf( 'WP_Post', $post );
		$this->assertEquals( 'tweets', $post->post_type );
		$this->assertEquals( 'Test Post', $post->post_title );
		$this->assertEquals( 'Test Content', $post->post_content );
		$this->assertEquals( 24, $post->post_author );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( '2000-01-12 00:00:00', $post->post_date );
	}

	/**
 * @if there is an issue creating the post, an exception should be thrown with a meaningful message.
*/
	public function test_create_post_fail(): void {
		// Mock the wp_insert_post to return a WP_Error.
		\add_filter(
			'wp_insert_post_empty_content',
			function ( $maybe_empty, $postarr ) {
				return new \WP_Error( 'failed', 'Failed to insert post' );
			},
			10,
			2
		);

		$post_repository = new Post_Repository( 'tweets' );
		$this->expectException( Exception::class );
		$this->expectExceptionMessageMatches( '/^Failed to create post: /' );

		$post_repository->create_post( 'Test Post', 'Test Content', 24, new DateTimeImmutable( '2000/01/12' ), 'publish' );
	}
}
