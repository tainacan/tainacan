<?php
/**
 * Class TestCollections
 *
 * @package Test_Tainacan
 */

/**
 * Sample test case.
 */
class TestCollections extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_add() {
		// Replace this with some actual testing code.
		global $TainacanCollections;
        $testTitle = 'Teste';
        $newId = $TainacanCollections->add($testTitle);
        
        $check = $TainacanCollections->getCollectionById($newId);
        
        $this->assertEquals($check->ID, $newId);
        $this->assertEquals($check->post_title, $testTitle);
        
	}
}
