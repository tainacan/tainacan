<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
use Tainacan\Entities\Collection;

class Collections extends Repository {
	public $entities_type = '\Tainacan\Entities\Collection';
    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(), [
            'name'           =>  [
                'map'        => 'post_title',
                'title'       => __('Name', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Name of the collection', 'tainacan'),
                'validation' => v::stringType(),
            ],
            'order'          =>  [
                'map'        => 'menu_order',
                'title'       => __('Order', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Collection order. Field used if collections are manually ordered', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'parent'         =>  [
                'map'        => 'post_parent',
                'title'       => __('Parent Collection', 'tainacan'),
                'type'       => 'integer',
                'description'=> __('Parent collection ID', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'description'    =>  [
                'map'        => 'post_content',
                'title'       => __('Description', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Collection description', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'slug'           =>  [
                'map'        => 'post_name',
                'title'       => __('Slug', 'tainacan'),
                'type'       => 'string',
                'description'=> __('A unique and santized string representation of the collection, used to build the collection URL', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            
            'default_orderby'           =>  [
                'map'        => 'meta',
                'title'       => __('Default Order field', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Default property items in this collections will be ordered by', 'tainacan'),
                'default'    => 'name',
                //'validation' => v::stringType(),
            ],
            'default_order'           =>  [
                'map'        => 'meta',
                'title'       => __('Default order', 'tainacan'),
                'description'=> __('Default order for items in this collection. ASC or DESC', 'tainacan'),
                'type'       => 'string',
                'default'    => 'ASC',
                'validation' => v::stringType()->in(['ASC', 'DESC']),
            ],
            'columns'           =>  [
                'map'        => 'meta',
                'title'       => __('Columns', 'tainacan'),
                'type'       => 'string',
                'description'=> __('List of collections property that will be displayed in the table view', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'default_view_mode'           =>  [
                'map'        => 'meta',
                'title'       => __('Default view mode', 'tainacan'),
                'type'       => 'string',
                'description'=> __('Collection default visualization mode', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            /*
            
            Isnt it just post status private?
            
            'privacy'           =>  [
                'map'        => 'meta',
                'name'       => __('Privacy', 'tainacan'),
                'description'=> __('Collection privacy, defines wether a collection is visible to the public or not', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            */
    
            /**
             * Properties yet to be implemented
             *
             * Moderators (a property attached to the collection or to the user?)
             * geo metadata?
             *
             *
             * 
             */

        ]);
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Collections', 'tainacan'),
            'singular_name'      => __('Collection', 'tainacan'),
            'add_new'            => __('Add new', 'tainacan'),
            'add_new_item'       => __('Add new Collection', 'tainacan'),
            'edit_item'          => __('Edit Collection', 'tainacan'),
            'new_item'           => __('New Collection', 'tainacan'),
            'view_item'          => __('View Collection', 'tainacan'),
            'search_items'       => __('Search Collections', 'tainacan'),
            'not_found'          => __('No Collections found ', 'tainacan'),
            'not_found_in_trash' => __('No Collections found in trash', 'tainacan'),
            'parent_item_colon'  => __('Parent Collection:', 'tainacan'),
            'menu_name'          => __('Collections', 'tainacan')
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
                'thumbnail',
                'revisions',
                'page-attributes'
            ]
        );
        register_post_type(Entities\Collection::get_post_type(), $args);
    }
    
    /**
     * @param \Tainacan\Entities\Collection $collection
     * @return \Tainacan\Entities\Collection
     * {@inheritDoc}
     * @see \Tainacan\Repositories\Repository::insert()
     */
    public function insert($collection){
    	$new_collection = parent::insert($collection);
    	$collection->register_collection_item_post_type();
    	return $new_collection;
    }
    
    public function update($object){

    }

	/**
	 * @param $args ( is a array like [post_id, [is_permanently => bool]] )
	 *
	 * @return mixed|Collection
	 */
	public function delete($args){
	    if(!empty($args[1]) && $args[1]['is_permanently'] === true){
		    return new Entities\Collection(wp_delete_post($args[0], $args[1]['is_permanently']));
	    }

	    return new Entities\Collection(wp_trash_post($args[0]));
    }

    /**
     * fetch collection based on ID or WP_Query args
     *
     * Collections are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
     * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
     * appropriate WP_Query argument
     *
     * @param array $args WP_Query args || int $args the collection id
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return \WP_Query|Array an instance of wp query OR array of entities;
     */
    public function fetch($args = [], $output = null){
        if(is_numeric( $args )){
            return new Entities\Collection($args);
        } elseif(is_array($args)) {
            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ], $args);
            
            $args = $this->parse_fetch_args($args);
            
            $args['post_type'] = Entities\Collection::get_post_type();

            // TODO: Pegar coleções registradas via código

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }
    
    // TODO: Implement this method
    public function fetch_by_db_identifier($db_identifier) {
        
    }
}