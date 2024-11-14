<?php

declare(strict_types=1);

/**
 * Settings Page for the plugin.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Admin;

use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Perique_Admin_Menu\Page\Page;
use PinkCrab\Perique_Admin_Menu\Page\Menu_Page;

/**
 * The settings page for the plugin.
 */
class Settings_Page extends Menu_Page {

	/**
	 * The page slug.
	 *
	 * @var string
	 */
	protected string $page_slug = 'pc_x_importer';

	/**
	 * The parent slug.
	 *
	 * @var string|null
	 */
	protected ?string $parent_slug = 'tools.php';

	/**
	 * The capability required to view the page.
	 *
	 * @var string
	 */
	protected string $capability = 'manage_options';

	/**
	 * The page menu position.
	 *
	 * @var integer|null
	 */
	protected ?int $position = 12;

	/**
	 * Access to App_Config
	 *
	 * @var \PinkCrab\Perique\Application\App_Config
	 */
	protected $app_config;

	/**
	 * Sets up the page.
	 *
	 * @param \PinkCrab\Perique\Application\App_Config $app_config The app config.
	 */
	public function __construct( App_Config $app_config ) {
		$this->app_config = $app_config;

		// Set the titles.
		$this->menu_title    = _x( 'X Importer', 'Settings Page Menu Title', 'pc-x' );
		$this->page_title    = _x( 'PinkCrab X Importer', 'Settings Page Page Title', 'pc-x' );
		$this->view_template = 'admin.settings-page';

		$this->view_data = array(
			'page'   => $this,
			'plugin' => $this->app_config,
		);
	}

	/**
	 * Set the page template and data.
	 *
	 * @param Page $page The page object.
	 *
	 * @return void
	 */
	public function load( Page $page ): void {
	}
}
