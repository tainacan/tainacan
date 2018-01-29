<?php

class TAINACAN_REST_Controller extends WP_REST_Controller {


	/**
	 * @param $entity
	 *
	 * @return array
	 */
	protected function get_only_needed_attributes($entity, $map){

		$entity_prepared = [
			'id'                => $entity->get_id(),
			'description'       => $entity->get_description(),
			'author_id'         => $entity->get_author_id(),
			'creation_date'     => $entity->get_creation_date(),
			'modification_date' => $entity->get_modification_date(),
		];

		if(array_key_exists('name', $map)){
			$entity_prepared['name'] = $entity->get_name();
		} elseif(array_key_exists('title', $map)){
			$entity_prepared['title'] = $entity->get_title();
		}

		if(array_key_exists('featured_image', $map)){
			$entity_prepared['featured_image'] = $entity->get_featured_img();
		}

		if(array_key_exists('columns', $map)){
			$entity_prepared['columns'] = $entity->get_columns();
		}

		return $entity_prepared;
	}

}

?>