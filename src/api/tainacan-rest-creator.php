<?php

const ENDPOINTS_DIR = __DIR__ . '/endpoints/';

require_once(ENDPOINTS_DIR . 'class-tainacan-rest-collections-controller.php');
$rest_collections_controller = new TAINACAN_REST_Collections_Controller();

require_once(ENDPOINTS_DIR . 'class-tainacan-rest-items-controller.php');
$rest_items_controller = new TAINACAN_REST_Items_Controller();


// Add here other endpoints imports

?>