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

    /*public function test_intance_old_tainacan()
    {
        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'          => 'OtherOldTainacan',
                'description'   => 'Collection from old tainacan',
                'default_order' => 'DESC',
                'status'		=> 'publish'
            ),
            true
        );

        $old_tainacan_importer = new Importer\Old_Tainacan();
        $id = $old_tainacan_importer->get_id();
        $_SESSION['tainacan_importer'][$id]->set_collection( $collection );
        this->assertEquals( $collection->get_id(),  $_SESSION['tainacan_importer'][$id]->collection->get_id() );
    }*/

    public function test_automapping_old_tainacan()
    {
        //$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        //$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $old_tainacan = new Importer\Old_Tainacan();
        $id = $old_tainacan->get_id();

        //if(!copy('./tests/attachment/json_old_tainacan_base.txt', './tests/attachment/json_old_tainacan.txt'))
        //{
            //return false;
        //}

        //$_SESSION['tainacan_importer'][$id]->set_file( './tests/attachment/json_old_tainacan.txt' );
        $url_repository = 'http://localhost/wordpress_tainacan/';
        $url_repository = '';
        if( $url_repository !== '' ){
            $_SESSION['tainacan_importer'][$id]->set_url($url_repository);

            while (!$_SESSION['tainacan_importer'][$id]->is_finished())
            {
                $_SESSION['tainacan_importer'][$id]->run();
            }

            $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
            $collections = $Tainacan_Collections->fetch([], 'OBJECT');
            $this->assertEquals(3, count( $collections ), 'total collection in this url does not match');

            $this->assertTrue(true);    
        }
        $this->assertTrue($old_tainacan instanceof \Tainacan\Importer\Old_Tainacan);
    }

    /*public function test_file_old_tainacan () {
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

        $old_tainacan = new Importer\Old_Tainacan();
        $id = $old_tainacan->get_id();

        $_SESSION['tainacan_importer'][$id]->set_items_per_step(2);

        if(!copy('./tests/attachment/json_old_tainacan_base.txt', './tests/attachment/json_old_tainacan.txt'))
        {
            return false;
        }

        //$_SESSION['tainacan_importer'][$id]->set_file( './tests/attachment/json_old_tainacan.txt' );

        //$_SESSION['tainacan_importer'][$id]->fetch_from_remote( 'http://localhost/wp-json/tainacan/v1/collections/970/items' );
        $_SESSION['tainacan_importer'][$id]->fetch_from_remote( 'http://localhost/colecao/colecao-to-import/' );

        // file isset on importer
        $this->assertTrue( isset( $_SESSION['tainacan_importer'][$id]->tmp_file ) );

        $_SESSION['tainacan_importer'][$id]->run();

        $this->assertEquals( 5, $_SESSION['tainacan_importer'][$id]->get_total_items() );

        // get metadata to mapping
        $headers =  $_SESSION['tainacan_importer'][$id]->get_metadata();
        $this->assertEquals( $headers[5], 'post_title' );

        // inserting the collection
        $collection = $this->tainacan_entity_factory->create_entity(
            'collection',
            array(
                'name'          => 'OtherOldTainacan',
                'description'   => 'Collection from old tainacan',
                'default_order' => 'DESC',
                'status'		=> 'publish'
            ),
            true
        );

        // set the importer
        $_SESSION['tainacan_importer'][$id]->set_collection( $collection );

        // get collection metadata to map
        $metadata = $Tainacan_Metadata->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        //create a random mapping
        $map = [];
        foreach ( $metadata as $index => $metadatum ){
            $map[$metadatum->get_id()] = $headers[$index];
        }

        // set the mapping
        $_SESSION['tainacan_importer'][$id]->set_mapping( $map );

        // check is equal
        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_mapping(), $map );

        //execute the process

        $this->assertEquals(2, $_SESSION['tainacan_importer'][$id]->run(), 'first step should import 2 items');
        $this->assertEquals(4, $_SESSION['tainacan_importer'][$id]->run(), 'second step should import 2 items');
        $this->assertEquals(5, $_SESSION['tainacan_importer'][$id]->run(), 'third step should import 1 item');

        $this->assertEquals(5, $_SESSION['tainacan_importer'][$id]->run(), 'if call run again after finish, do nothing');

        $items = $Tainacan_Items->fetch( [], $collection, 'OBJECT' );

        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_total_items(), count( $items ) );
    }*/
    
    /**
     * @group importer
     */
    public function test_file_csv () {
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $file_name = 'demosaved.csv';
        $csv_importer = new Importer\CSV();
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

        $_SESSION['tainacan_importer'][$id]->set_tmp_file( $file_name );
		
        // file isset on importer
        $this->assertTrue( !empty( $_SESSION['tainacan_importer'][$id]->get_tmp_file() ) );

        // count size of csv
        $this->assertEquals( 5, $_SESSION['tainacan_importer'][$id]->get_source_number_of_items() );

        // get metadata to mapping
        $headers =  $_SESSION['tainacan_importer'][$id]->get_source_metadata();
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
			'total_items' => $_SESSION['tainacan_importer'][$id]->get_source_number_of_items(),
		];
		
        // get collection metadata to map
        $metadata = $Tainacan_Metadata->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        //create a random mapping
        $map = [];
        foreach ( $metadata as $index => $metadatum ){
            $map[$metadatum->get_id()] = $headers[$index];
        }
		
		$collection_definition['mapping'] = $map;

        // add the collection
        $_SESSION['tainacan_importer'][$id]->add_collection( $collection_definition );

        //execute the process
		$this->assertEquals(1, $_SESSION['tainacan_importer'][$id]->run(), 'first step should import 1 item');
		$this->assertEquals(2, $_SESSION['tainacan_importer'][$id]->run(), 'second step should import 2 items');
		$this->assertEquals(3, $_SESSION['tainacan_importer'][$id]->run(), 'third step should import 3 items');
		$this->assertEquals(4, $_SESSION['tainacan_importer'][$id]->run(), 'third step should import 4 items');
		$this->assertEquals(false, $_SESSION['tainacan_importer'][$id]->run(), '5 items and return false because its finished');
		$this->assertEquals(false, $_SESSION['tainacan_importer'][$id]->run(), 'if call run again after finish, do nothing');

        $items = $Tainacan_Items->fetch( [], $collection, 'OBJECT' );

        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_source_number_of_items(), count( $items ) );
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
     * @group importer
     */
    public function test_file_csv_multiple () {
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $file_name = 'demosaved.csv';
        $csv_importer = new Importer\CSV();
        $id = $csv_importer->get_id();

        // open the file "demosaved.csv" for writing
        $file = fopen($file_name, 'w');

        // save the column headers
        fputcsv($file, array('Column 1', 'Column 2', 'Column 3', 'Column 4', 'Column 5'));

        // Sample data
        $data = array(
            array('Data 11', 'Data 12', 'Data 13||TESTE', 'Data 14', 'Data 15'),
            array('Data 21', 'Data 22', 'this
            is
            having
            multiple
            lines', 'Data 24', 'Data 25'),
            array('Data 31', 'Data 32', utf8_decode( 'Data 33||Rééço' ), 'Data 34', 'Data 35'),
            array('Data 41', 'Data 42', 'Data 43||limbbo', 'Data 44', 'Data 45'),
            array('Data 51', 'Data 52', 'Data 53', 'Data 54', 'Data 55')
        );

        // save each row of the data
        foreach ($data as $row){
            fputcsv($file, $row);
        }

        // Close the file
        fclose($file);

        $_SESSION['tainacan_importer'][$id]->set_tmp_file( $file_name );
		
        // file isset on importer
        $this->assertTrue( !empty( $_SESSION['tainacan_importer'][$id]->get_tmp_file() ) );

        // count size of csv
        $this->assertEquals( 5, $_SESSION['tainacan_importer'][$id]->get_source_number_of_items() );

        // get metadata to mapping
        $headers =  $_SESSION['tainacan_importer'][$id]->get_source_metadata();
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
			'total_items' => $_SESSION['tainacan_importer'][$id]->get_source_number_of_items(),
		];
		
        // get collection metadata to map
        $metadata = $Tainacan_Metadata->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        //create a random mapping
        $map = [];
        foreach ( $metadata as $index => $metadatum ){
            $map[$metadatum->get_id()] = $headers[$index];
        }
		
		$collection_definition['mapping'] = $map;

        // add the collection
        $_SESSION['tainacan_importer'][$id]->add_collection( $collection_definition );
        $_SESSION['tainacan_importer'][$id]->set_option('encode','iso88591');
        //$_SESSION['tainacan_importer'][$id]->set_option('encode','utf8');

        //execute the process
		$this->assertEquals(1, $_SESSION['tainacan_importer'][$id]->run(), 'first step should import 1 item');
		$this->assertEquals(2, $_SESSION['tainacan_importer'][$id]->run(), 'second step should import 2 items');
		$this->assertEquals(3, $_SESSION['tainacan_importer'][$id]->run(), 'third step should import 3 items');
		$this->assertEquals(4, $_SESSION['tainacan_importer'][$id]->run(), 'third step should import 4 items');
		$this->assertEquals(false, $_SESSION['tainacan_importer'][$id]->run(), '5 items and return false because its finished');
		$this->assertEquals(false, $_SESSION['tainacan_importer'][$id]->run(), 'if call run again after finish, do nothing');

        $items = $Tainacan_Items->fetch( [], $collection, 'OBJECT' );

        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_source_number_of_items(), count( $items ) );
    }

    /**
     * @group xis
     */
    public function test_get_registered() {
        global $Tainacan_Importer_Handler;
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
        $csv_importer = new Importer\CSV();
        $id = $csv_importer->get_id();

        // open the file "demosaved.csv" for writing
        $file = fopen($file_name, 'w');

        // save the column headers
        fputcsv($file, array('Column 1', 'special_ID', 'Column 3', 'special_attachments', 'special_document'));

        // Sample data
        $data = array(
            array('Data 11', '456', 'Data 13||TESTE', 'https://www.w3schools.com/w3css/img_lights.jpg', 'text:Vou dormir mais tarde'),
            array('Data 21', '457', 'Data 23', 'photos/unnamed.jpg', 'url:https://www.youtube.com/watch?v=V8dpmD4HG5s&start_radio=1&list=RDEMZS6OrHEAut8dOA38mVtVpg'),
            array(
                'Data 31', 
                '458', 
                utf8_decode( 'Data 33||Rééço' ), 
                'https://www.codeproject.com/KB/GDI-plus/ImageProcessing2/img.jpg||https://cloud.netlifyusercontent.com/assets/344dbf88-fdf9-42bb-adb4-46f01eedd629/58f72418-b5ee-4765-8e80-e463623a921d/01-httparchive-opt-small.png', 
                'file:https://images.pexels.com/photos/248797/pexels-photo-248797.jpeg'),
            array('Data 41', '459', 'Data 43||limbbo', 'photos/SamplePNGImage_100kbmb.png||audios/SampleAudio_0.4mb.mp3', 'url:http://www.pdf995.com/samples/pdf.pdf'),
            array('Data 51', '500', 'Data 53', 'http://techslides.com/demos/samples/sample.mp4', '')
        );

        // save each row of the data
        foreach ($data as $row){
            fputcsv($file, $row);
        }

        // Close the file
        fclose($file);

        $_SESSION['tainacan_importer'][$id]->set_tmp_file( $file_name );
		
        // file isset on importer
        $this->assertTrue( !empty( $_SESSION['tainacan_importer'][$id]->get_tmp_file() ) );

        // count total items
        $this->assertEquals( 5, $_SESSION['tainacan_importer'][$id]->get_source_number_of_items() );

        // get metadata to mapping AVOIDING special fields
        $headers =  $_SESSION['tainacan_importer'][$id]->get_source_metadata();
        $this->assertEquals( $headers[1], 'Column 3' );

        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_option('attachment_index'), 3 );
        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_option('document_index'), 4 );

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
			'total_items' => $_SESSION['tainacan_importer'][$id]->get_source_number_of_items(),
        ];
        
        // get collection metadata to map
        $metadata = $Tainacan_Metadata->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        //create a random mapping
        $map = [];
        foreach ( $metadata as $index => $metadatum ){
            if(isset($headers[$index]))
                $map[$metadatum->get_id()] = $headers[$index];
        }

        $collection_definition['mapping'] = $map;

        // add the collection
        $_SESSION['tainacan_importer'][$id]->add_collection( $collection_definition );
        $_SESSION['tainacan_importer'][$id]->set_option('encode','iso88591');

        while($_SESSION['tainacan_importer'][$id]->run()){
            continue;
        }

        $items = $Tainacan_Items->fetch( ['order'=> 'DESC', 'orderby' => 'ID'], $collection, 'OBJECT' );

        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_source_number_of_items(), count( $items ) );

        // test rows
        $document_id = $items[0]->get_document();
        $this->assertFalse( is_numeric($document_id) );

        $attachments = $items[0]->get_attachments();
        $this->assertEquals( 1, count( $attachments ) );

        $this->assertTrue( count($items[2]->get_attachments()) > 0 );

        $document_id = $items[2]->get_document();
        $this->assertTrue( is_numeric($document_id) );

        $document = $items[3]->get_document();
        $this->assertTrue( wp_http_validate_url($document) !== false );

        $document_id = $items[4]->get_document();
        $this->assertFalse( is_numeric($document_id) );

    }
}
