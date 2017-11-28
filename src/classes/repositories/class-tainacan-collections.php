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
                //'validation' => v::numeric(),
            ],
            'name'           =>  [
                'map'        => 'post_title',
                'validation' => v::stringType(),
            ],
            'order'          =>  [
                'map'        => 'menu_order',
                //'validation' => v::stringType(),
            ],
            'parent'         =>  [
                'map'        => 'parent',
                //'validation' => v::stringType(),
            ],
            'description'    =>  [
                'map'        => 'post_content',
                //'validation' => v::stringType(),
            ],
            'slug'           =>  [
                'map'        => 'post_name',
                //'validation' => v::stringType(),
            ],
            'itens_per_page' =>  [
                'map'        => 'meta',
                'default'    => 10,
                'validation' => v::intVal()->positive(),
            ],
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
     * Obtém um coleção específica pelo ID ou várias coleções
     *
     * @param array $object || int $object
     * @return Array || Collection 
     */
    public function fetch($object = []){
        if(is_numeric($object)){
            return new Entities\Collection($object);
        } elseif(is_array($object)) {
            $args = array_merge([
                'post_type'      => Entities\Collection::get_post_type(),
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ], $object);
            
            $posts = get_posts($args);
            
            $collections = [];
            foreach ($posts as $post) {
                $collections[] = new Entities\Collection($post);
            }
            
            // TODO: Pegar coleções registradas via código
            
            return $collections;
        }
    }
}