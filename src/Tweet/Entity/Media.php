<?php

/**
 * Model of a tweets, Media item
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tweet\Entity;

/**
 * Model of a tweets, Media item
 */
class Media {

	/**
	 * The media ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The media URL
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * The media type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The media display URL
	 *
	 * @var string
	 */
	protected $display_url;

	/**
	 * Create a new instance of the Media.
	 *
	 * @param string $id          The media ID.
	 * @param string $url         The media URL.
	 * @param string $type        The media type.
	 * @param string $display_url The media display URL.
	 */
	public function __construct( string $id, string $url, string $type, string $display_url ) {
		$this->id          = esc_attr( $id );
		$this->url         = esc_url( $url );
		$this->type        = esc_attr( $type );
		$this->display_url = esc_url( $display_url );
	}

	/**
	 * Get the media ID.
	 *
	 * @return string
	 */
	public function id(): string {
		return $this->id;
	}

	/**
	 * Get the media URL.
	 *
	 * @return string
	 */
	public function url(): string {
		return $this->url;
	}

	/**
	 * Get the media type.
	 *
	 * @return string
	 */
	public function type(): string {
		return $this->type;
	}

	/**
	 * Get the media display URL.
	 *
	 * @return string
	 */
	public function display_url(): string {
		return $this->display_url;
	}
}
