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

/**
 * Handles the ajax request for new imports.
 */
class New_Import_Ajax extends Ajax {

	/**
	 * Constructor
	 *
	 * @param App_Config $app_config App Config.
	 */
	public function __construct( App_Config $app_config ) {
		$this->action       = $app_config->constants->get_new_import_action();
		$this->nonce_handle = $app_config->constants->get_new_import_nonce_handle();
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
		// Do something with the request args, ideally in a service class
		$data_to_return = array(
			'args'  => $args,
			'files' => $_FILES, // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);

		// Return with a valid PSR Response.
		return $response_factory->success( $data_to_return );
	}
}
