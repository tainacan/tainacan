<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class HTML_Injection extends TAINACAN_UnitTestCase
{

	function test_item_metadata()
	{
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$link = '<a href="www.tainacan.org">link</a>';

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'collection name <a href="www.tainacan.org">link <a href="link2.com.br"> link2 </a> </a>',
				'description' => 'collection description',
			),
			true
		);
		$collection = $Tainacan_Collections->fetch($collection->get_id());

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadatum name <a href="www.tainacan.org">link</a>',
				'description'       => 'metadatum description',
				'collection'        => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
		$metadatum = $Tainacan_Metadata->fetch($metadatum->get_id());

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'title item <script>console.log("XSS")</script>',
				'description' => 'description item  <iframe src="www.tainacan.org" title="Taiancan"></iframe>',
				'collection'  => $collection
			),
			true
		);
		$item = $Tainacan_Items->fetch($item->get_id());

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
		$item_metadata->set_value("<script>alert('XSS')</script>");
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		// $this->assertEquals($collection->get_name(), 'collection name link link2');
		// $this->assertEquals($metadatum->get_name(), 'metadatum name link');
		// $this->assertEquals($item->get_title(), 'title item console.log("XSS")');
		// $this->assertEquals($item->get_description(), 'description item');
		$this->assertEquals($item_metadata->get_value(), "alert('XSS')");

		$item_metadata->set_value($link);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), 'link');

		//test terms
	}
}
