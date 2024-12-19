<?php

declare(strict_types=1);

/**
 * Constants for the plugin.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Plugin;

use PinkCrab\X_Importer\Plugin\Hooks;

/**
 * Constants for the plugin.
 */
class Constants {

	/**
	 * Get the new import action event name.
	 *
	 * @return string
	 */
	public function get_new_import_action(): string {
		return 'pc_x_new_import';
	}

	/**
	 * Get the new import nonce handle.
	 *
	 * @return string
	 */
	public function get_new_import_nonce_handle(): string {
		return 'pc_x_new_nonce';
	}

	/**
	 * Get the page slug.
	 *
	 * @return string
	 */
	public function get_page_slug(): string {
		return 'pc_x_importer';
	}

	/**
	 * Get the import tweet event name.
	 *
	 * @return string
	 */
	public function get_import_tweet_event(): string {
		return 'pc_x_import_tweets';
	}

	/**
	 * Get the import tweet group.
	 *
	 * @return string
	 */
	public function get_import_tweet_group(): string {
		return 'pinkcrab_x_imports';
	}

	/**
	 * Get the number of tweets to import per batch.
	 *
	 * @return integer
	 */
	public function get_import_per_batch(): int {
		return apply_filters( Hooks::TWEETS_PER_BATCH, 10 );
	}

	/**
	 * Get the delay between importing batches.
	 *
	 * @return integer
	 */
	public function get_import_delay(): int {
		return apply_filters( Hooks::TWEETS_BATCH_DELAY, 10 );
	}
}
