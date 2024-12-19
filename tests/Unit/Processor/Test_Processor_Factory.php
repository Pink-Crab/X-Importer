<?php

declare(strict_types=1);

/**
 * Unit Tests for the Processor Factory
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Processor;

use Exception;
use WP_UnitTestCase;
use DateTimeImmutable;
use PinkCrab\Perique\Interfaces\DI_Container;
use PinkCrab\X_Importer\Post_Type\Post_Repository;
use PinkCrab\X_Importer\Processor\Processor_Factory;

/**
 * Processor_Factory
 *
 * @group Unit
 * @group Processor
 * @group Processor_Factory
 */
class Test_Processor_Factory extends WP_UnitTestCase {

	/**
 * @testdox When creating a processor instance, if its not a valid Processor, type, null should be returned.
*/
	public function test_invalid_processor_type(): void {
		$container = $this->createMock( \PinkCrab\Perique\Interfaces\DI_Container::class );
		$container->method( 'get' )->willReturn( new \stdClass() );

		$factory  = new Processor_Factory( $container );
		$instance = $factory->create( 'invalid' );
		$this->assertNull( $instance );
	}

    /**
     * @testdox When creating a processor instance, if its a valid Processor, the instance should be returned.
     */
    public function test_valid_processor_type(): void {
        $processor = $this->createMock( \PinkCrab\X_Importer\Processor\Processor::class );
        
        $container = $this->createMock( \PinkCrab\Perique\Interfaces\DI_Container::class );
        $container->method( 'get' )->willReturn( $processor);

        $factory  = new Processor_Factory( $container );
        $instance = $factory->create( 'Acme\Mock-Processor' );
        $this->assertInstanceOf( \PinkCrab\X_Importer\Processor\Processor::class, $instance );
    }
}
