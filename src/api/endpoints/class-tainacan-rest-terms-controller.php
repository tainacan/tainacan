<?php

use Tainacan\Entities;
use Tainacan\Repositories;

class TAINACAN_REST_Terms_Controller extends WP_REST_Controller {
	private $term;
	private $terms_repository;
	private $taxonomy;
	private $taxonomy_repository;

	/**
	 * TAINACAN_REST_Terms_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'tainacan/v2';
		$this->rest_base = 'terms';

		$this->term = new Entities\Term();
		$this->terms_repository = new Repositories\Terms();
		$this->taxonomy = new Entities\Taxonomy();
		$this->taxonomy_repository = new Repositories\Taxonomies();

		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<taxonomy_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'create_item'),
					'permission_callback' => array($this, 'create_item_permissions_check')
				)
			)
		);
	}

	public function create_item( $request ) {

	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		if(current_user_can('edit_posts')){
			return true;
		}

		return false;
	}
}

?>