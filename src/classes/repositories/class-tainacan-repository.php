<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;
use Tainacan\Entities\Entity;
use Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Repository {
	protected $entities_type = '\Tainacan\Entities\Entity';
	
	function __construct() {
		add_action('init', array(&$this, 'register_post_type'));
	}
	
	public function get_map() {
		return array();
	}
	
	/**
	 * 
	 * @param \Tainacan\Entities\Entity $obj
	 * @return \Tainacan\Entities\Entity
	 */
	public function insert($obj) {
		// validate
		if (!$obj->get_validated()){
			throw new \Exception('Entities must be validated before you can save them');
            // TODO: Throw Warning saying you must validate object before insert()
		}
		
		
		$map = $this->get_map();
		
		// First iterate through the native post properties
		foreach ($map as $prop => $mapped) {
			if ($mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi') {
				$obj->WP_Post->{$mapped['map']} = $obj->get_mapped_property($prop);
			}
		}
		$obj->WP_Post->post_type = $obj::get_post_type();
		$obj->WP_Post->post_status = 'publish';
		
		// TODO verificar se salvou mesmo
		$id = wp_insert_post($obj->WP_Post);
		
		// reset object
		$obj->WP_Post = get_post($id);
		
		// Now run through properties stored as postmeta
		foreach ($map as $prop => $mapped) {
			if ($mapped['map'] == 'meta') {
				update_post_meta($id, $prop, $obj->get_mapped_property($prop));
			} elseif ($mapped['map'] == 'meta_multi') {
				$values = $obj->get_mapped_property($prop);
				
				delete_post_meta($id, $prop);
				
				if (is_array($values)){
					foreach ($values as $value){
						add_post_meta($id, $prop, $value);
					}
				}
			}
		}
		
		do_action('tainacan-insert', $obj);
		do_action('tainacan-insert-'.$obj->get_post_type(), $obj);
		
		// return a brand new object
		return new $this->entities_type($obj->WP_Post);
	}

    /**
     * Prepare the output for the fetch() methods.
     *
     * Possible outputs are:
     * WP_Query (default) - returns the WP_Object itself
     * OBJECT - return an Array of Tainacan\Entities
     * 
     * @param \WP_Query $WP_Query
     * @param string $output `WP_Query` for a single WP_Query object or `OBJECT` for an array of Tainacan\Entities
     * @return array|\WP_Query
     */
	public function fetch_output(\WP_Query $WP_Query, $output = 'WP_Query' ){
        
        if (is_null($output)) $output = 'WP_Query';

        if( $output === 'WP_Query'){
            return $WP_Query;
        }else if( $output === 'OBJECT' ) {
            $result = [];

            if (  $WP_Query->have_posts() ){
                while ( $WP_Query->have_posts() ) {
                    $WP_Query->the_post();
                    $result[] = new $this->entities_type(  get_the_ID() );
                }
            }

            return $result;
        }
    }
    
    /**
     * Maps repository mapped properties to WP_Query arguments.
     *
     * This allows to build fetch arguments using both WP_Query arguments
     * and the mapped properties for the repository.
     *
     * For example, you can use any of the following methods to browse collections by name:
     * $TaincanCollections->fetch(['title' => 'test']);
     * $TaincanCollections->fetch(['name' => 'test']);
     *
     * The property `name` is transformed into the native WordPress property `post_title`. (actually only title for query purpouses)
     *
     * Example 2, this also works with properties mapped to postmeta. The following methods are the same:
     * $TaincanMetadatas->fetch(['required' => 'yes']);
     * $TaincanMetadatas->fetch(['meta_query' => [
     *     [
     *         'key' => 'required',
     *         'value' => 'yes'
     *     ]
     * ]]);
     *
     * 
     * @param  array  $args [description]
     * @return Array $args new $args array with mapped properties
     */
    public function parse_fetch_args($args = []) {
        
        $map = $this->get_map();

        $wp_query_exceptions = [
            'ID'         => 'p',
            'post_title' => 'title'
        ];
        
        $meta_query = [];
        
        foreach ($map as $prop => $mapped) {
            if (array_key_exists($prop, $args)) {
                $prop_value = $args[$prop];
                
                unset($args[$prop]);
                
                if ( $mapped['map'] == 'meta' || $mapped['map'] == 'meta_multi' ) {
                    $meta_query[] = [
                        'key'   => $prop,
                        'value' => $prop_value
                    ];
                } else {
                    $prop_search_name = array_key_exists($mapped['map'], $wp_query_exceptions) ? $wp_query_exceptions[$mapped['map']] : $mapped['map'];
                    $args[$prop_search_name] = $prop_value;
                }
                
            }
        }
        
        if ( isset($args['meta_query']) && is_array($args['meta_query']) && !empty($args['meta_query'])) {
            $meta_query = array_merge($args['meta_query'],$meta_query);
        }
        
        $args['meta_query'] = $meta_query;
        
        return $args;
        
    }

    /**
     * @param $object
     * @return mixed
     */
    public abstract function delete($object);

    /**
     * @param $args
     * @return mixed
     */
    public abstract function fetch( $args , $output = null );

    /**
     * @param $object
     * @return mixed
     */
    public abstract function update($object);

    /**
     * @return mixed
     */
    public abstract function register_post_type();
    
}

?>