<?php

declare(strict_types=1);

/**
 * Unit tests for the content helper util.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Tweet;

use Gin0115\WPUnit_Helpers\Objects;
use WP_UnitTestCase;
use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\X_Importer\Tweet\Entity\Link;
use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\Util\Content_Helper;
use PinkCrab\X_Importer\Tweet\Entity\Mention;
use PinkCrab\X_Importer\Tests\Tools\Fixture_Helpers;

/**
 * Tweet
 *
 * @group Unit
 * @group Utils
 * @group Content_Helper
 */
class Test_Content_Helper extends WP_UnitTestCase {

	/**
	 * Clear the uploads directory on tear down
	 *
	 * @return void
	 */
	public function tear_down(): void {
		parent::tear_down();
		Fixture_Helpers::clear_uploads();

		// Reset the internal media url.
		$instance = new Content_Helper();
		Objects::set_property( $instance, 'remote_media_url', null );
	}


	/**
 * @testdox It should be possible to parse all hashtags in a tweets content and use the default args
*/
	public function test_can_parse_hashtags(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a #test tweet with #hashtags',
			'',
			0,
			0,
			array(
				'test',
				'hashtags',
			),
			array(),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_hashtags( $tweet->content(), $tweet );
		$this->assertEquals( 'This is a <a href="https://x.com/hashtag/test" class="hashtag" target="_blank">#test</a> tweet with <a href="https://x.com/hashtag/hashtags" class="hashtag" target="_blank">#hashtags</a>', $parsed );
	}

	/**
 * @testdox It should be possible to set a custom target for hashtags by passing the value in args.
*/
	public function test_can_set_custom_hashtag_target(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a #test tweet with #hashtags',
			'',
			0,
			0,
			array(
				'test',
				'hashtags',
			),
			array(),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_hashtags( $tweet->content(), $tweet, array( 'target' => '_self' ) );
		$this->assertEquals( 'This is a <a href="https://x.com/hashtag/test" class="hashtag" target="_self">#test</a> tweet with <a href="https://x.com/hashtag/hashtags" class="hashtag" target="_self">#hashtags</a>', $parsed );
	}

	/**
 * @testdox It should be possible to set a custom class for hashtags by passing the value in args.
*/
	public function test_can_set_custom_hashtag_class(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a #test tweet with #hashtags',
			'',
			0,
			0,
			array(
				'test',
				'hashtags',
			),
			array(),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_hashtags( $tweet->content(), $tweet, array( 'class' => 'custom-class' ) );
		$this->assertEquals( 'This is a <a href="https://x.com/hashtag/test" class="custom-class" target="_blank">#test</a> tweet with <a href="https://x.com/hashtag/hashtags" class="custom-class" target="_blank">#hashtags</a>', $parsed );
	}

	/**
 * @testdox it should be possible to use the callable in the args, to totally customise how the links is parsed.
*/
	public function test_can_use_callable_for_hashtags(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'Some #hashtag',
			'',
			0,
			0,
			array(
				'test',
			),
			array(),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_hashtags(
			$tweet->content(),
			$tweet,
			array(
				'callable' => function ( string $link, string $url, string $text ): string {
					return ">> was {$link} now url: {$url} text: {$text} <<";
				},
			)
		);
		$this->assertEquals( 'Some >> was <a href="https://x.com/hashtag/hashtag" class="hashtag" target="_blank">#hashtag</a> now url: https://x.com/hashtag/hashtag text: #hashtag <<', $parsed );
	}

	/**
 * @testdox It should be possible to take a tweet and parse its URL and have the default args applied to the links.
*/
	public function test_can_parse_urls(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a link to https://t.co/y2gpEVvjAd',
			'',
			0,
			0,
			array(),
			array(
				new Link( 'https://t.co/y2gpEVvjAd', 'https://youtu.be/C47ZCosJPAw', 'youtu.be/C47ZCosJPAw' ),
			),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_urls( $tweet->content(), $tweet );
		$this->assertEquals( 'This is a tweet with a link to <a href="https://youtu.be/C47ZCosJPAw" class="link" target="_blank">youtu.be/C47ZCosJPAw</a>', $parsed );
	}

	/**
 * @testdox It should be possible to set a custom target for links by passing the value in args.
*/
	public function test_can_set_custom_link_target(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a link to https://t.co/y2gpEVvjAd',
			'',
			0,
			0,
			array(),
			array(
				new Link( 'https://t.co/y2gpEVvjAd', 'https://youtu.be/C47ZCosJPAw', 'youtu.be/C47ZCosJPAw' ),
			),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_urls( $tweet->content(), $tweet, array( 'target' => '_self' ) );
		$this->assertEquals( 'This is a tweet with a link to <a href="https://youtu.be/C47ZCosJPAw" class="link" target="_self">youtu.be/C47ZCosJPAw</a>', $parsed );
	}

	/**
 * @testdox It should be possible to set a custom class for links by passing the value in args.
*/
	public function test_can_set_custom_link_class(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a link to https://t.co/y2gpEVvjAd',
			'',
			0,
			0,
			array(),
			array(
				new Link( 'https://t.co/y2gpEVvjAd', 'https://youtu.be/C47ZCosJPAw', 'youtu.be/C47ZCosJPAw' ),
			),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_urls( $tweet->content(), $tweet, array( 'class' => 'custom-class' ) );
		$this->assertEquals( 'This is a tweet with a link to <a href="https://youtu.be/C47ZCosJPAw" class="custom-class" target="_blank">youtu.be/C47ZCosJPAw</a>', $parsed );
	}

