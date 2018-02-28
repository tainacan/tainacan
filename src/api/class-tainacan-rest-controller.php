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
	 * @param $request
	 *
	 * @return array
	 */
	protected function prepare_filters($request){
		$map = [
			'name'         => 'title',
			'title'        => 'title',
			'id'           => 'p',
			'pageid'       => 'page_id',
			'authorid'     => 'author_id',
			'authorname'   => 'author_name',
			'search'       => 's',
			'status'       => 'post_status',
			'offset'       => 'offset',
			'metaquery'    => 'meta_query',
			'datequery'    => 'date_query',
			'taxquery'     => 'tax_query',
			'order'        => 'order',
			'orderby'      => 'orderby',
			'metakey'      => 'meta_key',
			'metavalue'    => 'meta_value',
			'metavaluenum' => 'meta_value_num',
			'metacompare'  => 'meta_compare',
			'hideempty'    => 'hide_empty',
			'perpage'      => 'posts_per_page',
			'paged'        => 'paged',
		];

		$meta_query = [
			'key'      => 'key',
			'value'    => 'value',
			'compare'  => 'compare',
			'relation' => 'relation',
			'type'     => 'type',
		];

		$date_query = [
			'year'      => 'year',
			'month'     => 'month',
			'day'       => 'day',
			'week'      => 'week',
			'hour'      => 'hour',
			'minute'    => 'minute',
			'second'    => 'second',
			'compare'   => 'compare',
			'dayofweek' => 'dayofweek',
			'inclusive' => 'inclusive',
			'before'    => 'before',
			'after'     => 'after',
		];

		$tax_query = [
			'taxonomy' => 'taxonomy',
			'field'    => 'field',
			'terms'    => 'terms',
			'operator' => 'operator',
			'relation' => 'relation',
		];

		$args = [];

		foreach ($map as $mapped => $mapped_v){
			if(isset($request[$mapped])){
				if($mapped === 'metaquery'){
					$args = $this->prepare_meta($mapped, $request, $meta_query, $mapped_v, $args);
				} elseif($mapped === 'datequery'){
					$args = $this->prepare_meta($mapped, $request, $date_query, $mapped_v, $args);
				} elseif($mapped === 'taxquery'){
					$args = $this->prepare_meta($mapped, $request, $tax_query, $mapped_v, $args);
				}
				else {
					$args[ $mapped_v ] = $request[ $mapped ];
				}
			}
		}

		$args['perm'] = 'readable';

		return $args;
	}

	/**
	 * @param $mapped
	 * @param $request
	 * @param $query
	 * @param $mapped_v
	 * @param $args
	 *
	 * @return mixed
	 */
	private function prepare_meta($mapped, $request, $query, $mapped_v, $args){
		$request_meta_query = $request[$mapped];

		// If is a multidimensional array (array of array)
		if($this->contains_array($request_meta_query, $query)) {
			foreach ( $request_meta_query as $index1 => $a ) {
				foreach ( $query as $mapped_meta => $meta_v ) {
					if ( isset( $a[ $meta_v ] ) ) {
						$args[ $mapped_v ][ $index1 ][ $meta_v ] = $request[ $mapped ][ $index1 ][ $meta_v ];
					}
				}
			}
		} else {
			foreach ( $query as $mapped_meta => $meta_v ) {
				if(isset($request[$mapped][$meta_v])) {
					$args[ $mapped_v ][ $meta_v ] = $request[ $mapped ][ $meta_v ];
				}
			}
		}

		return $args;
	}

	/**
	 * @param $array
	 *
	 * @param $query
	 *
	 * @return bool
	 */
	protected function contains_array($array, $query){
		foreach ($array as $index => $value){
			// Not will pass named meta query, which use reserved names
			if(is_array($value) && !key_exists($index, $query)){
				return true;
			}
		}

		return false;
	}

}

?>