<?php

namespace Tainacan\API;

class REST_Controller extends \WP_REST_Controller {


	/**
	 * REST_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = TAINACAN_REST_NAMESPACE;
		add_action('rest_api_init', array($this, 'register_routes'));
		add_filter('tainacan-api-prepare-items-args', array($this, 'add_support_to_tax_query_like'));
	}

	/**
	 * @param $object
	 * @param $attributes
	 *
	 * @return array
	 */
	protected function filter_object_by_attributes($object, $attributes){
		$object_filtered = [];

		if (is_array($attributes)) {
			foreach ( $attributes as $attribute ) {
				if ( ! is_array( $attribute ) ) {
					$object_filtered[ $attribute ] = $object->get( $attribute );
				}
			}
		} else {
			if(strstr($attributes, ',')){
				$attributes = explode(',', $attributes);

				return $this->filter_object_by_attributes($object, $attributes);
			} else {
				$object_filtered[ $attributes ] = $object->get( $attributes );
			}
		}

		return $object_filtered;
	}

	/**
	 * @param $object
	 * @param $new_values
	 *
	 * @return \Tainacan\Entities\Entity
	 */
	protected function prepare_item_for_updating($object, $new_values){
		foreach ($new_values as $key => $value) {
			$object->set($key, $value);
		}
		return $object;
	}

	/**
	 * @param $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function prepare_filters($request){
		$queries = [
			'name'         => 'title',
			'title'        => 'title',
			'id'           => 'p',
			'authorid'     => 'author',
			'authorname'   => 'author_name',
			'search'       => 's',
			'searchterm'   => 'search',
			'status'       => 'post_status',
			'offset'       => 'offset',
			'metaquery'    => 'meta_query',
			'datequery'    => 'date_query',
			'taxquery'     => 'tax_query',
			'order'        => 'order',
			'orderby'      => 'orderby',
			'metakey'      => 'meta_key',
			'metavalue'    => 'meta_value',
			'metavaluenum' => 'meta_value_num',
			'metacompare'  => 'meta_compare',
			'hideempty'    => 'hide_empty',
			'perpage'      => 'posts_per_page',
			'number'	   => 'number',
			'parent'	   => 'parent',
			'paged'        => 'paged',
			'postin'       => 'post__in',
			'relation'     => 'relation',
			'nopaging'     => 'nopaging',
			'metatype'     => 'meta_type',
			'hierarchical' => 'hierarchical',
			'exclude'      => 'exclude',
			'excludetree'  => 'exclude_tree',
			'include'      => 'include'
		];

		$meta_query = [
			'key'       	=> 'key',
			'value'     	=> 'value',
			'compare'   	=> 'compare',
			'relation'  	=> 'relation',
			'metadatumtype' => 'type',
		];

		$date_query = [
			'year'      => 'year',
			'month'     => 'month',
			'day'       => 'day',
			'week'      => 'week',
			'hour'      => 'hour',
			'minute'    => 'minute',
			'second'    => 'second',
			'compare'   => 'compare',
			'dayofweek' => 'dayofweek',
			'inclusive' => 'inclusive',
			'before'    => 'before',
			'after'     => 'after',
		];

		$tax_query = [
			'taxonomy'  => 'taxonomy',
			'metadatum' => 'field',
			'terms'     => 'terms',
			'operator'  => 'operator',
			'relation'  => 'relation',
		];

		$args = [];

		foreach ($queries as $mapped => $mapped_v){
			if(isset($request[$mapped])){
				if($mapped === 'metaquery'){
					$args = $this->prepare_meta($mapped, $request, $meta_query, $mapped_v, $args);
				} elseif($mapped === 'datequery'){
					$args = $this->prepare_meta($mapped, $request, $date_query, $mapped_v, $args);
				} elseif($mapped === 'taxquery'){
					$args = $this->prepare_meta($mapped, $request, $tax_query, $mapped_v, $args);
				}
				else {
					$args[ $mapped_v ] = $request[ $mapped ];
				}
			}
		}

		$args['perm'] = 'readable';

		return apply_filters('tainacan-api-prepare-items-args', $args, $request);
	}

	public function add_support_to_tax_query_like($args) {

		if (!isset($args['tax_query']) || !is_array($args['tax_query'])) {
			return $args;
		}

		$new_tax_query = [];

		foreach ($args['tax_query'] as $index => $tax_query) {

			if ( isset($tax_query['operator']) && $tax_query['operator'] == 'LIKE' &&
		 		isset($tax_query['terms']) && is_string($tax_query['terms']) ) {

				$terms = get_terms([
					'taxonomy' => $tax_query['taxonomy'],
					'fields' => 'ids',
					'hide_empty' => isset($args['hide_empty']) ? $args['hide_empty'] : true,
					'search' => $tax_query['terms']
				]);

				$new_tax_query[] = [
					'taxonomy' => $tax_query['taxonomy'],
					'terms' => $terms,
				];

			} elseif ( isset($tax_query['operator']) && $tax_query['operator'] == 'NOT LIKE' &&
		 		isset($tax_query['terms']) && is_string($tax_query['terms']) ) {

				$terms = get_terms([
					'taxonomy' => $tax_query['taxonomy'],
					'fields' => 'ids',
					'hide_empty' => isset($args['hide_empty']) ? $args['hide_empty'] : true,
					'search' => $tax_query['terms']
				]);
				if ($terms) {
					$new_tax_query[] = [
						'taxonomy' => $tax_query['taxonomy'],
						'terms' => $terms,
						'operator' => 'NOT IN'
					];
				}


			} else {
				$new_tax_query[] = $tax_query;
			}

		}

		$args['tax_query'] = $new_tax_query;

		return $args;

	}

	/**
	 * @param $mapped
	 * @param $request
	 * @param $query
	 * @param $mapped_v
	 * @param $args
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	private function prepare_meta($mapped, $request, $query, $mapped_v, $args){
		$request_meta_query = $request[$mapped];

		// if the meta/date/taxquery has a root relation
        if( isset( $request_meta_query['relation']) )
            $args[ $mapped_v ]['relation'] = $request_meta_query['relation'];

		// If is a multidimensional array (array of array)
		if($this->contains_array($request_meta_query, $query)) {

			foreach ( $request_meta_query as $index1 => $a ) {

				foreach ( $query as $mapped_meta => $meta_v ) {
					if ( isset( $a[ $meta_v ] ) ) {
						$args[ $mapped_v ][ $index1 ][ $meta_v ] = $request[ $mapped ][ $index1 ][ $meta_v ];
					}
				}

			}

		} else {
			foreach ( $query as $mapped_meta => $meta_v ) {
				if(isset($request[$mapped][$meta_v])) {
					$args[ $mapped_v ][ $meta_v ] = $request[ $mapped ][ $meta_v ];
				}
			}
		}

		return $args;
	}

	/**
	 * @param $array
	 *
	 * @param $query
	 *
	 * @return bool
	 */
	protected function contains_array($array, $query){
		foreach ($array as $index => $value){
			// Not will pass named meta query, which use reserved names
			if(is_array($value) && !key_exists($index, $query)){
				return true;
			}
		}

		return false;
	}

