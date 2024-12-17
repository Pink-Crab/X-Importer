<?php

/**
 * Registers all classes that are auto-loaded by the plugin.
 *
 * @return array<string>
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

use PinkCrab\X_Importer\Plugin\Init;
use PinkCrab\X_Importer\Admin\Plugin_Page;
use PinkCrab\X_Importer\Admin\Ajax\New_Import_Ajax;
use PinkCrab\X_Importer\Event\Import_Tweet\Import_Tweet_Event_Listener;


return array(
	Plugin_Page::class,
	New_Import_Ajax::class,
	Init::class,
	Import_Tweet_Event_Listener::class,
);
