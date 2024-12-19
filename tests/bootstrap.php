<?php
/**
 * PHPUnit bootstrap file
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

define( 'PC_X_IMPORTER_DIR', dirname( __DIR__ ) );
define( 'PC_X_IMPORTER_FIXTURES', __DIR__ . '/fixtures/' );
define( 'PC_X_IMPORTER_TEST_ROOT', __DIR__ . '/' );
define( 'PC_X_IMPORTER_VALID_IMG_URL', 'https://raw.githubusercontent.com/Pink-Crab/X-Importer/refs/heads/feature/create-import-tweet-events/tests/fixtures/images/bird.jpeg' );
define( 'PC_X_IMPORTER_VALID_VIDEO_URL', 'https://raw.githubusercontent.com/Pink-Crab/X-Importer/refs/heads/feature/create-import-tweet-events/tests/fixtures/images/video.mp4' );

// Load all environment variables into $_ENV
try {
	$dotenv = Dotenv\Dotenv::createUnsafeImmutable( __DIR__ );
	$dotenv->load();
} catch ( \Throwable $th ) {
}



tests_add_filter(
	'plugins_loaded',
	function () {
		// Activate the plugin.
		// include_once ABSPATH . 'wp-admin/includes/plugin.php';
		// activate_plugin( 'x-importer/x-importer.php' );
	}
);

// Ensure any uploads are stored in the fixtures directory.
tests_add_filter(
	'pre_option_upload_path',
	function () {
		return PC_X_IMPORTER_FIXTURES . 'uploads';
	}
);

tests_add_filter(
	'pre_option_upload_url_path',
	function () {
		return 'https://example.org/wp-content/uploads';
	}
);

tests_add_filter(
	'pre_option_uploads_use_yearmonth_folders',
	function () {
		return '0';
	},
	9999
);

// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
