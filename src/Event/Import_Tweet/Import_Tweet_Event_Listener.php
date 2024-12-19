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

use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\Tweet\Entity\Mention;
use PinkCrab\Queue\Listener\Abstract_Listener;

/**
 * Import Tweet Event
 */
class Import_Tweet_Event_Listener extends Abstract_Listener {

	/**
	 * Access to the Import Tweet Service.
	 *
	 * @var Import_Tweet_Service
	 */
	protected $import_tweet_service;

	/**
	 * Create a new instance of the Import_Tweet_Event.
	 *
	 * @param App_Config           $app_config           The applications config.
	 * @param Import_Tweet_Service $import_tweet_service The import tweet service.
	 */
	public function __construct( App_Config $app_config, Import_Tweet_Service $import_tweet_service ) {
		$this->hook                 = $app_config->constants->get_import_tweet_event();
		$this->import_tweet_service = $import_tweet_service;
	}

	/**
	 * Handles the call back.
	 *
	 * @param mixed[] $args The arguments passed to the event.
	 * @return void
	 */
	protected function handle( array $args ): void {
		$this->import_tweet_service->trigger_action( $args[0] );
	}
}
