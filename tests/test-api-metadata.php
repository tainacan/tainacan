<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Metadata_Controller extends TAINACAN_UnitApiTestCase {

	public function test_create_metadatum_in_a_collection() {
		$collection = $this->tainacan_entity_factory->create_entity('collection', '', true);

		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection
			),
			true
		);

		$metadatum = json_encode(
			array(
				'name'        => 'Moeda',
				'description' => 'Descreve campo moeda.',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);
		$request->set_body($metadatum);

		$response = $this->server->dispatch($request);

		$metadatum_added = $response->get_data();
		$this->assertTrue(is_array($metadatum_added) && array_key_exists('name', $metadatum_added), sprintf('cannot create metadatum, response: %s', print_r($metadatum_added, true)));
		$this->assertEquals('Moeda', $metadatum_added['name']);

		$this->assertNotEquals('default', $metadatum_added['collection_id']);
	}

	public function test_fetch_a_metadatum_from_a_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$request = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/metadata/' . $metadatumA->get_id());

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals('Data', $data['name']);
		$this->assertEquals($metadatumA->get_id(), $data['id']);
	}

	public function test_create_default_metadatum(){
		$metadatum = json_encode(
			array(
				'name'        => 'Ano de Publicação',
				'description' => 'Uma data no formato dd/mm/aaaa.',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/metadata'
		);
		$request->set_body($metadatum);

		$response = $this->server->dispatch($request);
		$metadatum_added = $response->get_data();

		$this->assertTrue(is_array($metadatum_added) && array_key_exists('name', $metadatum_added), sprintf('cannot create metadatum, response: %s', print_r($metadatum_added, true)));
		$this->assertEquals('Ano de Publicação', $metadatum_added['name']);

		$this->assertEquals('default', $metadatum_added['collection_id']);
	}

	public function test_fetch_default_metadata(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data 1',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'           => 'Data 2',
				'description'    => 'Descreve valor do campo data.',
				'collection_id'  => 'default',
				'status'         => 'publish',
				'metadata_type'     => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$request_fetch_defaults = new \WP_REST_Request('GET', $this->namespace . '/metadata');

		$response_defaults = $this->server->dispatch($request_fetch_defaults);

		$data = $response_defaults->get_data();

		$this->assertCount(1, $data);

		$this->assertEquals('default', $data[0]['collection_id']);
		$this->assertEquals('Data 2', $data[0]['name']);
	}

	public function test_get_item_and_collection_metadata(){
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement',
				'status' => 'publish'
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
		$item_metadata->set_value('12/12/2017');

		$item_metadata->validate();
		$Tainacan_Item_Metadata->insert($item_metadata);

		#################### Get metadatum of collection ######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$metadata_names = array_map(function($metadatum) {return $metadatum['name'];}, $data);

		$this->assertContains('Data', $metadata_names);

		################### Get metadatum of item with value #######################

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/item/' . $item->get_id() . '/metadata'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();
		$this->assertTrue(is_array($data) && array_key_exists(0, $data), sprintf('cannot read metadatum, response: %s', print_r($data, true)));


		$metadata_names = array_map(function($item_metadata) {return $item_metadata['metadatum']['name'];}, $data);
		$values = array_map(function($item_metadata) {return $item_metadata['value'];}, $data);

        $this->assertContains('Data', $metadata_names);
        $this->assertContains('12/12/2017', $values);
	}

	public function test_update_metadata(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement',
				'status' => 'publish'
			),
			true
		);

		$item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'yes'
			),
			true
		);

		$meta_values = json_encode(
			array(
				'values' => array(
					'19/01/2018',
					'19/02/2018',
				)
			)
		);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/item/' . $item->get_id() . '/metadata/' . $metadatum->get_id()
		);
		$request->set_body($meta_values);

		$response = $this->server->dispatch($request);

		$item_metadata_updated = $response->get_data();

		$metadatum_updated = $item_metadata_updated['metadatum'];

		$this->assertEquals($metadatum->get_id(), $metadatum_updated['id']);

		$this->assertEquals('19/01/2018', $item_metadata_updated['value'][0]);
		$this->assertEquals('19/02/2018', $item_metadata_updated['value'][1]);


		#### UPDATE METADATUM IN COLLECTION ####

		$values = json_encode([
			'name'        => 'Dia/Mês/Ano',
			'description' => 'Continua descrevendo o dado do campo.'
		]);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata/' . $metadatum->get_id()
		);

		$request->set_body($values);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($metadatum->get_id(), $data['id']);
		$this->assertEquals('Dia/Mês/Ano', $data['name']);

		// Mantém-se o valor antigo no item
		$metav = get_post_meta($item->get_id(), $data['id'], true);

		$this->assertEquals('19/01/2018', $metav);

	}

	public function test_trash_metadatum_in_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Metadatum Statement',
				'description' => 'No Statement',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'yes'
			),
			true
		);

		$trash_metadatum_request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/collection/'. $collection->get_id() . '/metadata/' . $metadatum->get_id()
		);

		$trash_metadatum_response = $this->server->dispatch($trash_metadatum_request);
		$data1 = $trash_metadatum_response->get_data();

		$this->assertEquals($metadatum->get_id(), $data1['id']);

		$metadatum_trashed = get_post($data1['id']);
		$this->assertEquals('trash', $metadatum_trashed->post_status);
	}

	public function test_trash_default_metadatum(){
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadatum Statement',
				'description'   => 'No Statement',
				'collection_id' => 'default',
				'status'        => 'publish',
				'metadata_type'    => 'Tainacan\Metadata_Types\Text',
				'multiple'      => 'yes'
			),
			true
		);

		$trash_metadatum_request = new \WP_REST_Request(
			'DELETE',
			$this->namespace . '/metadata/' . $metadatum->get_id()
		);

		$trash_metadatum_response = $this->server->dispatch($trash_metadatum_request);
		$data1 = $trash_metadatum_response->get_data();

		$this->assertEquals($metadatum->get_id(), $data1['id']);

		$metadatum_trashed = get_post($data1['id']);
		$this->assertEquals('trash', $metadatum_trashed->post_status);
	}

	public function test_update_default_metadatum(){
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'Metadatum Statement',
				'description'   => 'No Statement',
				'collection_id' => 'default',
				'status'        => 'publish',
				'metadata_type'    => 'Tainacan\Metadata_Types\Text',
				'multiple'      => 'no'
			),
			true
		);

		$new_attributes = json_encode([
			'name'        => 'No name',
			'description' => 'NOP!'
		]);

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/metadata/' . $metadatum->get_id()
		);

		$request->set_body($new_attributes);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($metadatum->get_id(), $data['id']);
		$this->assertEquals('No name', $data['name']);
	}

	public function test_return_metadata_type_options_in_get_item() {

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$core1 = $collection1->get_core_title_metadatum();

		$meta_relationship = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'relationship',
				'status' => 'publish',
				'collection' => $collection2,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'metadata_type_options' => [
					'repeated' => 'yes',
					'collection_id' => $collection1->get_id(),
					'search' => $core1->get_id()
				]
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/metadata/' . $meta_relationship->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($meta_relationship->get_id(), $data['id']);
		$this->assertEquals('relationship', $data['name']);
		$this->assertEquals('yes', $data['metadata_type_options']['repeated']);
		$this->assertEquals($collection1->get_id(), $data['metadata_type_options']['collection_id']);
		$this->assertEquals($core1->get_id(), $data['metadata_type_options']['search']);

	}

	public function test_return_metadata_type_options_in_get_items() {

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$collection2 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$core1 = $collection1->get_core_title_metadatum();

		$meta_relationship = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'relationship',
				'status' => 'publish',
				'collection' => $collection2,
				'metadata_type'  => 'Tainacan\Metadata_Types\Relationship',
				'metadata_type_options' => [
					'repeated' => 'yes',
					'collection_id' => $collection1->get_id(),
					'search' => $core1->get_id()
				]
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/collection/' . $collection2->get_id() . '/metadata'
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		//var_dump($data, $this->namespace . '/collection/' . $collection2->get_id() . '/metadata/');
		foreach ($data as $d) {
			if ($d['id'] == $meta_relationship->get_id()) {
				$meta = $d;
				break;
			}
		}

		$this->assertEquals($meta_relationship->get_id(), $meta['id']);
		$this->assertEquals('relationship', $meta['name']);
		$this->assertEquals('yes', $meta['metadata_type_options']['repeated']);
		$this->assertEquals($collection1->get_id(), $meta['metadata_type_options']['collection_id']);
		$this->assertEquals($core1->get_id(), $meta['metadata_type_options']['search']);

	}

	public function test_return_metadata_type_options_in_get_item_default_option() {

		$collection1 = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test',
				'collections' => [$collection1],
				'status' => 'publish'
			),
			true
		);

		$meta = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'tax',
				'status' => 'publish',
				'collection' => $collection1,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
				]
			),
			true
		);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/metadata/' . $meta->get_id()
		);

		$response = $this->server->dispatch($request);

		$data = $response->get_data();

		$this->assertEquals($meta->get_id(), $data['id']);
		$this->assertEquals('tax', $data['name']);
		$this->assertEquals($tax->get_id(), $data['metadata_type_options']['taxonomy_id']);
		$this->assertEquals('no', $data['metadata_type_options']['allow_new_terms']);

	}

	public function test_update_taxonomy_metadata() {
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'   => 'test_col',
				'status' => 'publish'
			),
			true
		);

		$tax = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name'   => 'tax_test',
				'collections' => [$collection],
				'status' => 'publish'
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax->get_db_identifier(),
				'name'     => 'Rock',
				'user'     => 56
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax->get_db_identifier(),
				'name'     => 'Samba',
				'user'     => 56
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'   => 'tax',
				'status' => 'publish',
				'collection' => $collection,
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'metadata_type_options' => [
					'taxonomy_id' => $tax->get_id(),
				]
			),
			true
		);

		$i1 = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'item teste',
				'description' => 'adasdasdsa',
				'collection'  => $collection
			),
			true
		);

		$itemMeta1 = new \Tainacan\Entities\Item_Metadata_Entity($i1, $metadatum);
		$itemMeta1->set_value('Rock');
		$itemMeta1->validate();
		$Tainacan_Item_Metadata->insert($itemMeta1);

		$request = new \WP_REST_Request(
			'GET',
			$this->namespace . '/item/' . $i1->get_id() . '/metadata/' . $metadatum->get_id()
		);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertEquals(false, empty($data['value']));

		$request = new \WP_REST_Request(
			'PATCH',
			$this->namespace . '/item/' . $i1->get_id() . '/metadata/' . $metadatum->get_id()
		);
		$attributes = json_encode(['values' => '']);
		$request->set_body($attributes);
		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertEquals(true, empty($data['value']));
	}

	public function test_visibility_the_metadatum_from_in_collection(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement'
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'private',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		wp_logout();
		wp_set_current_user(0);

		$requestA = new \WP_REST_Request('GET', $this->namespace . '/metadata/' . $metadatumA->get_id());
		$requestB = new \WP_REST_Request('GET', $this->namespace . '/metadata/' . $metadatumB->get_id());

		$response = $this->server->dispatch($requestA);
		$status = $response->status;
		$this->assertEquals(200, $status);

		$response = $this->server->dispatch($requestB);
		$status = $response->status;
		$this->assertEquals(401, $status);
	}

	public function test_private_filter_ids_not_in_metadata_list(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement',
				'status'      => 'publish',
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'private',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		wp_logout();
		wp_set_current_user(0);

		$requestA = new \WP_REST_Request('GET', $this->namespace . '/metadata/' . $metadatumA->get_id());
		$requestB = new \WP_REST_Request('GET', $this->namespace . '/metadata/' . $metadatumB->get_id());
		$requestC = new \WP_REST_Request('GET', $this->namespace . '/collection/' . $collection->get_id() . '/metadata');

		$response = $this->server->dispatch($requestA);
		$status = $response->status;
		$this->assertEquals(200, $status);

		$response = $this->server->dispatch($requestB);
		$status = $response->status;
		$this->assertEquals(401, $status);

		$response = $this->server->dispatch($requestC);
		$data = $response->get_data();
		$this->assertEquals(3, count($data));
		$this->assertNotEquals($metadatumB->get_id(), $data[0]['id']);
		$this->assertNotEquals($metadatumB->get_id(), $data[1]['id']);
		$this->assertNotEquals($metadatumB->get_id(), $data[2]['id']);
	}

	public function test_private_meta_ids_not_in_metadata_order(){
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Statement',
				'description' => 'No Statement',
				'status'      => 'publish',
			),
			true
		);

		$metadatumA = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$metadatumB = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data',
				'description' => 'Descreve valor do campo data.',
				'collection'  => $collection,
				'status'      => 'private',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
			), true
		);

		$order = array();

		$metas = $collection->get_metadata();

		foreach ( $metas as $m ) {
			$order[] = [
				'id' => $m->get_id(),
				'enabled' => true,
			];
		}

		$collection->set_metadata_order($order);
		$collection->validate();
		\tainacan_collections()->insert($collection);



		$request = new \WP_REST_Request('GET', $this->namespace . '/collections/' . $collection->get_id());

		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertEquals(4, count($data['metadata_order']));

		wp_logout();
		wp_set_current_user(0);

		$request = new \WP_REST_Request('GET', $this->namespace . '/collections/' . $collection->get_id());

		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertEquals(3, count($data['metadata_order']));
		$this->assertNotEquals($metadatumB->get_id(), $data['metadata_order'][0]['id']);
		$this->assertNotEquals($metadatumB->get_id(), $data['metadata_order'][1]['id']);
		$this->assertNotEquals($metadatumB->get_id(), $data['metadata_order'][2]['id']);
	}

	/**
	 * @group compound_metadatum
	 */
	public function test_create_compound_metadatum_API() {
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection', 
			array(
				'name'   => 'quadrado', 
				'status' => 'publish'
			), 
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'No name',
				'description' => 'No description',
				'collection'  => $collection,
				'status' => 'publish'
			),
			true
		);

		$metadatum_compound = json_encode(
			array(
				'name'        => 'quadrado',
				'description' => 'Descrição de um quadrado.',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Compound',
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);
		$request->set_body($metadatum_compound);
		$response = $this->server->dispatch($request);

		$metadatum_compound_added = $response->get_data();
		$this->assertTrue(is_array($metadatum_compound_added) && array_key_exists('name', $metadatum_compound_added), sprintf('cannot create metadatum, response: %s', print_r($metadatum_compound_added, true)));
		$this->assertEquals('quadrado', $metadatum_compound_added['name']);
		$this->assertNotEquals('default', $metadatum_compound_added['collection_id']);


		$metadatum_largura = json_encode(
			array(
				'name'        => 'largura',
				'description' => 'largura.',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent' 	 => $metadatum_compound_added['id']
			)
		);
		$metadatum_altura = json_encode(
			array(
				'name'        => 'altura',
				'description' => 'altura',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent' 	 => $metadatum_compound_added['id']
			)
		);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);
		$request->set_body($metadatum_largura);
		$response = $this->server->dispatch($request);
		$metadatum_largura = $response->get_data();

		$this->assertTrue(is_array($metadatum_largura) && array_key_exists('name', $metadatum_largura), sprintf('cannot create metadatum, response: %s', print_r($metadatum_largura, true)));
		$this->assertEquals('largura', $metadatum_largura['name']);
		$this->assertNotEquals('default', $metadatum_largura['collection_id']);

		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);
		$request->set_body($metadatum_altura);
		$response = $this->server->dispatch($request);
		$metadatum_altura = $response->get_data();

		$this->assertTrue(is_array($metadatum_altura) && array_key_exists('name', $metadatum_altura), sprintf('cannot create metadatum, response: %s', print_r($metadatum_altura, true)));
		$this->assertEquals('altura', $metadatum_altura['name']);
		$this->assertNotEquals('default', $metadatum_altura['collection_id']);


		$metadatum_multiplo = json_encode(
			array(
				'name'        => 'multiplo',
				'description' => 'multiplo.',
				'multiple' => 'yes',
				'status' => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
				'parent' 	 => $metadatum_compound_added['id']
			)
		);
		$request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection->get_id() . '/metadata'
		);
		$request->set_body($metadatum_multiplo);
		$response = $this->server->dispatch($request);
		$metadatum_multiplo_data = $response->get_data();
		$this->assertEquals(400, $response->get_status(), sprintf('cannot create metadatum, response: %s', print_r($metadatum_multiplo_data, true)) );
	}

}
