<?php

declare(strict_types=1);

/**
 * Unite Tests for the WP Admin pages.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Admin;

use WP_UnitTestCase;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\X_Importer\Plugin\Constants;
use PinkCrab\X_Importer\Admin\Plugin_Page;
use PinkCrab\Perique\Application\App_Config;

/**
 * Tweet
 *
 * @group Unit
 * @group Admin
 * @group Menu_Page
 */
class Test_Admin_Page extends WP_UnitTestCase {

    /** @testdox When the admin page is built, it should get and set various values from app_config. */
    public function test_admin_page_defined_vars(): void {
        $constants = $this->createMock( Constants::class );
        $constants->method( 'get_new_import_nonce_handle' )->willReturn( 'new_import_nonce_handle' );
        $constants->method( 'get_page_slug' )->willReturn( 'page_slug' );
        
        $app_config = new App_Config([
            'additional' => [
                'constants' => $constants
            ]
        ]);

        // Create instance.
        $admin_page = new Plugin_Page( $app_config );

        // Check the values set from config
        $view_data = Objects::get_property( $admin_page, 'view_data' );

        $this->assertEquals($view_data['plugin'], $app_config);
        $this->assertArrayHasKey('new_import', $view_data);
        $this->assertIsArray($view_data['new_import']);
        
        $this->assertArrayHasKey('nonce', $view_data['new_import']);
        $this->assertEquals(wp_create_nonce( 'new_import_nonce_handle' ), $view_data['new_import']['nonce']);
        
        $this->assertArrayHasKey('formats', $view_data['new_import']);

        $this->assertIsArray($view_data['new_import']['formats']);     
    }
}