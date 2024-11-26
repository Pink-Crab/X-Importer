<?php

declare(strict_types=1);

/**
 * Integration for the WP Admin pages.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Tweet;

use WP_UnitTestCase;
use PHPCSUtils\BackCompat\Helper;
use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\X_Importer\Tweet\Entity\Link;
use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\Tweet\Entity\Mention;
use Gin0115\WPUnit_Helpers\WP\Menu_Page_Inspector;
use Gin0115\WPUnit_Helpers\WP\Entities\Sub_Menu_Page_Entity;

/**
 * Tweet
 *
 * @group Integration
 * @group Admin
 * @group Menu_Page
 */
class Test_Admin_Page extends WP_UnitTestCase {

    use \PinkCrab\X_Importer\Tests\Tools\App_Helper_Trait;

    protected $hook_suffix_cache = null;

    /**
     * Setup the test case.
     * 
     * @return void
     */
    public function set_up(): void {
        parent::set_up();
        $this->hook_suffix_cache = $GLOBALS['hook_suffix'] ?? null;
    }

    /**
     * Tear down the test case.
     * 
     * @return void
     */
    public function tear_down(): void {
        parent::tear_down();
        $GLOBALS['hook_suffix'] = $this->hook_suffix_cache;
    }

    /** 
     * @testdox WP Admin Dashboard logged in as Administrator user.
     * @runInSeparateProcess
	 * @preserveGlobalState disabled
     */
    public function test_admin_page(): void {
        // Create admin user
        $admin_user = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_user );
		

        // Load the plugin.
        $this->load_plugin();

        // Include the toold menu item to allow page to be created.
        $GLOBALS['menu'] = array(70 => array('Tools', 'manage_options', 'tools.php', '', 'open-if-no-js menu-top menu-icon-tools', 'menu-tools', 'dashicons-admin-tools') );

        // // Run the admin page hooks.
        set_current_screen( 'dashboard' );
		do_action( 'init' );

        // Check to see if the  menu page is registered.
        $inspector =  Menu_Page_Inspector::initialise();
        $main_page = $inspector->set_pages()->find('pc_x_importer');

        // Check we have the entity
        $this->assertInstanceOf(Sub_Menu_Page_Entity::class, $main_page);
        $this->assertNotEmpty($main_page->menu_title);
        $this->assertNotEmpty($main_page->page_title);
        $this->assertNotEmpty($main_page->permission);
        $this->assertNotEmpty($main_page->menu_slug);
        $this->assertNotEmpty($main_page->parent_slug);
        $this->assertNotEmpty($main_page->url);

        // Parent should be tools.
        $this->assertEquals('tools.php', $main_page->parent_slug);
    }

    /**
     * @testdox The defined scripts and styles should only be enqueued on the correct page.
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_admin_page_enqueue_conditionally(): void {
        $GLOBALS['hook_suffix'] = 'admin_page_pc_x_importer';
        
        // Create admin user
        $admin_user = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_user );
		
        // Load the plugin.
        $this->load_plugin();

        // Include the toold menu item to allow page to be created.
        $GLOBALS['menu'] = array(70 => array('Tools', 'manage_options', 'tools.php', '', 'open-if-no-js menu-top menu-icon-tools', 'menu-tools', 'dashicons-admin-tools') );

        // // Run the admin page hooks.
        set_current_screen( 'dashboard' );
        do_action('init');
        do_action( 'admin_menu' );

        global $current_screen;
        $current_screen = convert_to_screen('tools_page_pc_x_importer');

        do_action( 'admin_enqueue_scripts', 'admin_page_pc_x_importer' );

        // Get the registered scripts.
        $registered_scripts = wp_scripts()->registered;

        $this->assertArrayHasKey('pinkcrab_x_importer_admin_js', $registered_scripts);
    }
}