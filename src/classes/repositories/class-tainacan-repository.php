<?php

namespace Tainacan\Repositories;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Repository {
	protected $entity_type = '\Tainacan\Traits\Entity'; 
	
	public function get_map()
	{
		return array();
	}
	
	/**
	 * 
	 * @param \Tainacan\Traits\Entity $obj
	 * @return \Tainacan\Traits\Entity
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
		
		// save post and geet its ID
		$obj->WP_Post->post_type = $obj->get_post_type();//$this->ObjectType::POST_TYPE;
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
		
		// return a brand new object
		return new $this->entity_type($obj->WP_Post);
	}
    
    public abstract function delete($object);
    public abstract function fetch($object);
    public abstract function update($object);

}

?>