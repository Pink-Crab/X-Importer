<?php

declare(strict_types=1);

/**
 * Unit tests for the tweet collection object
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Tweet;

use Gin0115\WPUnit_Helpers\Objects;
use WP_UnitTestCase;
use PinkCrab\X_Importer\Tweet\Entity\Link;
use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\Tweet\Entity\Mention;
use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\X_Importer\Tweet\Tweet_Collection;

/**
 * Tweet
 *
 * @group Unit
 * @group Tweet
 * @group Tweet_Collection
 */
class Test_Tweet_Collection extends WP_UnitTestCase {

	/**
 * @testdox It should be possible to create an instance of the collection and the internal JSON will not be decoded until called.
*/
	public function test_can_create_instance_of_tweet_collection(): void {
		$tweets = new Tweet_Collection( '[]' );

		$this->assertNull( Objects::get_property( $tweets, 'tweets' ) );

		// After calling  a function, this should be unpacked.
		$tweets->get_next_tweet();

		$this->assertIsArray( Objects::get_property( $tweets, 'tweets' ) );
	}

	/**
 * @testdox When trying to get the next tweet, passing an empty string for the id, will return the first one.
*/
	public function test_can_get_first_tweet(): void {
		$tweets = new Tweet_Collection( '[{"tweet":{"id":"123","entities":{"media":[]}}},{"tweet":{"id":"456","entities":{"media":[]}}}]' );
		$tweet  = $tweets->get_next_tweet( '' );
		$this->assertEquals( $tweet->id(), '123' );
	}

	/**
 * @testdox When attempting to get the next tweet, if selected only initial tweets should be returned, not replies.
*/
	public function test_can_get_next_tweet_only_initial_tweets_without_index(): void {
		$tweets = new Tweet_Collection( '[{"tweet":{"id":"123","in_reply_to_status_id":"789","entities":{"media":[]}}},{"tweet":{"id":"456","entities":{"media":[]}}}]' );
		$tweet  = $tweets->get_next_tweet( '', true );
		$this->assertEquals( $tweet->id(), '456' );
	}

	/**
 * @testdox When decoding the tweet, if its not held under an intial index of `tweet`, skip
*/
	public function test_can_skip_invalid_tweet(): void {
		$tweets = new Tweet_Collection( '[{"id":"123","entities":{"media":[]}},{"tweet":{"id":"456","entities":{"media":[]}}}]' );
		$tweet  = $tweets->get_next_tweet( '' );
		$this->assertEquals( $tweet->id(), '456' );
	}

	/**
 * @testdox When a tweet is found, we return the next tweet in the collection.
*/
	public function test_can_get_next_tweet(): void {
		$tweets = new Tweet_Collection( '[{"tweet":{"id":"123","entities":{"media":[]}}},{"tweet":{"id":"456","entities":{"media":[]}}}]' );
		$tweet  = $tweets->get_next_tweet( '123' );
		$this->assertEquals( $tweet->id(), '456' );
	}

    /** @testdox When there are no more tweets left to get the next of, null will be returned. */
    public function test_no_more_tweets_null_returned(): void {
        $tweets = new Tweet_Collection( '[{"tweet":{"id":"123","entities":{"media":[]}}}]' );
        $tweet  = $tweets->get_next_tweet( '123' );
        $this->assertNull( $tweet );
    }

	/**
 * @testdox It should be possible to find next tweet while respecting only initial tweets
*/
	public function test_can_get_next_tweet_only_initial_tweets(): void {
		$tweets = new Tweet_Collection( '[{"tweet":{"id":"123","in_reply_to_status_id":"987","entities":{"media":[]}}},{"tweet":{"id":"456","entities":{"media":[]}}},{"tweet":{"id":"789","in_reply_to_status_id":"123","entities":{"media":[]}}},{"tweet":{"id":"abc","entities":{"media":[]}}}]' );
		$tweet  = $tweets->get_next_tweet( '456', true );
		$this->assertEquals( $tweet->id(), 'abc' );
	}

	/**
 * @testdox It should be possible to get a thread of replies a given tweet id
*/
	public function test_can_get_thread_of_replies(): void {

		// This has 2 chains starting at a1 and b1. a1 ends on a4 and b1 ends on b2
		$tweets_json = '[{"tweet":{"id":"a1","entities":[]}},{"tweet":{"id":"a2","entities":[],"in_reply_to_status_id":"a1"}},{"tweet":{"id":"b1","entities":[]}},{"tweet":{"id":"b2","entities":[],"in_reply_to_status_id":"b1"}},{"tweet":{"id":"a3","entities":[],"in_reply_to_status_id":"a2"}},{"tweet":{"id":"a4","entities":[],"in_reply_to_status_id":"a3"}}]';

		$tweets   = new Tweet_Collection( $tweets_json );
		$children = $tweets->get_threaded_tweets( 'a1' );

		$this->assertCount( 3, $children );
		$this->assertEquals( 'a2', $children[0]->id() );
		$this->assertEquals( 'a1', $children[0]->reply_to() );

		$this->assertEquals( 'a3', $children[1]->id() );
		$this->assertEquals( 'a2', $children[1]->reply_to() );

		$this->assertEquals( 'a4', $children[2]->id() );
		$this->assertEquals( 'a3', $children[2]->reply_to() );
	}

