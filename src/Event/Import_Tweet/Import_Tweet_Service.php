<?php

declare(strict_types=1);

/**
 * Import Tweet Service for handling.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Event\Import_Tweet;

use PinkCrab\Queue\Dispatch\Queue_Service;
use PinkCrab\Queue\Event\Async_Event;
use PinkCrab\X_Importer\Action\Action;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Action;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Config;
use PinkCrab\X_Importer\Event\Import_Tweet\Import_Tweet_Event;
use PinkCrab\X_Importer\Processor\Processor_Factory;
use PinkCrab\X_Importer\Tests\Unit\Action\Import_Tweet\Test_Import_X_Post_Config;

/**
 * Import Tweet Service for handling.
 */
class Import_Tweet_Service {

	/**
	 * Access to the queue.
	 *
	 * @var Queue_Service
	 */
	protected $queue;

	/**
	 * Holds App_Config.
	 *
	 * @var App_Config
	 */
	protected $app_config;

	/**
	 * Import Tweet Action.
	 *
	 * @var Import_X_Post_Action
	 */
	protected $action;

	/**
	 * Formatter Factory
	 *
	 * @var Processor_Factory
	 */
	protected $processor_factory;

	/**
	 * Last tweet id.
	 *
	 * @var string|null
	 */
	protected $last_tweet_id = null;

	/**
	 * Create a new instance of the Import_Tweet_Service.
	 *
	 * @param Queue_Service        $queue             The queue dispatcher.
	 * @param App_Config           $app_config        The applications config.
	 * @param Import_X_Post_Action $action            The import tweet action.
	 * @param Processor_Factory    $processor_factory The formatter factory.
	 */
	public function __construct(
		Queue_Service $queue,
		App_Config $app_config,
		Import_X_Post_Action $action,
		Processor_Factory $processor_factory
	) {
		$this->queue             = $queue;
		$this->app_config        = $app_config;
		$this->action            = $action;
		$this->processor_factory = $processor_factory;
	}

	/**
	 * Trigger an action.
	 *
	 * @param array{json_path: string, image_url: string|null, duplicated: string, formatter: string} $args The arguments for the action.
	 *
	 * @return integer|null
	 */
	public function add_event( array $args ): ?int {
		$event = new Import_Tweet_Event( $this->app_config );
		$event->add_data( $this->map_for_event( $args ) );

		return $this->queue->dispatch( $event );
	}

	/**
	 * Maps the arguments to the correct format for Event.
	 *
	 * @param array<string, mixed> $args The arguments to map.
	 *
	 * @return array<string, mixed>
	 */
	public function map_for_event( array $args ): array {
		return array(
			'json_path'  => $args['json_path'],
			'img_url'    => $args['image_url'],
			'duplicated' => $args['duplicated'],
			'formatter'  => $args['formatter'],
		);
	}

	/**
	 * Map the arguments to the correct format for Action.
	 *
	 * @param array<string, mixed> $args The arguments to map.
	 *
	 * @return Import_X_Post_Config
	 */
	public function map_from_event_listener( array $args ): Import_X_Post_Config {
		return new Import_X_Post_Config(
			$args['json_path'],
			$args['image_url'],
			$args['processor'],
			$args['duplicated'],
			$this->app_config->constants->get_import_per_batch(),
			$this->app_config->constants->get_import_delay()
		);
	}

	/**
	 * Triggers the action.
	 *
	 * @param array<string, mixed> $args The arguments for the action.
	 *
	 * @return boolean
	 */
	public function trigger_action( array $args ): bool {
		$args = $this->map_from_event_listener( $args );
		try {
			$response            = $this->action->execute( $args );
			$this->last_tweet_id = $response->last_tweet_id();
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Get the last tweet id.
	 *
	 * @return string|null
	 */
	public function get_last_tweet_id(): ?string {
		return $this->last_tweet_id;
	}
}
