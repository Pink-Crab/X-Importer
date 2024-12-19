<?php

declare(strict_types=1);

/**
 * Abstract Class for creating custom processors.
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
abstract class Custom_Processor implements Processor {

	/**
	 * Holds the current status of the processor.
	 *
	 * @var string
	 */
	protected $status = self::PENDING;

	/**
	 * Holds any messages from the processor.
	 *
	 * @var string[]
	 */
	protected $messages = array();

	/**
	 * Setup function.
	 * Optional, used to set up the processor before processing.
	 *
	 * @return void
	 */
	public function setup(): void {}

	/**
	 * After processing function.
	 * Optional, used to clean up after processing.
	 *
	 * @return void
	 */
	public function teardown(): void {}

	/**
	 * Process tweet function.
	 *
	 * @param Tweet                        $tweet        The tweet to process.
	 * @param Tweet[]                      $thread       The thread of tweets.
	 * @param string|'new'|'update'|'skip' $on_duplicate The action to take.
	 *
	 * @return void
	 */
	abstract protected function process_tweet( Tweet $tweet, array $thread, string $on_duplicate ): void;

	/**
	 * Process a tweet and its thread.
	 *
	 * @param Tweet   $tweet        The tweet to process.
	 * @param Tweet[] $thread       The thread of tweets.
	 * @param string  $on_duplicate The action to take.
	 *
	 * @return void
	 */
	final public function process( Tweet $tweet, array $thread, string $on_duplicate ): void {
		$this->setup();
		try {
			$this->process_tweet( $tweet, $thread, $on_duplicate );
			$this->status = self::SUCCESS;
		} catch ( \Exception $e ) {
			$this->status     = self::ERROR;
			$this->messages[] = $e->getMessage();
		} finally {
			$this->teardown();
		}
	}

	/**
	 * Get the status of the processor.
	 *
	 * @return string
	 */
	final public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get the messages.
	 *
	 * @return string[]
	 */
	final public function get_messages(): array {
		return $this->messages;
	}
}
