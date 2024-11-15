<?php

/**
 * Plugin Name: PinkCrab X Importer
 * Plugin URI: https://github.com/Pink-Crab/X-Importer
 * Description: IImports a x/Twitter data into WordPress
 * Version: 0.1.0
 * Text Domain: pc-x
 * Requires at least: 6.1
 * Requires PHP: 7.4
 */

use PinkCrab\BladeOne\BladeOne;
use PinkCrab\BladeOne\BladeOne_Engine;
use PinkCrab\BladeOne\PinkCrab_BladeOne;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\Queue\Module\Perique_Queue as Queue;
use PinkCrab\Perique_Admin_Menu\Module\Admin_Menu;
use PinkCrab\Ajax\Module\Ajax;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';

( new App_Factory() )
	->module( Queue::class )
	->module( Admin_Menu::class )
	->module( Ajax::class )
	->module(
		BladeOne::class,
		function ( BladeOne $blade ): BladeOne {
			$blade->config(
				function ( BladeOne_Engine $engine ) {
					$engine->directive(
						'__',
						function ( $e ) {
							return "<?php echo __( $e, 'pc-x' ); ?>";
						}
					)
					->set_comment_mode( PinkCrab_BladeOne::COMMENT_NONE )
					->set_mode( PinkCrab_BladeOne::MODE_SLOW );
					return $engine;
				}
			);

			return $blade;
		}
	)
	->default_setup()
	->di_rules( require __DIR__ . '/config/dependencies.php' )
	->app_config( require __DIR__ . '/config/settings.php' )
	->registration_classes( require __DIR__ . '/config/registration.php' )
	->boot();
