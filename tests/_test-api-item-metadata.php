<?php

namespace Tainacan\Tests;

/**
 * @group api_item_meta
 */
class TAINACAN_REST_Item_Metadata_Controller extends TAINACAN_UnitApiTestCase {
	protected $item;
	protected $collection;
	protected $metadatum;
	
	protected function create_meta_requirements() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'testeItemMetadata',
				'description' => 'No description',
			),
			true,
			true
		);
		
		$type = $this->tainacan_metadatum_factory->create_metadatum('text');
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'teste_metadado',
				'description'       => 'descricao',
				'collection'        => $collection,
				'metadata_type'		=> $type,
				'accept_suggestion'	=> true
			),
			true,
			true
		);
		
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item_teste_metadado',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true,
			true
		);
		$this->collection = $collection;
		$this->item = $item;
		$this->metadatum = $metadatum;
		return ['collection' => $collection, 'item' => $item, 'metadatum' => $metadatum];
	}


	
}

?>