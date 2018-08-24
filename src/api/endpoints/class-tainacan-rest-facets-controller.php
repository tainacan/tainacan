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

			if(isset($request['current_query'])){
				//TODO: HANDLE FILTERS
			}

			if( $metadatum_type === 'Tainacan\Metadata_Types\Relationship' ){

				$restItemsClass = new REST_Items_Controller();

				$items = $this->items_repository->fetch($args, $options['collection_id'], 'WP_Query');
				if ($items->have_posts()) {
					while ( $items->have_posts() ) {
						$items->the_post();
		
						$item = new Entities\Item($items->post);
						$prepared_item = $restItemsClass->prepare_item_for_response($item, $request);
		
						array_push($response, $prepared_item);
					}
		
					wp_reset_postdata();
				}

			} else if ( $metadatum_type === 'Tainacan\Metadata_Types\Taxonomy' ){
				
				if( isset($request['term_id']) ){
					$this->taxonomy = $this->taxonomy_repository->fetch($options['taxonomy_id']);
					$terms[] = $this->terms_repository->fetch($request['term_id'], $this->taxonomy);
					$restTermClass = new REST_Terms_Controller();

				} else {
					$this->taxonomy = $this->taxonomy_repository->fetch($options['taxonomy_id']);
					$terms = $this->terms_repository->fetch($args, $this->taxonomy);
					$restTermClass = new REST_Terms_Controller();
				}
				
				
				foreach ($terms as $term) {
					array_push($response, $restTermClass->prepare_item_for_response( $term, $request ));
				}

			} else {
				$metadatum_id = $metadatum->get_id();
				$offset = '';
				$number = '';
				if($request['offset'] >= 0 && $request['number'] >= 1){
					$offset = $request['offset'];
					$number = $request['number'];
				}

				if($request['search']){
					if($collection_id) {
						$response = $this->metadatum_repository->fetch_all_metadatum_values( $collection_id, $metadatum_id, $request['search'], $offset, $number );
					} else {
						$response = $this->metadatum_repository->fetch_all_metadatum_values( null, $metadatum_id, $request['search'], $offset, $number);
					}
		
				} else {
					if($collection_id) {
						$response = $this->metadatum_repository->fetch_all_metadatum_values( $collection_id, $metadatum_id, '', $offset, $number);
					} else {
						$response = $this->metadatum_repository->fetch_all_metadatum_values( null, $metadatum_id, '', $offset, $number);
					}
				}

			}
        }

        return $this->prepare_response( $response, $metadatum_type );
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
	 * @param array $response the original response
	 * @param string $type the metadata type
	 * 
	 * @return mixed|string|void|\WP_Error|\WP_REST_Response 
	 */
	public function prepare_response( $response, $type ){
        $result = [];

        if( $response ){
			foreach ( $response as $key => $item ) {

				if( $type === 'Tainacan\Metadata_Types\Taxonomy' ){

					$row = [
						'label' => $item['name'],
						'value' => $item['id'],
						'img' => $item['header_image'],
						'parent' => $item['parent'],
						'total_children' => $item['total_children'],
						'type' => 'Taxonomy',
						'taxonomy_id' => $this->taxonomy->WP_Post->ID,
						'taxonomy' => $item['taxonomy'],
					];

				} else if( $type === 'Tainacan\Metadata_Types\Relationship' ){

					$row = [
						'label' => $item['title'],
						'value' => $item['id'],
						'img' => $item['thumbnail']['thumb'],
						'parent' => false,
						'total_children' => 0,
						'type' => 'Relationship'
					];

				} else {

					$row = [
						'label' => $item['mvalue'],
						'value' => $item['mvalue'],
						'img' => false,
						'parent' => false,
						'total_children' => 0,
						'type' => 'Text'
					];

				}

				$result[] = $row;
			}
		}
		
		return $result;
	}
}

?>