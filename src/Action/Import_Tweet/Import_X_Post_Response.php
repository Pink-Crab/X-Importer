<?php

declare(strict_types=1);

/**
 * Import X Post action response data object.
 *
 * @package PinkCrab\X_Importer
 */

namespace PinkCrab\X_Importer\Action\Import_Tweet;

use Gettext\Extractors\Twig;
use PinkCrab\X_Importer\Tweet\Tweet;

/**
 * Import X Post action response data object.
 */
class Import_X_Post_Response {

	/**
	 * Messages from the import.
	 *
	 * @var string[]
	 */
	public $messages = array();

	/**
	 * Tweets processed.
	 *
	 * @var string[]
	 */
	public $tweets_processed = array();

	/**
	 * Tweets that failed to process.
	 *
	 * @var string[]
	 */
	public $tweets_failed = array();

	/**
	 * The last tweet ID processed.
	 *
	 * @var string|null
	 */
	public $last_tweet_id = null;

	/**
	 * Set the messages.
	 *
	 * @param string[] $messages The messages.
	 * @return void
	 */
	public function set_messages( array $messages ): void {
		$this->messages = array_merge( $this->messages, $messages );
	}

	/**
	 * Logs an exception.
	 *
	 * @param Tweet      $tweet The tweet that failed.
	 * @param \Exception $e     The exception.
	 *
	 * @return void
	 */
	public function log_exception( Tweet $tweet, \Exception $e ): void {
		$this->failed_tweet( $tweet );
		$this->messages[] = \sprintf( 'Failed to process tweet: %s. %s', esc_attr( $tweet->id() ), esc_html( $e->getMessage() ) );
	}

	/**
	 * Adds a tweet tp the processed list.
	 *
	 * @param Tweet $tweet The tweet to add.
	 *
	 * @return void
	 */
	public function processed_tweet( Tweet $tweet ): void {
		$this->tweets_processed[] = $tweet->id();
		$this->last_tweet_id      = $tweet->id();
	}

	/**
	 * Adds a tweet to the failed list.
	 *
	 * @param Tweet $tweet The tweet to add.
	 *
	 * @return void
	 */
	public function failed_tweet( Tweet $tweet ): void {
		$this->tweets_failed[] = $tweet->id();
		$this->last_tweet_id   = $tweet->id();
	}

	/**
	 * Get the total number of tweets processed.
	 *
	 * @return integer
	 */
	public function total_processed(): int {
		return count( $this->tweets_processed );
	}

	/**
	 * Get the total number of tweets that failed.
	 *
	 * @return integer
	 */
	public function total_failed(): int {
		return count( $this->tweets_failed );
	}

	/**
	 * Get all the processed tweet IDs.
	 *
	 * @return string[]
	 */
	public function processed_tweet_ids(): array {
		return array_map( 'esc_attr', $this->tweets_processed );
	}

	/**
	 * Get all the failed tweet IDs.
	 *
	 * @return string[]
	 */
	public function failed_tweet_ids(): array {
		return array_map( 'esc_attr', $this->tweets_failed );
	}

	/**
	 * Get the last tweet ID processed.
	 *
	 * @return string|null
	 */
	public function last_tweet_id(): ?string {
		return $this->last_tweet_id;
	}

	/**
	 * Get the messages.
	 *
	 * @return string[]
	 */
	public function messages(): array {
		return $this->messages;
	}
}
