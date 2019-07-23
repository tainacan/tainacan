<?php

namespace Tainacan\Tests;

/**
 * @group api
 */
class TAINACAN_REST_Private_Files extends TAINACAN_UnitApiTestCase {


	
	public function test_create_item() {
		
		$orig_file       = './tests/attachment/codeispoetrywp.jpg';
		$this->test_file = '/tmp/codeispoetrywp.jpg';
		copy( $orig_file, $this->test_file );
		
		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
				'status'      => 'publish'
			),
			true
		);
		
		$this->item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $this->collection,
				'status'      => 'publish'
			),
			true
		);
		
		$request = new \WP_REST_Request( 'POST', '/wp/v2/media' );
		$request->set_header( 'Content-Type', 'image/jpeg' );
		$request->set_header( 'Content-Disposition', 'attachment; filename=codeispoetrywp.jpg' );
		$request->set_param( 'post', $this->item->get_id() );
		
		global $TAINACAN_UPLOADING_ATTACHMENT_TO_POST;
		$TAINACAN_UPLOADING_ATTACHMENT_TO_POST = $this->item->get_id();
		
		$request->set_body( file_get_contents( $this->test_file ) );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		
		unset($TAINACAN_UPLOADING_ATTACHMENT_TO_POST);
		
		$this->assertEquals( 201, $response->get_status() );
		$this->assertEquals( 'image', $data['media_type'] );

		$attachment = get_post( $data['id'] );
		
		$this->assertEquals( $this->item->get_id(), $attachment->post_parent );
		
		$attachment_data = wp_get_attachment_metadata($attachment->ID);
		
		$folder = 'tainacan-items/' . $this->collection->get_id() . '/' . $this->item->get_id();
		
		$this->assertContains( $folder, $attachment_data['file'] );

		
	}
	
	function test_internal_api() {
		
		$orig_file       = './tests/attachment/codeispoetrywp.jpg';
		$this->test_file = '/tmp/codeispoetrywp.jpg';
		copy( $orig_file, $this->test_file );
		
		$this->collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'        => 'Agile',
				'description' => 'Agile methods',
				'status'      => 'publish'
			),
			true
		);
		
		$this->item = $this->tainacan_entity_factory->create_entity(
			'item',
			array(
				'title'       => 'Lean Startup',
				'description' => 'Um processo ágil de criação de novos negócios.',
				'collection'  => $this->collection,
				'status'      => 'publish'
			),
			true
		);
		
		$attachment_id = \Tainacan\Media::get_instance()->insert_attachment_from_file($this->test_file, $this->item->get_id());
		
		$attachment_data = wp_get_attachment_metadata($attachment_id);
		
		$folder = 'tainacan-items/' . $this->collection->get_id() . '/' . $this->item->get_id();
		
		$this->assertContains( $folder, $attachment_data['file'] );
		
	}

}

?>