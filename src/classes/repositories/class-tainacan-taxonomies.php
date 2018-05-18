<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


use \Respect\Validation\Validator as v;
/**
 * Class Tainacan_Taxonomies
 */
class Taxonomies extends Repository {
	public $entities_type = '\Tainacan\Entities\Taxonomy';

    private static $instance = null;

    public static function get_instance()
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

	protected function __construct() {
		parent::__construct();
 		add_action('tainacan-taxonomy-removed-from-collection', array($this, 'removed_collection'), 10, 2);
 		add_action('tainacan-taxonomy-added-to-collection', array($this, 'added_collection'), 10, 2);
	}
	
    public function get_map() {
    	return apply_filters('tainacan-get-map-'.$this->get_name(), [
            'name'            =>  [
                'map'         => 'post_title',
                'title'       => __('Name', 'tainacan'),
                'type'        => 'string',
                'description' => __('Name of the taxonomy', 'tainacan'),
                'on_error'    => __('The taxonomy name should be a text value and should not be empty.', 'tainacan'),
                'validation'  => v::stringType()->notEmpty(),
            ],
            'description'     =>  [
                'map'         => 'post_content',
                'title'       => __('Description', 'tainacan'),
                'type'        => 'string',
                'description' => __('The taxonomy description', 'tainacan'),
            	'default'	 => '',
                'validation'  => ''
            ],
            'slug'            =>  [
                'map'         => 'post_name',
                'title'       => __('Slug', 'tainacan'),
                'type'        => 'string',
                'description' => __('The taxonomy slug', 'tainacan'),
                'validation'  => ''
            ],
            'allow_insert'    =>  [
                'map'         => 'meta',
                'title'       => __('Allow insert', 'tainacan'),
                'type'        => 'string',
                'description' => __('Allow/Deny the creation of new terms in the taxonomy', 'tainacan'),
                'on_error'    => __('Invalid insertion, allowed values are ( yes/no )', 'tainacan'),
                'validation'  => v::stringType()->in(['yes', 'no']), // yes or no
                'default'     => 'yes'
            ],
        	'collections_ids' =>  [
        		'map'         => 'meta_multi',
                'title'       => __('Collections', 'tainacan'),
                'type'        => 'string',
                'description' => __('The IDs of collection where the taxonomy is used', 'tainacan'),
        		'validation'  => ''
        	],
        ]);
    }
	
	/**
	 * Get the labels for the custom post type of this repository
	 * @return array Labels in the format expected by register_post_type()
	 */
	public function get_cpt_labels() {
		return array(
			'name'               => __('Taxonomies', 'tainacan'),
            'singular_name'      => __('Taxonomy', 'tainacan'),
            'add_new'            => __('Add new', 'tainacan'),
            'add_new_item'       => __('Add new Taxonomy', 'tainacan'),
            'edit_item'          => __('Edit Taxonomy', 'tainacan'),
            'new_item'           => __('New Taxonomy', 'tainacan'),
            'view_item'          => __('View Taxonomy', 'tainacan'),
            'search_items'       => __('Search Taxonomies', 'tainacan'),
            'not_found'          => __('No Taxonomies found ', 'tainacan'),
            'not_found_in_trash' => __('No Taxonomies found in trash', 'tainacan'),
            'parent_item_colon'  => __('Parent Taxonomy:', 'tainacan'),
            'menu_name'          => __('Taxonomies', 'tainacan')
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
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'query_var'           => true,
            'can_export'          => true,
            'rewrite'             => true,
        	'map_meta_cap'		  => true,
        	'capability_type'	  => Entities\Taxonomy::get_capability_type(),
            'supports'            => [
                'title',
                'editor',
                'page-attributes'
            ]
        );
        register_post_type(Entities\Taxonomy::get_post_type(), $args);
    }

	/**
	 * @param Entities\Taxonomy $taxonomy
	 *
	 * @return Entities\Entity
	 */
    public function insert($taxonomy) {

    	$new_taxonomy = parent::insert($taxonomy);
        $new_taxonomy->register_taxonomy();
        
        // return a brand new object
        return $new_taxonomy;
    }

    /**
     * fetch taxonomies based on ID or WP_Query args
     *
     * Taxonomies are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
     * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
     * appropriate WP_Query argument
     *
     * @param array $args WP_Query args | int $args the taxonomy id
     * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
     * @return \WP_Query|Array an instance of wp query OR array of entities;
     */
    public function fetch( $args = [], $output = null ) {
        
        // TODO: Pegar taxonomias registradas via código
        
        if( is_numeric($args) ){
            $existing_post = get_post($args);
            if ($existing_post instanceof \WP_Post) {
                return new Entities\Taxonomy($existing_post);
            } else {
                return [];
            }
        } elseif (is_array($args)) {

            $args = array_merge([
                'posts_per_page' => -1,
            ], $args);

            $args = $this->parse_fetch_args($args);

            $args['post_type'] = Entities\Taxonomy::get_post_type();

            $wp_query = new \WP_Query($args);
            return $this->fetch_output($wp_query, $output);
        }
    }

    public function update($object, $new_values = null){
    	return $this->insert($object);
    }

    public function delete($args){
	    $taxonomy_id    = $args[0];
	    $taxonomy_name  = $args[1];
	    $permanently    = $args[2];

	    if($permanently === true){
		    $unregistered = unregister_taxonomy($taxonomy_name);

		    if($unregistered instanceof \WP_Error){
			    return $unregistered;
		    }

    		$deleted = new Entities\Taxonomy(wp_delete_post($taxonomy_id, true));

    		if(!$deleted){
    			return $deleted;
		    }

		    do_action('tainacan-deleted', $deleted, [], false, true);

    		return $deleted;
	    }

	    $trashed = new Entities\Taxonomy(wp_trash_post($taxonomy_id));

    	if(!$trashed){
    		return $trashed;
	    }

	    do_action('tainacan-trashed',  $trashed, [], false, false, true );

    	return $trashed;
    }
	
	
	public function added_collection($taxonomy_id, $collection) {
		$id = $taxonomy_id;
		if (!empty($id) && is_numeric($id)) {
			$tax = $this->fetch((int) $id);
			$tax->add_collection_id($collection->get_id());
			if ($tax->validate()) {
				$this->insert($tax);
			}
		}
	}
	
	public function removed_collection($taxonomy_id, $collection) {
        $id = $taxonomy_id;
		if (!empty($id) && is_numeric($id)) {
			$tax = $this->fetch((int) $id);
			$tax->remove_collection_id($collection->get_id());
			if ($tax->validate()) {
				$this->insert($tax);
			}
		}
	}
	
	
}