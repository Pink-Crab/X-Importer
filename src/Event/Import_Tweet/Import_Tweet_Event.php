<?php

declare(strict_types=1);

/**
 * Import Tweet Event
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Event\Import_Tweet;

use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Queue\Event\Async_Event;

/**
 * Import Tweet Event
 */
class Import_Tweet_Event extends Async_Event {

	/**
	 * Create a new instance of the Import_Tweet_Event.
	 *
	 * @param App_Config $app_config The applications config.
	 */
	public function __construct( App_Config $app_config ) {
		$this->hook  = $app_config->constants->get_import_tweet_event();
		$this->group = $app_config->constants->get_import_tweet_group();
	}

	/**
	 * Add data to the event.
	 *
	 * @param array $data The data to add to the event.
	 *
	 * @return void
	 */
	public function add_data( array $data ): void {
		$this->data[] = $data;
	}
}
