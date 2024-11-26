<?php

declare(strict_types=1);

/**
 * Action interface.
 *
 * This interface is used to define the required methods for an action.
 *
 * @package PinkCrab\X_Importer
 */

namespace PinkCrab\X_Importer\Action;

interface Action {

	/**
	 * The action to be executed.
	 *
	 * @param array<string, mixed> $args The arguments for the action.
	 * @return void
	 */
	public function execute( array $args ): void;
}
