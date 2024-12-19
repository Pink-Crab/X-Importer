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

use PinkCrab\Ajax\Module\Ajax;
use PinkCrab\BladeOne\BladeOne;
use PinkCrab\Perique\Application\App;
use PinkCrab\BladeOne\BladeOne_Engine;
use PinkCrab\BladeOne\PinkCrab_BladeOne;
use PinkCrab\Perique\Application\App_Factory;
use PinkCrab\X_Importer\File_System\JSON_File_Handler;
use PinkCrab\X_Importer\Tweet\Tweet_Collection;
use PinkCrab\Queue\Module\Perique_Queue as Queue;
use PinkCrab\Perique_Admin_Menu\Module\Admin_Menu;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Action;
use PinkCrab\X_Importer\Action\Import_Tweet\Import_X_Post_Config;

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

add_action(
	'init',
	function () {
		// Register a tweet cpt.
		register_post_type(
			'tweet',
			array(
				'public'       => true,
				'label'        => 'Tweet',
				'has_archive'  => true,
				'public'       => true,
				'show_in_rest' => true,
				'supports'     => array( 'editor' ),
			)
		);

		// if glynn=test is not set, return
		if ( ! isset( $_GET['glynn'] ) || $_GET['glynn'] !== 'test' ) {
			return;
		}

		$args =
		array(
			'json_path'  => '/shared/httpd/pinkcrab/htdocs/wp-content/uploads/pc_x_importer/tweets.json',
			'img_url'    => '',
			'duplicated' => 'update',
			'formatter'  => 'PinkCrab\X_Importer\Processor\Block_Processor',
			'per_batch'  => 10,
			'delay'      => 10,
			'tweet_id'   => '1578418110755115008',
		);

		$config = new Import_X_Post_Config(
			'/shared/httpd/pinkcrab/htdocs/wp-content/uploads/pc_x_importer/tweets.json',
			'',
			'PinkCrab\X_Importer\Processor\Block_Processor',
			'skip',
			'1578418110755115008',
			10,
			2
		);
		// dd( $config );

		$action = App::make( Import_X_Post_Action::class );
		dd( /*1, $action,*/ $action->execute( $config ), $config );
	}
);
