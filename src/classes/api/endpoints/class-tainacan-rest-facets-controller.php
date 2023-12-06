<?php

namespace Tainacan\API\EndPoints;

use Tainacan\Repositories;
use Tainacan\Entities;
use \Tainacan\API\REST_Controller;

class REST_Facets_Controller extends REST_Controller {
	private $metadatum_repository;

	/**
	 * REST_Facets_Controller constructor.
	 */
	public function __construct() {
		$this->rest_base = 'facets';
		$this->total_pages = 0;
		$this->total_items = 0;
		parent::__construct();
        add_action('init', array(&$this, 'init_objects'), 11);
	}

	/**
	 * Initialize objects after post_type register
	 */
	public function init_objects() {
		$this->metadatum_repository = Repositories\Metadata::get_instance();
	}

	public function register_routes() {
		register_rest_route($this->namespace, '/collection/(?P<collection_id>[\d]+)/' . $this->rest_base . '/(?P<metadatum_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				'args'				  => array(
					'collection_id' => array(
						'description'       => __('Collection ID.', 'tainacan'),
						'required' 			=> true,
					),
					'metadatum_id' => array(
						'description'       => __('Metadatum ID.', 'tainacan'),
						'required' 			=> true,
					)
				)
			),
			'schema' =>	[$this, 'get_schema']
		));

		register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<metadatum_id>[\d]+)', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				'args'				  => array(
					'metadatum_id' => array(
						'description'       => __('Metadatum ID.', 'tainacan'),
						'required' 			=> true,
					)
				)
			),
			'schema' =>	[$this, 'get_schema']
		));
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {

		// Free php session early so simultaneous requests dont get queued
		session_write_close();

		$metadatum_id = $request['metadatum_id'];

		if( !empty($metadatum_id) ) {

			$metadatum = $this->metadatum_repository->fetch($metadatum_id);
			$metadatum_type = $metadatum->get_metadata_type();
			$metadatum_type_object = $metadatum->get_metadata_type_object();

			$offset = null;
			$number = null;
			$_search = null;
			$collection_id = ( isset($request['collection_id']) ) ? $request['collection_id'] : null;
			$last_term = ( isset($request['last_term']) ) ? $request['last_term'] : '';

			$query_args = $request['current_query'];
			if(defined('TAINACAN_FACETS_DISABLE_FILTER_ITEMS') && true === TAINACAN_FACETS_DISABLE_FILTER_ITEMS) {
				$query_args = is_user_logged_in() && is_admin() ? ["status" =>  ["publish", "private", "draft"]] : [];
			}
			$query_args = $this->prepare_filters($query_args);

			if ( isset($request['hideempty']) && $request['hideempty'] == 0 ) {
				$query_args = false;
			}

			if($request['offset'] >= 0 && $request['number'] >= 1){
				$offset = $request['offset'];
				$number = $request['number'];
			}

			if($request['search']) {
				$_search = $request['search'];
			}

			$include = [];
			if ( isset($request['getSelected']) && $request['getSelected'] == 1 ) {
				if ( $metadatum_type === 'Tainacan\Metadata_Types\Taxonomy' ) {
					$metadatum_options = $metadatum->get_metadata_type_options();
					$taxonomy_id = $metadatum_options['taxonomy_id'];
					$taxonomy_slug = Repositories\Taxonomies::get_instance()->get_db_identifier_by_id($taxonomy_id);
					if( isset($request['current_query']['taxquery']) ){
						foreach( $request['current_query']['taxquery'] as $taxquery ){
							if( $taxquery['taxonomy'] === $taxonomy_slug ){
								$include = $taxquery['terms'];
							}
						}
					}
				} else {
					if( isset($request['current_query']['metaquery']) ){
						foreach( $request['current_query']['metaquery'] as $metaquery ){
							if( $metaquery['key'] == $metadatum_id ){
								$include = $metaquery['value'];
							}
						}
					}
				}
			}

			$parent_id = 0;
			if ( isset($request['parent']) ) {
				$parent_id = (int) $request['parent'];
			}

			$count_items = isset($request['count_items']) ? $request['count_items'] : true;

			$args = [
				'collection_id' => $collection_id,
				'search' => $_search,
				'offset' => $offset,
				'number' => $number,
				'items_filter' => $query_args,
				'include' => $include,
				'parent_id' => $parent_id,
				'count_items' => defined('TAINACAN_FACETS_DISABLE_COUNT_ITEMS') && true === TAINACAN_FACETS_DISABLE_COUNT_ITEMS ? false : $count_items,
				'last_term' => $last_term
			];

			$all_values = $this->metadatum_repository->fetch_all_metadatum_values( $metadatum_id, $args );

			if (isset($request['context']) && $request['context'] == 'extended') {

				if ($metadatum_type_object->get_repository() instanceof \Tainacan\Repositories\Repository) {
					$all_values['values'] = array_map(function($val) use($metadatum_type_object) {

						$second_arg = [];
						if (isset($val['taxonomy'])) {
							$second_arg = $val['taxonomy'];
						}
						$entity = $metadatum_type_object->get_repository()->fetch( (int) $val['value'], $second_arg );
						
						$add_attt_item = function($array_item, $item) {
							$array_item['thumbnail'] = $item->get_thumbnail();
							return $array_item;
						};
						add_filter('tainacan-item-to-array', $add_attt_item, 10, 2);
						$val['entity'] = $entity->_toArray();
						remove_filter( 'tainacan-item-to-array', $add_attt_item, 10);

						return $val;
						
					}, $all_values['values']);
				}

			}

			$response = [
				'values' => $all_values['values'],
				'last_term' => $all_values['last_term']
			];

			$rest_response = new \WP_REST_Response($response, 200);

			$rest_response->header('X-WP-Total', isset($all_values['total']) ? $all_values['total'] : 0 );
			$rest_response->header('X-WP-TotalPages', isset($all_values['pages']) ? $all_values['pages'] : 0 );

			return $rest_response;

		}

	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		$metadatum_id = $request['metadatum_id'];
		$metadatum = $this->metadatum_repository->fetch($metadatum_id);

		if ($metadatum instanceof Entities\Metadatum) {
			return $metadatum->can_read();
		}

		return false;

	}

	function get_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'title' => $this->rest_base,
			'type' => 'object',
			'tags' => [ $this->rest_base ],
			'properties' => array(
				'values' => array(
					'type' => 'array',
					'description' => __( 'Values for the facet', 'tainacan' ),
					'items' => [
						'type' => 'object',
						'properties' => [
							'value' => [
								'type' => 'string',
								'description' => __( 'Value of the facet', 'tainacan' ),
							],
							'label' => [
								'type' => 'string',
								'description' => __( 'Label of the facet', 'tainacan' ),
							],
							'total_children' => [
								'type' => 'string',
								'description' => __( 'Total of children of the facet', 'tainacan' ),
							],
							'taxonomy' => [
								'type' => 'string',
								'description' => __( 'Taxonomy associated with the facet, if coming from a Taxonomy metadata type. This will be the id of the taxonomy with the prefix "tnc_col_"', 'tainacan' ),
							],
							'taxonomy_id' => [
								'type' => 'integer',
								'description' => __( 'Taxonomy associated with the facet, if coming from a Taxonomy metadata type.', 'tainacan' ),
							],
							'parent' => [
								'type' => 'string',
								'description' => __( 'Parent term id, if coming from a Taxonomy metadata type that has hierarchy.', 'tainacan' ),
							],
							'total_items' => [
								'type' => 'integer',
								'description' => __( 'Total of items of the facet', 'tainacan' ),
							],
							'type' => [
								'type' => 'string',
								'description' => __( 'Type of the metadatum related to the facet', 'tainacan' ),
							],
							'hierarchy_path' => [
								'type' => 'string',
								'description' => __( 'Hierarchy path to be appended to the facet label if it is related to a Taxonomy metadata and it has terms with hierarchy.', 'tainacan' ),
							]
						]
					]
				),
				'last_term' => array(
					'type' => 'string',
					'description' => __( 'Last term passed for pagination when Elastic Search is used.', 'tainacan' ),
				),
			)
		];

		return $schema;
    }

}

?>
