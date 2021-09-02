<?php

namespace Tainacan\Tests;

use Tainacan\Entities\Item_Metadata_Entity;

/**
 * Class Item_Metadata
 *
 * @package Test_Tainacan
 */

/**
 * Item_Metadata test case.
 */
class Item_Metadata extends TAINACAN_UnitTestCase {

	private $collection = null;
	private $item = null;
	private $separator = '<span class="multivalue-separator"> | </span>';

	public function setUp() {
		parent::setUp();
		$c = $this->tainacan_entity_factory->create_entity('collection', ['name' => 'My Collection'], true);
		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'My test item',
				'description' => 'item description',
				'collection'  => $c,
				'status'      => 'publish'
			),
			true
		);

		$this->collection = $c;
		$this->item = $i;
	}

	/**
	 * Teste da insercao de um metadado simples sem o tipo
	 */
	function test_add() {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'test',
				'description' => 'No description',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadado',
				'description'   => 'descricao',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		
		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste title',
				'description' => 'item description',
				'collection'  => $collection
			),
			true
		);
		
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		$item = $Tainacan_Items->fetch($i->get_id());

		$item_metadata = new Item_Metadata_Entity($item, $test);
		$item_metadata->set_value('teste_value');
		
		$item_metadata->validate();
		
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
		
		$this->assertEquals('teste_value', $item_metadata->get_value());
	}

	/**
	 * Teste da insercao de um metadado simples com o tipo
	 */
	function test_required(){
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste',
				'description' => 'No description',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadado',
				'description'       => 'descricao',
				'collection'        => $collection,
				'required'          => 'yes',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		
		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'status'      => 'publish',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true
		);
		
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		
		$item = $Tainacan_Items->fetch($i->get_id());
		$item_metadata = new Item_Metadata_Entity($item, $test);
		
		// false because its required
		$this->assertFalse($item_metadata->validate());
		
		$item_metadata->set_value('teste_value');
		
		$this->assertTrue($item_metadata->validate());
		
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);
		
		$this->assertEquals('teste_value', $item_metadata->get_value());
	}
	
	function test_collection_key(){
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste',
				'description' => 'No description',
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadado',
				'description'       => 'descricao',
				'collection'        => $collection,
				'collection_key'    => 'yes',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'adasdasdsa',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);
		
		$i2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'other item',
				'description' => 'adasdasdsa',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);
		
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$value = 'teste_val';
		
		$item_metadata = new Item_Metadata_Entity($i, $metadatum);
		$item_metadata->set_value($value);

		$this->assertTrue($item_metadata->validate());

		$item_metadata->validate();
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata);

		$n_item_metadata = new Item_Metadata_Entity($i, $metadatum);
		$n_item_metadata->set_value($value);
		$this->assertTrue($n_item_metadata->validate(), 'trying to validate the same item with same value should be ok');

		$n_item_metadata2 = new Item_Metadata_Entity($i2, $metadatum);
		$n_item_metadata2->set_value($value);
		$this->assertFalse($n_item_metadata2->validate(), 'Collection key should not validate another item metadatada with the same value');
	}
	
	function test_fetch(){
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste',
				'description' => 'No description',
			),
			true
		);


		$this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadado',
				'description'       => 'descricao',
				'collection'        => $collection,
				'status'            => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		//$test = $Tainacan_Metadata->fetch($metadatum->get_id());
		
		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title' => 'item teste',
				'description' => 'adasdasdsa',
				'collection' => $collection
			),
			true
		);

		$item_metadatas = $Tainacan_Item_Metadata->fetch($i, 'OBJECT');

		$names = [];
		foreach ($item_metadatas as $item_metadata) {
			$names[] = $item_metadata->get_metadatum()->get_name();
		}
		
		$this->assertTrue(is_array($item_metadatas));

		// notice for repository metadata
		$this->assertEquals(3, sizeof($item_metadatas));
		//first 2 metadata are repository metadata
		$this->assertTrue( in_array('metadado', $names) );
		
	}
	
	function test_metadata_text_textarea() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste'
			),
			true
		);
		
		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'description',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);

		$metadatum_text = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadadoText',
				'description'       => 'descricao',
				'collection_id'     => $collection->get_id(),
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);
		
		$metadatum_textarea = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadadoTextarea',
				'description'       => 'descricao',
				'collection_id'     => $collection->get_id(),
				'metadata_type'  => 'Tainacan\Metadata_Types\Textarea',
			),
			true
		);
		
		$value_text = 'GOOGLE: www.google.com';
		$item_metadata_text = new Item_Metadata_Entity($i, $metadatum_text);
		$item_metadata_text->set_value($value_text);
		
		$value_textarea = 'GOOGLE: www.google.com \n GOOGLE: https://www.google.com';
		$item_metadata_textarea = new Item_Metadata_Entity($i, $metadatum_textarea);
		$item_metadata_textarea->set_value($value_textarea);

		$response_text = 'GOOGLE: <a href="http://www.google.com" target="_blank" title="www.google.com">www.google.com</a>';
		$response_textarea = 'GOOGLE: <a href="http://www.google.com" target="_blank" title="www.google.com">www.google.com</a> \n GOOGLE: <a href="https://www.google.com" target="_blank" title="https://www.google.com">https://www.google.com</a>';

		$this->assertEquals($item_metadata_text->get_value_as_html(), $response_text);
		$this->assertEquals($item_metadata_textarea->get_value_as_html(), $response_textarea);

		// Poor HTML entry tests
		$badFormatted_HTML = "<p> I started my content <div> and make something else here </div> without closing its HTML properly";

		$item_metadata_text->set_value($badFormatted_HTML);
		$item_metadata_textarea->set_value($badFormatted_HTML);

		$this->assertEquals($item_metadata_text->get_value_as_html(), $badFormatted_HTML ."</p>");
		$this->assertEquals($item_metadata_textarea->get_value_as_html(), $badFormatted_HTML ."</p>");
	}

	/**
	 * @group test_item_metadata_has_value
	 */
	function test_item_metadata_has_value() {
		$test_value = 'has_value';
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name' => 'teste'
			),
			true
		);

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'description',
				'collection'  => $collection,
				'status'      => 'publish'
			),
			true
		);


		$metadatum_textarea = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadadoTextarea',
				'description'       => 'descricao',
				'collection_id'     => $collection->get_id(),
				'metadata_type'  => 'Tainacan\Metadata_Types\Textarea',
			),
			true
		);

		$value_textarea = '';
		$item_metadata_textarea = new Item_Metadata_Entity($i, $metadatum_textarea);
		$item_metadata_textarea->set_value($value_textarea);

		$item_metadata_textarea->validate();
		$item_metadata_textarea = $Tainacan_Item_Metadata->insert($item_metadata_textarea);

		$this->assertFalse($item_metadata_textarea->has_value());

		$item_metadata_textarea->set_value($test_value);
		$item_metadata_textarea->validate();
		$item_metadata_textarea = $Tainacan_Item_Metadata->insert($item_metadata_textarea);

		$this->assertTrue($item_metadata_textarea->has_value());

		$metadatum_text = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadadoText',
				'description'       => 'descricao',
				'collection_id'     => $collection->get_id(),
				'metadata_type'     => 'Tainacan\Metadata_Types\Text',
				'multiple'          => 'yes'
			),
			true
		);

		$item_metadata_text = new Item_Metadata_Entity($i, $metadatum_text);

		$item_metadata_text->set_value([ $value_textarea ]);

		$item_metadata_text->validate();
		$item_metadata = $Tainacan_Item_Metadata->insert($item_metadata_text);

		$this->assertFalse($item_metadata->has_value());

		$item_metadata_text->set_value([ $test_value ]);

		$item_metadata_text->validate();
		$item_metadata_text = $Tainacan_Item_Metadata->insert($item_metadata_text);

		$this->assertTrue($item_metadata_text->has_value());
	}

	function test_numeric_metadata_html() {
		// Simple numeric metadata
		$metadatum_numeric = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Numeric important meta',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Numeric',
				'status'      	=> 'publish'
			),
			true
		);

		// Multiple numeric metadata
		$metadatum_numeric_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Numeric important meta',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Numeric',
				'status'      	=> 'publish',
				'multiple'		=> 'yes'
			),
			true
		);

		$item_metadata_numeric = new Item_Metadata_Entity($this->item, $metadatum_numeric);
		$item_metadata_numeric->set_value(10);
		$item_metadata_numeric->validate();
		$this->assertEquals($item_metadata_numeric->get_value_as_html(), 10);

		$item_metadata_numeric_mult = new Item_Metadata_Entity($this->item, $metadatum_numeric_multiple);
		$item_metadata_numeric_mult->set_value([10,22,4]);
		$item_metadata_numeric_mult->validate();
		$this->assertEquals($item_metadata_numeric_mult->get_value_as_html(), "10" . $this->separator . "22" . $this->separator . "4");
	}

	function test_date_metadata_html() {
		// Simple date metadata
		$metadatum_date = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Date important meta',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Date',
				'status'      	=> 'publish'
			),
			true
		);

		// Multiple date metadata
		$metadatum_date_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Date meta',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Date',
				'status'      	=> 'publish',
				'multiple'		=> 'yes'
			),
			true
		);

		$item_metadata_date = new Item_Metadata_Entity($this->item, $metadatum_date);
		// Invalid date value
		$item_metadata_date->set_value(10);
		$item_metadata_date->validate();
		$this->assertFalse($item_metadata_date->get_value_as_html());

		$item_metadata_date->set_value("2021-04-05");
		$item_metadata_date->validate();
		$this->assertEquals($item_metadata_date->get_value_as_html(), "April 5, 2021");

		$item_metadata_date_mult = new Item_Metadata_Entity($this->item, $metadatum_date_multiple);
		// Invalid date values
		$item_metadata_date_mult->set_value([10,22,4]);
		$item_metadata_date_mult->validate();
		$this->assertEquals($item_metadata_date_mult->get_value_as_html(),  $this->separator . $this->separator );
		
		$item_metadata_date_mult->set_value(["2021-04-05", "2021-12-30"]); 
		$item_metadata_date_mult->validate();
		$this->assertEquals($item_metadata_date_mult->get_value_as_html(), 'April 5, 2021' . $this->separator . 'December 30, 2021');
	}

	function test_user_metadata_html() {
		$user_metadata = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'User metadata',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\User',
				'status'      	=> 'publish'
			),
			true
		);

		$user_meta = new Item_Metadata_Entity($this->item, $user_metadata);
		// Empty val
		$this->assertEmpty($user_meta->get_value_as_html());

		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber', 'display_name' => 'User Name' ));

		$user_meta->set_value($new_user);
		$user_meta->validate();
		$this->assertEquals($user_meta->get_value_as_html(), "User Name");
		
		$user_metadata_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'User metadata',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\User',
				'status'      	=> 'publish',
				'multiple'		=> 'yes'
			),
			true
		);
		$user_meta_multi = new Item_Metadata_Entity($this->item, $user_metadata_multiple);
		$this->assertEmpty($user_meta_multi->get_value_as_html());

		$sec_user = $this->factory()->user->create(array( 'role' => 'subscriber', 'display_name' => 'User Name 2' ));
		$user_meta_multi->set_value([$new_user, $sec_user]);
		$user_meta_multi->validate();
		$this->assertEquals($user_meta_multi->get_value_as_html(), 'User Name' . $this->separator . 'User Name 2');
	}

	function test_selectbox_metadata_html() {
		$selectbox_metadata = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'My selectbox meta',
				'status'        => 'publish',
				'collection'    => $this->collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Selectbox',
				'metadata_type_options' => [
					'options' => ['tainacan', 'wordpress', 'php']
				]
			),
			true
		);

		$selectbox_metadata_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'My selectbox meta',
				'status'        => 'publish',
				'collection'    => $this->collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Selectbox',
				'multiple'		=> 'yes',
				'metadata_type_options' => [
					'options' => ['tainacan', 'wordpress', 'php']
				]
			),
			true
		);

		$sb_meta = new Item_Metadata_Entity($this->item, $selectbox_metadata);
		$sb_meta->set_value('tainacan');
		$sb_meta->validate();
		$this->assertEquals($sb_meta->get_value_as_html(), 'tainacan');

		$sb_meta->set_value('php');
		$this->assertEquals($sb_meta->get_value_as_html(), 'php');

		$sb_meta_multi = new Item_Metadata_Entity($this->item, $selectbox_metadata_multiple);
		$sb_meta_multi->set_value(['tainacan', 'wordpress']);
		$sb_meta_multi->validate();
		$this->assertEquals($sb_meta_multi->get_value_as_html(), 'tainacan' . $this->separator . 'wordpress');
	}

	function test_relationship_metadata_html() {
		$referenced_collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			['name' => 'INXS Songs'],
			true
		);
		$mystify = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Mystify',
				'description' => '"Mystify" is the 9th track of INXS 6th album "Kick".',
				'collection'  => $referenced_collection,
				'status'      => 'publish'
			),
			true
		);
		$disappear = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Disappear',
				'description' => '"Disappear" is the second single from INXS 7th album "X".It was released in December 1990.',
				'collection'  => $referenced_collection,
				'status'      => 'publish'
			),
			true
		);
		$expected_return  = $this->relationship_expected_return($mystify->get_id(), $mystify->get_title());
		$expected_return2 = $this->relationship_expected_return($disappear->get_id(), $disappear->get_title());

		$relationship_metadata = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Relationship meta',
				'description'	=> 'My desc',
				'collection'    => $this->collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Relationship',
				'status'        => 'publish',
				'metadata_type_options' => [
					'collection_id' => $referenced_collection->get_id(),
					'search' => $referenced_collection->get_core_title_metadatum()->get_id()
				]
			),
			true
		);

		$item_metadata_relationship = new Item_Metadata_Entity($this->item, $relationship_metadata);
		$item_metadata_relationship->validate();
		$this->assertEquals($item_metadata_relationship->get_value_as_html(), '');

		$item_metadata_relationship->set_value($mystify->get_id());
		$item_metadata_relationship->validate();
		$this->assertEquals($item_metadata_relationship->get_value_as_html(), $expected_return);

		$item_metadata_relationship->set_value([$this->collection->get_id()]);
		$item_metadata_relationship->validate();
		$this->assertEquals($item_metadata_relationship->get_value_as_html(), '');

		$relationship_metadata->set_multiple('yes');
		$item_metadata_relationship->set_value([ $mystify->get_id(), $disappear->get_id() ]);
		$this->assertEquals($item_metadata_relationship->get_value_as_html(), "${expected_return}" . $this->separator . "${expected_return2}");
	}

	function test_taxonomy_metadata_html() {
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'My Taxonomy test',
				'collections' => [$this->collection],
				'status' => 'publish'
			),
			true
		);

		$term = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'first term from my tax',
			),
			true
		);

		$term2 = $this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $taxonomy->get_db_identifier(),
				'name'     => 'Second term from my tax',
			),
			true
		);

		$taxonomy_meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'My tax meta',
				'status' => 'publish',
				'collection' => $this->collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'taxonomy_id' => $taxonomy->get_id(),
				]
			),
			true
		);

		$item_taxonomy_metadata = new Item_Metadata_Entity($this->item, $taxonomy_meta);
		$item_taxonomy_metadata->set_value('');
		$item_taxonomy_metadata->validate();
		$this->assertEmpty($item_taxonomy_metadata->get_value_as_html());
		
		$item_taxonomy_metadata->set_value($term);
		$item_taxonomy_metadata->validate();
		$term_id = $term->get_id();
		$link = get_term_link( (int) $term_id );
		$this->assertEquals($item_taxonomy_metadata->get_value_as_html(), "<a data-linkto='term' data-id='{$term_id}' href='{$link}'>first term from my tax</a>");

		$taxonomy_meta->set_multiple('yes');
		$item_taxonomy_metadata->set_value([ $term, $term2 ]);
		$item_taxonomy_metadata->validate();
		$term_id = $term->get_id();
		$term_id2 = $term2->get_id();
		$link = get_term_link( (int) $term_id );
		$link2 = get_term_link( (int) $term_id2 );
		$this->assertEquals($item_taxonomy_metadata->get_value_as_html(), "<a data-linkto='term' data-id='{$term_id}' href='{$link}'>first term from my tax</a><span class=\"multivalue-separator\"> | </span><a data-linkto='term' data-id='{$term_id2}' href='{$link2}'>Second term from my tax</a>");
	}

	function test_compound_metadata_html() {
		$compound_meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name' => 'My compound Meta',
				'status'        => 'publish',
				'collection'    => $this->collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
			),
			true
		);
		$text_meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'My Text meta',
				'status'        => 'publish',
				'collection'    => $this->collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
				'parent'        => $compound_meta->get_id()
			),
			true
		);
		$textarea_meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'My Textarea meta!',
				'status'        => 'publish',
				'collection'    => $this->collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Textarea',
				'parent'        => $compound_meta->get_id()
			),
			true
		);
		$item_compound_metadata = new Item_Metadata_Entity($this->item, $compound_meta);
		$item_compound_metadata->set_value(['invalid meta value!']);
		$this->assertEquals($item_compound_metadata->get_value_as_html(), "<div class='tainacan-compound-group'>  </div>");
	}

	private function relationship_expected_return($id, $title) {
		$URL = get_permalink($id);
		
		return "<a data-linkto='item' data-id='${id}' href='${URL}'>${title}</a>";
	}

	function test_multiple_with_cardinality() {
		// Multiple numeric metadata
		$metadatum_numeric_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Numeric important meta',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Numeric',
				'status'        => 'publish',
				'multiple'      => 'yes',
				'cardinality'   => 3
			),
			true
		);

		$item_metadata_numeric_mult = new Item_Metadata_Entity($this->item, $metadatum_numeric_multiple);
		$item_metadata_numeric_mult->set_value([10,22,4]);
		$ok = $item_metadata_numeric_mult->validate();
		$this->assertTrue($ok);

		$item_metadata_numeric_mult->set_value([10,22,4,4]);
		$fail = $item_metadata_numeric_mult->validate();
		$this->assertFalse($fail);

		$metadatum_numeric_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Numeric important meta',
				'description'   => 'and its description',
				'collection_id' => $this->collection->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Numeric',
				'status'        => 'publish',
				'multiple'      => 'yes',
				'cardinality'   => 0
			),
			true
		);

		$item_metadata_numeric_mult = new Item_Metadata_Entity($this->item, $metadatum_numeric_multiple);
		$item_metadata_numeric_mult->set_value([10,22,4]);
		$ok = $item_metadata_numeric_mult->validate();
		$this->assertTrue($ok);

		$item_metadata_numeric_mult->set_value([10,22,4,4]);
		$fail = $item_metadata_numeric_mult->validate();
		$this->assertTrue($fail);
	}
}