<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;
use Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
/**
 * Class Fields
 */
class Fields extends Repository {
	public $entities_type = '\Tainacan\Entities\Field';
	protected $default_metadata = 'default';

	public $field_types = [];

	public $core_fields = [
	    'Tainacan\Field_Types\Core_Title',
	    'Tainacan\Field_Types\Core_Description'
    ];
    /**
     * Register specific hooks for field repository
     */
    function __construct() {
        parent::__construct();
        add_filter('pre_trash_post', array( &$this, 'disable_delete_core_fields' ), 10, 2 );
        add_filter('pre_delete_post', array( &$this, 'force_delete_core_fields' ), 10, 3 );
    }
	
    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(), [
            'name'           => [
                'map'        => 'post_title',
                'title'       => __('Name', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Name of the field', 'tainacan'),
                'on_error'   => __('The name should be a text value and not empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'slug'           =>  [
                'map'        => 'post_name',
                'title'      => __('Slug', 'tainacan'),
                'type'       => 'string',
                'description'=> __('A unique and santized string representation of the field', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'order'          => [
                'map'        => 'menu_order',
                'title'       => __('Order', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Field order. Field used if collections are manually ordered', 'tainacan'),
                'on_error'   => __('The menu order should be a numeric value', 'tainacan'),
                //'validation' => v::numeric(),
            ],
            'parent'         => [
                'map'        => 'parent',
                'title'      => __('Parent', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('Parent field', 'tainacan'),
                //'on_error'   => __('The Parent should be numeric value', 'tainacan'),
                //'validation' => v::numeric(),
            ],
            'description'    => [
                'map'        => 'post_content',
                'title'      => __('Description', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The field description', 'tainacan'),
            	'default'	 => '',
                //'on_error'   => __('The description should be a text value', 'tainacan'),
                //'validation' => v::stringType()->notEmpty(),
            ],
            'field_type'     => [
                'map'        => 'meta',
                'title'      => __('Type', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The field type', 'tainacan'),
                'on_error'   => __('Field type is empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'required'       => [
                'map'        => 'meta',
                'title'      => __('Required', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The field is required', 'tainacan'),
                'on_error'   => __('Field required field is invalid', 'tainacan'),
                'validation' => v::stringType()->in(['yes', 'no']), // yes or no
                'default'    => 'no'
            ],
            'collection_key' => [
                'map'        => 'meta',
                'title'      => __('Collection key', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Field value should not be repeated', 'tainacan'),
                'on_error'   => __('Collection key is invalid', 'tainacan'),
                'validation' => v::stringType()->in(['yes', 'no']), // yes or no
                'default'    => 'no'
            ],
            'multiple'       => [
                'map'        => 'meta',
                'title'      => __('Multiple', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Allow multiple fields for the field', 'tainacan'),
                'on_error'   => __('Multiple fields is invalid', 'tainacan'),
                'validation' =>  v::stringType()->in(['yes', 'no']), // yes or no. It cant be multiple if its collection_key
                'default'    => 'no'
            ],
            'cardinality'    => [
                'map'        => 'meta',
                'title'      => __('Cardinality', 'tainacan'),
                'type'       => 'string/number',
                'description'=> __('Number of multiples possible fields', 'tainacan'),
                'on_error'   => __('The number of fields not allowed', 'tainacan'),
                'validation' => v::numeric()->positive(),
                'default'    => 1
            ],
            'privacy'        => [
                'map'        => 'meta',
                'title'      => __('Privacy', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The field should be omitted in item view', 'tainacan'),
                'on_error'   => __('Privacy is invalid', 'tainacan'),
                'validation' =>  v::stringType()->in(['yes', 'no']), // yes or no. It cant be multiple if its collection_key
                'default'    => 'no'
            ],
            'mask'           => [
                'map'        => 'meta',
                'title'      => __('Mask', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The mask to be used in the field', 'tainacan'),
                //'on_error'   => __('Mask is invalid', 'tainacan'),
                //'validation' => ''
            ],
            'default_value'  => [
                'map'        => 'meta',
                'title'      => __('Default value', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The value default fot the field', 'tainacan'),
            ],
            'field_type_options' => [ // not showed in form
                'map'        => 'meta',
                'title'      => __('Field Type options', 'tainacan'),
                'type'       => 'array',
                'description'=> __('Options specific for field type', 'tainacan'),
                'default'    => [],
               // 'validation' => ''
            ],
            'collection_id'  => [ // not showed in form
                'map'        => 'meta',
                'title'      => __('Collection', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('The collection ID', 'tainacan'),
                //'validation' => ''
            ],
            'accept_suggestion' => [
    			'map'		 => 'meta',
    			'title'		 => __('Field Value Accepts Suggestions', 'tainacan'),
    			'type'		 => 'bool',
    			'description'=> __('Allow the community suggest a different values for that field', 'tainacan'),
    			'default'	 => false,
    			'validation' => v::boolType()
    	    ],
            'can_delete'        => [
                'map'        => 'meta',
                'title'      => __('Can delete', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The field can be deleted', 'tainacan'),
                'on_error'   => __('Can delete is invalid', 'tainacan'),
                'validation' =>  v::stringType()->in(['yes', 'no']), // yes or no. It cant be multiple if its collection_key
                'default'    => 'yes'
            ],
        ]);
    }
	
	/**
	 * Get the labels for the custom post type of this repository
	 * @return array Labels in the format expected by register_post_type()
	 */
	public function get_cpt_labels() {
		return array(
			'name'               => __('Field', 'tainacan'),
            'singular_name'      => __('Field', 'tainacan'),
            'add_new'            => __('Add new', 'tainacan'),
            'add_new_item'       => __('Add new Field', 'tainacan'),
            'edit_item'          => __('Edit Field', 'tainacan'),
            'new_item'           => __('New Field', 'tainacan'),
            'view_item'          => __('View Field', 'tainacan'),
            'search_items'       => __('Search Field', 'tainacan'),
            'not_found'          => __('No Field found ', 'tainacan'),
            'not_found_in_trash' => __('No Field found in trash', 'tainacan'),
            'parent_item_colon'  => __('Parent Field:', 'tainacan'),
            'menu_name'          => __('Fields', 'tainacan')
        );
	}

    public function register_post_type() {
        $labels = $this->get_cpt_labels();
        $args = array(
            'labels'              => $labels,
            'hierarchical'        => true,
            //'supports'          => array('title'),
            //'taxonomies'        => array(self::TAXONOMY),
            'public'              => true,
            'show_ui'             => tnc_enable_dev_wp_interface(),
            'show_in_menu'        => tnc_enable_dev_wp_interface(),
            //'menu_position'     => 5,
            //'show_in_nav_menus' => false,
            'publicly_queryable'  => true,
            'exclude_from_search' => true,
            'has_archive'         => true,
            'query_var'           => true,
            'can_export'          => true,
            'rewrite'             => true,
        	'map_meta_cap'		  => true,
        	'capability_type'	  => Entities\Field::get_capability_type(),
            'supports'            => [
                'title',
                'editor',
                'page-attributes'
            ]
        );
        register_post_type(Entities\Field::get_post_type(), $args);
    }

    /**
     * constant used in default field in attribute collection_id
     *
     * @return string the value of constant
     */
    public function get_default_metadata_attribute(){
        return $this->default_metadata;
    }

    /**
     * register field types class on array of types
     *
     * @param $class_name string | object The class name or the instance
     */
    public function register_field_type( $class_name ){
        if( is_object( $class_name ) ){
            $class_name = get_class( $class_name );
        }

        if(!in_array( $class_name, $this->field_types)){
            $this->field_types[] = $class_name;
        }
    }

    /**
     * register field types class on array of types
     *
     * @param $class_name string | object The class name or the instance
     */
    public function unregister_field_type( $class_name ){
        if( is_object( $class_name ) ){
            $class_name = get_class( $class_name );
        }

        $key = array_search( $class_name, $this->field_types );
        if($key !== false){
            unset( $this->field_types[$key] );
        }
    }


	/**
	 * fetch field based on ID or WP_Query args
	 *
	 * field are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 *
	 * @param array $args WP_Query args || int $args the field id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return Entities\Field|\WP_Query|Array an instance of wp query OR array of entities;
	 * @throws \Exception
	 */
    public function fetch( $args, $output = null ) {

        if( is_numeric($args) ){
            $existing_post = get_post($args);
            if ($existing_post instanceof \WP_Post) {
                return new Entities\Field($existing_post);
            } else {
                return [];
            }
        } elseif (is_array($args)) {

            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ], $args);
			
			$args = $this->parse_fetch_args($args);
			
            $args['post_type'] = Entities\Field::get_post_type();

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }

	/**
	 * fetch field by collection, searches all field available
	 *
	 * @param Entities\Collection $collection
	 * @param array $args WP_Query args plus disabled_fields
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return array Entities\Field
	 * @throws \Exception
	 */
    public function fetch_by_collection(Entities\Collection $collection, $args = [], $output = null){
        $collection_id = $collection->get_id();

        //get parent collections
        $parents = get_post_ancestors( $collection_id );

        //insert the actual collection
        $parents[] = $collection_id;

        //search for default field
        $parents[] = $this->get_default_metadata_attribute();

        $meta_query = array(
            'key'     => 'collection_id',
            'value'   => $parents,
            'compare' => 'IN',
        );

        if( isset( $args['meta_query'] ) ){
            $args['meta_query'][] = $meta_query;
        } elseif(is_array($args)){
            $args['meta_query'] = array( $meta_query );
        }

        return $this->order_result(
            $this->fetch( $args, $output ),
            $collection,
            isset( $args['include_disabled'] ) ? $args['include_disabled'] : false
        );
    }

    /**
     * Ordinate the result from fetch response if $collection has an ordination,
     * fields not ordinated appear on the end of the list
     *
     *
     * @param $result Response from method fetch
     * @param Entities\Collection $collection
     * @param bool $include_disabled Wether to include disabled fields in the results or not
     * @return array or WP_Query ordinate
     */
    public function order_result( $result, Entities\Collection $collection, $include_disabled = false ){
        $order = $collection->get_fields_order();
        if($order) {
            $order = ( is_array($order) ) ? $order : unserialize($order);

            if ( is_array($result)  ){
                $result_ordinate = [];
                $not_ordinate = [];

                foreach ( $result as $item ) {
                    $id = $item->WP_Post->ID;
                    $index = array_search ( $id , array_column( $order , 'id') );

                    if( $index !== false ) {

                        // skipping fields disabled if the arg is set
                        if( !$include_disabled && isset( $order[$index]['enable'] ) && !$order[$index]['enable'] ) {
						   continue;
					   } elseif ($include_disabled && isset( $order[$index]['enable'] ) && !$order[$index]['enable']) {
						   $item->set_disabled_for_collection(true);
					   }

                        $result_ordinate[$index] = $item;
                    } else {
                        $not_ordinate[] = $item;
                    }
                }

                ksort ( $result_ordinate );
                $result_ordinate = array_merge( $result_ordinate, $not_ordinate );

                return $result_ordinate;
            }
            // if the result is a wp query object
            else {
                $posts = $result->posts;
                $result_ordinate = [];
                $not_ordinate = [];

                foreach ( $posts as $item ) {
                    $id = $item->ID;
                    $index = array_search ( $id ,  array_column( $order , 'id') );

                    if( $index !== false ){
                        $result_ordinate[$index] = $item;
                    } else {
                        $not_ordinate[] = $item;
                    }
                }

                ksort ( $result_ordinate );
                $result->posts = $result_ordinate;
                $result->posts = array_merge( $result->posts, $not_ordinate );

                return $result;
            }
        }
        return $result;
    }
	
	/**
     * @param \Tainacan\Entities\Field $field
     * @return \Tainacan\Entities\Field
     * {@inheritDoc}
     * @see \Tainacan\Repositories\Repository::insert()
     */
    public function insert($field){
        global $Tainacan_Fields;

    	$this->pre_update_category_field($field);
        $new_field = parent::insert($field);

        $this->update_category_field($new_field);
    	return $new_field;
    }

	/**
	 * @param $object
	 * @param $new_values
	 *
	 * @return mixed|string|Entities\Entity
	 * @throws \Exception
	 */
	public function update($object, $new_values = null){
		return $this->insert($object);
    }

    public function delete($field_id){
		$this->delete_category_field($field_id);
		return new Entities\Field( wp_trash_post( $field_id ) );
    }

    /**
     * fetch all registered field type classes
     *
     * Possible outputs are:
     * CLASS (default) - returns the Class name of of field types registered
     * NAME - return an Array of the names of field types registered
     *
     * @param $output string CLASS | NAME
     * @return array of Entities\Field_Types\Field_Type classes path name
     */
    public function fetch_field_types( $output = 'CLASS'){
        $return = [];

        do_action('register_field_types');

        if( $output === 'NAME' ){
            foreach ($this->field_types as $field_type) {
                $return[] = str_replace('Tainacan\Field_Types\\','', $field_type);
            }

            return $return;
        }

        return $this->field_types;
    }

	/**
	 * @param Entities\Collection $collection
	 *
	 * @return bool
	 * @throws \ErrorException
	 * @throws \Exception
	 */
    public function register_core_fields( Entities\Collection $collection ){

        $fields = $this->get_core_fields( $collection );

        // TODO: create a better way to retrieve this data
        $data_core_fields = [
            'core_description' => [
                'name' => 'Description',
                'description' => 'description',
                'collection_id' => $collection->get_id(),
                'field_type' => 'Tainacan\Field_Types\Core_Description',
                'status'     => 'publish'
            ],
            'core_title' => [
                'name' => 'Title',
                'description' => 'title',
                'collection_id' => $collection->get_id(),
                'field_type' => 'Tainacan\Field_Types\Core_Title',
                'status'     => 'publish'
            ]
        ];

        if( $collection->get_parent() !== 0 ){
            return false;
        }

        foreach ( $data_core_fields as $index => $data_core_field ) {
            if( empty( $fields ) ){
                $this->insert_array_field( $data_core_field );
            } else {
                $exists = false;
                foreach ( $fields as $field ){
                    if ( $field->get_field_type() === $data_core_field['field_type'] ) {
                        $exists = true;
                    }
                }

                if( !$exists ){
                    $this->insert_array_field( $data_core_field );
                }
            }
        }
    }

	/**
	 * block user from remove core fields
	 *
	 * @param $before  wordpress pass a null value
	 * @param $post the post which is moving to trash
	 *
	 * @return null/bool
	 * @throws \Exception
	 */
    public function disable_delete_core_fields( $before, $post ){
        $field = $this->fetch( $post->ID );

        if ( $field && in_array( $field->get_field_type(), $this->core_fields ) &&  is_numeric($field->get_collection_id()) ) {
            return false;
        }
    }

	/**
	 * block user from remove core fields ( if use wp_delete_post)
	 *
	 * @param $before  wordpress pass a null value
	 * @param $post the post which is deleting
	 * @param $force_delete a boolean that force the deleting
	 *
	 * @return null /bool
	 * @throws \Exception
	 * @internal param The $post_id post ID which is deleting
	 */
    public function force_delete_core_fields( $before, $post, $force_delete ){
        $field = $this->fetch( $post->ID );

        if ( $field && in_array( $field->get_field_type(), $this->core_fields ) &&  is_numeric($field->get_collection_id()) ) {
            return false;
        }
    }

	/**
	 * returns all core items from a specific collection
	 *
	 * @param Entities\Collection $collection
	 *
	 * @return Array|\WP_Query
	 * @throws \Exception
	 */
    public function get_core_fields( Entities\Collection $collection ){
        $args = [];

        $meta_query = array(
            array(
                'key'     => 'collection_id',
                'value'   => $collection->get_id(),
                'compare' => 'IN',
            ),
            array(
                'key'     => 'field_type',
                'value'   => $this->core_fields,
                'compare' => 'IN',
            )
        );

        $args['meta_query'] = $meta_query;

        return $this->fetch( $args, 'OBJECT' );
    }

	/**
	 * create a field entity and insert by an associative array ( attribute => value )
	 *
	 * @param Array $data the array of attributes to insert a field
	 *
	 * @return int the field id inserted
	 * @throws \ErrorException
	 * @throws \Exception
	 */
    public function insert_array_field( $data ){
        $field = new Entities\Field();
        foreach ( $data as $attribute => $value ) {
            $set_ = 'set_' . $attribute;
            $field->$set_( $value );
        }

        if ( $field->validate( )) {
            $field = $this->insert( $field );
            return $field->get_id();
        } else {
            throw new \ErrorException('The entity wasn\'t validated.' . print_r( $field->get_errors(), true));
        }
    }

	/**
	 * Fetch all values of a field from a collection in all it collection items
	 *
	 * @param $collection_id
	 * @param $field_id
	 *
	 * @return array|null|object
	 */
	public function fetch_all_field_values($collection_id, $field_id){
		global $wpdb;

		// Clear the result cache
		$wpdb->flush();

		$item_post_type = "%%{$collection_id}_item";

		$collection = new Entities\Collection($collection_id);
		$capabilities = $collection->get_capabilities();

		$results = [];

		// If no has logged user or actual user can not read private posts
		if(get_current_user_id() === 0 || !current_user_can( $capabilities->read_private_posts)) {
			$args = [
				'exclude_from_search' => false,
				'public'              => true,
				'private'             => false,
				'internal'            => false,
			];

			$post_statuses = get_post_stati( $args, 'names', 'and' );

			foreach ($post_statuses as $post_status) {
				$sql_string = $wpdb->prepare(
					"SELECT item_id, field_id, mvalue 
				  		FROM (
			  				SELECT ID as item_id
		  					FROM $wpdb->posts
	  						WHERE post_type LIKE %s AND post_status = %s
  						) items
						JOIN (
						  	SELECT meta_key as field_id, meta_value as mvalue, post_id
					  	  	FROM $wpdb->postmeta
				  		) metas
			  			ON items.item_id = metas.post_id AND metas.field_id = %d",
					$item_post_type, $post_status, $field_id
				);

				$pre_result = $wpdb->get_results( $sql_string, ARRAY_A );

				if (!empty($pre_result)) {
					$results[] = $pre_result[0];
				}
			}
		} elseif ( current_user_can( $capabilities->read_private_posts) ) {
			$args = [
				'exclude_from_search' => false,
			];

			$post_statuses = get_post_stati( $args, 'names', 'and' );

			foreach ($post_statuses as $post_status) {
				$sql_string = $wpdb->prepare(
					"SELECT item_id, field_id, mvalue 
		  	        	FROM (
	  	  		        	SELECT ID as item_id
  	  			        	FROM $wpdb->posts
  				        	WHERE post_type LIKE %s AND post_status = %s
					  	) items
					  	JOIN (
					    	SELECT meta_key as field_id, meta_value as mvalue, post_id
							FROM $wpdb->postmeta
					  	) metas
					  	ON items.item_id = metas.post_id AND metas.field_id = %d",
					$item_post_type, $post_status, $field_id
				);

				$pre_result = $wpdb->get_results( $sql_string, ARRAY_A );

				if (!empty($pre_result)) {
					$results[] = $pre_result[0];
				}
			}
		}

		return $results;
	}
	
	/**
	 * Stores the value of the taxonomy_id option to use on update_category_field method.
	 *
	 */
	private function pre_update_category_field($field) {
		$field_type = $field->get_field_type_object();
		$current_tax = '';
		if ($field_type->get_primitive_type() == 'term') {
			
			$options = $this->get_mapped_property($field, 'field_type_options');
			$field_type->set_options($options);
			$current_tax = $field_type->get_option('taxonomy_id');
		}
		$this->current_taxonomy = $current_tax;
	}
	
	/**
	 * Triggers hooks when saving a Category Field, indicating wich taxonomy was added or removed from a collection.
	 *
	 * This is used by Taxonomies repository to update the collections_ids property of the taxonomy as
	 * a field type category is inserted or removed
	 * 
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 */
	private function update_category_field($field) {
		$field_type = $field->get_field_type_object();
		$new_tax = '';
		
		if ($field_type->get_primitive_type() == 'term') {
			$new_tax = $field_type->get_option('taxonomy_id');
		}
		
		if ($new_tax != $this->current_taxonomy) {
			if (!empty($this->current_taxonomy)) {
				do_action('tainacan-taxonomy-removed-from-collection', $this->current_taxonomy, $field->get_collection());
			}
			if (!empty($new_tax)) {
				do_action('tainacan-taxonomy-added-to-collection', $new_tax, $field->get_collection());
			}
				
		}
	}
	
	private function delete_category_field($field_id) {
		$field = $this->fetch($field_id);
		$field_type = $field->get_field_type_object();
		if ($field_type->get_primitive_type() == 'term') {
			$removed_tax = $field_type->get_option('taxonomy_id');
			if (!empty($removed_tax))
				do_action('tainacan-taxonomy-removed-from-collection', $removed_tax, $field->get_collection());
		}
	}
}
