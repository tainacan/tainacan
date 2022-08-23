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
class Collections extends TAINACAN_UnitTestCase {

	/**
	 * @group permissions
	 */
	function test_permissions () {
		global $current_user;
		$collection_test = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeCaps',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);
		$new_user = $this->factory()->user->create(array( 'role' => 'subscriber' ));
		wp_set_current_user($new_user);
		$user_id = get_current_user_id();
		$this->assertEquals($new_user, $user_id);

		$autor1 = $this->factory()->user->create(array( 'role' => 'author' ));
		wp_set_current_user($autor1);
		$autor1_id = get_current_user_id();
		$collection_test = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeCapsOwner',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);
		$this->assertEquals($autor1_id, $collection_test->WP_Post->post_author);
		$autor2 = $this->factory()->user->create(array( 'role' => 'author', 'display_name' => 'Author2' ));
		wp_set_current_user($autor2);
		$collection_test2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'testeCapsOwner2',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);
		$autor_2_user = get_userdata( $autor2 );
		$autor_2_user->add_cap('tnc_rep_edit_collections');
		$current_user = $autor_2_user;
		$current_user_id = get_current_user_id();
		$this->assertEquals($autor2, $current_user_id);
		$this->assertFalse(current_user_can($collection_test->cap->edit_post, $collection_test->WP_Post->ID));
		$this->assertTrue(current_user_can($collection_test2->cap->edit_post, $collection_test2->WP_Post->ID));
		$this->assertFalse(user_can($autor2, $collection_test->cap->edit_post, $collection_test->WP_Post->ID));


	}

	function debug_meta($user = false)
	{
		if($user !== false) wp_set_current_user($user);
		$data = get_userdata( get_current_user_id() );

		if ( is_object( $data) ) {
			$current_user_caps = $data->allcaps;

			// print it to the screen
			echo '<pre>' .print_r($data->ID, true).'\n'.print_r($data->display_name, true).'\n'. print_r( $current_user_caps, true ) . '</pre>';
		}
	}

	/**
	 * A single example test.
	 */
	function test_add() {
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);

		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

		$this->assertEquals('Tainacan\Entities\Collection', get_class($x));

		$test = $Tainacan_Collections->fetch($x->get_id());

		$this->assertEquals('teste', $test->get_name());
		$this->assertEquals('adasdasdsa', $test->get_description());
		$this->assertEquals('DESC', $test->get_default_order());
		$this->assertEquals('draft', $test->get_status());
	}

	function test_unique_slugs() {
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC',
				'slug'          => 'duplicated_slug',
				'status'        => 'publish'
			),
			true
		);

		$y = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC',
				'slug'          => 'duplicated_slug',
				'status'        => 'publish'
			),
			true
		);

		$this->assertNotEquals($x->get_slug(), $y->get_slug());

		// Create as draft and publish later
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC',
				'slug'          => 'duplicated_slug',
			),
			true
		);

		$y = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC',
				'slug'          => 'duplicated_slug',
			),
			true
		);

		$this->assertEquals($x->get_slug(), $y->get_slug());

		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$x->set_status('publish');
		$x->validate();
		$x = $Tainacan_Collections->insert($x);
		$y->set_status('private'); // or publish shoud behave the same
		$y->validate();
		$y = $Tainacan_Collections->insert($y);

		$this->assertNotEquals($x->get_slug(), $y->get_slug());

	}

	function test_item() {
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC'
			),
			true
		);

		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$collection = $Tainacan_Collections->fetch($x->get_id());

		$i = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'         => 'item test',
				'description'   => 'adasdasdsa',
				'order'         => 'DESC',
				'collection'    => $collection
			),
			true
		);

		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$item = $Tainacan_Items->fetch( $i->get_id() );

		$this->assertEquals($item->get_title(), 'item test');
		$this->assertEquals($item->get_description(), 'adasdasdsa');
		$this->assertEquals($item->get_collection_id(), $collection->get_id());

	}

	function test_validation() {
		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'teste',
				'description'   => 'adasdasdsa',
				'default_order' => 13,
				'status'        => 'publish'
			)
		);

		$this->assertFalse($x->validate());
		$this->assertTrue(sizeof($x->get_errors()) > 0);

		$x->set_default_order('ASDASD');

		$this->assertFalse($x->validate());
		$this->assertTrue(sizeof($x->get_errors()) > 0);

		$x->set_default_order('DESC');
		$this->assertTrue($x->validate());
		$this->assertTrue(empty($x->get_errors()));
	}

	function test_hooks() {
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$this->assertTrue(has_action('init', array($Tainacan_Collections, 'register_post_type')) !== false, 'Collections Init is not registred!');
	}


	function test_create_child_collection() {

		$x = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'				=> 'test',
				'description'		=> 'adasdasdsa',
				'default_order' 	=> 'DESC',
			),
			true
		);

		$col = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'				=> 'test',
				'description'		=> 'adasdasdsa',
				'default_order' 	=> 'DESC',
				'status'			=> 'auto-draft'
			),
			true
		);

		$Collections = \Tainacan\Repositories\Collections::get_instance();

		$col->set_parent($x->get_id());
		$col->set_status('publish');

		$col->validate();

		$col = $Collections->insert($col);

		$this->assertEquals($x->get_id(), $col->get_parent());

	}
}
