<?php

namespace Tainacan\API;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Abstract base class for Tainacan REST API controllers.
 *
 * Provides common functionality for all Tainacan REST API endpoints including
 * object filtering, attribute handling, and standard WordPress REST API integration.
 *
 * @since 1.0.0
 */
abstract class REST_Controller extends \WP_REST_Controller {

	/**
	 * Constructor for the REST_Controller class.
	 *
	 * Sets up the namespace and registers routes and filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = TAINACAN_REST_NAMESPACE;
		add_action('rest_api_init', array($this, 'register_routes'));
		add_filter('tainacan-api-prepare-items-args', array($this, 'add_support_to_tax_query_like'));
	}

	/**
	 * Filters an object by specified attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $object     The object to filter.
	 * @param string|array $attributes The attributes to include in the filtered result.
	 * @return array Filtered object data.
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
	 * Prepares an item for updating with new values.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $object     The object to update.
	 * @param array $new_values New values to set on the object.
	 * @return \Tainacan\Entities\Entity The updated entity.
	 */
	protected function prepare_item_for_updating($object, $new_values){
		$readonly = $this->get_readonly_fields();
		foreach ($new_values as $key => $value) {
			if ( in_array($key, $readonly, true) ) {
				continue;
			}
			$object->set($key, $value);
		}
		return $object;
	}