	/**
	 * Return the fetch_only param
	 *
	 * @param string $object_name
	 *
	 * @return array|void
	 */
	public function get_fetch_only_param(){
		return [
			'fetch_only' => array(
				'type'        => 'string/array',
				'description' => __( 'Fetch only specific attribute. The specifics attributes are the same in schema.', 'tainacan' ),
				//TODO: explicar o fetch only meta.. cabe aqui?
			)
		];
	}

	/**
	 * Return the common params
	 *
	 * @param string $object_name
	 *
	 * @return array|void
	 */
	public function get_wp_query_params(){

		$query_params['id'] = array(
			'description' => __("Limit result to objects with specific id.", 'tainacan'),
			'type'        => 'integer',
		);

		$query_params['context'] = array(
			'type'    	  => 'string',
			'default' 	  => 'view',
			'description' => 'The context in which the request is made.',
			'enum'    	  => array(
				'view',
				'edit'
			),
		);

		$query_params['search'] = array(
			'description'        => __( 'Limit results to those matching a string.', 'tainacan' ),
			'type'               => 'string',
			'sanitize_callback'  => 'sanitize_text_field',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		$query_params['authorid'] = array(
			'description' => __("Limit result set to objects assigned to specific authors by id.", 'tainacan'),
			'type'        => 'integer',
		);

		$query_params['authorname'] = array(
			'description' => __("Limit result set to objects assigned to specific authors by name", 'tainacan'),
			'type'        => 'string',
		);

		$query_params['status'] = array(
			'description' => __("Limit result set to objects assigned one or more statuses.", 'tainacan'),
			'type'        => 'array',
			'items'       => array(
				'enum'    => array_merge(array_keys(get_post_stati()), array('any')),
				'type'    => 'string',
			),
		);

		$query_params['offset'] = array(
			'description'        => __( "Offset the result set by a specific number of objects.", 'tainacan' ),
			'type'               => 'integer',
		);

		$query_params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.', 'tainacan' ),
			'type'               => 'string/array',
			'default'            => 'desc',
			'enum'               => array( 'asc', 'desc', 'ASC', 'DESC' ),
		);

		$query_params['orderby'] = array(
			'description'        => __( "Sort objects by object attribute.", 'tainacan' ),
			'type'               => 'string/array',
			'default'            => 'date',
			'enum'               => array(
				'author',
				'date',
				'id',
				'include',
				'modified',
				'parent',
				'relevance',
				'slug',
				'include_slugs',
				'title',
				'meta_value',
				'meta_value_num'
			),
		);

		$query_params['perpage'] = array(
			'description'        => __( "Maximum number of objects to be returned in result set.", 'tainacan' ),
			'type'               => 'numeric',
			'default'            => 10,
		);

		$query_params['paged'] = array(
			'description' => __("The results page to be return.", 'tainacan'),
			'type'        => 'integer',
		);

		return $query_params;
	}

