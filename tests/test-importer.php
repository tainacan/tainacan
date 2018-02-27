<?php

namespace Tainacan\Tests;
use Tainacan\Importer;
/**
* Class Importer
*
* @package Test_Tainacan
*/

class ImporterTests extends TAINACAN_UnitTestCase {

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
        global $Tainacan_Fields, $Tainacan_Items;

        $csv_importer = new Importer\CSV();
        $id = $csv_importer->get_id();

        // open the file "demosaved.csv" for writing
        $file = fopen('demosaved.csv', 'w');

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

        $_SESSION['tainacan_importer'][$id]->set_file( 'demosaved.csv' );

        // file isset on importer
        $this->assertTrue( isset( $_SESSION['tainacan_importer'][$id]->tmp_file ) );

        // count size of csv
        $this->assertEquals( 5, $_SESSION['tainacan_importer'][$id]->get_total_items() );

        // get fields to mapping
        $headers =  $_SESSION['tainacan_importer'][$id]->get_fields_source();
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
        $_SESSION['tainacan_importer'][$id]->run();

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