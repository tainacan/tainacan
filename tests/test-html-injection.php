<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * HTML INJECTION test case.
 */
class HTML_Injection extends TAINACAN_UnitTestCase
{

	function test_item_metadata()
	{
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

		// Evil attempts
		$link   = "<a href='www.tainacan.org'>link</a>";
		$js     = "<script>alert('XSS')</script>";
		$css    = "my text along with some style <style>a { display: none }</style>";
		$iframe = "<iframe src='www.tainacan.org' title='Taiancan'></iframe>";
        $text_and_link = "my very interesting name and $link as well";

		// Accepted formatting
		$strong = "I have some info to tell the world. And I can <strong> bold it </strong>";
		$html	= "<div><h1>Main Info</h1><h3>sub title</h3><p>My structure description<p></p>and another paragraph</p></div>";

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'collection name <a href="www.tainacan.org">link <a href="link2.com.br"> link2 </a> </a>',
				'description' => 'collection description',
			),
			true
		);
		$collection = $Tainacan_Collections->fetch($collection->get_id());

		// Test Collection
		$this->assertEquals($collection->get_name(), 'collection name link  link2');
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => $text_and_link,
				'description'       => 'metadatum description',
				'collection'        => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
		$metadatum = $Tainacan_Metadata->fetch($metadatum->get_id());
		$this->assertEquals($metadatum->get_name(), 'my very interesting name and link as well');

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'title item <script>console.log("XSS")</script>',
				'description' => 'description item  <iframe src="www.tainacan.org" title="Taiancan"></iframe>',
				'collection'  => $collection
			),
			true
		);

        $taxonomy = $this->tainacan_entity_factory->create_entity(
            'taxonomy',
            array(
                'name'   => $text_and_link,
                'collections' => [$collection],
                'status' => 'publish'
            ),
            true
        );

		$item = $Tainacan_Items->fetch($item->get_id());
		$this->assertEquals($item->get_title(), 'title item console.log("XSS")');
		$this->assertEquals($item->get_description(), 'description item');

		// Test metadata
		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
		$item_metadata->set_value($js);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		$this->assertEquals($item_metadata->get_value(), "alert('XSS')");

		$item_metadata->set_value($link);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), 'link');

		$item_metadata->set_value($css);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), 'my text along with some style a { display: none }');

		$item_metadata->set_value($iframe);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), '');

		$item_metadata->set_value($strong);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), $strong);

		$item_metadata->set_value($html);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), $html);

		// Test terms

		// Test taxonomies
        $tx = $Tainacan_Taxonomies->fetch($taxonomy->get_id());
        $this->assertEquals($tx->get_name(), 'my very interesting name and link as well');
	}
}
