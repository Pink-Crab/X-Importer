<?php

declare(strict_types=1);

/**
 * Tweet Collection.
 *
 * This class is responsible for managing a collection of tweets.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tweet;

use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\Tweet\Entity\Link;
use PinkCrab\X_Importer\Tweet\Entity\Mention;

/**
 * Tweet Collection.
 *
 * @phpstan-type TweetRawLink array{
 *  url: string,
 *  expanded_url: string,
 *  display_url: string
 * }
 *
 * @phpstan-type TweetRawMedia array{
 *  id_str: string,
 *  media_url_https: string,
 *  type: string,
 *  url: string
 * }
 *
 * @phpstan-type TweetRawMention array{
 *  name: string,
 *  screen_name: string,
 *  id_str: string
 * }
 *
 * @phpstan-type TweetRawHashtag array{
 *  text: string,
 *  indices: array<int, int>
 * }
 *
 * @phpstan-type TweetRaw array{
 *  id: string,
 *  in_reply_to_status_id?: string,
 *  in_reply_to_user_id?: string,
 *  full_text: string,
 *  created_at: string,
 *  favorite_count: int,
 *  retweet_count: int,
 *  entities: array{
 *      hashtags: TweetRawHashtag[],
 *      urls: TweetRawLink[],
 *      media:  TweetRawMedia[],
 *      user_mentions:  TweetRawMention[]
 *  }
 * }
 */
class Tweet_Collection {

	/**
	 * The JSON representation of the tweets.
	 *
	 * @var string
	 */
	protected $json;

	/**
	 * The collection of tweets.
	 *
	 * @var mixed[]|null
	 */
	protected $tweets = null;

	/**
	 * Creates a new instance of the Tweet_Collection.
	 *
	 * @param string $json The JSON representation of the tweets.
	 */
	public function __construct( string $json ) {
		$this->json = $json;
	}

	/**
	 * Parse the JSON.
	 *
	 * @return void
	 */
	protected function parse_tweets(): void {
		$this->tweets = json_decode( $this->json, true );
	}

	/**
	 * Get the next tweet in the collection.
	 *
	 * @param string  $id              The tweet ID, passing and empty string will get the first tweet.
	 * @param boolean $only_own_tweets If only the users tweets should be returned.
	 *
	 * @return Tweet|null
	 */
	public function get_next_tweet( string $id = '', bool $only_own_tweets = false ): ?Tweet {
		// If we have not yet parsed the tweets, do so now.
		if ( is_null( $this->tweets ) ) {
			$this->parse_tweets();
		}

		// If we have no tweets, return null.
		if ( empty( $this->tweets ) ) {
			return null;
		}

		$located = false;

		// Iterate over tweets.
		foreach ( $this->tweets as $tweet ) {
			// If we dont have the tweet index, skip.
			if ( ! isset( $tweet['tweet'] ) ) {
				continue;
			}
			$tweet = $tweet['tweet'];

			// If we are only return own tweets and this is a reply, skip.
			if ( $only_own_tweets && ! empty( $tweet['in_reply_to_status_id'] ) ) {
				continue;
			}

			// If we have no current id, send the first.
			if ( '' === $id ) {
				return $this->map_tweet( $tweet );
			}

			// If located has been setm return this tweet.
			if ( $located ) {
				return $this->map_tweet( $tweet );
			}

			// If we have the ID, check if this is the next tweet.
			if ( $tweet['id'] === $id ) {
				$located = true;
				continue;
			}
		}

		return null;
	}

