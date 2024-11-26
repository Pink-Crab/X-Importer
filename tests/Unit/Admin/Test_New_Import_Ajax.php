<?php

declare(strict_types=1);

/**
 * Unite Tests for the wp admin new import ajax call.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tests\Unit\Admin;

use WP_UnitTestCase;
use Gin0115\WPUnit_Helpers\Objects;
use PinkCrab\X_Importer\Plugin\Constants;
use PinkCrab\X_Importer\Importer\Json_Importer;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\X_Importer\Admin\Ajax\New_Import_Ajax;

/**
 * Tweet
 *
 * @group Unit
 * @group Admin
 * @group Menu_Page
 */
class Test_New_Import_Ajax extends WP_UnitTestCase {

	/**
	 * Gets app config with mocked constants.
	 *
	 * @param string $nonce_handle  The nonce handle to use.
	 * @param string $import_action The import action to use.
	 *
	 * @return App_Config
	 */
	protected function get_app_config( string $nonce_handle, string $import_action ): App_Config {
		$constants = $this->createMock( Constants::class );
		$constants->method( 'get_new_import_nonce_handle' )->willReturn( $nonce_handle );
		$constants->method( 'get_new_import_action' )->willReturn( $import_action );

		return new App_Config(
			array(
				'additional' => array(
					'constants' => $constants,
				),
			)
		);
	}

	/**
	 * Gets a sever request with a given parsed query.
	 *
	 * @param array<string, mixed> $query
	 *
	 * @return \Psr\Http\Message\ServerRequestInterface
	 */
	protected function get_server_request( array $query ): \Psr\Http\Message\ServerRequestInterface {
		$request = $this->createMock( \Psr\Http\Message\ServerRequestInterface::class );
		$request->method( 'getQueryParams' )->willReturn( $query );
		$request->method( 'getMethod' )->willReturn( 'GET' );
		return $request;
	}

	/**
	 * Data provider for missing request params.
	 *
	 * @return array<
	 *  string,
	 *  array{0:array<string, mixed>, 1: string}
	 * >
	 */
	public function missing_request_params(): array {
		return array(
			'missing_format'     => array( array(), 'format' ),
			'empty_format'       => array( array( 'format' => '' ), 'format' ),
			'missing_duplicated' => array( array( 'format' => 'a' ), 'Duplicated' ),
			'empty_duplicated'   => array(
				array(
					'format'     => 'a',
					'duplicated' => '',
				),
				'Duplicated',
			),
		);
	}

	/**
	 * @testdox Ajax should fail validation if there is no format value passed or its an empty value.
	 * @dataProvider missing_request_params
	 */
	public function test_new_import_ajax_missing_request_param( array $request, string $field ): void {
		$json_importer = $this->createMock( Json_Importer::class );
		$ajax          = new New_Import_Ajax( $this->get_app_config( 'new_import_nonce_handle', 'new_import_action' ), $json_importer );

		$errors           = array();
		$response_factory = $this->createMock( \PinkCrab\Ajax\Dispatcher\Response_Factory::class );
		$response_factory->method( 'failure' )->willReturnCallback(
			function ( $error ) use ( &$errors ) {
				$errors = $error;
				return $this->createMock( \Psr\Http\Message\ResponseInterface::class );
			}
		);

		$ajax->callback( $this->get_server_request( $request ), $response_factory );

		$this->assertEquals( 'verify_args', $errors['action'] );
		$this->assertStringContainsString( \strtolower( $field ), \strtolower( $errors['message'] ) );
	}

	/**
 * @testdox If no files have been upload the process cant start and should result in an error
*/
	public function test_new_import_ajax_no_file_uploaded(): void {
		$json_importer = $this->createMock( Json_Importer::class );
		$json_importer->expects( $this->never() )->method( 'create_from_upload' );

		$ajax = new New_Import_Ajax( $this->get_app_config( 'new_import_nonce_handle', 'new_import_action' ), $json_importer );

		$errors           = array();
		$response_factory = $this->createMock( \PinkCrab\Ajax\Dispatcher\Response_Factory::class );
		$response_factory->method( 'failure' )->willReturnCallback(
			function ( $error ) use ( &$errors ) {
				$errors = $error;
				return $this->createMock( \Psr\Http\Message\ResponseInterface::class );
			}
		);

		$ajax->callback(
			$this->get_server_request(
				array(
					'format'     => 'a',
					'duplicated' => 'b',
				)
			),
			$response_factory
		);

		$this->assertEquals( 'verify_args', $errors['action'] );
		$this->assertStringContainsString( 'file', \strtolower( $errors['message'] ) );
	}

