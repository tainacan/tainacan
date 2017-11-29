<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;
use Tainacan\Entities\Collection;

class Collections extends Repository {
	protected $entities_type = '\Tainacan\Entities\Collection';
    public function get_map() {
        return [
            'id'             => [
                'map'        => 'ID',
                'name'       => __('ID', 'tainacan'),
                'description'=> __('Unique identifier', 'tainacan'),
                //'validation' => v::numeric(),
            ],
            'name'           =>  [
                'map'        => 'post_title',
                'name'       => __('Name', 'tainacan'),
                'description'=> __('Name of the collection', 'tainacan'),
                'validation' => v::stringType(),
            ],
            'order'          =>  [
                'map'        => 'menu_order',
                'name'       => __('Order', 'tainacan'),
                'description'=> __('Collection order. Field used if collections are manually ordered', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'parent'         =>  [
                'map'        => 'parent',
                'name'       => __('Parent Collection', 'tainacan'),
                'description'=> __('Parent collection ID', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'description'    =>  [
                'map'        => 'post_content',
                'name'       => __('Description', 'tainacan'),
                'description'=> __('Collection description', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'slug'           =>  [
                'map'        => 'post_name',
                'name'       => __('Slug', 'tainacan'),
                'description'=> __('A unique and santized string representation of the collection, used to build the collection URL', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            
            'default_orderby'           =>  [
                'map'        => 'meta',
                'name'       => __('Default Order field', 'tainacan'),
                'description'=> __('Default property items in this collections will be ordered by', 'tainacan'),
                'default'    => 'name',
                //'validation' => v::stringType(),
            ],
            'default_order'           =>  [
                'map'        => 'meta',
                'name'       => __('Default order', 'tainacan'),
                'description'=> __('Default order for items in this collection. ASC or DESC', 'tainacan'),
                'default'    => 'ASC',
                'validation' => v::stringType()->in(['ASC', 'DESC']),
            ],
            'columns'           =>  [
                'map'        => 'meta',
                'name'       => __('Columns', 'tainacan'),
                'description'=> __('List of collections property that will be displayed in the table view', 'tainacan'),
                //'validation' => v::stringType(),
            ],
            'default_view_mode'           =>  [
                'map'        => 'meta',
                'name'       => __('Default view mode', 'tainacan'),
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

        ];
    }

    public function register_post_type() {
        $labels = array(
            'name'               => 'Collections',
            'singular_name'      => 'Collections',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       => 'Adicionar Collections',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Collections',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum ticket encontrado',
            'not_found_in_trash' => 'Nenhum Collections encontrado na lixeira',
            'parent_item_colon'  => 'Collections acima:',
            'menu_name'          => 'Collections'
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
        );
        register_post_type(Entities\Collection::get_post_type(), $args);
    }
    
    /**
     * @param Tainacan\Entities\Collection $collection
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

    public function delete($object){

    }

    /**
     * fetch collection based on ID or WP_Query args
     *
     * Collections are stored as posts. Check WP_Query docs
     * to learn all args accepted in the $args parameter
     *
     * @param array $args WP_Query args || int $args the collection id
     * @return \WP_Query an instance of wp query
     */
    public function fetch($args = []){
        if(is_numeric( $args )){
            return new Entities\Collection($args);
        } elseif(is_array($args)) {
            $args = array_merge([
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ], $args);

            $args['post_type'] = Entities\Collection::get_post_type();

            // TODO: Pegar coleções registradas via código

            return new \WP_Query($args);
        }
    }
}