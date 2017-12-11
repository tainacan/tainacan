<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
class Filters extends Repository {
	public $entities_type = '\Tainacan\Entities\Filter';
	
    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(),  [
            'name'               => [
                'map'        => 'post_title',
                'title'       => __('Name', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Name of the filter', 'tainacan'),
                'on_error'   => __('The filter name should be a text value and not empty', 'tainacan'),
                'validation' => v::stringType()->notEmpty(),
            ],
            'order'              => [
                'map'        => 'menu_order',
                'title'       => __('Order', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Filter order. Field used if filters are manually ordered', 'tainacan'),
                'validation' => ''
            ],
            'description'        => [
                'map'        => 'post_content',
                'title'      => __('Description', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The filter description', 'tainacan'),
                'validation'  => ''
            ],
            'filter_type_options' => [
                'map'        => 'meta',
                'title'      => __('Filter type options', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The filter type options', 'tainacan'),
                'validation' => ''
            ],
            'filter_type'        => [
                'map' => 'meta',
                'title'      => __('Type', 'tainacan'),
                'type'       => 'string',
                'description'=> __('The filter type', 'tainacan'),
                'validation' => ''
            ],
            'collection_id'      => [
                'map'        => 'meta',
                'title'      => __('Collection', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('The collection ID', 'tainacan'),
                'validation' => ''
            ],
            'color'              => [
                'map'        => 'meta',
                'title'      => __('Color', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('Filter color', 'tainacan'),
                'validation' => ''
            ],
            'metadata'           => [
                'map'        => 'meta',
                'title'      => __('Metadata', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('Filter metadata', 'tainacan'),
                'validation' => ''
            ],
        ]);
    }

    public function register_post_type(){
        $labels = array(
            'name'               => __('Filters', 'tainacan'),
            'singular_name'      => __('Filter', 'tainacan'),
            'add_new'            => __('Add new', 'tainacan'),
            'add_new_item'       => __('Add new Filter', 'tainacan'),
            'edit_item'          => __('Edit Filter', 'tainacan'),
            'new_item'           => __('New Filter', 'tainacan'),
            'view_item'          => __('View Filter', 'tainacan'),
            'search_items'       => __('Search Filters', 'tainacan'),
            'not_found'          => __('No Filters found ', 'tainacan'),
            'not_found_in_trash' => __('No Filters found in trash', 'tainacan'),
            'parent_item_colon'  => __('Parent Filter:', 'tainacan'),
            'menu_name'          => __('Filters', 'tainacan')
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
        register_post_type(Entities\Filter::get_post_type(), $args);
    }


    /**
     * @param Entities\Metadata $metadata
     * @return int
     *
    public function insert($metadata) {
        // First iterate through the native post properties
        $map = $this->get_map();
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $metadata->WP_Post->{$mapped['map']} = $metadata->get_mapped_property($prop);
            }
        }

        // save post and get its ID
        $metadata->WP_Post->post_type = Entities\Filter::get_post_type();
        $metadata->WP_Post->post_status = 'publish';
        $id = wp_insert_post($metadata->WP_Post);
        $metadata->WP_Post = get_post($id);

        // Now run through properties stored as postmeta
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $metadata->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $metadata->get_mapped_property($prop);
                
                delete_post_meta($id, $prop);
                
                if (is_array($values)){
                    foreach ($values as $value){
                        add_post_meta($id, $prop, $value);
                    }
                }
            }
        }

        // return a brand new object
        return new Entities\Filter($metadata->WP_Post);
    }*/

    public function delete($object){

    }

    public function update($object){

    }

    /**
     * fetch filter based on ID or WP_Query args
     *
     * Filters are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
     * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
     * appropriate WP_Query argument
     *
     * @param array $args WP_Query args || int $args the filter id
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return \WP_Query|Array an instance of wp query OR array of entities;
     */
    public function fetch($args = [], $output = null){
        if( is_numeric($args) ){
            return new Entities\Filter($args);
        } elseif (is_array($args)) {
            // TODO: get filters from parent collections
            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish'
            ], $args);
            
            $args = $this->parse_fetch_args($args);
            
            $args['post_type'] = Entities\Filter::get_post_type();

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }

    /**
     * fetch all declared filter type classes
     *
     * @return Array of Entities\Filter_Types\Filter_Type objects
     */
    public function fetch_filter_types(){
        $filters = array();

        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, '\Tainacan\Filter_Types\Filter_Type')){
                $filters[] = new $class();
            }
        }

        return $filters;
    }

    /**
     * fetch only supported filters for the type specified
     *
     * @param ( string || array )  $types Primitve types of metadata ( float, string, int)
     * @return array Filters supported by the primitive types passed in $types
     */
    public function fetch_supported_filter_types($types){
        $filter_types = $this->fetch_filter_types();
        $supported_filter_types = [];

        foreach ( $filter_types as $filter_type){
            $filter = new $filter_type();

            if( ( is_array( $types ) )){
                foreach ( $types as $single_type ) {
                    if( in_array( $single_type ,$filter->get_supported_types() )){
                          $supported_filter_types[] = $filter;
                    }
                }
            }else if( in_array( $types ,$filter->get_supported_types() )){
                $supported_filter_types[] = $filter;
            }
        }

        return $supported_filter_types;
    }
}