	/**
 * @testdox If the nonce check fails, then an error should be thrown validating the request.
*/
	public function test_new_import_ajax_nonce_check_fails(): void {
		$json_importer = $this->createMock( Json_Importer::class );
		$json_importer->expects( $this->never() )->method( 'create_from_upload' );

		$ajax = new New_Import_Ajax( $this->get_app_config( 'new_import_nonce_handle', 'new_import_action' ), $json_importer );

		$errors           = array();
		$response_factory = $this->createMock( \PinkCrab\Ajax\Dispatcher\Response_Factory::class );
		$response_factory->method( 'failure' )->willReturnCallback(
			function ( $error ) use ( &$errors ) {
				$errors = $error;
				return $this->createMock( \Psr\Http\Message\ResponseInterface::class );
			}
		);

		// Mock the file upload.
		$_FILES = array(
			'file' => array(
				'name'     => 'test.json',
				'type'     => 'application/json',
				'tmp_name' => __DIR__ . '/test.json',
				'error'    => 0,
				'size'     => 123,
			),
		);

		$ajax->callback(
			$this->get_server_request(
				array(
					'format'     => 'a',
					'duplicated' => 'b',
				)
			),
			$response_factory
		);

		$this->assertEquals( 'verify_args', $errors['action'] );
		$this->assertStringContainsString( 'nonce', \strtolower( $errors['message'] ) );
		// dump($errors);
		// Clear the files.
		$_FILES = array();
	}

	/**
 * @testdox An error should be thrown if the nonce is invalid.
*/
	public function test_new_import_ajax_invalid_nonce(): void {
		$json_importer = $this->createMock( Json_Importer::class );
		$json_importer->expects( $this->never() )->method( 'create_from_upload' );

		$ajax = new New_Import_Ajax( $this->get_app_config( 'new_import_nonce_handle', 'new_import_action' ), $json_importer );

		$errors           = array();
		$response_factory = $this->createMock( \PinkCrab\Ajax\Dispatcher\Response_Factory::class );
		$response_factory->method( 'failure' )->willReturnCallback(
			function ( $error ) use ( &$errors ) {
				$errors = $error;
				return $this->createMock( \Psr\Http\Message\ResponseInterface::class );
			}
		);

		// Mock the file upload.
		$_FILES = array(
			'file' => array(
				'name'     => 'test.json',
				'type'     => 'application/json',
				'tmp_name' => __DIR__ . '/test.json',
				'error'    => 0,
				'size'     => 123,
			),
		);

		$ajax->callback(
			$this->get_server_request(
				array(
					'format'     => 'a',
					'duplicated' => 'b',
					'nonce'      => 'invalid',
				)
			),
			$response_factory
		);

		$this->assertEquals( 'verify_args', $errors['action'] );
		$this->assertStringContainsString( 'nonce', \strtolower( $errors['message'] ) );

		// Clear the files.
		$_FILES = array();
	}

	/**
 * @testdox if all request params are there, the request should be validated
*/
	public function test_new_import_ajax_valid_request(): void {
		$json_importer = $this->createMock( Json_Importer::class );
		$json_importer->expects( $this->once() )->method( 'create_from_upload' )->willReturn( '{"test": "data"}' );

		$ajax = new New_Import_Ajax( $this->get_app_config( 'new_import_nonce_handle', 'new_import_action' ), $json_importer );

		$errors           = array();
		$response_factory = $this->createMock( \PinkCrab\Ajax\Dispatcher\Response_Factory::class );
		$response_factory->method( 'failure' )->willReturnCallback(
			function ( $error ) use ( &$errors ) {
				$errors = $error;
				return $this->createMock( \Psr\Http\Message\ResponseInterface::class );
			}
		);

		// Mock the file upload.
		$_FILES = array(
			'file' => array(
				'name'     => 'test.json',
				'type'     => 'application/json',
				'tmp_name' => __DIR__ . '/test.json',
				'error'    => 0,
				'size'     => 123,
			),
		);

		$ajax->callback(
			$this->get_server_request(
				array(
					'format'     => 'a',
					'duplicated' => 'b',
					'nonce'      => wp_create_nonce( 'new_import_nonce_handle' ),
				)
			),
			$response_factory
		);

		// @TODO CLEAN THIS UP
		if ( ! isset( $errors['action'] ) ) {
			dump( 'This needs updating later' );
		} else {
			// Should not contain a verify_args action.
			$this->assertNotEquals( 'verify_args', $errors['action'] );
		}

		// Clear the files.
		$_FILES = array();
	}
}