	protected function get_readonly_fields() {
		return [ 'id', 'author_id', 'creation_date', 'modification_date' ];
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
			's'			   => 's',	
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
			'exclude'      => 'post__not_in',
			'excludetree'  => 'exclude_tree',
			'exclude_tree' => 'exclude_tree',
			'include'      => 'include',
			'sentence'     => 'sentence'
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
			'column'    => 'column',
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

	protected function sanitize_value($value) {
		if (is_numeric($value) || empty($value) ) {
			return $value;
		}

		$allowed_html = wp_kses_allowed_html('post');
		unset($allowed_html["a"]);
	
		return trim(wp_kses($value, $allowed_html));
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
		$query_field_scaped = ["value", "terms"];

		// if the meta/date/taxquery has a root relation
        if( isset( $request_meta_query['relation']) )
            $args[ $mapped_v ]['relation'] = $request_meta_query['relation'];

		// If is a multidimensional array (array of array)
		if($this->contains_array($request_meta_query, $query)) {
			
			foreach ( $request_meta_query as $index1 => $a ) {

				foreach ( $query as $mapped_meta => $meta_v ) {
					if ( isset( $a[ $mapped_meta ] ) ) {
						if( in_array($mapped_meta, $query_field_scaped) ) {
							$valeu =  is_array($request[ $mapped ][ $index1 ][ $mapped_meta ])
								? array_map([$this, 'sanitize_value'], $request[ $mapped ][ $index1 ][ $mapped_meta ])
								: $this->sanitize_value($request[ $mapped ][ $index1 ][ $mapped_meta ]);
							$args[ $mapped_v ][ $index1 ][ $meta_v ] = $valeu;
						} else {
							$args[ $mapped_v ][ $index1 ][ $meta_v ] = $request[ $mapped ][ $index1 ][ $mapped_meta ];

						}
					}
				}

			}

		} else {
			foreach ( $query as $mapped_meta => $meta_v ) {
				if(isset($request[$mapped][$mapped_meta])) {
					if( in_array($mapped_meta, $query_field_scaped) ) {
						$args[ $mapped_v ][ $meta_v ] =  is_array($request[ $mapped ][ $mapped_meta ])
							? array_map([$this, 'sanitize_value'], $request[ $mapped ][ $mapped_meta ])
							: $this->sanitize_value($request[ $mapped ][ $mapped_meta ]);
					} else {
						$args[ $mapped_v ][ $meta_v ] = $request[ $mapped ][ $mapped_meta ];
					}
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
				'type'        => ['string','array'],
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

		$query_params['sentence'] = array(
			'description'        => __( 'Whether to treat the search string as a single phrase.', 'tainacan' ),
			'type'               => 'boolean',
			'default'            => true,
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
			'sanitize_callback' => array( $this, 'tainacan_sanitize_post_statuses' ),
		);

		$query_params['offset'] = array(
			'description'        => __( "Offset the result set by a specific number of objects.", 'tainacan' ),
			'type'               => 'integer',
		);

		$query_params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.', 'tainacan' ),
			'type'               => ['string','array'],
			'default'            => 'desc',
			'enum'               => array( 'asc', 'desc', 'ASC', 'DESC' ),
		);

		$query_params['orderby'] = array(
			'description'        => __( "Sort objects by object attribute.", 'tainacan' ),
			'type'               => ['string', 'array'],
			'default'            => 'date',
			// 'items' => [
			// 	'type' => 'string'
			// ],
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
				'meta_value_num',
				'menu_order',
				'rand',
				'post__in'
			),
		);

		$query_params['perpage'] = array(
			'description'        => __( "Maximum number of objects to be returned in result set.", 'tainacan' ),
			'type'               => 'number',
			'default'            => 10,
		);

		$query_params['paged'] = array(
			'description' => __("The results page to be returned.", 'tainacan'),
			'type'        => 'integer',
		);

		$query_params['exclude'] = array(
			'description' => __("Ensure result set excludes specific IDs.", 'tainacan'),
			'type'        => 'array',
			'items' => [
				'type' => 'integer'
			]
		);

		return $query_params;
	}

	/**
	 * Return the common meta, date and tax queries params
	 *
	 * @return array
	 */
	protected function get_meta_queries_params(){

		$metaquery_properties = array(
			'key'      => array(
				'type'        => ['integer', 'string'],
				'description' => __('Custom metadata key.', 'tainacan'),
			),
			'value'    => array(
				'type'        => ['string', 'array'],
				'items'       => array('type' => 'string'),
				'description' => __('Custom metadata value. It can be an array only when compare is IN, NOT IN, BETWEEN, or NOT BETWEEN. You dont have to specify a value when using the EXISTS or NOT EXISTS comparisons in WordPress 3.9 and up. (Note: Due to bug #23268, value is required for NOT EXISTS comparisons to work correctly prior to 3.9. You must supply some string for the value parameter. An empty string or NULL will NOT work. However, any other string will do the trick and will NOT show up in your SQL when using NOT EXISTS. Need inspiration? How about \'bug #23268\'.', 'tainacan'),
				'sanitize_callback'  => 'sanitize_text_field',
			),
			'compare'  => array(
				'type'        => 'string',
				'description' => __('Operator to test.', 'tainacan'),
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
				'description' => __('OR or AND, how the sub-arrays should be compared.', 'tainacan'),
				'default'     => 'AND',
			),
			'metadatumtype' => array(
				'type'        => 'string',
				'description' => __('Custom metadata type. Possible values are NUMERIC, BINARY, CHAR, DATE, DATETIME, DECIMAL, SIGNED, TIME, UNSIGNED. Default value is CHAR. You can also specify precision and scale for the DECIMAL and NUMERIC types (for example, DECIMAL(10,5) or NUMERIC(10) are valid). The type DATE works with the compare value BETWEEN only if the date is stored at the format YYYY-MM-DD and tested with this format.', 'tainacan'),
			),
		);

		return array(
			'metakey'      => array(
				'type'        => ['integer', 'string'],
				'description' => __('Custom metadata key.', 'tainacan'),
			),
			'metavalue'    => array(
				'type'        => ['string', 'array'],
				'description' => __('Custom metadata value', 'tainacan'),
				'sanitize_callback'  => 'sanitize_text_field',
			),
			'metavaluenum' => array(
				'type'        => 'number',
				'description' => __('Custom metadata value', 'tainacan'),
			),
			'metacompare'  => array(
				'type'        => 'string',
				'description' => __('Operator to test the metavalue', 'tainacan'),
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
				'description' => __('Limits result set to items that have specific custom metadata', 'tainacan'),
				'type'        => ['array', 'object',],
				'properties'  => $metaquery_properties,
				'items'       => array(
					'type'            => 'object',
					'properties'  => $metaquery_properties,
				),
			),
			'datequery'    => array(
				'description' => __('Limits the result set to items that were created or modified in some specific date', 'tainacan'),
				'type'        => ['array', 'object'],
				'items'       => array(
					'keys' => array(
						'year'      => array(
							'type'        => 'integer',
							'description' => __('4 digit year (e.g. 2018).', 'tainacan'),
						),
						'month'     => array(
							'type'        => 'integer',
							'description' => __('Month number (from 1 to 12).', 'tainacan'),
						),
						'day'       => array(
							'type'        => 'integer',
							'description' => __('Day of the month (from 1 to 31).', 'tainacan'),
						),
						'week'      => array(
							'type'        => 'integer',
							'description' => __('Week of the year (from 0 to 53).', 'tainacan'),
						),
						'hour'      => array(
							'type'        => 'integer',
							'description' => __('Hour (from 0 to 23).', 'tainacan'),
						),
						'minute'    => array(
							'type'        => 'integer',
							'description' => __('Minute (from 0 to 59).', 'tainacan'),
						),
						'second'    => array(
							'type'        => 'integer',
							'description' => __('Second (from 0 to 59).', 'tainacan'),
						),
						'compare'   => array(
							'type'        => 'string',
							'description' => __('Operator to test.', 'tainacan'),
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
							'description' => __('For after/before, whether exact value should be matched or not.', 'tainacan'),
						),
						'before'    => array(
							'type'        => ['string', 'array'],
							'description' => __('Date to retrieve posts before. Accepts strtotime()-compatible string, or array of year, month, day ', 'tainacan'),
						),
						'after'     => array(
							'type'        => ['string', 'array'],
							'description' => __('Date to retrieve posts after. Accepts strtotime()-compatible string, or array of year, month, day ', 'tainacan'),
						),
						'column'     => array(
							'type'        => 'string',
							'description' => __('Posts column to query against, possible values: post_date, post_date_gmt, post_modified, post_modified_gmt. Default: ‘post_date’.', 'tainacan'),
						),
					),
					'type'      => ['array', 'object']
				),
			),
			'taxquery'     => array(
				'description' => __('Show items associated with certain taxonomy.', 'tainacan'),
				'type'        => ['array', 'object'],
				'items'       => array(
					'keys' => array(
						'taxonomy' => array(
							'type'        => 'string',
							'description' => __('The taxonomy data base identifier.', 'tainacan')
						),
						'metadatum'    => array(
							'type'        => 'string',
							'default'	  => 'term_id',
							'description' => __('Select taxonomy term by', 'tainacan'),
							'enum'		  => array(
								'term_id',
								'name',
								'slug',
								'term_taxonomy_id'
							)
						),
						'terms'    => array(
							'type'        => ['integer', 'string', 'array'],
							'sanitize_callback'  => 'sanitize_text_field',
							'description' => __('Taxonomy term(s).', 'tainacan'),
						),
						'operator' => array(
							'type'        => 'string',
							'description' => __('Operator to test.', 'tainacan'),
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
							'description' => __('The logical relationship between each inner taxonomy array when there is more than one. Do not use with a single inner taxonomy array.', 'tainacan'),
							'default'     => 'AND',
							'enum'		  => array(
								'AND',
								'OR'
							)
						),
					),
					'type'     => ['array', 'object']
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
			if( isset($value['items'])) {
				$schema[$mapped]['items'] = $value['items'];
			}
		}

		return $schema;

	}

	/**
	 * Returns a schema definition for permission-related object properties.
	 *
	 * This helper builds a schema object describing whether the current user
	 * can edit or delete the object, including the context in which the
	 * permissions apply. It is used for documenting API endpoints and for
	 * client-side validation.
	 *
	 * @since 1.2.0
	 *
	 * @return array The schema definition.
	 */
	protected function get_permissions_schema() {
		return [
			'current_user_can_edit' => [
				'description' => __( 'Whether current user can edit this object', 'tainacan' ),
				'type'        => 'boolean',
				'context'     => 'edit',
			],
			'current_user_can_delete' => [
				'description' => __( 'Whether current user can delete this object', 'tainacan' ),
				'type'        => 'boolean',
				'context'     => 'edit',
			],
		];
	}

	function get_base_properties_schema() {
		return [

			'id' => [
				'description'  => __('Unique identifier for the object.', 'tainacan'),
				'type'         => 'integer',
				'context'      => array( 'view', 'edit' ),
				'readonly'     => true
			]
		];
	}

	protected abstract function get_schema();

	function get_list_schema() {
		$schema = [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'type' => 'array',
			'items' => $this->get_schema(),
			'title' => $this->rest_base,
			'tags' => [$this->rest_base],
		];
		return $schema;
	}

	/**
	 * Sanitizes and validates a list of post statuses for use in REST requests.
	 *
	 * Accepts a list of status slugs (string or array). If it contains 'any',
	 * returns all non-internal post statuses. Otherwise, validates each
	 * status against those allowed by get_post_stati(); returns WP_Error if any
	 * status is invalid.
	 *
	 * @param string|array $statuses   List of statuses (comma-separated string or array of slugs).
	 * @param \WP_REST_Request $request REST request object (not used in the current logic).
	 * @param string $parameter        Parameter name in the request (not used in the current logic).
	 *
	 * @return array|\WP_Error Array of valid status slugs or WP_Error if any status is not allowed.
	 */
	public function tainacan_sanitize_post_statuses( $statuses, $request, $parameter ) {
		$statuses = wp_parse_slug_list( $statuses );
		$allowStatuses = array_values(get_post_stati([]));
		foreach ( $statuses as $status ) {
			if ( $status === 'any' ) {
				$allstatuses = get_post_stati(
					['internal' => false]
				);
				return array_values($allstatuses);
			} else {
				if(!in_array($status, $allowStatuses)) {
				return new \WP_Error(
					'rest_forbidden_status',
					__( 'Status is forbidden.', 'tainacan' ),
					array( 'status' => rest_authorization_required_code() )
				);
			}

			}
		}
		return $statuses;
	}

	/**
		* Returns a schema definition for request parameters.
		*
		* This helper builds a schema object describing the request parameters,
		* including their types, descriptions, required status, and validation
		* callbacks. It is used for documenting API endpoints and for client-side
		* validation.
		*
		* @since 1.2.0
		*
		* @param string $param_name The name of the parameter.
		* @param array  $properties The properties of the parameter.
		* @return array The schema definition.
		*/
	protected function get_param_schema( $param_name, $properties ) {
		$schema = [
			'title'          => $param_name,
			'description'    => isset( $properties['description'] ) ? $properties['description'] : '',
			'type'           => isset( $properties['type'] ) ? $properties['type'] : 'string',
			'required'       => isset( $properties['required'] ) && $properties['required'],
			'enum'           => isset( $properties['enum'] ) ? $properties['enum'] : null,
			'default'        => isset( $properties['default'] ) ? $properties['default'] : null,
		];

		if ( isset( $properties['items'] ) ) {
			$schema['items'] = $properties['items'];
		}

		if ( isset( $properties['properties'] ) ) {
			$schema['properties'] = $properties['properties'];
		}

		return $schema;
	}


	/**
	 * Returns a schema definition for field selection.
	 *
	 * This helper builds a schema object describing the _fields parameter
	 * used for selecting specific fields to return in the response. It
	 * accepts a list of allowed fields and validates that only those
	 * fields are requested. This enables efficient responses by allowing
	 * clients to request only the fields they need.
	 *
	 * @since 1.3.0
	 *
	 * @param string $field_name The name of the field parameter.
	 * @param array  $allowed_fields The allowed fields.
	 * @return array The schema definition.
	 */
	protected function get_field_schema( $field_name, $allowed_fields ) {
		return [
			'title'          => $field_name,
			'description'    => __( 'Comma-separated list of fields to include in response. Use empty string for all fields.', 'tainacan' ),
			'type'           => 'string',
			'default'        => '',
			'enum'           => array_merge( [ '' ], $allowed_fields ),
			'sanitize_callback' => function( $value ) {
				if ( $value === '' ) {
					return '';
				}
				$fields = wp_parse_list( $value );
				$allowed = array_map( 'sanitize_key', $allowed_fields );
				return array_intersect( $fields, $allowed );
			},
		];
	}

	/**
	 * Returns field selection arguments for use in register_rest_route.
	 *
	 * @since 1.3.0
	 *
	 * @param string $field_name The name of the field parameter.
	 * @param array  $allowed_fields The allowed fields.
	 * @return array The args for register_rest_route.
	 */
	protected function get_field_args( $field_name, $allowed_fields ) {
		return [
			$field_name => $this->get_field_schema( $field_name, $allowed_fields ),
		];
	}

	/**
		* Returns a schema definition for error responses.
		*
		* This helper builds a schema object describing the structure of error
		* responses returned by the API. It includes fields for the error code,
		* a human-readable message, and any additional contextual information.
		*
		* @since 1.2.0
		*
		* @return array The schema definition.
		*/
	protected function get_error_response_schema() {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'title'      => 'error',
			'tags'       => [ 'error' ],
			'properties' => [
				'error_code' => [
					'description' => __( 'Error code identifying the type of error', 'tainacan' ),
					'type'        => 'string',
				],
				'error_message' => [
					'description' => __( 'Human-readable error message', 'tainacan' ),
					'type'        => 'string',
				],
				'status' => [
					'description' => __( 'HTTP status code', 'tainacan' ),
					'type'        => 'integer',
				],
				'data' => [
					'description' => __( 'Additional data related to the error', 'tainacan' ),
					'type'        => 'object',
				],
			],
		];
	}

	/**
	 * Returns a schema definition for paginated list responses.
	 *
	 * This helper builds a schema object describing the structure of paginated
	 * list responses returned by the API. It includes fields for the total
	 * number of items, total number of pages, the current page number, the
	 * number of items per page, and the array of items.
	 *
	 * @since 1.2.0
	 *
	 * @return array The schema definition.
	 */
	protected function get_paginated_list_schema() {
		return [
			'$schema'  => 'http://json-schema.org/draft-04/schema#',
			'type'     => 'object',
			'title'    => 'paginated_list',
			'tags'     => [ 'paginated_list' ],
			'properties' => [
				'total' => [
					'description' => __( 'Total number of items in the result set', 'tainacan' ),
					'type'        => 'integer',
				],
				'total_pages' => [
					'description' => __( 'Total number of pages', 'tainacan' ),
					'type'        => 'integer',
				],
				'current_page' => [
					'description' => __( 'Current page number', 'tainacan' ),
					'type'        => 'integer',
				],
				'per_page' => [
					'description' => __( 'Number of items per page', 'tainacan' ),
					'type'        => 'integer',
				],
				'items' => [
					'description' => __( 'Array of items on the current page', 'tainacan' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'object',
					],
				],
			],
		];
	}

	/**
	 * Returns a schema definition for paginated list responses.
	 *
	 * This helper builds a schema object describing the structure of paginated
	 * list responses returned by the API. It includes fields for the total
	 * number of items, total number of pages, the current page number, the
	 * number of items per page, and the array of items.
	 *
	 * @since 1.3.0
	 *
	 * @return array The schema definition.
	 */
	public function get_paginated_response_schema() {
		return $this->get_paginated_list_schema();
	}

	/**
	 * Prepares a paginated response with proper headers and Link header.
	 *
	 * This helper builds a paginated response including the X-WP-Total,
	 * X-WP-TotalPages, and X-WP-ItemsPerPage headers, as well as the Link
	 * header for pagination navigation. It is designed to work with the
	 * get_paginated_list_schema() method.
	 *
	 * @param array        $data        The data to include in the response.
	 * @param int          $total       Total number of items.
	 * @param int          $total_pages Total number of pages.
	 * @param int          $current_page Current page number.
	 * @param int          $per_page    Number of items per page.
	 * @param \WP_REST_Response|null $response Optional. The response object to modify.
	 *
	 * @return \WP_REST_Response The paginated response.
	 *
	 * @since 1.3.0
	 */
	protected function prepare_paginated_response( $data, $total, $total_pages, $current_page, $per_page, $response = null ) {
		$response = $response ?: new \WP_REST_Response( $data, 200 );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $total_pages );
		$response->header( 'X-WP-ItemsPerPage', (int) $per_page );

		// Add Link header for pagination navigation.
		// WP_REST_Response does not expose a link() method, so we build the
		// RFC 8288 Link header manually (multiple links comma-separated).
		$base   = rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) );
		$links  = array();

		if ( $current_page < $total_pages ) {
			$next_link = sprintf( '%s?page=%d&per_page=%d', $base, $current_page + 1, $per_page );
			$links[]   = sprintf( '<%s>; rel="next"', $next_link );
		}
		if ( $current_page > 1 ) {
			$prev_link = sprintf( '%s?page=%d&per_page=%d', $base, $current_page - 1, $per_page );
			$links[]   = sprintf( '<%s>; rel="prev"', $prev_link );
		}

		if ( ! empty( $links ) ) {
			$response->header( 'Link', implode( ', ', $links ) );
		}

		return $response;
	}

	/**
	 * Adds resource expansion support for the _embed parameter.
	 *
	 * @param \WP_REST_Response $response The response to modify.
	 * @param \WP_REST_Request  $request  The request object.
	 *
	 * @return \WP_REST_Response The modified response with embedded resources.
	 *
	 * @since 1.3.0
	 */
	protected function add_embed_support( $response, $request ) {
		if ( empty( $request['_embed'] ) ) {
			return $response;
		}

		$data = $response->get_data();

		if ( method_exists( $this, 'embed_resource' ) ) {
			$embedded = $this->embed_resource( $request );

			if ( ! empty( $embedded ) ) {
				$data['_embedded'] = array_merge(
					isset( $data['_embedded'] ) ? $data['_embedded'] : array(),
					$embedded
				);
			}
		}

		$response->set_data( $data );
		return $response;
	}

	/**
	 * Embeds related resources for a single item.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return array An array of embedded resources keyed by embed name.
	 *
	 * @since 1.3.0
	 */
	protected function embed_resource( $request ) {
		return array();
	}

}
