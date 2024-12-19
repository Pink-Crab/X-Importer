<?php

declare(strict_types=1);

/**
 * Processor Factory
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Processor;

use PinkCrab\Perique\Interfaces\DI_Container;

/**
 * Processor Factory
 */
class Processor_Factory {

	/**
	 * Access to DI Container.
	 *
	 * @var DI_Container
	 */
	protected $container;

	/**
	 * Create a new instance of the Processor_Factory.
	 *
	 * @param DI_Container $container The DI container.
	 */
	public function __construct( DI_Container $container ) {
		$this->container = $container;
	}

	/**
	 * Create a new instance of the Processor.
	 *
	 * @param string|class-string<Processor> $processor The Processor to create.
	 *
	 * @return Processor|null
	 */
	public function create( string $processor ): ?Processor {
		$instance = $this->container->get( $processor );
		return $instance instanceof Processor ? $instance : null;
	}
}