	/**
	 * Get threaded tweets.
	 *
	 * Recursively gets all tweets in a thread.
	 *
	 * @param string $id The tweet ID.
	 *
	 * @return Tweet[]
	 */
	public function get_threaded_tweets( string $id ): array {
		// If we have not yet parsed the tweets, do so now.
		if ( is_null( $this->tweets ) ) {
			$this->parse_tweets();
		}

		// If we have no tweets, return null.
		if ( empty( $this->tweets ) ) {
			return array();
		}

		$thread = array();
		$found  = false;

		// Iterate over tweets.
		foreach ( $this->tweets as $tweet ) {
			// dd($tweet);
			// If we dont have the tweet index, skip.
			if ( ! isset( $tweet['tweet'] ) ) {
				continue;
			}
			$tweet = $tweet['tweet'];

			// The tweet was in reply to our tweet.
			if ( isset( $tweet['in_reply_to_status_id'] ) && $tweet['in_reply_to_status_id'] === $id ) {
				$found    = true;
				$thread[] = $this->map_tweet( $tweet );
				$thread   = array_merge( $thread, $this->get_threaded_tweets( $tweet['id'] ) );
			}
		}

		return $thread;
	}

	/**
	 * Maps a tweet array to a Tweet object.
	 *
	 * @param TweetRaw $tweet The tweet array.
	 *
	 * @return Tweet
	 */
	protected function map_tweet( array $tweet ): Tweet { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint, using phpstan types.
		return new Tweet(
			esc_attr( $tweet['id'] ),
			array_key_exists( 'in_reply_to_status_id', $tweet ) ? esc_attr( $tweet['in_reply_to_status_id'] ) : '',
			\array_key_exists( 'in_reply_to_user_id', $tweet ) ? esc_attr( $tweet['in_reply_to_user_id'] ) : '',
			array_key_exists( 'full_text', $tweet ) ? \wp_kses_post( $tweet['full_text'] ) : '',
			array_key_exists( 'created_at', $tweet ) ? esc_html( $tweet['created_at'] ) : '',
			array_key_exists( 'favorite_count', $tweet ) ? (int) $tweet['favorite_count'] : 0,
			array_key_exists( 'retweet_count', $tweet ) ? (int) $tweet['retweet_count'] : 0,
			array_key_exists( 'hashtags', $tweet['entities'] ) ? $this->map_hashtags( $tweet['entities']['hashtags'] ) : array(),
			array_key_exists( 'urls', $tweet['entities'] ) ? $this->map_urls( $tweet['entities']['urls'] ) : array(),
			array_key_exists( 'media', $tweet['entities'] ) ? $this->map_media( $tweet['entities']['media'] ) : array(),
			array_key_exists( 'user_mentions', $tweet['entities'] ) ? $this->map_mentions( $tweet['entities']['user_mentions'] ) : array()
		);
	}

	/**
	 * Maps the urls to a standard array.
	 *
	 * @param TweetRawLink[] $urls The urls to map.
	 *
	 * @return Link[]
	 */
	protected function map_urls( array $urls ): array {
		return array_map(
			fn( array $url ): Link => new Link(
				esc_url( $url['url'] ),
				esc_url( $url['expanded_url'] ),
				esc_html( $url['display_url'] )
			),
			$urls
		);
	}

	/**
	 * Maps the media to a standard array.
	 *
	 * @param TweetRawMedia[] $media The media to map.
	 *
	 * @return Media[]
	 */
	protected function map_media( array $media ): array {
		return array_map(
			fn( array $media ): Media => new Media(
				esc_html( $media['id_str'] ),
				esc_url( $media['media_url_https'] ),
				esc_html( $media['type'] ),
				esc_url( $media['url'] )
			),
			$media
		);
	}

	/**
	 * Maps the hashtags to a standard array.
	 *
	 * @param TweetRawHashtag[] $hashtags The hashtags to map.
	 *
	 * @return string[]
	 */
	protected function map_hashtags( array $hashtags ): array {
		return array_map(
			fn( array $hashtag ): string => esc_html( $hashtag['text'] ),
			$hashtags
		);
	}

	/**
	 * Maps the mentions to a standard array.
	 *
	 * @param TweetRawMention[] $mentions The mentions to map.
	 *
	 * @return Mention[]
	 */
	protected function map_mentions( array $mentions ): array {
		return array_map(
			fn ( array $mention ): Mention => new Mention(
				esc_html( $mention['name'] ),
				esc_html( $mention['screen_name'] ),
				esc_html( $mention['id_str'] )
			),
			$mentions
		);
	}
}
