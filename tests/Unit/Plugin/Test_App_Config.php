<?php

declare(strict_types=1);

/**
 * Test the App_Config values
 * 
 * @package PinkCrab\X_Importer
 * 
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Plugin;

use PinkCrab\Perique\Application\App_Config;
use PinkCrab\X_Importer\Plugin\Constants;
use WP_UnitTestCase;

/**
 * Test the App_Config values
 * 
 * @group Unit
 * @group Plugin
 * @group App_Config
 */
class Test_App_Config extends WP_UnitTestCase {

    /**
     * Gets a populated version of the App_Config, using the plugins settings config.
     * 
     * @return App_Config
     */
    protected function get_populated_app_config(): App_Config {
        $app_config = new App_Config( include PC_X_IMPORTER_DIR . '/config/settings.php' );
        return $app_config;
    }
    
    /** @testdox It should be possible to call the Constants object from App_Config. */
    public function test_can_call_constants_from_app_config(): void {
        $this->assertInstanceOf( Constants::class, $this->get_populated_app_config()->constants );
    }

    /** @testdox  It should be possible to get the JSON path from the App_Config.     */
    public function test_can_get_json_path_from_app_config(): void {
        // This should be in the wp content dir.
        $this->assertEquals( wp_upload_dir()['basedir'] . '/pc_x_importer/', $this->get_populated_app_config()->json_path );
    }

    /** @testdox  It should be possible to get the JSON URL from the App_Config.     */
    public function test_can_get_json_url_from_app_config(): void {
        // This should be in the wp content dir.
        $this->assertEquals( wp_upload_dir()['baseurl'] . '/pc_x_importer/', $this->get_populated_app_config()->json_url );
    }

}