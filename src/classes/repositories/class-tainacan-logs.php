<?php
namespace Tainacan\Repositories;
use Tainacan\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implement a Logs system
 *  
 * @author medialab
 *
 */
class Logs implements Repository {
    
    function __construct() {
        add_action('init', array(&$this, 'register_post_type'));
    }
    
    function get_map() {
        return [
            'id'             => [
                'map'        => 'ID',
                //'validation' => ''
            ],
            'title'          =>  [
                'map'        => 'post_title',
                'validation' => ''
            ],
            'order'          =>  [
                'map'        => 'menu_order',
                'validation' => ''
            ],
            'parent'         =>  [
                'map'        => 'parent',
                'validation' => ''
            ],
            'description'    =>  [
                'map'        => 'post_content',
                'validation' => ''
            ],
            'slug'           =>  [
                'map'        => 'post_name',
                'validation' => ''
            ],
            'itens_per_page' =>  [
                'map'        => 'meta',
                'validation' => ''
            ],
        	'user_id'        => [
        		'map'        => 'post_author',
        		'validation' => ''
        	],
        	'blog_id'        => [
        		'map'        => 'meta',
        		'validation' => ''
        	],
        ];
    }
    
    function register_post_type() {
        $labels = array(
            'name'               => 'logs',
            'singular_name'      => 'logs',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       => 'Adicionar Log',
            'edit_item'          => 'Editar',
            'new_item'           => 'Novo Log',
            'view_item'          => 'Visualizar',
            'search_items'       => 'Pesquisar',
            'not_found'          => 'Nenhum log encontrado',
            'not_found_in_trash' => 'Nenhum log encontrado na lixeira',
            'parent_item_colon'  => 'Log aterior:',
            'menu_name'          => 'Logs'
        );
        $args = array(
            'labels'              => $labels,
            'hierarchical'        => true,
            //'supports'          => array('title'),
            //'taxonomies'        => array(self::TAXONOMY),
            'public'              => false,
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
            'capability_type'     => 'post',
        );
        register_post_type(Entities\Log::POST_TYPE, $args);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Tainacan\Repositories\Repository::insert()
     * 
     * @param \Tainacan\Entities\Log $log
     * 
     */
    function insert($log) {
        // First iterate through the native post properties
        $map = $this->get_map();
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
                $log->WP_Post->{$mapped['map']} = $log->get_mapped_property($prop);
            }
        }
        
        // save post and geet its ID
        $log->WP_Post->post_type = Entities\Log::POST_TYPE;
        $log->WP_Post->post_status = 'publish';
        
        // TODO verificar se salvou mesmo
        $id = wp_insert_post($log->WP_Post);
        $log->WP_Post = get_post($id);
        
        /* Now run through properties stored as postmeta TODO maybe a parent class function leave for future use */
        foreach ($map as $prop => $mapped) {
            if ($mapped['map'] == 'meta') {
                update_post_meta($id, $prop, $log->get_mapped_property($prop));
            } elseif ($mapped['map'] == 'meta_multi') {
                $values = $log->get_mapped_property($prop);
                delete_post_meta($id, $prop);
                if (is_array($values))
                    foreach ($values as $value)
                        add_post_meta($id, $prop, $value);
            }
        }
        
        // return a brand new object
        return new Entities\Log($log->WP_Post);
    }
    
    public function fetch($object = []){
        if(is_numeric($object)){
    	    return new Entities\Log($object);
        } else {
            $args = array_merge([
                'post_type'      => Entities\Log::POST_TYPE,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
            ], $object);
            
            $posts = get_posts($args);
            
            $logs = [];
            
            foreach ($posts as $post) {
                $logs[] = new Entities\Log($post);
            }
            // TODO: Pegar coleções registradas via código
            return $logs;
        }
    }

    public function delete($object){

    }

    public function update($object){

    }
}