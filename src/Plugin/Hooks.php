<?php

declare(strict_types=1);

/**
 * Hook alias for the plugin.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Plugin;

/**
 * Hook alias for the plugin.
 */
class Hooks {

	/**
	 * The number of tweets to import per batch
	 *
	 * Used by both action scheduler and CLI.
	 *
	 * @hook pc_x_tweets_per_batch
	 *
	 * @var int $tweets_per_batch
	 * @return int
	 */
	public const TWEETS_PER_BATCH = 'pc_x_tweets_per_batch';

	/**
	 * The delay between each batch of tweets.
	 *
	 * Used by only action scheduler.
	 *
	 * @hook pc_x_tweets_batch_delay
	 *
	 * @var int $tweets_batch_delay
	 * @return int
	 */
	public const TWEETS_BATCH_DELAY = 'pc_x_tweets_batch_delay';

	/**
	 * The post type for the tweets.
	 *
	 * @hook pc_x_importer_post_type
	 *
	 * @var string $post_type
	 * @return string
	 */
	public const POST_TYPE = 'pc_x_importer_post_type';

	/**
	 * The meta key for the tweet id.
	 *
	 * @hook pc_x_importer_meta_key
	 *
	 * @var string $meta_key
	 * @return string
	 */
	public const META_KEY = 'pc_x_importer_meta_key';
}
