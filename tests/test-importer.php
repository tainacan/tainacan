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

    public function test_intance_old_tainacan()
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
        $this->assertEquals( $collection->get_id(),  $_SESSION['tainacan_importer'][$id]->collection->get_id() );
    }

    /*public function test_automapping_old_tainacan()
    {
        //$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        //$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

        $old_tainacan = new Importer\Old_Tainacan();
        $id = $old_tainacan->get_id();

        $_SESSION['tainacan_importer'][$id]->set_items_per_step(50);

        //if(!copy('./tests/attachment/json_old_tainacan_base.txt', './tests/attachment/json_old_tainacan.txt'))
        //{
            //return false;
        //}

        //$_SESSION['tainacan_importer'][$id]->set_file( './tests/attachment/json_old_tainacan.txt' );
        $url = 'http://localhost/wordpress_tainacan/';
        $_SESSION['tainacan_importer'][$id]->set_url($url);
        $_SESSION['tainacan_importer'][$id]->set_repository();

        while (!$_SESSION['tainacan_importer'][$id]->is_finished())
        {
            $_SESSION['tainacan_importer'][$id]->run();
        }

        $Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
        $collections = $Tainacan_Collections->fetch([], 'OBJECT');
        $this->assertEquals(3, $collections, 'total collection in this url does not match');

        $this->assertTrue(true);
    }*/

    /*public function test_file_old_tainacan () {
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

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

        // get fields to mapping
        $headers =  $_SESSION['tainacan_importer'][$id]->get_fields();
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

        // get collection fields to map
        $fields = $Tainacan_Fields->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        //create a random mapping
        $map = [];
        foreach ( $fields as $index => $field ){
            $map[$field->get_id()] = $headers[$index];
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
    public function test_instance_csv () {

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

        $csv_importer = new Importer\CSV();
        $id = $csv_importer->get_id();
        $_SESSION['tainacan_importer'][$id]->set_collection( $collection );

        // here the session is init already
        $this->assertEquals( $collection->get_id(),  $_SESSION['tainacan_importer'][$id]->collection->get_id() );
    }

    /**
     * @group importer
     */
    public function test_file_csv () {
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
        $Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
        $file_name = 'demosaved.csv';
        $csv_importer = new Importer\CSV();
        $id = $csv_importer->get_id();

		$_SESSION['tainacan_importer'][$id]->set_items_per_step(2);

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

        $_SESSION['tainacan_importer'][$id]->set_file( $file_name );

        // file isset on importer
        $this->assertTrue( isset( $_SESSION['tainacan_importer'][$id]->tmp_file ) );

        // count size of csv
        $this->assertEquals( 5, $_SESSION['tainacan_importer'][$id]->get_total_items() );

        // get fields to mapping
        $headers =  $_SESSION['tainacan_importer'][$id]->get_fields();
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

        // set the importer
        $_SESSION['tainacan_importer'][$id]->set_collection( $collection );

        // get collection fields to map
        $fields = $Tainacan_Fields->fetch_by_collection( $collection, [], 'OBJECT' ) ;

        //create a random mapping
        $map = [];
        foreach ( $fields as $index => $field ){
            $map[$field->get_id()] = $headers[$index];
        }

        // set the mapping
        $_SESSION['tainacan_importer'][$id]->set_mapping( $map );

        // check is equal
        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_mapping(), $map );

        //execute the process

		$this->assertEquals(2, $_SESSION['tainacan_importer'][$id]->run(), 'first step should import 2 items');
		$this->assertEquals(4, $_SESSION['tainacan_importer'][$id]->run(), 'second step should import 2 items');
		$this->assertEquals(5, $_SESSION['tainacan_importer'][$id]->run(), 'third step should import 3 items');
		$this->assertEquals(5, $_SESSION['tainacan_importer'][$id]->run(), 'if call run again after finish, do nothing');

        $items = $Tainacan_Items->fetch( [], $collection, 'OBJECT' );

        $this->assertEquals( $_SESSION['tainacan_importer'][$id]->get_total_items(), count( $items ) );
    }

    /**
     * @group importer
     */
    public function test_fetch_file(){
        $csv_importer = new Importer\CSV();
        $id = $csv_importer->get_id();
        $_SESSION['tainacan_importer'][$id]->fetch_from_remote( 'http://localhost/wordpress-test/wp-json' );
        $this->assertTrue( isset( $_SESSION['tainacan_importer'][$id]->tmp_file ) );
    }
}
