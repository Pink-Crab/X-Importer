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
use PinkCrab\Perique\Interfaces\Renderable;
use Psr\Http\Message\ServerRequestInterface;


return array(
	'*' => array(
		'substitutions' => array(
			ServerRequestInterface::class => HTTP_Helper::global_server_request(),
			Renderable::class             => BladeOne_Engine::class,
		),
	),
);
