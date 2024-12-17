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

use WP_UnitTestCase;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\X_Importer\File_System\File_Manager;
use PinkCrab\X_Importer\Post_Type\Post_Repository;
use PinkCrab\X_Importer\File_System\JSON_File_Handler;

/**
 * Tweet
 *
 * @group Unit
 * @group Post_Type
 * @group Post_Repository
 */
class Test_Post_Repository extends WP_UnitTestCase {

    /**
     * Get app config.
     * 
     * @param array $args
     * 
     * @return App_Config
     */
    protected function get_app_config( array $args ): App_Config {
        return new App_Config( $args );
    }
    
    /** @testdox It should be possible to get the post type and have this defined by the App Config */
    public function test_get_post_type(): void {
        $post_repository = new Post_Repository( 'tweets' );
        $this->assertEquals( 'tweets', $post_repository->get_post_type() );
    }

    /** @testdox It should be possible to find a post using wp query args */
    public function test_find_post(): void {
        // Create a post in the `post` post type
        $post_id = self::factory()->post->create( array( 'post_title' => 'Test Post' ) );

        // Create a tweets post 
        $tweet_id = self::factory()->post->create( array( 'post_title' => 'Test Tweet', 'post_type' => 'tweets' ) );
        
        $post_repository = new Post_Repository( 'tweets' );

        $result = $post_repository->get_post(['post_type' => 'tweet' ]);

        $this->assertNotNull( $result );
        $this->assertNotInstanceOf( 'WP_Error', $result );
        $this->assertInstanceOf( 'WP_Post', $result );
        $this->assertEquals('tweets', $result->post_type);
        $this->assertEquals($tweet_id, $result->ID);
    }

    /** @testdox It should not be possible to change the post type of a get_post query */
    public function test_find_post_invalid_post_type(): void {
        // Create a post in the `post` post type
        $post_id = self::factory()->post->create( array( 'post_title' => 'Test Post' ) );

        // Create a tweets post 
        $tweet_id = self::factory()->post->create( array( 'post_title' => 'Test Tweet', 'post_type' => 'tweets' ) );
        
        $post_repository = new Post_Repository( 'tweets' );

        // This will only return the tweet post due to the post type being replaced in the call.
        $result = $post_repository->get_post(['post_type' => 'post' ]);
        $this->assertEquals($tweet_id, $result->ID);
    }

}