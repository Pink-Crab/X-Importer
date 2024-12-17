<?php

declare(strict_types=1);

/**
 * Import X Post action
 *
 * @package PinkCrab\X_Importer
 */

namespace PinkCrab\X_Importer\Action\Import_Tweet;

use PinkCrab\X_Importer\File_System\JSON_File_Handler;
use PinkCrab\X_Importer\Tweet\Tweet_Collection;
use PinkCrab\X_Importer\Processor\Processor_Factory;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Response;

/**
 * Import X Post action
 */
class Import_X_Post_Action {

	/**
	 * The importer.
	 *
	 * @var JSON_File_Handler
	 */
	protected $json_importer;

	/**
	 * Path to the JSON file.
	 *
	 * @var string
	 */
	protected $file_path;

	/**
	 * The formatter factory.
	 *
	 * @var Processor_Factory
	 */
	protected $processors;

	/**
	 * Tweet collection.
	 *
	 * @var Tweet_Collection|null
	 */
	protected $tweet_collection;

	/**
	 * Results of the action.
	 *
	 * @var Import_X_Post_Response
	 */
	protected $results;

	/**
	 * Create a new instance of the Import_X_Post_Action.
	 *
	 * @param JSON_File_Handler     $json_importer The importer.
	 * @param Processor_Factory $processors    The formatter factory.
	 */
	public function __construct( JSON_File_Handler $json_importer, Processor_Factory $processors ) {
		$this->json_importer = $json_importer;
		$this->processors    = $processors;
		$this->results       = new Import_X_Post_Response();
	}



	/**
	 * The action to be executed.
	 *
	 * @param Import_X_Post_Config $args The arguments for the action.
	 *
	 * @return Import_X_Post_Response
	 */
	public function execute( Import_X_Post_Config $args ): Import_X_Post_Response {
		// @TODO Validate the args.
		// if ( empty( $args ) ) {
		//  throw new \Exception( 'TODO: Add validation for args' );
		// }

		// Layout the args.
		$batch_size = $args->batch_size();
		$tweet_id   = $args->last_tweet_id() ?? '';
		$iteration  = 0;
		try {
			$this->processors->create( $args->processor() );
		} catch ( \Exception $e ) {
			dd($e);
		}

		// Compile the services.
		$processor              = $this->processors->create( $args->processor() );
		$this->tweet_collection = new Tweet_Collection( $this->json_importer->create_from_filename( $args->file_path() ) );

		// While the iteration is smaller than the batch size.
		while ( $iteration < $batch_size ) {
			// Get the next tweet.
			$tweet = $this->tweet_collection->get_next_tweet( $tweet_id, true );
			// If we have no tweet, break the loop.
			if ( null === $tweet ) {
				break;
			}

			// Update the tweet_id.
			$tweet_id = $tweet->id();

			// Progress the iteration count
			++$iteration;

			// Process the tweet.
			try {
				$processor->process( $tweet, $this->tweet_collection->get_threaded_tweets( $tweet->id() ), $args->on_duplicate() );
			} catch ( \Exception $e ) {
				// Add to the ID to the failed list.
				$this->results->log_exception( $tweet, $e );
				continue;
			}

			// Add the tweet to the processed list.
			$this->results->processed_tweet( $tweet );

			dump( "Finished iteration: $iteration of $batch_size", $tweet );
		}

		// Add the results to the messages.
		$this->results->set_messages( $processor->get_messages() );

		return $this->results;
	}
}
