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
				'submission_anonymous_user' => true
			],
			true
		);

		$this->col_user_logged = $this->tainacan_entity_factory->create_entity(
			'collection',
			[
				'name'        => 'Col-2',
				'description' => 'Col-2',
				'status'      => 'publish',
				'submission_anonymous_user' => false
			],
			true
		);

		$this->collections_metadatum = array(
			$this->col_user_anonymous->get_id() => $this->create_metadatum($this->col_user_anonymous),
			$this->col_user_logged->get_id() => $this->create_metadatum($this->col_user_logged)
		);
	}

	private function create_metadatum(&$collection) {
		$metadatum = array(
			'text' => $this->tainacan_entity_factory->create_entity(
						'metadatum',
						array(
							'name'   => 'text',
							'status' => 'publish',
							'collection' => $collection,
							'metadata_type'  => 'Tainacan\Metadata_Types\Text',
						),
						true
					)->get_id(),

			'numeric' => $this->tainacan_entity_factory->create_entity(
						'metadatum',
						array(
							'name'   => 'numeric',
							'status' => 'publish',
							'collection' => $collection,
							'metadata_type'  => 'Tainacan\Metadata_Types\Numeric',
						),
						true
					)->get_id()
		);
		return $metadatum;
	}

	public function test_submission_item_user_anonymous() {
		wp_logout();
		$this->assertEquals($this->col_user_anonymous->get_submission_anonymous_user(), true);
		$this->assertEquals($this->col_user_logged->get_submission_anonymous_user(), false);

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

		$request  = new \WP_REST_Request('POST', $this->namespace . '/collection/' . $this->col_user_logged->get_id() . '/items/submission');
		$response = $this->server->dispatch($request);
		$this->assertEquals(401, $response->get_status());
	}

	public function test_submission_item_user_logged() {
		// echo "user_id" . $this->user_id;
		$this->assertEquals($this->col_user_anonymous->get_submission_anonymous_user(), true);
		$this->assertEquals($this->col_user_logged->get_submission_anonymous_user(), false);

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
