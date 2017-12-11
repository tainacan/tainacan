<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
/**
 * Class Metadatas
 */
class Metadatas extends Repository {
	public $entities_type = '\Tainacan\Entities\Metadata';
	protected $default_metadata = 'default';

	public $field_types = [];
	
    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(), [
            'name'           => [
                'map'        => 'post_title',
                'title'       => __('Name', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Name of the metadata', 'tainacan'),
                'on_error'   => __('The name should be a text value and not empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'order'          => [
                'map'        => 'menu_order',
                'title'       => __('Order', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Metadata order. Field used if collections are manually ordered', 'tainacan'),
                'on_error'   => __('The menu order should be a numeric value', 'tainacan'),
                //'validation' => v::numeric(),
            ],
            'parent'         => [
                'map'        => 'parent',
                'title'      => __('Parent', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('Parent metadata', 'tainacan'),
                //'on_error'   => __('The Parent should be numeric value', 'tainacan'),
                //'validation' => v::numeric(),
            ],
            'description'    => [
                'map'        => 'post_content',
                'title'      => __('Description', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The metadata description', 'tainacan'),
                //'on_error'   => __('The description should be a text value', 'tainacan'),
                //'validation' => v::stringType()->notEmpty(),
            ],
            'field_type'     => [
                'map'        => 'meta',
                'title'      => __('Type', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The metadata type', 'tainacan'),
                'on_error'   => __('Metadata type is empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'required'       => [
                'map'        => 'meta',
                'title'      => __('Required', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The metadata is required', 'tainacan'),
                'on_error'   => __('Metadata required field is invalid', 'tainacan'),
                'validation' => v::stringType()->in(['yes', 'no']), // yes or no
                'default'    => 'no'
            ],
            'collection_key' => [
                'map'        => 'meta',
                'title'      => __('Collection key', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Metadata value should not be repeated', 'tainacan'),
                'on_error'   => __('Collection key is invalid', 'tainacan'),
                'validation' => v::stringType()->in(['yes', 'no']), // yes or no
                'default'    => 'no'
            ],
            'multiple'       => [
                'map'        => 'meta',
                'title'      => __('Multiple', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Allow multiple fields for the metadata', 'tainacan'),
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
                'description'=> __('The metadata should be omitted in item view', 'tainacan'),
                'on_error'   => __('Privacy is invalid', 'tainacan'),
                'validation' =>  v::stringType()->in(['yes', 'no']), // yes or no. It cant be multiple if its collection_key
                'default'    => 'no'
            ],
            'mask'           => [
                'map'        => 'meta',
                'title'      => __('Mask', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The mask to be used in the metadata', 'tainacan'),
                //'on_error'   => __('Mask is invalid', 'tainacan'),
                //'validation' => ''
            ],
            'default_value'  => [
                'map'        => 'meta',
                'title'      => __('Default value', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The value default fot the metadata', 'tainacan'),
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
            'name'               => __('Metadata', 'tainacan'),
            'singular_name'      => __('Metadata', 'tainacan'),
            'add_new'            => __('Add new', 'tainacan'),
            'add_new_item'       => __('Add new Metadata', 'tainacan'),
            'edit_item'          => __('Edit Metadata', 'tainacan'),
            'new_item'           => __('New Metadata', 'tainacan'),
            'view_item'          => __('View Metadata', 'tainacan'),
            'search_items'       => __('Search Metadata', 'tainacan'),
            'not_found'          => __('No Metadata found ', 'tainacan'),
            'not_found_in_trash' => __('No Metadata found in trash', 'tainacan'),
            'parent_item_colon'  => __('Parent Metadata:', 'tainacan'),
            'menu_name'          => __('Metadata', 'tainacan')
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
            'capability_type'     => 'post',
            'supports'            => [
                'title',
                'editor',
                'page-attributes'
            ]
        );
        register_post_type(Entities\Metadata::get_post_type(), $args);
    }

    /**
     * constant used in default metadata in attribute collection_id
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
     * fetch metadata based on ID or WP_Query args
     *
     * metadata are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
     * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
     * appropriate WP_Query argument
     *
     * @param array $args WP_Query args || int $args the metadata id
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return \WP_Query|Array an instance of wp query OR array of entities;
     */
    public function fetch( $args, $output = null ) {

        if( is_numeric($args) ){
            return new Entities\Metadata($args);
        } elseif (is_array($args)) {

            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ], $args);

            $args['post_type'] = Entities\Metadata::get_post_type();

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }

    /**
     * fetch metadata by collection, searches all metadata available
     *
     * @param Entities\Collection $collection
     * @param array $args
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return Array Entities\Metadata
     */
    public function fetch_by_collection(Entities\Collection $collection, $args = [], $output = null){
        $collection_id = $collection->get_id();

        //get parent collections
        $parents = get_post_ancestors( $collection_id );

        //insert the actual collection
        $parents[] = $collection_id;

        //search for default metadata
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

    public function update($object){

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