<?php

class WP_REST_Stairs_Controller extends WP_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @since 4.7.0
	 * @var string
	 */
	protected $post_type = 'stairquest_stair';    

	/**
	 * Instance of a post meta fields object.
	 *
	 * @since 4.7.0
	 * @var WP_REST_Post_Meta_Fields
	 */
	protected $meta;




	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 *
	 * @param string $post_type Post type.
	 */
	public function __construct() {
		//$this->namespace = 'wp/v2';
		$this->namespace = 'stairquest/v1';
		$this->rest_base = 'stairs';
		$this->post_type = $this->post_type;

		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
	}

	/**
	 * Register the component routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			)
		) );

		$schema = $this->get_item_schema();
		$get_item_args = array(
			'context'  => $this->get_context_param( array( 'default' => 'view' ) ),
		);
		if ( isset( $schema['properties']['password'] ) ) {
			$get_item_args['password'] = array(
				'description' => __( 'The password for the post if it is password protected.' ),
				'type'        => 'string',
			);
		}
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => $get_item_args,
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(
					'force' => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Whether to bypass trash and force deletion.' ),
					),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Prepares restaurant data for return as an object.
	 */
	public function prepare_item_for_response( $stair, $request ) {
		$stair_info = stairquest_get_stairData($stair->ID);

		// get photos
	    $photos = get_pictures($stair_info['stair_id']);

	    // get stories (comments)
		$args = array(
		    'post_id' => $stair->ID,
		);
		$comments = get_comments( $args );

		dlog($stair);

		$data = array(
			'id'      => $stair->ID,
			'name'    => $stair->post_title,
			'current_name' => $stair_info['current_name'],
			'content' => $stair->post_content,
			'stair_id' => $stair_info['stair_id'],
			'location' => $stair_info['location'],
			'type' => get_stairtype_lov($stair_info['type']),
			'handrails' => get_handrails( $stair_info['stair_id'] ),
			'stepcount' => get_stepcount( $stair_info['stair_id'] ),
			'comments' => $comments,
			'photos' => $photos,
			'lat' => $stair_info['lat'],
			'lng' => $stair_info['lng'],
			'mayor_id' => $stair_info['mayor_id'],
			'polygon_data' => $stair_info['polygon_data'],
			'completion' => $stair_info['completion'],
			'link'    => get_the_permalink( $stair->ID ),
			'status'  => $stair->post_status,
			'post_date' => $stair->post_date,
			'post_date_gmt' => $stair->post_date_gmt,
			'post_modified' => $stair->post_modified,
			'post_modified_gmt' => $stair->post_modified_gmt,
		);

		return $data;
	}

}