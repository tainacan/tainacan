<?php

namespace Tainacan\Tests;
use Tainacan\Importer;
/**
* Class Importer
*
* @group importer
* @package Test_Tainacan
*/

class ImporterTests extends TAINACAN_UnitTestCase {

	public function __construct(){
		parent::__construct();
		@session_start();
	}

	public function __destruct() {
		session_write_close();
	}
	
	/**
	 * @group importer
	 */
	public function test_file_csv () {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$file_name = 'demosaved.csv';
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$csv_importer = $Tainacan_Importer_Handler->initialize_importer('csv');
		$Tainacan_Importer_Handler->save_importer_instance($csv_importer);
		$id = $csv_importer->get_id();

		// open the file "demosaved.csv" for writing
		$file = fopen($file_name, 'w');

		// save the column headers
		fputcsv($file, array('Column 1', 'Column 2', 'Column 3', 'Column 4', 'Column 5'));

		// Sample data
		$data = array(
			array('Data 11', 'Data 12', 'Data 13', 'Data 14', 'Data 15'),
			array('Data 21', 'Data 22', 'Data 23', 'Data 24', 'Data 25'),
			array('Data 31', 'Data 32', 'Data 33', 'Data 34', 'Data 35'),
			array('Data 41', 'Data 42', 'Data 43', 'Data 44', 'Data 45'),
			array('Data 51', 'Data 52', 'Data 53', 'Data 54', 'Data 55')
		);

		// save each row of the data
		foreach ($data as $row){
			fputcsv($file, $row);
		}

		// Close the file
		fclose($file);

		$importer_instance = $Tainacan_Importer_Handler->get_importer_instance_by_session_id($id); 
		$importer_instance->set_tmp_file( $file_name );
		
		// file isset on importer
		$this->assertTrue( !empty( $importer_instance->get_tmp_file() ) );

		// count size of csv
		$this->assertEquals( 5, $importer_instance->get_source_number_of_items() );

		// get metadata to mapping
		$headers =  $importer_instance->get_source_metadata();
		$this->assertEquals( $headers[4], 'Column 5' );

		// inserting the collection
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'Other',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC',
				'status'		=> 'publish'
			),
			true
		);
		
		$collection_definition = [
			'id' => $collection->get_id(),
			'total_items' => $importer_instance->get_source_number_of_items(),
		];
		
		// get collection metadata to map
		$metadata = $Tainacan_Metadata->fetch_by_collection( $collection ) ;

		//create a random mapping
		$map = [];
		foreach ( $metadata as $index => $metadatum ){
			$map[$metadatum->get_id()] = $headers[$index];
		}
		
		$collection_definition['mapping'] = $map;

		// add the collection
		$importer_instance->add_collection( $collection_definition );

		//execute the process
		$this->assertEquals(1, $importer_instance->run(), 'first step should import 1 item');
		$this->assertEquals(2, $importer_instance->run(), 'second step should import 2 items');
		$this->assertEquals(3, $importer_instance->run(), 'third step should import 3 items');
		$this->assertEquals(4, $importer_instance->run(), 'third step should import 4 items');
		$this->assertEquals(false, $importer_instance->run(), '5 items and return false because its finished');
		$this->assertEquals(false, $importer_instance->run(), 'if call run again after finish, do nothing');

		$items = $Tainacan_Items->fetch( [], $collection, 'OBJECT' );

		$this->assertEquals( $importer_instance->get_source_number_of_items(), count( $items ) );
	}

	/**
	 * @group importer
	 */
	public function test_source_file_is_removed_when_background_import_finishes() {
		$this->assert_source_file_is_removed_when_background_import_closes( 'finished' );
	}

	/**
	 * @group importer
	 */
	public function test_source_file_is_removed_when_background_import_finishes_with_errors() {
		$this->assert_source_file_is_removed_when_background_import_closes( 'finished-errors' );
	}

	/**
	 * @group importer
	 */
	public function test_source_file_is_removed_when_background_import_errors() {
		$this->assert_source_file_is_removed_when_background_import_closes( 'errored' );
	}

	/**
	 * @group importer
	 */
	public function test_source_file_is_removed_when_background_import_is_cancelled() {
		$this->assert_source_file_is_removed_when_background_import_closes( 'cancelled', 'csv', true );
	}

	private function assert_source_file_is_removed_when_background_import_closes( $status, $importer_slug = 'csv', $cancel_via_api = false ) {
		$file_name = wp_tempnam( 'issue-1091.csv' );
		$this->assertNotFalse( $file_name );

		$file = fopen( $file_name, 'w' );
		fputcsv( $file, [ 'Name' ] );
		fputcsv( $file, [ 'Item 1' ] );
		fclose( $file );

		$handler = \Tainacan\Importer_Handler::get_instance();
		$importer = $handler->initialize_importer( $importer_slug );

		$file_array = [
			'name'     => 'issue-1091.csv',
			'type'     => 'text/csv',
			'tmp_name' => $file_name,
			'error'    => 0,
			'size'     => filesize( $file_name ),
		];

		$this->assertTrue( $importer->add_file( $file_array ) );

		$uploaded_file = $importer->get_tmp_file();
		$this->assertFileExists( $uploaded_file );

		$attachment_ids = get_posts( [
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_attached_file',
			'meta_value'     => _wp_relative_upload_path( $uploaded_file ),
		] );

		$this->assertNotEmpty( $attachment_ids );
		$attachment_id = (int) $attachment_ids[0];

		$background_importer = new \Tainacan\Background_Importer();

		$background_importer
			->data( $importer->_to_Array( true ) )
			->set_name( 'CSV Importer' )
			->save();

		$process_id = $background_importer->ID;

		if ( $cancel_via_api ) {
			$request = new \WP_REST_Request( 'POST', '/' );
			$request->set_param( 'id', $process_id );
			$request->set_body( wp_json_encode( [ 'status' => 'closed' ] ) );

			$controller = new \Tainacan\API\EndPoints\REST_Background_Processes_Controller();
			$response = $controller->update_item( $request );

			$this->assertEquals( 200, $response->get_status() );
			$response_data = $response->get_data();
			$this->assertEquals( 'cancelled', $response_data->status );
		} else {
			$background_importer->close( $process_id, $status );
		}

		$file_exists_after_close = file_exists( $uploaded_file );
		$attachment_after_close = get_post( $attachment_id );

		// Prevent test artifacts from remaining when an assertion fails.
		$background_importer->delete( $process_id );

		if ( $attachment_after_close ) {
			wp_delete_attachment( $attachment_id, true );
		} elseif ( $file_exists_after_close ) {
			wp_delete_file( $uploaded_file );
		}

		$this->assertFalse(
			$file_exists_after_close,
			'The importer source file should be removed when the background import reaches a terminal state.'
		);

		$this->assertNull(
			$attachment_after_close,
			'The WordPress attachment should also be removed when the background import reaches a terminal state.'
		);
	}

	/**
	 * @group importer
	 */
	public function test_vocabulary_csv_source_file_is_removed_when_background_import_finishes() {
		$this->assert_source_file_is_removed_when_background_import_closes( 'finished', 'terms' );
	}

	/**
	 * @group importer
	 */
	/*
	public function test_fetch_file(){
		$csv_importer = new Importer\CSV();
		$id = $csv_importer->get_id();
		$_SESSION['tainacan_importer'][$id]->fetch_from_remote( 'http://localhost/wordpress-test/wp-json' );

		if(!isset( $_SESSION['tainacan_importer'][$id]->tmp_file )){
			#TODO: Remove dependence of web server (see fetch_from_remote)
			$this->markTestSkipped('This test need a apache installation available.');
		}

		$this->assertTrue( isset( $_SESSION['tainacan_importer'][$id]->tmp_file ) );
	}
	*/
	
	/**
	 * @group importer_created
	 */
	public function test_file_csv_multiple () {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$file_name = 'demosaved.csv';
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$csv_importer = $Tainacan_Importer_Handler->initialize_importer('csv');
		$Tainacan_Importer_Handler->save_importer_instance($csv_importer);
		$id = $csv_importer->get_id();
		$importer_instance = $Tainacan_Importer_Handler->get_importer_instance_by_session_id($id);

		// open the file "demosaved.csv" for writing
		$file = fopen($file_name, 'w');

		// save the column headers
		fputcsv($file, array('author', 'Column 1', 'Column 2', 'Column 3', 'Column 4', 'Column 5'));

		// Sample data
		$data = array(
			array(get_current_user_id(), 'Data 11', 'Data 12', 'Data 13||TESTE', 'Data 14', 'Data 15>>DATA 151'),
			array(get_current_user_id(), 'Data 21', 'Data 22', 'this
			is
			having
			multiple
			lines', 'Data 24', 'Data 25'),
			array(get_current_user_id(), 'Data 31', 'Data 32', mb_convert_encoding('Data 33||Rééço', 'UTF-8', 'ISO-8859-1'), 'Data 34', 'Data 35'),
			array(get_current_user_id(), 'Data 41', 'Data 42', 'Data 43||limbbo', 'Data 44', 'Data 45'),
			array(get_current_user_id(), 'Data 51', 'Data 52', 'Data 53', 'Data 54', 'Data 55>>DATA551')
		);

		// save each row of the data
		foreach ($data as $row){
			fputcsv($file, $row);
		}

		// Close the file
		fclose($file);

		$importer_instance->set_tmp_file( $file_name );
		
		// file isset on importer
		$this->assertTrue( !empty( $importer_instance->get_tmp_file() ) );

		// count size of csv
		$this->assertEquals( 5, $importer_instance->get_source_number_of_items() );

		// get metadata to mapping
		$headers =  $importer_instance->get_source_metadata();
		$this->assertEquals( $headers[5], 'Column 5' );

		// inserting the collection
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'Other',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC',
				'status'		=> 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data multiplo',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'yes'
			),
			true
		);
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Texto simples',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'no'
			),
			true
		);
		
		$taxonomy = $this->tainacan_entity_factory->create_entity(
			'taxonomy',
			array(
				'name' => 'genero',
				'status' => 'publish'
			),
			true
		);
		
		$metadata_taxonomy =$this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'              => 'taxonomia',
				'collection_id'     => $collection->get_id(),
				'metadata_type_options' => ['taxonomy_id' => $taxonomy->get_id(), 'allow_new_terms' => 'yes' ],
				'metadata_type'  => 'Tainacan\Metadata_Types\Taxonomy',
				'status'            => 'publish'
			),
			true
		);

		$collection_definition = [
			'id' => $collection->get_id(),
			'total_items' => $importer_instance->get_source_number_of_items(),
		];
		
		// get collection metadata to map
		$metadata = $Tainacan_Metadata->fetch_by_collection( $collection ) ;

		//create a random mapping
		$map = [];
		$index = 1;
		foreach ( $metadata as $metadatum ){
			if ($metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Core_Author') {
				$map[$metadatum->get_id()] = $headers[0];
				continue;
			}
			if( $index === 1){
				$map['create_metadata'] = $headers[$index];
			} else {
				$map[$metadatum->get_id()] = $headers[$index];
			}
			$index++;
		}

		$collection_definition['mapping'] = $map;

		// add the collection
		$importer_instance->add_collection( $collection_definition );
		$importer_instance->set_option('encode','iso88591');

		//execute the process
		$this->assertEquals(1, $importer_instance->run(), 'first step should import 1 item');
		$this->assertEquals(2, $importer_instance->run(), 'second step should import 2 items');
		$this->assertEquals(3, $importer_instance->run(), 'third step should import 3 items');
		$this->assertEquals(4, $importer_instance->run(), 'third step should import 4 items');
		$this->assertEquals(false, $importer_instance->run(), '5 items and return false because its finished');
		$this->assertEquals(false, $importer_instance->run(), 'if call run again after finish, do nothing');

		$items = $Tainacan_Items->fetch( ['order'=> 'ASC','orderby' => 'date'], $collection, 'OBJECT' );

		$this->assertEquals( $importer_instance->get_source_number_of_items(), count( $items ) );
	}

	/**
	 * @group xis
	 */
	public function test_get_registered() {
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$csv_importer = new Importer\CSV();
		$registered = $Tainacan_Importer_Handler->get_importer_by_object($csv_importer);
		$this->assertEquals('csv', $registered['slug']);
		$this->assertEquals('CSV', $registered['name']);
	}

	/**
	 * @group importer_csv_special_fields
	 */
	public function test_special_fields(){
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$file_name = 'demosaved.csv';
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$csv_importer = $Tainacan_Importer_Handler->initialize_importer('csv');
		$Tainacan_Importer_Handler->save_importer_instance($csv_importer);
		$id = $csv_importer->get_id();
		$importer_instance = $Tainacan_Importer_Handler->get_importer_instance_by_session_id($id);

		// open the file "demosaved.csv" for writing
		$file = fopen($file_name, 'w');

		// save the column headers
		fputcsv($file, array('Column 1', 'special_ID', 'Column 3', 'special_attachments', 'special_document', 'special_thumbnail'));

		// Sample data
		$data = array(
			array('Data 11', '456', 'Data 13||TESTE', 'https://www.w3schools.com/w3css/img_lights.jpg', 'text:Vou dormir mais tarde', 'https://www.w3schools.com/w3css/img_lights.jpg'),
			array('Data 21', '457', 'Data 23', 'photos/unnamed.jpg', 'url:https://www.youtube.com/watch?v=V8dpmD4HG5s&start_radio=1&list=RDEMZS6OrHEAut8dOA38mVtVpg', ''),
			array(
				'Data 31', 
				'458', 
				mb_convert_encoding( 'Data 33||Rééço', 'ISO-8859-1', 'UTF-8' ), 
				'https://www.codeproject.com/KB/GDI-plus/ImageProcessing2/img.jpg||https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/58f72418-b5ee-4765-8e80-e463623a921d/01-httparchive-opt-small.png', 
				'file:https://images.pexels.com/photos/248797/pexels-photo-248797.jpeg', 'https://images.pexels.com/photos/15074398/pexels-photo-15074398.jpeg'),
			array('Data 41', '459', 'Data 43||limbbo', 'photos/SamplePNGImage_100kbmb.png||audios/SampleAudio_0.4mb.mp3', 'url:http://www.pdf995.com/samples/pdf.pdf','photos/unnamed.jpg'),
			array('Data 51', '500', 'Data 53', 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Space_station_Penyulap.ogv', '', '')
		);

		// save each row of the data
		foreach ($data as $row){
			fputcsv($file, $row);
		}

		// Close the file
		fclose($file);

		$importer_instance->set_tmp_file( $file_name );
		
		// file isset on importer
		$this->assertTrue( !empty( $importer_instance->get_tmp_file() ) );

		// count total items
		$this->assertEquals( 5, $importer_instance->get_source_number_of_items() );

		// get metadata to mapping AVOIDING special fields
		$headers =  $importer_instance->get_source_metadata();
		$this->assertEquals( $headers[1], 'Column 3' );

		$this->assertEquals( $importer_instance->get_option('attachment_index'), 3 );
		$this->assertEquals( $importer_instance->get_option('document_index'), 4 );
		$this->assertEquals( $importer_instance->get_option('thumbnail_index'), 5 );

		// inserting the collection
		$collection = $this->tainacan_entity_factory->create_entity(
			'collection',
			array(
				'name'          => 'Other',
				'description'   => 'adasdasdsa',
				'default_order' => 'DESC',
				'status'		=> 'publish'
			),
			true
		);

		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Data multiplo',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'yes'
			),
			true
		);
		
		$metadatum = $this->tainacan_entity_factory->create_entity(
			'metadatum',
			array(
				'name'        => 'Texto simples',
				'description' => 'Descreve o dado do campo data.',
				'collection'  => $collection,
				'status'      => 'publish',
				'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				'multiple'    => 'no'
			),
			true
		);
		
		$collection_definition = [
			'id' => $collection->get_id(),
			'total_items' => $importer_instance->get_source_number_of_items(),
		];
		
		// get collection metadata to map
		$metadata = $Tainacan_Metadata->fetch_by_collection( $collection ) ;

		//create a random mapping
		$map = [];
		foreach ( $metadata as $index => $metadatum ){
			if(isset($headers[$index]))
				$map[$metadatum->get_id()] = $headers[$index];
		}

		$collection_definition['mapping'] = $map;

		// add the collection
		$importer_instance->add_collection( $collection_definition );
		$importer_instance->set_option('encode','iso88591');
		
		while($importer_instance->run()) {
			continue;
		}

		$items = $Tainacan_Items->fetch( ['order'=> 'ASC', 'orderby' => 'ID'], $collection, 'OBJECT' );

		$this->assertEquals( $importer_instance->get_source_number_of_items(), count( $items ) );

		// test rows
		$document_id = $items[0]->get_document();
		$this->assertFalse( is_numeric($document_id) );

		$thumbnail_id = $items[0]->get__thumbnail_id();
		$this->assertFalse( is_numeric($thumbnail_id) );

		$attachments = $items[0]->get_attachments();

		if(@file_get_contents ( 'https://www.w3schools.com/w3css/img_lights.jpg' ))
			$this->assertEquals( 1, count( $attachments ) );

		$document_id = $items[2]->get_document();

		if(@file_get_contents ( 'https://www.codeproject.com/KB/GDI-plus/ImageProcessing2/img.jpg' ))
			$this->assertTrue( is_numeric($document_id) );

		$thumbnail_id = $items[2]->get__thumbnail_id();

		if (@file_get_contents ( 'https://images.pexels.com/photos/15074398/pexels-photo-15074398.jpeg' ))
			$this->assertTrue( is_numeric($thumbnail_id) );
		
		$document = $items[3]->get_document();
		$this->assertTrue( wp_http_validate_url($document) !== false );

		$document_id = $items[4]->get_document();
		$this->assertFalse( is_numeric($document_id) );

	}

	/**
	 * @group importer_csv_special_fields
	 */
	public function test_special_fields_status_and_id(){
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		$file_name = 'demosaved.csv';
		$csv_importer = $Tainacan_Importer_Handler->initialize_importer('csv');
		$Tainacan_Importer_Handler->save_importer_instance($csv_importer);
		$id = $csv_importer->get_id();
		$importer_instance = $Tainacan_Importer_Handler->get_importer_instance_by_session_id($id);
		// open the file "demosaved.csv" for writing
		$file = fopen($file_name, 'w');

		// save the column headers
		fputcsv($file, array('Column 1', 'special_item_status', 'Unknow Column', 'special_comment_status'));

		// Sample data
		$data = array(
			array('Data 11', 'publish', 'nothing', 'open'),
			array('Data 21', 'private', 'void', 'closed'),
			array('Data 31', 'trash', 'empty', 'open'),
			array('Data 41', 'future', 'null', 'closed'),
			array('Data 51', 'trash', 'zero', 'open')
		);

		// save each row of the data
		foreach ($data as $row){
			fputcsv($file, $row);
		}

		// Close the file
		fclose($file);

		$importer_instance->set_tmp_file( $file_name );
		
		// file isset on importer
		$this->assertTrue( !empty( $importer_instance->get_tmp_file() ) );

		// count total items
		$this->assertEquals( 5, $importer_instance->get_source_number_of_items() );
  
		// get metadata to mapping AVOIDING special fields
		$headers =  $importer_instance->get_source_metadata();
		$this->assertEquals( $headers[1], 'Unknow Column' );
  
		$this->assertEquals( $importer_instance->get_option('item_status_index'), 1 );
  
		// inserting the collection
		$collection = $this->tainacan_entity_factory->create_entity(
			  'collection',
			  array(
				  'name'          => 'Other',
				  'description'   => 'adasdasdsa',
				  'default_order' => 'DESC',
				  'status'		=> 'publish'
			  ),
			  true
		  );
  
		  $metadatum = $this->tainacan_entity_factory->create_entity(
			  'metadatum',
			  array(
				  'name'        => 'Data multiplo',
				  'description' => 'Descreve o dado do campo data.',
				  'collection'  => $collection,
				  'status'      => 'publish',
				  'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				  'multiple'    => 'yes'
			  ),
			  true
		  );
		  
		  $metadatum = $this->tainacan_entity_factory->create_entity(
			  'metadatum',
			  array(
				  'name'        => 'Texto simples',
				  'description' => 'Descreve o dado do campo data.',
				  'collection'  => $collection,
				  'status'      => 'publish',
				  'metadata_type'  => 'Tainacan\Metadata_Types\Text',
				  'multiple'    => 'no'
			  ),
			  true
		  );
		  
		  $collection_definition = [
			  'id' => $collection->get_id(),
			  'total_items' => $importer_instance->get_source_number_of_items(),
		  ];

		  // get collection metadata to map
		$metadata = $Tainacan_Metadata->fetch_by_collection( $collection ) ;

		//create a random mapping
		$map = [];
		foreach ( $metadata as $index => $metadatum ){
			if(isset($headers[$index]))
				$map[$metadatum->get_id()] = $headers[$index];
		}

		$collection_definition['mapping'] = $map;

		// add the collection
		$importer_instance->add_collection( $collection_definition );

		while($importer_instance->run()){
			continue;
		}

		$items = $Tainacan_Items->fetch( ['order'=> 'DESC', 'orderby' => 'ID'], $collection, 'OBJECT' );

		// only 3 items should be published
		$this->assertEquals( 3, count( $items ) );

		//foreach ($items as $item) {
		//  if ( \in_array( $item->get_description(), ['Data 11', 'Data 31', 'Data 51'] ) ) {
		//    $this->assertEquals( 'open', $item->get_comment_status() );
		//  } else {
		//    $this->assertEquals( 'closed', $item->get_comment_status() );
		//  }
		//}
	}

	/**
	 * UTF-8 BOM on the first column must not break special field detection.
	 *
	 * @group importer_csv_special_fields
	 */
	public function test_utf8_bom_special_item_id() {
		$Tainacan_Importer_Handler = \Tainacan\Importer_Handler::get_instance();
		$file_name = 'demosaved_bom.csv';
		$csv_importer = $Tainacan_Importer_Handler->initialize_importer('csv');
		$Tainacan_Importer_Handler->save_importer_instance($csv_importer);
		$id = $csv_importer->get_id();
		$importer_instance = $Tainacan_Importer_Handler->get_importer_instance_by_session_id($id);

		$file = fopen($file_name, 'w');
		fwrite($file, "\xEF\xBB\xBF");
		fputcsv($file, array('special_item_id', 'Column 1', 'Column 2'));
		fputcsv($file, array('101', 'Data 11', 'Data 12'));
		fputcsv($file, array('102', 'Data 21', 'Data 22'));
		fclose($file);

		$importer_instance->set_tmp_file( $file_name );

		$this->assertEquals( 2, $importer_instance->get_source_number_of_items() );

		$raw_headers = $importer_instance->raw_source_metadata();
		$this->assertEquals( 'special_item_id', $raw_headers[0] );
		$this->assertStringNotContainsString( "\xEF\xBB\xBF", $raw_headers[0] );

		$headers = $importer_instance->get_source_metadata();
		$this->assertEquals( array( 'Column 1', 'Column 2' ), $headers );
		$this->assertEquals( 0, $importer_instance->get_option('item_id_index') );

		$special_fields = $importer_instance->get_source_special_fields();
		$this->assertEquals( array( 'special_item_id' ), $special_fields );
	}
}
