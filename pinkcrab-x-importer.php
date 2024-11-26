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
use PinkCrab\X_Importer\Importer\Json_Importer;
use PinkCrab\X_Importer\Tweet\Tweet_Collection;
use PinkCrab\Queue\Module\Perique_Queue as Queue;
use PinkCrab\Perique_Admin_Menu\Module\Admin_Menu;

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


	// add_action('admin_menu', function(){
	// 	dd($GLOBALS['menu'], $GLOBALS['submenu']);
	// }, 999999999999);
// dump(1);
// add_action('plugins_loaded', function(){

// 	$path = dirname(__DIR__, 2). '/tweets.json';
// 	$IDs = [];
// // foreach(json_decode(file_get_contents( $path )) as $tweet){
// // 	$tweet = (object) $tweet->tweet;
// // 	// If tweet->in_reply_to_user_id_str is set, we have a reply.
// // 	if( isset($tweet->in_reply_to_user_id_str) && !empty($tweet->in_reply_to_user_id_str)){
// // 		continue;
// // 	}
// // 	$IDs[] = $tweet->id_str;
// // }
// // dd(json_encode($IDs));

// 	// App::make(Json_Importer::class);
// 	$co = new Tweet_Collection( file_get_contents( $path ));
// $id = '1609356212184780804';

// // // while loop over the id, until we get some threads.
// // while(! empty($thread)){
	
// // 	$id = $co->get_next_tweet( $id, true );
// // 	$thread = $co->get_threaded_tweets( $id );
// // 	dump($thread);
// // }

// 	//

// $tweet = $co->get_next_tweet( $id, true );
// $thread = $co->get_threaded_tweets( $tweet->id() );
// dd($tweet, $thread);

// 	dd($co->get_next_tweet( $id, true ));
// 	// dd(1);
// });