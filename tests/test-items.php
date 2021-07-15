<?php

namespace Tainacan\Tests;

/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

use Tainacan\Entities;

/**
 * Sample test case.
 */
class Items extends TAINACAN_UnitTestCase {

	/**
	 * @group permissions
	 */
	public function test_permissions () {

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'testePerm',
				'status' => 'publish'
			),
			true
		);
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
			),
			true
		);
		$this->assertTrue($item->can_read(), 'Administrator cannot read the Item');
		$this->assertTrue($item->can_edit(), 'Administrator cannot edit the Item');

		// another administrator should be able to edit items
		$new_admin = $this->factory()->user->create(array( 'role' => 'administrator' ));
		wp_set_current_user($new_admin);

		$this->assertTrue($item->can_read(), 'Administrator cannot read the Item');
		$this->assertTrue($item->can_edit(), 'Administrator cannot edit the Item');
		$this->assertTrue(current_user_can($collection->get_items_capabilities()->edit_post, $item->get_id()), 'Administrator cannot edit an item!');
	}

	function test_query(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'teste',
				'status' => 'publish'
			),
			true
		);

		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'teste2',
				'status' => 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadado',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadado2',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum3 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'metadado3',
				'status'            => 'publish',
				'collection'        => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'orange',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum, 'value_1');

		$item = $Tainacan_Items->fetch($i->get_id());
		$meta_test = new Entities\Item_Metadata_Entity($item, $metadatum);
		$this->assertTrue( $meta_test instanceof Entities\Item_Metadata_Entity );
		$this->assertEquals( $metadatum->get_id(), $meta_test->get_metadatum()->get_id() );
		$this->assertEquals( 'value_1', $meta_test->get_value());

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'        => 'apple',
				'collection'   => $collection2,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum3, 'value_2');

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'        => 'lemon',
				'collection'   => $collection2,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum2, 'value_2');
		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum2, 'value_3');
		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum3, 'value_3');

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'        => 'pineapple',
				'collection'   => $collection2,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum2, 'value_3');
		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum3, 'value_6');

		// should return all 4 items
		$test_query = $Tainacan_Items->fetch([]);
		$this->assertEquals(4, $test_query->post_count );

		// should also return all 4 items
		$test_query = $Tainacan_Items->fetch([], [$collection, $collection2]);
		$this->assertEquals(4, $test_query->post_count);

		// should return 2 items
		$test_query = $Tainacan_Items->fetch(['posts_per_page' => 2], [$collection, $collection2]);
		$this->assertEquals(2, $test_query->post_count);

		// should return only the first item
		$test_query = $Tainacan_Items->fetch([], $collection);
		$this->assertEquals(1,$test_query->post_count);

		$test_query->the_post();
		$item1 = new Entities\Item( get_the_ID() );
		$this->assertEquals('orange', $item1->get_title() );

		$test_query = $Tainacan_Items->fetch(['title' => 'orange']);
		$test_query->the_post();
		$item2 = new Entities\Item( get_the_ID() );

		$this->assertEquals(1, $test_query->post_count);
		$this->assertEquals('orange', $item2->get_title());

		// should return the other 3 items
		$test_query = $Tainacan_Items->fetch([], $collection2);
		$this->assertEquals(3,$test_query->post_count);

		$test_query = $Tainacan_Items->fetch(['title' => 'apple']);
		$test_query->the_post();
		$item3 = new Entities\Item( get_the_ID() );

		$this->assertEquals(1, $test_query->post_count);
		$this->assertEquals('apple', $item3->get_title());

		// should return 1 item
		$test_query = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $metadatum2->get_id(),
					'value' => 'value_3'
				]
			]
		], $collection2);
		$this->assertEquals(2, $test_query->post_count);

		// should return 2 items
		$test_query = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $metadatum2->get_id(),
					'value' => 'value_3'
				]
			]
		], $collection2);
		$this->assertEquals(2, $test_query->post_count);

		// should return 2 items
		$test_query = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $metadatum3->get_id(),
					'value' => 'value_2',
					'compare' => '>'
				]
			]
		], $collection2);
		$this->assertEquals(2, $test_query->post_count);

		// test fetch ids
		$test_query = $Tainacan_Items->fetch_ids([]);
		$this->assertTrue( is_array($test_query) );
		$this->assertEquals(4, sizeof($test_query) );
		$this->assertTrue( is_int($test_query[0]) );
		$this->assertTrue( is_int($test_query[1]) );
		$this->assertTrue( is_int($test_query[2]) );
		$this->assertTrue( is_int($test_query[3]) );

		$test_query = $Tainacan_Items->fetch_ids(['title' => 'inexistent']);
		$this->assertTrue( is_array($test_query) );
		$this->assertEquals(0, sizeof($test_query) );

	}

	function test_meta_query_in(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'teste',
				'status' => 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'metadado',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'teste10',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum, 'value_10');

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'test100',
				'collection' => $collection,
				'status'      => 'publish'
			),
			true
		);

		$this->tainacan_item_metadata_factory->create_item_metadata($i, $metadatum, 'value_100');

		// should return 1 items
		$test_query = $Tainacan_Items->fetch([
			'meta_query' => [
				[
					'key' => $metadatum->get_id(),
					'value' => ['value_10'],
					'compare' => 'IN'
				]
			]
		], $collection);
		$this->assertEquals(1, $test_query->post_count);


	}

	/**
	 * @group comments
	 */
	public function test_items_comment() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'collectionComments',
				'allow_comments' => 'closed'
			),
			true,
			true
		);
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'itemComments1',
				'collection' => $collection,
				'comment_status' => 'open'
			),
			true,
			true
		);
		global $wp_query;

		$wp_query = new \WP_Query();

		$this->assertTrue(setup_postdata($item->WP_Post));

		$this->assertFalse(comments_open($item->get_id()));

		$collections = \Tainacan\Repositories\Collections::get_instance();
		$collection->set('allow_comments', 'open');
		$this->assertTrue($collection->validate());
		$collections->update($collection);

		$this->assertTrue(comments_open($item->get_id()));

		$items = \Tainacan\Repositories\Items::get_instance();

		$item->set('comment_status', 'closed');
		$this->assertTrue($item->validate());
		$items->update($item);

		$this->assertFalse(comments_open($item->get_id()));
	}

	public function test_delete_item() {

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'collectionComments',
				'allow_comments' => 'closed'
			),
			true,
			true
		);
		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'itemComments1',
				'collection' => $collection,
				'comment_status' => 'open'
			),
			true,
			true
		);

		$item_id = $item->get_id();

		$items = \Tainacan\Repositories\Items::get_instance();

		$items->delete($item);

		$fetch_item = $items->fetch($item_id);

		$this->assertEmpty($fetch_item);

	}

	function test_update_collection() {

		$ItemRepo = \Tainacan\Repositories\Items::get_instance();
		$ColRepo = \Tainacan\Repositories\Collections::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste1',
				'description'   => 'adasdasdsa',
				'status' => 'publish'
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
				'status' => 'publish'
			),
			true
		);

		$collection->set_status('private');
		$collection->validate();
		$collection = $ColRepo->insert($collection);

		$this->assertEquals('private', $item->get_collection()->get_status());


	}

	function test_order_by_date_same_date() {

		$date = '2019-10-10 10:10:10';

		$ItemRepo = \Tainacan\Repositories\Items::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste1',
				'description'   => 'adasdasdsa',
				'status' => 'publish'
			),
			true
		);

		$item1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
				'status' => 'publish',
				'creation_date' => $date
			),
			true
		);
		$item2 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
				'status' => 'publish',
				'creation_date' => $date
			),
			true
		);
		$item3 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
				'status' => 'publish',
				'creation_date' => $date
			),
			true
		);
		$item4 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
				'status' => 'publish',
				'creation_date' => $date
			),
			true
		);
		$item5 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'      => 'testeItem',
				'collection' => $collection,
				'status' => 'publish',
				'creation_date' => $date
			),
			true
		);



		// should always return in the same order
		for ($i=1; $i<20; $i++) {
			$items = $ItemRepo->fetch([], $collection, 'OBJECT');

			$this->assertEquals($item5->get_id(), $items[0]->get_id());
			$this->assertEquals($item1->get_id(), $items[4]->get_id());

		}

		// also when ordering by metadata
		for ($i=1; $i<20; $i++) {
			$items = $ItemRepo->fetch([
				'meta_key' => 'collection_id',
				'orderby' => 'meta_value'
			], $collection, 'OBJECT');

			$this->assertEquals($item5->get_id(), $items[0]->get_id());
			$this->assertEquals($item1->get_id(), $items[4]->get_id());

		}


	}

	function test_item_blurhash() {
		$orig_file = './tests/attachment/tainacan.jpg';
		$test_file = '/tmp/tainacan.jpg';
		copy( $orig_file, $test_file );
		$blurhash = \Tainacan\Media::get_instance()->get_image_blurhash($test_file, 40,40);
		$this->assertContains($blurhash, ['V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj', 'VATI:i~qNG~WNG~qNGxaNGt6M|xaNGxaRk~WNGxaR*s:']);
	}

}