	/**
	 * Return the common meta, date and tax queries params
	 *
	 * @return array
	 */
	protected function get_meta_queries_params(){
		return array(
			'metakey'      => array(
				'type'        => 'integer/string',
				'description' => __('Custom metadata key.'),
			),
			'metavalue'    => array(
				'type'        => 'string/array',
				'description' => __('Custom metadata value'),
			),
			'metavaluenum' => array(
				'type'        => 'number',
				'description' => __('Custom metadata value'),
			),
			'metacompare'  => array(
				'type'        => 'string',
				'description' => __('Operator to test the metavalue'),
				'default'     => '=',
				'enum'        => array(
					'=',
					'!=',
					'>',
					'>=',
					'<',
					'<=',
					'LIKE',
					'NOT LIKE',
					'IN',
					'NOT IN',
					'BETWEEN',
					'NOT BETWEEN',
					'NOT EXISTS',
					'REGEXP',
					'NOT REGEXP',
					'RLIKE'
				)
			),
			'metaquery'    => array(
				'description' => __('Limits result set to items that have specific custom metadata'),
				'type'        => 'array/object',
				'items'       => array(
					'keys' => array(
						'key'      => array(
							'type'        => 'string',
							'description' => __('Custom metadata key.'),
						),
						'value'    => array(
							'type'        => 'string/array',
							'description' => __('Custom metadata value. It can be an array only when compare is IN, NOT IN, BETWEEN, or NOT BETWEEN. You dont have to specify a value when using the EXISTS or NOT EXISTS comparisons in WordPress 3.9 and up.
	(Note: Due to bug #23268, value is required for NOT EXISTS comparisons to work correctly prior to 3.9. You must supply some string for the value parameter. An empty string or NULL will NOT work. However, any other string will do the trick and will NOT show up in your SQL when using NOT EXISTS. Need inspiration? How about \'bug #23268\'.'),
						),
						'compare'  => array(
							'type'        => 'string',
							'description' => __('Operator to test.'),
							'default'     => '=',
							'enum'		  => array(
								'=',
								'!=',
								'>',
								'>=',
								'<',
								'<=',
								'LIKE',
								'NOT LIKE',
								'IN',
								'NOT IN',
								'BETWEEN',
								'NOT BETWEEN',
								'EXISTS',
								'NOT EXISTS'
							)
						),
						'relation' => array(
							'type'        => 'string',
							'description' => __('OR or AND, how the sub-arrays should be compared.'),
							'default'     => 'AND',
						),
						'metadatumtype' => array(
							'type'        => 'string',
							'description' => __('Custom metadata type. Possible values are NUMERIC, BINARY, CHAR, DATE, DATETIME, DECIMAL, SIGNED, TIME, UNSIGNED. Default value is CHAR. You can also specify precision and scale for the DECIMAL and NUMERIC types (for example, DECIMAL(10,5) or NUMERIC(10) are valid). The type DATE works with the compare value BETWEEN only if the date is stored at the format YYYY-MM-DD and tested with this format.'),
						),
					),
					'type'            => 'array'
				),
			),
			'datequery'    => array(
				'description' => __('Limits the result set to items that were created in some specific date'),
				'type'        => 'array/object',
				'items'       => array(
					'keys' => array(
						'year'      => array(
							'type'        => 'integer',
							'description' => __('4 digit year (e.g. 2018).'),
						),
						'month'     => array(
							'type'        => 'integer',
							'description' => __('Month number (from 1 to 12).'),
						),
						'day'       => array(
							'type'        => 'integer',
							'description' => __('Day of the month (from 1 to 31).'),
						),
						'week'      => array(
							'type'        => 'integer',
							'description' => __('Week of the year (from 0 to 53).'),
						),
						'hour'      => array(
							'type'        => 'integer',
							'description' => __('Hour (from 0 to 23).'),
						),
						'minute'    => array(
							'type'        => 'integer',
							'description' => __('Minute (from 0 to 59).'),
						),
						'second'    => array(
							'type'        => 'integer',
							'description' => __('Second (from 0 to 59).')
						),
						'compare'   => array(
							'type'        => 'string',
							'description' => __('Operator to test.'),
							'default'     => '=',
							'enum'        => array(
								'=',
								'!=',
								'>',
								'>=',
								'<',
								'<=',
								'LIKE',
								'NOT LIKE',
								'IN',
								'NOT IN',
								'BETWEEN',
								'NOT BETWEEN',
								'EXISTS',
								'NOT EXISTS'
							)
						),
						'dayofweek' => array('type' => 'array'),
						'inclusive' => array(
							'type'        => 'boolean',
							'description' => __('For after/before, whether exact value should be matched or not.'),
						),
						'before'    => array(
							'type'        => 'string/array',
							'description' => __('Date to retrieve posts before. Accepts strtotime()-compatible string, or array of year, month, day '),
						),
						'after'     => array(
							'type'        => 'string/array',
							'description' => __('Date to retrieve posts after. Accepts strtotime()-compatible string, or array of year, month, day '),
						),
					),
					'type'      => 'array'
				),
			),
			'taxquery'     => array(
				'description' => __('Show items associated with certain taxonomy.'),
				'type'        => 'array/object',
				'items'       => array(
					'keys' => array(
						'taxonomy' => array(
							'type'        => 'string',
							'description' => __('The taxonomy data base identifier.')
						),
						'metadatum'    => array(
							'type'        => 'string',
							'default'	  => 'term_id',
							'description' => __('Select taxonomy term by'),
							'enum'		  => array(
								'term_id',
								'name',
								'slug',
								'term_taxonomy_id'
							)
						),
						'terms'    => array(
							'type'        => 'int/string/array',
							'description' => __('Taxonomy term(s).'),
						),
						'operator' => array(
							'type'        => 'string',
							'description' => __('Operator to test.'),
							'default'     => 'IN',
							'enum'        => array(
								'IN',
								'NOT IN',
								'AND',
								'EXISTS',
								'NOT EXISTS'
							)
						),
						'relation' => array(
							'type'        => 'string',
							'description' => __('The logical relationship between each inner taxonomy array when there is more than one. Do not use with a single inner taxonomy array.'),
							'default'     => 'AND',
							'enum'		  => array(
								'AND',
								'OR'
							)
						),
					),
					'type'     => 'array'
				),
			),
		);
	}

	function get_repository_schema( \Tainacan\Repositories\Repository $repository ) {

		$schema = [];

		$map = $repository->get_map();

		foreach ($map as $mapped => $value){
			$schema[$mapped] = [
				'description' => $value['description'],
				'type' => $value['type']
			];
		}

		return $schema;

	}


	function get_permissions_schema() {

		return [
			'current_user_can_edit' => [
				'description' => esc_html__('Whether current user can edit this object', 'tainacan'),
				'type' => 'boolean',
				'context' => 'edit'
			],
			'current_user_can_delete' => [
				'description' => esc_html__('Whether current user can delete this object', 'tainacan'),
				'type' => 'boolean',
				'context' => 'edit'
			]
		];

	}

	function get_base_properties_schema() {
		return [

			'id' => [
				'description'  => esc_html__( 'Unique identifier for the object.', 'tainacan' ),
				'type'         => 'integer',
				'context'      => array( 'view', 'edit' ),
				'readonly'     => true
			]
		];
	}

}

?>
