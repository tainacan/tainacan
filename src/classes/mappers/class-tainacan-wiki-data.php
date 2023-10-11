<?php

namespace Tainacan\Mappers;

/**
 * Support Dublin Core Mapping 
 * http://purl.org/dc/elements/1.1/
 *
 */
class Wiki_Data extends Mapper {
	public $slug = 'wiki-data';
	public $name = 'WikiData';
	public $description = 'The model mapper using the WikiData source';
	public $allow_extra_metadata = true;
	public $context_url = 'http://??????';
	public $header = '??????';
	public $prefixes = [
		'dc' => 'http://purl.org/dc/elements/1.1/'
	];
	public $metadata = [
		
	];	

	public function get_metadata($collection_id = null) {
		if($collection_id == null)
			return $this->metadata;
		
		$metadatum_repository = \tainacan_metadata();
		$collection = new \Tainacan\Entities\Collection( $collection_id );
		$args = [
			'meta_query' => [
				[
					'key'     => 'exposer_mapping',
					'compare' => 'EXISTS',
				]
			],
			'posts_per_page' => -1
		];
		$metadata = $metadatum_repository->fetch_by_collection( $collection, $args );
		$prepared_custom_mapper = [];
		foreach ( $metadata as $item ) {
			$exposer_mapping = $item->get_exposer_mapping();
			if(isset($exposer_mapping[$this->slug])) {
				$slug = $exposer_mapping[$this->slug]['slug'];
				$prepared_custom_mapper[$slug] = $exposer_mapping[$this->slug];
			}
		}
		return array_merge( $this->metadata,  $prepared_custom_mapper);
	}
}