<?php
/**
 * Plugin Name:     Stairs Extensions
 * Plugin URI:      https://aporia.info/wp-plugins/stairs-extensions
 * Description:     This adds an API endpoint and other functionality to the stairquest
 * Author:          Darcy Christ
 * Author URI:      https://aporia.info
 * Text Domain:     stairs-extensions
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Stairs_Extensions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define constants.
define( 'REST_API_TUTORIAL_PLUGIN_VERSION', '1.0.0' );
define( 'REST_API_TUTORIAL_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );



add_action( 'rest_api_init', function () {
	require plugin_dir_path( __FILE__ ) . 'lib/class-wp-rest-stairs-controller.php';
	$controller = new WP_REST_Stairs_Controller();
	$controller->register_routes();
} );



/**
 * Get Stair Pictures
 */
function get_pictures($stair_id) {
	global $wpdb;
	global $gq_table_prefix;

	$table_name = $wpdb->prefix . GQ_TABLE_PREFIX . 'picture';
	$query = $wpdb->prepare("
		SELECT *
		FROM $table_name
		WHERE stair_id = %d
		", $stair_id);
	$results = $wpdb->get_results($query);

	return $results;
}


function dlog($var) {
	error_log(print_r($var,TRUE));
}