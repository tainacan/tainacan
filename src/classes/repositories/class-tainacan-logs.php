<?php
namespace Tainacan\Repositories;
use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Implement a Logs system
 *  
 * @author medialab
 *
 */
class Logs extends Repository {
	protected $entities_type = '\Tainacan\Entities\Log';
	
	public function __construct() {
		parent::__construct();
		add_action('tainacan-insert', array($this, 'log_inserts'));
	}
	
    public function get_map() {
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
        	'value'        => [
        		'map'        => 'meta',
        		'validation' => ''
        	],
        	'old_value'        => [
        		'map'        => 'meta',
        		'validation' => ''
        	],
        ];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Tainacan\Repositories\Repository::register_post_type()
     */
    public function register_post_type() {
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
        register_post_type(Entities\Log::get_post_type(), $args);
    }
    
    public function fetch($object = []){
        if(is_numeric($object)){
    	    return new Entities\Log($object);
        } else {
            $args = array_merge([
                'post_type'      => Entities\Log::get_post_type(),
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
    
    public function fetch_last() {
    	$args = [
    		'post_type'      => Entities\Log::get_post_type(),
    		'posts_per_page' => 1,
    		'post_status'    => 'publish',
    	];
    	
    	$posts = get_posts($args);
    	
    	foreach ($posts as $post) {
    		$log = new Entities\Log($post);
    	}
    	// TODO: Pegar coleções registradas via código
    	return $log;
    }
    
    public function log_inserts($new_value, $value = null)
    {
    	$msn = "";
   		if(is_object($new_value))
   		{
   			// do not log a log
   			if(method_exists($new_value, 'get_post_type') && $new_value->get_post_type() == 'tainacan-logs') return;
   			
   			$type = get_class($new_value);
   			$msn = sprintf( esc_html__( 'a %s has been created/modified.', 'tainacan' ), $type );
   		}
   		$msn = apply_filters('tainacan-insert-log-message-title', $msn, $type, $new_value);
    	Entities\Log::create($msn, '', $new_value, $value);
    }
}