	/**
 * @testdox If there are no tweets loaded, no attempt to find children will be performed.
*/
	public function test_no_tweets_loaded_no_children(): void {
		$tweets   = new Tweet_Collection( '[]' );
		$children = $tweets->get_threaded_tweets( 'a1' );
		$this->assertEmpty( $children );
	}

	/** @testdox Any malformed tweet will be ignored, the model should be held under a parent key of `tweet` */
	public function test_malformed_tweet_ignored(): void {
		$tweets   = new Tweet_Collection( '[{"tweet":{"id":"a1","entities":[]}},{"invalid":{"id":"a2","entities":[],"in_reply_to_status_id":"a1"}}]' );
		$children = $tweets->get_threaded_tweets( 'a1' );
		$this->assertEmpty( $children );
	}

    /** @testdox A tweet should have it entities correctly mapped to there models */
    public function test_can_map_tweet_entities(): void {
        $tweets_json = '[{"tweet":{"id":"a1","entities":{"media":[{"id_str":"123","media_url_https":"http:\/\/example.com/img.jpg","type":"photo","url":"http:\/\/example.com"}],"urls":[{"url":"https:\/\/short.lnk","expanded_url":"https:\/\/full.link","display_url":"display"},{"url":"https:\/\/short2.lnk","expanded_url":"https:\/\/full2.link","display_url":"display2"}],"user_mentions":[{"id_str":"123","screen_name":"user","name":"User"}],"hashtags":[{"text":"tag"},{"text":"tag2"}]}}}]';
        $tweets   = new Tweet_Collection( $tweets_json );
        $tweet = $tweets->get_next_tweet();
        
        $this->assertCount( 1, $tweet->media() );
        $this->assertInstanceOf( Media::class, $tweet->media()[0] );
        $this->assertEquals( '123', $tweet->media()[0]->id() );
        $this->assertEquals( 'http://example.com/img.jpg', $tweet->media()[0]->url() );
        $this->assertEquals( 'photo', $tweet->media()[0]->type() );
        $this->assertEquals( 'http://example.com', $tweet->media()[0]->display_url() );

        $this->assertCount( 2, $tweet->links() );
        $this->assertInstanceOf( Link::class, $tweet->links()[0] );
        $this->assertInstanceOf( Link::class, $tweet->links()[1] );
        $this->assertEquals( 'https://short.lnk', $tweet->links()[0]->url() );
        $this->assertEquals( 'https://full.link', $tweet->links()[0]->expanded_url() );
        $this->assertEquals( 'display', $tweet->links()[0]->display_url() );
        $this->assertEquals( 'https://short2.lnk', $tweet->links()[1]->url() );
        $this->assertEquals( 'https://full2.link', $tweet->links()[1]->expanded_url() );
        $this->assertEquals( 'display2', $tweet->links()[1]->display_url() );

        $this->assertCount( 1, $tweet->mentions() );
        $this->assertInstanceOf( Mention::class, $tweet->mentions()[0] );
        $this->assertEquals( '123', $tweet->mentions()[0]->id() );
        $this->assertEquals( 'user', $tweet->mentions()[0]->screen_name() );
        $this->assertEquals( 'User', $tweet->mentions()[0]->name() );

        $this->assertCount( 2, $tweet->hashtags() );
        $this->assertEquals( 'tag', $tweet->hashtags()[0] );
        $this->assertEquals( 'tag2', $tweet->hashtags()[1] );        
    }


	private function ff() {
		return json_encode(
			array(
				array(
					'tweet' => array(
						'id'       => 'a1',
						'entities' => array(
                            'media' => array(
                                array(
                                    'id_str' => '123',
                                    'media_url_https' => 'http://example.com',
                                    'type' => 'photo',
                                    'url' => 'http://example.com',
                                )
                            ),
                            'urls' => array(
                                array(
                                    'url' => 'https://short.lnk',
                                    'expanded_url' => 'https://full.link',
                                    'display_url' => 'display',
                                ),
                                array(
                                    'url' => 'https://short2.lnk',
                                    'expanded_url' => 'https://full2.link',
                                    'display_url' => 'display2',
                                ),
                            ),
                            'user_mentions' => array(
                                array(
                                    'id_str' => '123',
                                    'screen_name' => 'user',
                                    'name' => 'User',
                                ),
                            ),
                            'hashtags' => array(
                                array(
                                    'text' => 'tag',
                                ),
                                array(
                                    'text' => 'tag2',
                                ),
                            ),
                        ),

					),
				),
				// array(
				// 	'tweet' => array(
				// 		'id'       => 'b1',
				// 		'entities' => array(),
				// 	),
				// ),

				// array(
				// 	'tweet' => array(
				// 		'id'                    => 'b2',
				// 		'entities'              => array(),
				// 		'in_reply_to_status_id' => 'b1',
				// 	),
				// ),
				// array(
				// 	'tweet' => array(
				// 		'id'                    => 'a3',
				// 		'entities'              => array(),
				// 		'in_reply_to_status_id' => 'a2',

				// 	),
				// ),
				// array(
				// 	'tweet' => array(
				// 		'id'                    => 'a4',
				// 		'entities'              => array(),
				// 		'in_reply_to_status_id' => 'a3',

				// 	),
				// ),
			)
		);
	}
}
