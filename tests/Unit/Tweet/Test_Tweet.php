<?php

declare(strict_types=1);

/**
 * Unit tests for the tweet model
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Tweet;

use WP_UnitTestCase;
use PinkCrab\X_Importer\Tweet\Entity\Link;
use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\Tweet\Entity\Mention;
use PinkCrab\X_Importer\Tweet\Tweet;

/**
 * Tweet
 *
 * @group Unit
 * @group Tweet
 * @group Tweet_Model
 */
class Test_Tweet extends WP_UnitTestCase {

	/**
 * @testdox It should be possible to get the set tweet id.
*/
	public function test_can_get_tweet_id(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array()
		);
		$this->assertEquals( '123', $tweet->id() );
	}

	/**
 * @testdox It should be possible to get the set tweet reply_to.
*/
	public function test_can_get_tweet_reply_to(): void {
		$tweet = new Tweet(
			'',
			'4d5s6f456er654e',
			'',
			'',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array()
		);
		$this->assertEquals( '4d5s6f456er654e', $tweet->reply_to() );
	}

	/**
 * @testdox It should be possible to get the set tweet reply_to_user.
*/
	public function test_can_get_tweet_reply_to_user(): void {
		$tweet = new Tweet(
			'',
			'',
			'someuser',
			'',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array()
		);

		$this->assertEquals( 'someuser', $tweet->reply_to_user() );
	}

	/**
 * @testdox It should be possible to get the set tweet content.
*/
	public function test_can_get_tweet_content(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'Some content',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array()
		);

		$this->assertEquals( 'Some content', $tweet->content() );
	}

	/**
 * @testdox It should be possible to get the set tweet date.
*/
	public function test_can_get_tweet_date(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'2020-01-01 12:00:00',
			0,
			0,
			array(),
			array(),
			array(),
			array()
		);

		$this->assertEquals( '2020-01-01 12:00:00', $tweet->date() );
	}

	/**
 * @testdox It should be possible to get the set tweet favorites.
*/
	public function test_can_get_tweet_favorites(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'',
			10,
			0,
			array(),
			array(),
			array(),
			array()
		);

		$this->assertEquals( 10, $tweet->favorites() );
	}

	/**
 * @testdox It should be possible to get the set tweet retweets.
*/
	public function test_can_get_tweet_retweets(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'',
			0,
			20,
			array(),
			array(),
			array(),
			array()
		);

		$this->assertEquals( 20, $tweet->retweets() );
	}

	/**
 * @testdox It should be possible to get the set tweet hashtags.
*/
	public function test_can_get_tweet_hashtags(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'',
			0,
			0,
			array( 'tag1', 'tag2' ), // Hashtags
			array(),
			array(),
			array()
		);

		$this->assertEquals( array( 'tag1', 'tag2' ), $tweet->hashtags() );
	}

	/**
	 * @testdox It should be possible to get the set tweet mentions.
	*/
	public function test_can_get_tweet_mentions(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array(
				new Mention( 'Mention One', 'mention1', 'm1' ),
				new Mention( 'Mention Two', 'mention2', 'm2' ),
			)
		);

		$this->assertCount( 2, $tweet->mentions() );
		$this->assertInstanceOf( Mention::class, $tweet->mentions()[0] );
		$this->assertInstanceOf( Mention::class, $tweet->mentions()[1] );

		$this->assertEquals( 'Mention One', $tweet->mentions()[0]->name() );
		$this->assertEquals( 'mention1', $tweet->mentions()[0]->screen_name() );
		$this->assertEquals( 'm1', $tweet->mentions()[0]->id() );

		$this->assertEquals( 'Mention Two', $tweet->mentions()[1]->name() );
		$this->assertEquals( 'mention2', $tweet->mentions()[1]->screen_name() );
		$this->assertEquals( 'm2', $tweet->mentions()[1]->id() );
	}

	/**
 * @testdox It should not be possible to pass any other type of object as a mention.
*/
	public function test_cannot_pass_non_mention_as_mention(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array(
				new \stdClass(),
				new Mention( 'Mention Two', 'mention2', 'm2' ),
			)
		);

		$this->assertCount( 1, $tweet->mentions() );
		$this->assertInstanceOf( Mention::class, $tweet->mentions()[0] );
	}

	/**
 * @testdox It should be possible to get the set links.
*/
	public function test_can_get_tweet_links(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'',
			0,
			0,
			array(),
			array(
				new Link( 'https://short.lnk', 'https://full.link', 'display' ),
				new Link( 'https://short2.lnk', 'https://full2.link', 'display2' ),
			),
			array(),
			array()
		);

		$this->assertCount( 2, $tweet->links() );
		$this->assertInstanceOf( Link::class, $tweet->links()[0] );
		$this->assertInstanceOf( Link::class, $tweet->links()[1] );
        
        $this->assertEquals( 'https://short.lnk', $tweet->links()[0]->url() );
        $this->assertEquals( 'https://full.link', $tweet->links()[0]->expanded_url() );
        $this->assertEquals( 'display', $tweet->links()[0]->display_url() );

        $this->assertEquals( 'https://short2.lnk', $tweet->links()[1]->url() );
        $this->assertEquals( 'https://full2.link', $tweet->links()[1]->expanded_url() );
        $this->assertEquals( 'display2', $tweet->links()[1]->display_url() );
	}

	/**  @testdox It should not be possible to pass any other type of object as a link.  */
	public function test_cannot_pass_non_link_as_link(): void {
		$tweet = new Tweet(
			'',
			'',
			'',
			'',
			'',
			0,
			0,
			array(),
			array(
				new \stdClass(),
				new Link( 'https://short2.lnk', 'https://full2.link', 'display2' ),
			),
			array(),
			array()
		);

		$this->assertCount( 1, $tweet->links() );
		$this->assertInstanceOf( Link::class, $tweet->links()[0] );
	}

    /** @testdox It should be possible to get the set media. */
    public function test_can_get_tweet_media(): void {
        $tweet = new Tweet(
            '',
            '',
            '',
            '',
            '',
            0,
            0,
            array(),
            array(),
            array(
                new Media( '123', 'https://media.lnk/img/some-name.jpg', 'image', 'https://display.dis' ),
                new Media( '456', 'https://media2.lnk/file.png', 'video', 'https://display2.dis' ),
            ),
            array()
        );

        $this->assertCount( 2, $tweet->media() );
        $this->assertInstanceOf( Media::class, $tweet->media()[0] );
        $this->assertInstanceOf( Media::class, $tweet->media()[1] );

        $this->assertEquals( '123', $tweet->media()[0]->id() );
        $this->assertEquals( 'https://media.lnk/img/some-name.jpg', $tweet->media()[0]->url() );
        $this->assertEquals( 'image', $tweet->media()[0]->type() );
        $this->assertEquals( 'https://display.dis', $tweet->media()[0]->display_url() );

        $this->assertEquals( '456', $tweet->media()[1]->id() );
        $this->assertEquals( 'https://media2.lnk/file.png', $tweet->media()[1]->url() );
        $this->assertEquals( 'video', $tweet->media()[1]->type() );
        $this->assertEquals( 'https://display2.dis', $tweet->media()[1]->display_url() );
    }
}
