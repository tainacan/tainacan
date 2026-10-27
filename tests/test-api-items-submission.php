<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Items_Submission extends TAINACAN_UnitApiTestCase {
	private $col_user_anonymous;
	private $col_user_logged;
	private $collections_metadatum;
	
	function setUp(): void {
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

	function tearDown(): void {
		$this->remove_added_uploads();
		parent::tearDown();
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

	public function test_submission_item_finish_with_files() {
		$collection_id = $this->col_user_logged->get_id();
		$metadatums    = $this->collections_metadatum[ $collection_id ];

		$item_json = wp_json_encode(
			array(
				'title'       => 'Item submission with files',
				'description' => 'Item sent by submission with document, thumbnail and attachments',
				'metadata'    => array(
					array(
						'metadatum_id' => $metadatums['text'],
						'value'        => 'Text submission',
					),
					array(
						'metadatum_id' => $metadatums['numeric'],
						'value'        => 10,
					),
				),
			)
		);

		$request = new \WP_REST_Request( 'POST', $this->namespace . '/collection/' . $collection_id . '/items/submission' );
		$request->set_body( $item_json );
		$response = $this->server->dispatch( $request );
		$this->assertEquals( 201, $response->get_status() );

		$submission_data = $response->get_data();
		$submission_id   = $submission_data['id'];

		$doc_tmp   = sys_get_temp_dir() . '/document-file.jpg';
		$thumb_tmp = sys_get_temp_dir() . '/custom-thumb.jpg';
		$attach_b  = sys_get_temp_dir() . '/beta-attachment.jpg';
		$attach_a  = sys_get_temp_dir() . '/alpha-attachment.jpg';

		copy( './tests/attachment/tainacan.jpg', $doc_tmp );
		copy( './tests/attachment/codeispoetrywp.jpg', $thumb_tmp );
		copy( './tests/attachment/codeispoetrywp.jpg', $attach_b );
		copy( './tests/attachment/tainacan.jpg', $attach_a );

		$finish_request = new \WP_REST_Request(
			'POST',
			$this->namespace . '/collection/' . $collection_id . '/items/submission/' . $submission_id . '/finish'
		);
		$finish_request->set_file_params(
			array(
				'document'    => array(
					'name'     => 'document-file.jpg',
					'type'     => 'image/jpeg',
					'tmp_name' => $doc_tmp,
					'error'    => 0,
					'size'     => filesize( $doc_tmp ),
				),
				'thumbnail'   => array(
					'name'     => 'custom-thumb.jpg',
					'type'     => 'image/jpeg',
					'tmp_name' => $thumb_tmp,
					'error'    => 0,
					'size'     => filesize( $thumb_tmp ),
				),
				'attachments' => array(
					'name'     => array( 'beta-attachment.jpg', 'alpha-attachment.jpg' ),
					'type'     => array( 'image/jpeg', 'image/jpeg' ),
					'tmp_name' => array( $attach_b, $attach_a ),
					'error'    => array( 0, 0 ),
					'size'     => array( filesize( $attach_b ), filesize( $attach_a ) ),
				),
			)
		);

		$finish_response = $this->server->dispatch( $finish_request );
		$this->assertEquals( 201, $finish_response->get_status() );
		$this->assertFalse( isset( $GLOBALS['TAINACAN_UPLOADING_ATTACHMENT_TO_POST'] ) );

		$finish_data = $finish_response->get_data();
		$item        = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $finish_data['id'] );
		$this->assertInstanceOf( \Tainacan\Entities\Item::class, $item );

		$expected_item_folder = 'tainacan-items/' . $collection_id . '/_x_' . $item->get_id();

		// verify document upload, original filename/title (no uniqid prefix), and Private_Files directory
		$this->assertEquals( 'attachment', $item->get_document_type() );
		$document_id   = (int) $item->get_document();
		$document_post = get_post( $document_id );
		$this->assertNotNull( $document_post );
		$this->assertEquals( $item->get_id(), $document_post->post_parent );
		$this->assertEquals( 'document-file', $document_post->post_title );
		$this->assertEquals( 'document-file.jpg', wp_basename( get_attached_file( $document_id ) ) );
		$this->assertStringContainsString( $expected_item_folder, get_attached_file( $document_id ) );

		// verify thumbnail upload stays in standard WP uploads directory (post_parent = 0)
		$thumbnail_id   = (int) $item->get__thumbnail_id();
		$thumbnail_post = get_post( $thumbnail_id );
		$this->assertNotNull( $thumbnail_post );
		$this->assertEquals( 0, $thumbnail_post->post_parent );
		$this->assertEquals( 'custom-thumb', $thumbnail_post->post_title );
		$this->assertEquals( 'custom-thumb.jpg', wp_basename( get_attached_file( $thumbnail_id ) ) );
		$this->assertStringNotContainsString( 'tainacan-items', get_attached_file( $thumbnail_id ) );

		// verify attachments preserve original filenames/titles, are sorted alphabetically, and use Private_Files directory
		$attachments = $item->get_attachments();
		$this->assertCount( 2, $attachments );
		$this->assertEquals( 'alpha-attachment', $attachments[0]->post_title );
		$this->assertEquals( 'alpha-attachment.jpg', wp_basename( get_attached_file( $attachments[0]->ID ) ) );
		$this->assertStringContainsString( $expected_item_folder, get_attached_file( $attachments[0]->ID ) );

		$this->assertEquals( 'beta-attachment', $attachments[1]->post_title );
		$this->assertEquals( 'beta-attachment.jpg', wp_basename( get_attached_file( $attachments[1]->ID ) ) );
		$this->assertStringContainsString( $expected_item_folder, get_attached_file( $attachments[1]->ID ) );
	}

}

?>
