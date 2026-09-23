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
class TAINACAN_HTML_Injection extends TAINACAN_UnitTestCase
{
    // Evil attempts
    private $link   = "<a href='www.tainacan.org'>link</a>";
    private $js     = "<script>alert('XSS')</script>";
    private $css    = "my text along with some style <style>a { display: none }</style>";
    private $iframe = "<iframe src='www.tainacan.org' title='Tainacan'></iframe>";
    private $text_and_link = "";
    private $text_and_iframe = "";

    // Accepted formatting
    private $strong = "I have some info to tell the world. And I can <strong> bold it </strong>";
    private $html	= "<div><h1>Main Info</h1><h3>sub title</h3><p>My structure description<p></p>and another paragraph</p></div>";

    // Expected returns
    private $expected_title = 'my very interesting name and link as well';
    private $expected_desc = 'description item';

    private $collection = null;
    private $item = null;
    private $metadatum = null;
    private $taxonomy = null;
    private $taxonomy_db = null;

    public function setUp(): void
    {
        parent::setUp();
        $link = $this->link;
        $iframe = $this->iframe;
        $this->text_and_link = "my very interesting name and $link as well";
        $this->text_and_iframe = "description item $iframe";

        $this->test_collections();
    }

    function test_collections() {
        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name' => 'collection name <a href="www.tainacan.org">link <a href="link2.com.br"> link2 </a> </a>',
                'description' => $this->text_and_iframe,
            ),
            true
        );
        $collection = $Tainacan_Collections->fetch($collection->get_id());
        $this->assertEquals($collection->get_name(), 'collection name link  link2');
        $this->assertEquals($collection->get_description(), $this->expected_desc);

        $this->collection = $collection;
        $this->test_items();
    }

    function test_items() {
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $item = $this->tainacan_entity_factory->create_entity(
            'item',
            array(
                'title'       => 'title item <script>console.log("XSS")</script>',
                'description' => $this->text_and_iframe,
                'collection'  => $this->collection
            ),
            true
        );

        $item = $Tainacan_Items->fetch($item->get_id());
        $this->assertEquals($item->get_title(), 'title item console.log("XSS")');
        $this->assertEquals($item->get_description(), $this->expected_desc);

        $this->item = $item;
        $this->test_metadata();
    }

    function test_metadata() {
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => $this->text_and_link,
				'description'       => 'metadatum description',
				'collection'        => $this->collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
		$metadatum = $Tainacan_Metadata->fetch($metadatum->get_id());
		$this->assertEquals($metadatum->get_name(), $this->expected_title);

        $this->metadatum = $metadatum;
        $this->test_taxonomies();
    }

    function test_taxonomies() {
        $Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();
        $taxonomy = $this->tainacan_entity_factory->create_entity(
		    'taxonomy',
            array(
                'name'   => $this->text_and_link,
                'collections' => [$this->collection],
                'description' => $this->text_and_iframe,
                'status' => 'publish'
            ),
            true
        );

        $tx = $Tainacan_Taxonomies->fetch($taxonomy->get_id());
        $this->assertEquals($tx->get_name(), $this->expected_title);
        $this->assertEquals($tx->get_description(), $this->expected_desc);

        $this->taxonomy = $taxonomy;
        $this->taxonomy_db = $taxonomy->get_db_identifier();
    }

    function test_terms() {
        $Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();
        $term = $this->tainacan_entity_factory->create_entity(
            'term',
            array(
                'taxonomy' => $this->taxonomy_db,
                'name'     => $this->css,
            ),
            true
        );
        $t =  $Tainacan_Terms->fetch($term->get_term_id(), $this->taxonomy);
        $this->assertEquals($t->get_name(), 'my text along with some style');
    }

    function test_item_metadata() {
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($this->item, $this->metadatum);
		$item_metadata->set_value($this->js);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		$this->assertEquals($item_metadata->get_value(), "alert('XSS')");

		$item_metadata->set_value($this->link);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), 'link');

		$item_metadata->set_value($this->css);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), 'my text along with some style a { display: none }');

		$item_metadata->set_value($this->iframe);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), '');

		$item_metadata->set_value($this->strong);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), $this->strong);

		$item_metadata->set_value($this->html);
		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->update($item_metadata);
		$this->assertEquals($item_metadata->get_value(), $this->html);
    }

	public function test_preserves_links_in_descriptions_but_not_names() {
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$link = '<a href="https://tainacan.org">Tainacan</a>';
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name' => 'Collection ' . $link,
				'description' => 'Description ' . $link
			],
			true
		);

		$collection = $Tainacan_Collections->fetch( $collection->get_id() );

		$this->assertSame( 'Collection Tainacan', $collection->get_name() );
		$this->assertSame( 'Description ' . $link, $collection->get_description() );
	}

	public function test_preserves_links_for_rich_text_capable_metadata_only() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		$link = '<a href="https://tainacan.org">Tainacan</a>';
		$collection = $this->tainacan_entity_factory->create_entity( 'collection', [ 'name' => 'Collection' ], true );
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			[
				'title' => 'Item',
				'collection' => $collection,
				'status' => 'publish'
			],
			true
		);
		$rich_text_metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name' => 'Rich text',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\\Metadata_Types\\Textarea',
				'metadata_type_options' => [ 'use_rich_text_editor' => 'yes' ]
			],
			true
		);
		$plain_text_metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			[
				'name' => 'Plain text',
				'collection' => $collection,
				'metadata_type' => 'Tainacan\\Metadata_Types\\Text'
			],
			true
		);

		$rich_text_value = new \Tainacan\Entities\Item_Metadata_Entity( $item, $Tainacan_Metadata->fetch( $rich_text_metadatum->get_id() ) );
		$rich_text_value->set_value( $link );
		$rich_text_value->validate();
		$rich_text_value = $Tainacan_Item_Metadata->insert( $rich_text_value );

		$plain_text_value = new \Tainacan\Entities\Item_Metadata_Entity( $item, $Tainacan_Metadata->fetch( $plain_text_metadatum->get_id() ) );
		$plain_text_value->set_value( $link );
		$plain_text_value->validate();
		$plain_text_value = $Tainacan_Item_Metadata->insert( $plain_text_value );

		$this->assertSame( $link, $rich_text_value->get_value() );
		$this->assertSame( 'Tainacan', $plain_text_value->get_value() );

		$core_description_value = new \Tainacan\Entities\Item_Metadata_Entity( $item, $collection->get_core_description_metadatum() );
		$core_description_value->set_value( $link );
		$core_description_value->validate();
		$core_description_value = $Tainacan_Item_Metadata->insert( $core_description_value );

		$this->assertSame( $link, $core_description_value->get_value() );
	}

}
