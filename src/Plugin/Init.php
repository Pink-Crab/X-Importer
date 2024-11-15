<?php

declare(strict_types=1);

/**
 * Initialisation for the plugin.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Plugin;

use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Loader\Hook_Loader;
use PinkCrab\Perique\Interfaces\Hookable;

/**
 * Initialisation for the plugin.
 */
class Init implements Hookable {

	/**
	 * App Config
	 *
	 * @var App_Config
	 */
	protected $app_config;

	/**
	 * Construct
	 *
	 * @param App_Config $app_config App Config.
	 */
	public function __construct( App_Config $app_config ) {
		$this->app_config = $app_config;
	}

	/**
	 * Register all hooks.
	 *
	 * @param Hook_Loader $loader The hook loader.
	 *
	 * @return void
	 */
	public function register( Hook_Loader $loader ): void {
	}
}
