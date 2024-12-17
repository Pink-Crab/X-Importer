<?php

/**
 * Registers all the dependency rules for the plugins DI container.
 *
 * @return array<string, mixed>
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

use Dice\Dice;
use PinkCrab\HTTP\HTTP_Helper;
use PinkCrab\BladeOne\BladeOne_Engine;
use PinkCrab\Perique\Application\Config;
use PinkCrab\X_Importer\Importer\Importer;
use PinkCrab\Perique\Interfaces\Renderable;
use Psr\Http\Message\ServerRequestInterface;
use PinkCrab\X_Importer\File_System\File_Manager;
use PinkCrab\X_Importer\Post_Type\Post_Repository;
use PinkCrab\X_Importer\Processor\Block_Processor;
use PinkCrab\X_Importer\File_System\JSON_File_Handler;


return array(
	'*'                      => array(
		'substitutions' => array(
			ServerRequestInterface::class => HTTP_Helper::global_server_request(),
			Renderable::class             => BladeOne_Engine::class,
		),
	),
	JSON_File_Handler::class => array(
		'shared'        => true,
		'substitutions' => array( File_Manager::class => array( \Dice\Dice::INSTANCE => fn() => new File_Manager( Config::additional( 'json_path' ) ) ) ),
	),
	Block_Processor::class   => array(
		// 'substitutions' => array( Post_Repository::class => array( \Dice\Dice::INSTANCE => fn() => new Post_Repository( Config::post_types( 'tweets' ) ) ) ),
		'substitutions' => array(
			Post_Repository::class => array(
				\Dice\Dice::INSTANCE => function () {
					$key = Config::post_types( 'tweets' );
					$i  = new Post_Repository( $key );
					return $i;
					dd( $key, $i );
							// new Post_Repository( Config::post_type( 'tweet' ) )
				},
			),
		),
	),
	// Importer::class => array(
	//  'shared'        => true,
	//  'substitutions' => array( File_Manager::class => array( \Dice\Dice::INSTANCE => fn() => new File_Manager( Config::additional( 'json_path' ) ) ) ),
	// ),
);
