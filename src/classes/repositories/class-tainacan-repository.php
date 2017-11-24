<?php

namespace Tainacan\Repositories;
use Tainacan\Entities;
use Tainacan\Entities\Entity;
use Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Repository {
	protected $entities_type = '\Tainacan\Entities\Entity';
	/**
	 * 
	 * @var string Text to put on log Title, false to use default 
	 */
	protected $log_message = false;
	
	/**
	 *
	 * @var string Text to put on log Description, false for no description
	 */
	protected $log_description = false;
	
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
		
		// not have a post_type get its collection relation, else get post type from entity
		if ( $obj->get_post_type() === false ) {
			$collection = $obj->get_collection();
			
			if (!$collection){
				return false;
			}
			$cpt = $collection->get_db_identifier();
			$obj->WP_Post->post_type = $cpt;
		}
		else {
			$obj->WP_Post->post_type = $obj::get_post_type();
		}
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
		
		//not log the log 
		if($this->entities_type != '\Tainacan\Entities\Log') Entities\Log::create($this->log_message, $this->log_description, $obj);
		
		// return a brand new object
		return new $this->entities_type($obj->WP_Post);
	}
    
    public abstract function delete($object);
    public abstract function fetch($object);
    public abstract function update($object);
    public abstract function register_post_type();
    
}

?>