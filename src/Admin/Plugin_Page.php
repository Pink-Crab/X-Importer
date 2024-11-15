<?php

declare(strict_types=1);

/**
 * Plugin Page for the plugin.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Admin;

use PinkCrab\Nonce\Nonce;
use PinkCrab\Enqueue\Enqueue;
use PinkCrab\Perique_Admin_Menu\Page\Page;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\Perique_Admin_Menu\Page\Menu_Page;

/**
 * The plugin page for the plugin.
 */
class Plugin_Page extends Menu_Page {

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
	 * New import nonce.
	 *
	 * @var Nonce
	 */
	protected $new_import_nonce;

	/**
	 * Sets up the page.
	 *
	 * @param \PinkCrab\Perique\Application\App_Config $app_config The app config.
	 */
	public function __construct( App_Config $app_config ) {
		$this->app_config       = $app_config;
		$this->new_import_nonce = new Nonce( $app_config->constants->get_new_import_nonce_handle() );

		$this->page_slug   = $app_config->constants->get_page_slug();
		$this->parent_slug = 'tools.php';
		$this->menu_title  = _x( 'X Importer', 'Plugin Page Menu Title', 'pc-x' );

		$this->page_title    = _x( 'PinkCrab X Importer', 'Plugin Page Page Title', 'pc-x' );
		$this->view_template = 'admin.main-page';
		$this->view_data     = array(
			'page'       => $this,
			'plugin'     => $this->app_config,
			'new_import' => array(
				'nonce'   => $this->new_import_nonce->token(),
				'formats' => array(
					'blocks'    => _x( 'Blocks', 'Import Format', 'pc-x' ),
					'post_meta' => _x( 'Post Meta', 'Import Format', 'pc-x' ),
				),
			),
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

	/**
	 * Enqueue the required scripts and styles.
	 *
	 * @param \PinkCrab\Perique_Admin_Menu\Page\Page $page The page object.
	 *
	 * @return void
	 */
	public function enqueue( Page $page ): void {
		Enqueue::script( 'pinkcrab_x_importer_admin_js' )
			->src( $this->app_config->url( 'assets' ) . 'js' . \DIRECTORY_SEPARATOR . 'admin.js' )
			->footer()
			->localize(
				array(
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'nonce'             => $this->new_import_nonce->token(),
					'new_import_action' => $this->app_config->constants->get_new_import_action(),
				)
			)
			->ver( $this->app_config->version() )
			->defer()
			->register();
	}
}
