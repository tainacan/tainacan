<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Items_Submission extends TAINACAN_UnitApiTestCase {

	function setUp() {
		parent::setUp();

		// collections:
		$this->col_user_anonymous = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'Col-1',
				'description' => 'Col-1',
				'status'      => 'publish',
				'submission_anonymous_user' => 'yes',
				'allows_submission' => 'yes'
			],
			true
		);

		$this->col_user_logged = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'Col-2',
				'description' => 'Col-2',
				'status'      => 'publish',
				'submission_anonymous_user' => 'no',
				'allows_submission' => 'yes'
			],
			true
		);

		$this->collections_metadatum = array(
			$this->col_user_anonymous->get_id() => $this->create_metadatum($this->col_user_anonymous),
			$this->col_user_logged->get_id() => $this->create_metadatum($this->col_user_logged)
		);
	}

	private function create_metadatum(&$collection) {
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
				'name'     => 'term-1',
			),
			true
		);

		$this->tainacan_entity_factory->create_entity(
			'term',
			array(
				'taxonomy' => $tax->get_db_identifier(),
				'name'     => 'term-2',
			),
			true
		);

		$metadatum_compound = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadatum_compound',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
			),
			true
		);

		$metadatum_child1 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadatum_child1',
				'status'        => 'publish',
				'collection'    => $collection,
				'parent'        => $metadatum_compound->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_child2 = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadatum_child2',
				'status'        => 'publish',
				'collection'    => $collection,
				'parent'        => $metadatum_compound->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_compound_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadatum_compound_multiple',
				'status'        => 'publish',
				'collection'    => $collection,
				'metadata_type' => 'Tainacan\Metadata_Types\Compound',
				'multiple'      => 'yes'
			),
			true
		);

		$metadatum_child1_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadatum_child1_multiple',
				'status'        => 'publish',
				'collection'    => $collection,
				'parent'        => $metadatum_compound_multiple->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum_child2_multiple = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'          => 'metadatum_child2_multiple',
				'status'        => 'publish',
				'collection'    => $collection,
				'parent'        => $metadatum_compound_multiple->get_id(),
				'metadata_type' => 'Tainacan\Metadata_Types\Text',
			),
			true
		);

		$metadatum = array(
			'text' => $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'          => 'text',
					'status'        => 'publish',
					'collection'    => $collection,
					'metadata_type' => 'Tainacan\Metadata_Types\Text',
				),
				true
			)->get_id(),
			
			'textarea' => $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'          => 'textarea',
					'status'        => 'publish',
					'collection'    => $collection,
					'metadata_type' => 'Tainacan\Metadata_Types\Textarea',
				),
				true
			)->get_id(),

			'numeric' => $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'          => 'numeric',
					'status'        => 'publish',
					'collection'    => $collection,
					'metadata_type' => 'Tainacan\Metadata_Types\Numeric',
				),
				true
			)->get_id(),

			'date' => $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'          => 'date',
					'status'        => 'publish',
					'collection'    => $collection,
					'metadata_type' => 'Tainacan\Metadata_Types\Date',
				),
				true
			)->get_id(),

			'selectbox' => $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'          => 'selectbox',
					'status'        => 'publish',
					'collection'    => $collection,
					'metadata_type' => 'Tainacan\Metadata_Types\Selectbox',
					'metadata_type_options' => [
						'options' => ['op1', 'op2', 'op3']
					]
				),
				true
			)->get_id(),

			'taxonomy' => $this->tainacan_entity_factory->create_entity(
				'metadatum',
				array(
					'name'          => 'taxonomy',
					'status'        => 'publish',
					'collection'    => $collection,
					'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
					'metadata_type_options' => [
						'taxonomy_id' => $tax->get_id(),
						'allow_new_terms' => 'no'
					],
				),
				true
			)->get_id(),

			'compound' => [
				'parent' => $metadatum_compound->get_id(),
				'childrens' => [
					$metadatum_child1->get_id(),
					$metadatum_child2->get_id()
				]
			],

			'compound_multiple' => [
				'parent' => $metadatum_compound_multiple->get_id(),
				'childrens' => [
					$metadatum_child1_multiple->get_id(),
					$metadatum_child2_multiple->get_id()
				]
			]
		);
		//relationship?
		//user?
		return $metadatum;
	}

	public function test_submission_item_user_anonymous() {
		wp_logout();
		$this->assertEquals($this->col_user_anonymous->get_submission_anonymous_user(), 'yes');
		$this->assertEquals($this->col_user_logged->get_submission_anonymous_user(), 'no');

		$metadatums = $this->collections_metadatum[$this->col_user_anonymous->get_id()];

		$item_json = json_encode([
			'title'       => 'Item submission',
			'description' => 'one item send by submission',
			'metadata'    => array(
				[
					'metadatum_id' => $metadatums['text'],
					'value' => 'Text submission'
				],
				[
					'metadatum_id' => $metadatums['textarea'],
					'value' => 'textarea submission'
				],
				[
					'metadatum_id' => $metadatums['date'],
					'value' => '2020-12-31'
				],
				[
					'metadatum_id' => $metadatums['numeric'],
					'value' => 10
				],
				[
					'metadatum_id' => $metadatums['selectbox'],
					'value' => 'op2'
				],
				[
					'metadatum_id' => $metadatums['taxonomy'],
					'value' => 'term-1'
				],
				[
					'metadatum_id' => $metadatums['compound']['parent'],
					'value' => [
						['metadatum_id' => $metadatums['compound']['childrens'][0], 'value' => 'metadatum_child1'],
						['metadatum_id' => $metadatums['compound']['childrens'][1], 'value' => 'metadatum_child2']
					]
				],
				[
					'metadatum_id' => $metadatums['compound_multiple']['parent'],
					'value' => [
						[
							['metadatum_id' => $metadatums['compound_multiple']['childrens'][0], 'value' => 'metadatum_child1_multiple_row_1'],
							['metadatum_id' => $metadatums['compound_multiple']['childrens'][1], 'value' => 'metadatum_child2_multiple_row_1']
						],
						[
							['metadatum_id' => $metadatums['compound_multiple']['childrens'][0], 'value' => 'metadatum_child1_multiple_row_2'],
							['metadatum_id' => $metadatums['compound_multiple']['childrens'][1], 'value' => 'metadatum_child2_multiple_row_2']
						]
						
					]
				]
			)
		]);

		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $this->col_user_anonymous->get_id() . '/items/submission');
		$request->set_body($item_json);

		$response = $this->server->dispatch($request);
		$this->assertEquals(201, $response->get_status());
		$data = $response->get_data();

		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $this->col_user_logged->get_id() . '/items/submission');
		$response = $this->server->dispatch($request);
		$this->assertEquals(401, $response->get_status());
	}

	public function test_submission_item_user_logged() {
		// echo "user_id" . $this->user_id;
		$this->assertEquals($this->col_user_anonymous->get_submission_anonymous_user(), 'yes');
		$this->assertEquals($this->col_user_logged->get_submission_anonymous_user(), 'no');

		$metadatums = $this->collections_metadatum[$this->col_user_anonymous->get_id()];
		$item_json = json_encode([
			'title'       => 'Item submission',
			'description' => 'one item send by submission',
			'metadata'    => array(
				[
					'metadatum_id' => $metadatums['text'],
					'value' => 'Text submission'
				],
				[
					'metadatum_id' => $metadatums['numeric'],
					'value' => 10
				]
			)
		]);

		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $this->col_user_anonymous->get_id() . '/items/submission');
		$request->set_body($item_json);

		$response = $this->server->dispatch($request);
		$this->assertEquals(201, $response->get_status());
		$data = $response->get_data();


		$metadatums = $this->collections_metadatum[$this->col_user_logged->get_id()];
		$item_json = json_encode([
			'title'       => 'Item submission',
			'description' => 'one item send by submission',
			'metadata'    => array(
				[
					'metadatum_id' => $metadatums['text'],
					'value' => 'Text submission'
				],
				[
					'metadatum_id' => $metadatums['numeric'],
					'value' => 10
				]
			)
		]);

		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $this->col_user_logged->get_id() . '/items/submission');
		$request->set_body($item_json);
		$response = $this->server->dispatch($request);
		$this->assertEquals(201, $response->get_status());
	}

}

?>
