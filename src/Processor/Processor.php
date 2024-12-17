<?php

declare(strict_types=1);

/**
 * Processor Interface
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Processor;

use PinkCrab\X_Importer\Tweet\Tweet;

/**
 * Processor Interface
 */
interface Processor {

	public const ERROR   = 'error';
	public const SUCCESS = 'success';
	public const PENDING = 'pending';

	public const NEW_ON_DUPLICATE    = 'new';
	public const UPDATE_ON_DUPLICATE = 'update';
	public const SKIP_ON_DUPLICATE   = 'skip';

	/**
	 * Process a tweet and its thread.
	 *
	 * @param Tweet                        $tweet        The tweet to process.
	 * @param Tweet[]                      $thread       The thread of tweets.
	 * @param string|'new'|'update'|'skip' $on_duplicate The action to take.
	 *
	 * @return void
	 */
	public function process( Tweet $tweet, array $thread, string $on_duplicate ): void;

	/**
	 * Get the status of the processor.
	 *
	 * @return string
	 */
	public function get_status(): string;

	/**
	 * Get the messages.
	 *
	 * @return string[]
	 */
	public function get_messages(): array;
}
