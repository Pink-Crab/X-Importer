<?php

declare(strict_types=1);

/**
 * Constants for the plugin.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Plugin;

/**
 * Constants for the plugin.
 */
class Constants {

	/**
	 * Get the new import action event name.
	 *
	 * @return string
	 */
	public function get_new_import_action(): string {
		return 'pc_x_new_import';
	}

	/**
	 * Get the new import nonce handle.
	 *
	 * @return string
	 */
	public function get_new_import_nonce_handle(): string {
		return 'pc_x_new_nonce';
	}

	/**
	 * Get the page slug.
	 *
	 * @return string
	 */
	public function get_page_slug(): string {
		return 'pc_x_importer';
	}
}
