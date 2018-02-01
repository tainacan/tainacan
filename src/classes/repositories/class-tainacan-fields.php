<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
/**
 * Class Fields
 */
class Fields extends Repository {
	public $entities_type = '\Tainacan\Entities\Field';
	protected $default_metadata = 'default';

	public $field_types = [];
	
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
                'type'       => 'string',
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
                'type'       => 'string',
                'description'=> __('Options specific for field type', 'tainacan'),
               // 'validation' => ''
            ],
            'collection_id'  => [ // not showed in form
                'map'        => 'meta',
                'title'      => __('Collection', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('The collection ID', 'tainacan'),
                //'validation' => ''
            ],
        ]);
    }

    public function register_post_type() {
        $labels = array(
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
        	'capability_type'	  => Entities\Field::get_post_type(),
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
	 * @return \WP_Query|Array an instance of wp query OR array of entities;
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

            $args['post_type'] = Entities\Field::get_post_type();

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }

	/**
	 * fetch field by collection, searches all field available
	 *
	 * @param Entities\Collection $collection
	 * @param array $args
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return Array Entities\Field
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
        }else{
            $args['meta_query'] = array( $meta_query );
        }

        return $this->fetch( $args, $output );
    }

	/**
	 * @param $object
	 * @param $new_values
	 *
	 * @return mixed|string|Entities\Entity
	 * @throws \Exception
	 */
	public function update($object, $new_values = null){
	    foreach ($new_values as $key => $value) {
		    try {
			    $set_ = 'set_' . $key;
			    $object->$set_( $value );
		    } catch (\Error $error){
		    	return $error->getMessage();
		    }
	    }

	    if($object->validate()){
	    	return $this->insert($object);
	    }

	    return $object->get_errors();
    }

    public function delete($object){

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
}