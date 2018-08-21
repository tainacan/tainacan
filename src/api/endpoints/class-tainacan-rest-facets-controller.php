<?php

namespace Tainacan\API\EndPoints;

use Tainacan\Repositories;
use Tainacan\Entities;
use \Tainacan\API\REST_Controller;

class REST_Facets_Controller extends REST_Controller {

	/**
	 * REST_Facets_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'facets';
		parent::__construct();
        add_action('init', array(&$this, 'init_objects'), 11);
	}
	
	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
        $this->collection = new Entities\Collection();
        
		$this->collection_repository = Repositories\Collections::get_instance();
		$this->metadatum_repository = Repositories\Metadata::get_instance();
		$this->filter_repository = Repositories\Filters::get_instance();
		$this->terms_repository = Repositories\Terms::get_instance();
		$this->taxonomy_repository = Repositories\Taxonomies::get_instance();
		$this->items_repository = Repositories\Items::get_instance();
        
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<metadatum_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_items_permissions_check')
			)
        ));
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<metadatum_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check')
			)
		));
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {
        
        $metadatum_id = $request['metadatum_id'];
        $metadatum = $this->metadatum_repository->fetch($metadatum_id);

		$response = $this->prepare_item_for_response($metadatum, $request );

        return new \WP_REST_Response($response, 200);
    }

	/**
	 *
	 * Receive a \WP_Query or a metadatum object and return both in JSON
	 *
	 * @param mixed $metadatum
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|string|void|\WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response($metadatum, $request){
		$response = [];

        if( !empty($metadatum) ){

			$metadatum_type = $metadatum->get_metadata_type();
			$options = $metadatum->get_metadata_type_options();
			$args = $this->prepare_filters($request);

			if( $metadatum_type === 'Tainacan\Metadata_Types\Relationship' ){

				$response = $this->items_repository->fetch($args, $options['collection_id'], 'OBJECT');

			} else if ( $metadatum_type === 'Tainacan\Metadata_Types\Taxonomy' ){

				$taxonomy = $this->taxonomy_repository->fetch($options['taxonomy_id']);
				$terms = $this->terms_repository->fetch($args, $taxonomy);
				
				foreach ($terms as $term) {
					array_push($response, $this->prepare_term_for_response( $term, $request ));
				}

			} else {

				if($request['collection_id']) {
					$response = $this->metadatum_repository->fetch_all_metadatum_values( $request['collection_id'], $metadatum->get_id() );
				} else {
					$response = $this->metadatum_repository->fetch_all_metadatum_values( null, $metadatum->get_id() );
				}

			}
        }

        return $response;
    }

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}

	/**
	 * @param mixed $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array|mixed|\WP_Error|\WP_REST_Response
	 */
	public function prepare_term_for_response( $item, $request ) {
		if(!empty($item)){
			if(!isset($request['fetch_only'])) {
				$item_arr = $item->_toArray();

				$children =  get_terms([
					'taxonomy' => $item_arr['taxonomy'],
					'parent' => $item_arr['id'],
					'fields' => 'ids',
					'hide_empty' => false,
				]);

				$item_arr['total_children'] = count($children);
			} else {
				$attributes_to_filter = $request['fetch_only'];

				$item_arr = $this->filter_object_by_attributes($item, $attributes_to_filter);
			}

			return $item_arr;
		}

		return $item;
	}
}

?>