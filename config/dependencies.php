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

use PinkCrab\HTTP\HTTP_Helper;
use PinkCrab\BladeOne\BladeOne_Engine;
use PinkCrab\Perique\Application\Config;
use PinkCrab\Perique\Interfaces\Renderable;
use PinkCrab\X_Importer\File_Manager\File_Manager;
use PinkCrab\X_Importer\Importer\Json_Importer;
use Psr\Http\Message\ServerRequestInterface;


return array(
	'*'                  => array(
		'substitutions' => array(
			ServerRequestInterface::class => HTTP_Helper::global_server_request(),
			Renderable::class             => BladeOne_Engine::class,
		),
	),
	Json_Importer::class => array(
		'shared'        => true,
		'substitutions' => array( File_Manager::class => array( \Dice\Dice::INSTANCE => fn() => new File_Manager( Config::additional( 'json_path' ) ) ) ),
	),
);
