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
    public function test_instance () {
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
        $csv_importer->set_collection( $collection );

        // here the session is init already
        $this->assertEquals( $_SESSION['tainacan_importer'], $csv_importer );
    }
}