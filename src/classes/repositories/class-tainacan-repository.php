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
		if (!$obj->validate()){
			return $obj->get_errors();
		}
		// TODO: Throw Warning saying you must validate object before insert()
		
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
     * @param \WP_Query $WP_Query
     * @param string $output
     * @return array|\WP_Query
     */
	public function fetch_output(\WP_Query $WP_Query, $output = 'WP_Query' ){
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
     * @param $object
     * @return mixed
     */
    public abstract function delete($object);

    /**
     * @param $args
     * @return mixed
     */
    public abstract function fetch($args);

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