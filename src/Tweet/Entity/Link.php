<?php

/**
 * Model of a tweets, Url link.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tweet\Entity;

/**
 * Model of a tweets, Url link
 */
class Link {

	/**
	 * The URL
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The expanded URL
	 *
	 * @var string
	 */
	protected $expanded_url;

	/**
	 * The display URL
	 *
	 * @var string
	 */
	protected $display_url;

	/**
	 * Create a new instance of the URL.
	 *
	 * @param string $url          The URL.
	 * @param string $expanded_url The expanded URL.
	 * @param string $display_url  The display URL.
	 */
	public function __construct( string $url, string $expanded_url, string $display_url ) {
		$this->url          = $url;
		$this->expanded_url = $expanded_url;
		$this->display_url  = $display_url;
	}

	/**
	 * Get the URL.
	 *
	 * @return string
	 */
	public function url(): string {
		return $this->url;
	}

	/**
	 * Get the expanded URL.
	 *
	 * @return string
	 */
	public function expanded_url(): string {
		return $this->expanded_url;
	}

	/**
	 * Get the display URL.
	 *
	 * @return string
	 */
	public function display_url(): string {
		return $this->display_url;
	}
}
