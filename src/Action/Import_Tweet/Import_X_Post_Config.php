<?php

declare(strict_types=1);

/**
 * Import X Post action config data object.
 *
 * @package PinkCrab\X_Importer
 */

namespace PinkCrab\X_Importer\Action\Import_Tweet;

use PinkCrab\X_Importer\Processor\Processor;

/**
 * Import X Post action config data object.
 */
class Import_X_Post_Config {

	/**
	 * Json File Path
	 *
	 * @var string|null
	 */
	protected $file_path;

	/**
	 * The local media url.
	 *
	 * @var string|null
	 */
	protected $media_url;

	/**
	 * No of imports per batch.
	 *
	 * @var integer
	 */
	protected $batch_size = 10;

	/**
	 * Duplicated tweet solution
	 *
	 * @var string|'new'|'update'|'skip'
	 */
	protected $on_duplicate = 'new';

	/**
	 * Delay between imports.
	 *
	 * @var integer
	 */
	protected $delay = 0;

	/**
	 * Last tweet ID processed.
	 *
	 * @var string|null
	 */
	protected $last_tweet_id = null;

	/**
	 * Processor to use.
	 *
	 * @var class-string<Processor>
	 */
	protected $processor;

	/**
	 * Create a new instance of the Import_X_Post_Config.
	 *
	 * @param string|null             $file_path     The file path.
	 * @param string|null             $media_url     The media url.
	 * @param class-string<Processor> $processor     The processor.
	 * @param string                  $on_duplicate  The duplicate solution.
	 * @param string                  $last_tweet_id The last tweet ID processed.
	 * @param integer|null            $batch_size    The batch size.
	 * @param integer|null            $delay         The delay between imports.
	 */
	public function __construct( // phpcs:ignore
		?string $file_path,
		?string $media_url,
		string $processor,
		string $on_duplicate = 'new',
		?string $last_tweet_id = null,
		?int $batch_size = null,
		?int $delay = null
	) {
		$this->file_path     = $file_path;
		$this->media_url     = $media_url;
		$this->processor     = $processor;
		$this->on_duplicate  = $on_duplicate;
		$this->last_tweet_id = $last_tweet_id;
		$this->batch_size    = $batch_size ?? $this->batch_size;
		$this->delay         = $delay ?? $this->delay;
	}

	/**
	 * Get the file path.
	 *
	 * @return string|null
	 */
	public function file_path(): ?string {
		return $this->file_path;
	}

	/**
	 * Get the media url.
	 *
	 * @return string|null
	 */
	public function media_url(): ?string {
		return $this->media_url;
	}

	/**
	 * Get the duplicate solution.
	 *
	 * @return string
	 */
	public function on_duplicate(): string {
		return in_array( $this->on_duplicate, array( 'new', 'update', 'skip' ), true )
			? $this->on_duplicate
			: 'new';
	}

	/**
	 * Get the batch size.
	 *
	 * @return integer
	 */
	public function batch_size(): int {
		return absint( $this->batch_size );
	}

	/**
	 * Get the delay.
	 *
	 * @return integer
	 */
	public function delay(): int {
		return absint( $this->delay );
	}

	/**
	 * Get the last tweet ID.
	 *
	 * @return string|null
	 */
	public function last_tweet_id(): ?string {
		return $this->last_tweet_id
			? esc_attr( $this->last_tweet_id )
			: null;
	}

	/**
	 * Get the processor.
	 *
	 * @return class-string<Processor>
	 */
	public function processor(): string {
		return $this->processor;
	}
}
