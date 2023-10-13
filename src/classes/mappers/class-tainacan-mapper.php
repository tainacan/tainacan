<?php

namespace Tainacan\Mappers;

abstract class Mapper {
	public $slug = null; // Slug of Mapper, used as option on api call 
	public $name = null; // Public name of the mapper
	public $description = null; // Public descript of the mapper
	public $allow_extra_metadata = true; // Allow more metadatum to be register
	public $context_url = null; // URL of mapper documentation
	public $type = ''; // The Class of the ontology that this mapping refers to. For example `CreativeWork`, which is a class of Schema.org if applied
	
	/**
	 * array of supported metadata, false for not validade the list format:
	 * ['slug'] => [
	 *     'URI' => 'http://...',          // URI of the metadatum description
	 *     'label' => 'Label',             // Label to show on UI
	 *     'metadata_type' => 'date',         // Tainacan recomended metadatum type, default text
	 *     'core_metadatum' => false   // if have a core tainacan metadatum, what?
	 * ['date' => [
	 *     	'URI' => 'http://purl.org/dc/elements/1.1/date',
	 *  	'label' => 'Date',
     *      'metadata_type' => 'date'
	 *  ],
	 *  'description' => [
	 *  	'URI' => 'http://purl.org/dc/elements/1.1/description',
	 *  	'label' => 'Description',
	 *      'core_metadatum' => true
	 *  ]]
	 * @var array
	 */
	public $metadata = false;
	public $add_meta_form = '';
	public $prefix = ''; // Tag prefix like "dc:"
	public $sufix = ''; // Tag sufix
	public $header = false; // API response header or file header to be used with
	public $show_ui = true; // Show mapper in ui and api calls

	public function __construct() {}
	
	public function _toArray($collection_id = null) {
		return [
			'slug' => $this->slug,
			'name' => $this->name,
			'description' => $this->description,
			'allow_extra_metadata' => $this->allow_extra_metadata,
			'context_url' => $this->context_url,
			'metadata' => $this->get_metadata($collection_id),
			'prefix' => $this->prefix,
			'sufix' => $this->sufix,
			'header' => $this->header,
			'add_meta_form' => $this->add_meta_form
		];
	}

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
			if(isset($exposer_mapping[$this->slug]) && isset($exposer_mapping[$this->slug]['slug'])) {
				$slug = $exposer_mapping[$this->slug]['slug'];
				$prepared_custom_mapper[$slug] = $exposer_mapping[$this->slug];
			}
		}
		return array_merge( $this->metadata,  $prepared_custom_mapper);
	}
	
	/**
	 * Gets the semantic URL for a given metadatum of this mapper.
	 * Basically it identifies the property prefix and replace it with the URL of that prefix
	 * 
	 * @param  string $meta_slug The slug of the metadata present in this mapper to get the URL from
	 * @return string The semantic URL for this metadata. Empty string in case of failure
	 */
	public function get_url($meta_slug) {
		$parts = explode(':', $meta_slug);
		$url = '';
		if (sizeof($parts) == 2) {
			if (isset($this->prefixes[$parts[0]])) {
				$url = trailingslashit( $this->prefixes[$parts[0]] ) . $parts[1];
			} 
		}
		return $url;
	}
	
}