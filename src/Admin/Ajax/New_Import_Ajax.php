<?php

declare(strict_types=1);

/**
 * Ajax handler for new imports.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Admin\Ajax;

use PinkCrab\Ajax\Ajax;
use PinkCrab\Ajax\Ajax_Helper;
use PinkCrab\Perique\Application\App_Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PinkCrab\Ajax\Dispatcher\Response_Factory;
use PinkCrab\Nonce\Nonce;
use PinkCrab\X_Importer\Importer\Json_Importer;

/**
 * Handles the ajax request for new imports.
 */
class New_Import_Ajax extends Ajax {

	/**
	 * The JSON importer.
	 *
	 * @var Json_Importer
	 */
	protected $json_importer;

	/**
	 * Constructor
	 *
	 * @param App_Config    $app_config    App Config.
	 * @param Json_Importer $json_importer JSON Importer.
	 */
	public function __construct( App_Config $app_config, Json_Importer $json_importer ) {
		$this->action       = $app_config->constants->get_new_import_action();
		$this->nonce_handle = $app_config->constants->get_new_import_nonce_handle();

		$this->json_importer = $json_importer;
	}

	/**
	 * The callback
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface   $request          The request object.
	 * @param \PinkCrab\Ajax\Dispatcher\Response_Factory $response_factory The response factory.
	 *
	 * @return \Psr\Http\Message\ResponseInterface The response.
	 */
	public function callback( ServerRequestInterface $request, Response_Factory $response_factory ): ResponseInterface {
		// Extract the args from the request, you can also do this manually
		$args = Ajax_Helper::extract_server_request_args( $request );

		// Verify the request args.
		try {
			$this->verify_args( $args );
		} catch ( \Exception $e ) {
			return $response_factory->failure(
				array(
					'action'  => 'verify_args',
					'message' => $e->getMessage(),
				)
			);
		}

		// Create the json file.
		try {
			$json = $this->json_importer->create_from_upload( 'file' );
		} catch ( \Exception $e ) {
			return $response_factory->failure(
				array(
					'action'  => 'create_json',
					'message' => $e->getMessage(),
				)
			);
		}

		// Do something with the request args, ideally in a service class
		$data_to_return = array(
			'args'  => $args,
			'files' => $_FILES, // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);

		// Return with a valid PSR Response.
		return $response_factory->success( $data_to_return );
	}

	/**
	 * Verify the request args.
	 *
	 * @param array<string, mixed> $args The args to verify.
	 *
	 * @return boolean
	 *
	 * @throws \Exception If any of the required args are missing.
	 */
	protected function verify_args( array $args ): bool {
		// Check we have format and its not an empty string.
		if ( ! isset( $args['format'] ) || empty( $args['format'] ) ) {
			throw new \Exception( 'Format is required and cannot be empty.' );
		}

		// Check we have a duplicate key and its not an empty string.
		if ( ! isset( $args['duplicated'] ) || empty( $args['duplicated'] ) ) {
			throw new \Exception( 'Duplicated tweet action required' );
		}

		// Check we have a file name.
		if ( empty( $_FILES['file']['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			throw new \Exception( 'No file was uploaded.' );
		}

		// Check we have the nonce and its not an empty string.
		if ( ! isset( $args['nonce'] ) || empty( $args['nonce'] ) ) {
			throw new \Exception( 'Nonce is required and cannot be empty.' );
		}

		$nonce = new Nonce( $this->nonce_handle ); // @phpstan-ignore-line
		if ( ! $nonce->validate( $args['nonce'] ) ) {
			throw new \Exception( 'Invalid nonce.' );
		}

		return true;
	}
}