	/**
 * @testdox it should be possible to use the callable in the args, to totally customise how the links is parsed.
*/
	public function test_can_use_callable_for_links(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a link to https://t.co/y2gpEVvjAd',
			'',
			0,
			0,
			array(),
			array(
				new Link( 'https://t.co/y2gpEVvjAd', 'https://youtu.be/C47ZCosJPAw', 'youtu.be/C47ZCosJPAw' ),
			),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_urls(
			$tweet->content(),
			$tweet,
			array(
				'callable' => function ( string $link, string $url, string $text ): string {
					return ">> was {$link} now url: {$url} text: {$text} <<";
				},
			)
		);
		$this->assertEquals( 'This is a tweet with a link to >> was <a href="https://youtu.be/C47ZCosJPAw" class="link" target="_blank">youtu.be/C47ZCosJPAw</a> now url: https://youtu.be/C47ZCosJPAw text: youtu.be/C47ZCosJPAw <<', $parsed );
	}

	/**
 * @testdox if a link existing in the urls array that doesnt exist in the content should not be written and any link in the content, thats not in the array of links will be ignored and the short link shown.
*/
	public function test_should_ignore_links_not_in_content(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a link to https://t.co/qwer4Tdgsd',
			'',
			0,
			0,
			array(),
			array(
				new Link( 'https://t.co/y2gpEVvjAd', 'https://youtu.be/C47ZCosJPAw', 'youtu.be/C47ZCosJPAw' ),
			),
			array(),
			array()
		);

		$parsed = Content_Helper::populate_urls( $tweet->content(), $tweet );
		$this->assertEquals( 'This is a tweet with a link to https://t.co/qwer4Tdgsd', $parsed );
	}

	/**
 * @testdox It should be possible to have all mentions replaced with links to the users profile page.
*/
	public function test_can_parse_mentions(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a mention to @username',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array(
				new Mention( 'Display Name', 'username', '123456' ),
			),
		);

		$parsed = Content_Helper::populate_mentions( $tweet->content(), $tweet );
		$this->assertEquals( 'This is a tweet with a mention to <a href="https://x.com/username" class="mention" target="_blank">@username</a>', $parsed );
	}

	/**
	 * @testdox It should be possible to set a custom target for mentions by passing the value in args.
	 */
	public function test_can_set_custom_mention_target(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a mention to @username',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array(
				new Mention( 'Display Name', 'username', '123456' ),
			),
		);

		$parsed = Content_Helper::populate_mentions( $tweet->content(), $tweet, array( 'target' => '_self' ) );
		$this->assertEquals( 'This is a tweet with a mention to <a href="https://x.com/username" class="mention" target="_self">@username</a>', $parsed );
	}

	/**
	 * @testdox It should be possible to set a custom class for mentions by passing the value in args.
	 */
	public function test_can_set_custom_mention_class(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a mention to @username',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array(
				new Mention( 'Display Name', 'username', '123456' ),
			),
		);

		$parsed = Content_Helper::populate_mentions( $tweet->content(), $tweet, array( 'class' => 'custom-class' ) );
		$this->assertEquals( 'This is a tweet with a mention to <a href="https://x.com/username" class="custom-class" target="_blank">@username</a>', $parsed );
	}

	/**
	 * @testdox it should be possible to use the callable in the args, to totally customise how the mentions is parsed.
	 */
	public function test_can_use_callable_for_mentions(): void {
		$tweet = new Tweet(
			'123',
			'',
			'',
			'This is a tweet with a mention to @username',
			'',
			0,
			0,
			array(),
			array(),
			array(),
			array(
				new Mention( 'Display Name', 'username', '123456' ),
			),
		);

		$parsed = Content_Helper::populate_mentions(
			$tweet->content(),
			$tweet,
			array(
				'callable' => function ( string $link, string $url, string $text ): string {
					return ">> was {$link} now url: {$url} text: {$text} <<";
				},
			)
		);

		$this->assertEquals( 'This is a tweet with a mention to >> was <a href="https://x.com/username" class="mention" target="_blank">@username</a> now url: https://x.com/username text: @username <<', $parsed );
	}

	/**
 * @testdox It should be possible to be passed a Media object and have it uploaded to the media library [PICTURE]
*/
	public function test_can_upload_media(): void {
		$uploaded = Content_Helper::upload_media( new Media( '11', PC_X_IMPORTER_VALID_IMG_URL, 'photo', 'https://t.co/123456' ) );

		$this->assertIsArray( $uploaded );
		$this->assertArrayHasKey( 'attachment_id', $uploaded );
		$this->assertIsNumeric( $uploaded['attachment_id'] );

		$attachment = get_post( $uploaded['attachment_id'] );
		$this->assertEquals( 'image/jpeg', $attachment->post_mime_type );
		$this->assertEquals( 'bird', $attachment->post_name );
		$this->assertEquals( $uploaded['full_url'], $attachment->guid );
	}

	/**
 * @testdox It should be  possible to define a custom base URL and the images should be looked for in this dir
*/
	public function test_can_upload_media_with_custom_base_url(): void {
		// remove /bird.jpeg from the end of the url.
		$remote = str_replace( '/bird.jpeg', '', PC_X_IMPORTER_VALID_IMG_URL );
		Content_Helper::set_remote_media_url( $remote );

		$uploaded = Content_Helper::upload_media(
			new Media( '11', 'https://some.where/bird.jpeg', 'photo', 'https://t.co/123456' )
		);
		// dd($uploaded);
		$this->assertIsArray( $uploaded );
		$this->assertArrayHasKey( 'attachment_id', $uploaded );
		$this->assertIsNumeric( $uploaded['attachment_id'] );

		$attachment = get_post( $uploaded['attachment_id'] );
		$this->assertEquals( 'image/jpeg', $attachment->post_mime_type );
		$this->assertEquals( 'bird', $attachment->post_name );
		$this->assertEquals( $uploaded['full_url'], $attachment->guid );
	}
}
