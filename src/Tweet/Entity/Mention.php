<?php

/**
 * Model of a tweets, User Mention.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tweet\Entity;

/**
 * Model of a tweets, User Mention.
 */
class Mention {

	/**
	 * Users name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Users screen name
	 *
	 * @var string
	 */
	protected $screen_name;

	/**
	 * Users ID
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Create a new instance of the Mention.
	 *
	 * @param string $name        The users name.
	 * @param string $screen_name The users screen name.
	 * @param string $id          The users ID.
	 */
	public function __construct( string $name, string $screen_name, string $id ) {
		$this->name        = $name;
		$this->screen_name = $screen_name;
		$this->id          = $id;
	}

	/**
	 * Get the users name.
	 *
	 * @return string
	 */
	public function name(): string {
		return $this->name;
	}

	/**
	 * Get the users screen name.
	 *
	 * @return string
	 */
	public function screen_name(): string {
		return $this->screen_name;
	}

	/**
	 * Get the users ID.
	 *
	 * @return string
	 */
	public function id(): string {
		return $this->id;
	}
}
