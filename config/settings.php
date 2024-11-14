<?php

/**
 * Returns the plugin settings.
 *
 * @return array<string, mixed>
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$pc_x_importer_plugin_data = get_plugin_data( dirname( __DIR__, 1 ) . '/pinkcrab-x-importer.php' );

global $wpdb;

return array(
	'plugin'    => array(
		'version' => esc_attr( $pc_x_importer_plugin_data['Version'] ),
	),
);
