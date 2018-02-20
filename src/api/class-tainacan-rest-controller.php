<?php

class TAINACAN_REST_Controller extends WP_REST_Controller {

	/**
	 * @param $object
	 * @param $new_values
	 *
	 * @return Tainacan\Entities\Entity
	 */
	protected function prepare_item_for_updating($object, $new_values){

		foreach ($new_values as $key => $value) {
			try {
				$set_ = 'set_' . $key;
				$object->$set_( $value );
			} catch (\Error $error){
				// Do nothing
			}
		}

		return $object;
	}

	/**
	 * @param $entity
	 *
	 * @return array
	 */
	protected function get_only_needed_attributes($entity, $map){

		$entity_prepared = [
			'id'          => $entity->get_id(),
			'description' => $entity->get_description(),
		];

		if(array_key_exists('modification_date', $map)){
			$entity_prepared['modification_date'] = $entity->get_modification_date();
		}

		if(array_key_exists('creation_date', $map)){
			$entity_prepared['creation_date'] = $entity->get_creation_date();
		}

		if(array_key_exists('author_id', $map)){
			$entity_prepared['author_id'] = $entity->get_author_id();
		}

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

		if(array_key_exists('status', $map)){
			$entity_prepared['status'] = $entity->get_status();
		}

		return $entity_prepared;
	}

	/**
	 * @param $request
	 *
	 * @return array
	 */
	protected function prepare_filters($request){
		$map = [
			'name'       => 'title',
			'title'      => 'title',
			'id'         => 'p',
			'pageid'     => 'page_id',
			'authorid'   => 'author_id',
			'authorname' => 'author_name',
			'search'     => 's',
			'status'     => 'post_status',
			'offset'     => 'offset',
			'metaquery'  => 'meta_query',
			'datequery'  => 'date_query',
			'order'      => 'order',
			'orderby'    => 'orderby',
			'metakey'    => 'meta_key',
			'hideempty'  => 'hide_empty',
			'perpage'    => 'posts_per_page',
			'paged'      => 'paged'
		];

		$meta_query = [
			'key'      => 'key',
			'value'    => 'value',
			'compare'  => 'compare',
			'relation' => 'relation',
		];

		$date_query = [
			'year'   => 'year',
			'month'  => 'month',
			'day'    => 'day',
			'week'   => 'week',
			'hour'   => 'hour',
			'minute' => 'minute',
			'second' => 'second'
		];

		$args = [];

		foreach ($map as $mapped => $mapped_v){
			if(isset($request[$mapped])){
				if($mapped === 'metaquery'){
					$request_meta_query = $request[$mapped];

					// If is a multidimensional array (array of array)
					if($this->contains_array($request_meta_query)) {

						foreach ( $request_meta_query as $index1 => $a ) {
							foreach ( $meta_query as $mapped_meta => $meta_v ) {
								if ( isset( $a[ $meta_v ] ) ) {
									$args[ $mapped_v ][ $index1 ][ $meta_v ] = $request[ $mapped ][ $index1 ][ $meta_v ];
								}
							}
						}
					} else {
						foreach ( $meta_query as $mapped_meta => $meta_v ) {
							if(isset($request[$mapped][$meta_v])) {
								$args[ $mapped_v ][ $meta_v ] = $request[ $mapped ][ $meta_v ];
							}
						}
					}

				} elseif ($mapped === 'datequery') {
					foreach ($date_query as $date_meta => $date_v){
						$args[$mapped_v][$date_v] = $request[$mapped][$date_meta];
					}
				} else {
					$args[ $mapped_v ] = $request[ $mapped ];
				}
			}
		}

		$args['perm'] = 'readable';

		var_dump($args);
		return $args;
	}

	protected function contains_array($array){
		foreach ($array as $value){
			if(is_array($value)){
				return true;
			}
		}

		return false;
	}

}

?>