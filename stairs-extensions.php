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


/**
* Add REST API support to an already registered post type.
*/
function add_post_type_rest_support() {
	global $wp_post_types;

	//be sure to set this to the name of your post type!
	$post_type_name = 'stairquest_stair';
	if( isset( $wp_post_types[ $post_type_name ] ) ) {
		$wp_post_types[$post_type_name]->show_in_rest = true;
		// $wp_post_types[$post_type_name]->rest_base = $post_type_name;
		// $wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
	}
}
add_action( 'init', 'add_post_type_rest_support', 25 );



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

	foreach($results as $key => $result) {
		$results[$key]->orig_url = content_url('/uploads').$result->orig_url;
		$results[$key]->gallery_url = content_url('/uploads').$result->gallery_url;
		$results[$key]->thumb_url = content_url('/uploads').$result->thumb_url;
	}

	return $results;
}

function get_stairtype_lov($type_id) {
	global $wpdb;
	global $gq_table_prefix;

	$table_name = $wpdb->prefix . GQ_TABLE_PREFIX . 'stairtype_lov';
	$query = $wpdb->prepare("
					SELECT stairtype
					FROM $table_name
					WHERE rowid = %d
					", array($type_id)
				);
	$stair_type = $wpdb->get_var($query);
	if($stair_type) {
		return $stair_type;
	} else {
		error_log('Unknown stairtype id: '.$type_id);
		return FALSE;
	}
}

/**
 * Get the highest voted handrails
 */
function get_handrails($stair_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . GQ_TABLE_PREFIX . 'stair_vote';
	$query = $wpdb->prepare("
						SELECT text_value, count(*) as counter
						FROM $table_name
						WHERE stair_id = %d AND type = %d
						GROUP BY text_value
						", array($stair_id, SQ_VOTE_TYPE_HANDRAIL)
					);
	$results = $wpdb->get_results($query);

	$answer = "?";
	$yay_votes = 0;
	$nay_votes = 0;

	if ($wpdb->num_rows == 0){
		return 'Unknown';
	}
	else{
		foreach ( $results as $row) {
			if ($row->text_value == SQ_VOTE_HANDRAIL_YES){
				$yay_votes = $row->counter;
			}
			if ($row->text_value == SQ_VOTE_HANDRAIL_NO){
				$nay_votes = $row->counter;
			}
		}
		if ($yay_votes > $nay_votes){
			$answer = "Yes";
		}
		else if ($yay_votes < $nay_votes){
			$answer = "No";
		}
		else{
			$answer = "Undecided";
		}
		return $answer;
	}
}

/**
 * Get the highest voted step count
 */
function get_stepcount($stair_id){ 
	global $wpdb;

	$table_name = $wpdb->prefix . GQ_TABLE_PREFIX . 'stair_vote';
	$query = $wpdb->prepare("
						SELECT AVG (number_value) as steps, count(*) as counter
						FROM $table_name
						WHERE stair_id = %d AND type = %d
						", array($stair_id, SQ_VOTE_TYPE_STEPS)
					);
	$results = $wpdb->get_results($query);

	if ($wpdb->num_rows == 0 || $results == null){
		error_log('Number of Steps: Error!');
		return False;
	}
	else{
		if ($results[0]->counter == 0){
			return 'Unknown';
		}
		else {
			$answer = $results[0]->steps;
			$answer = round($answer, 1);
			return $answer;
		}
	}
}


function dlog($var) {
	error_log(print_r($var,TRUE));
}