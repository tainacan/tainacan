<?php

const TAINACAN_REST_NAMESPACE  = 'tainacan/v2';

//$rest_controller               = new \Tainacan\API\REST_Controller();
$rest_collections_controller   = new \Tainacan\API\EndPoints\REST_Collections_Controller();
$rest_items_controller         = new \Tainacan\API\EndPoints\REST_Items_Controller();
$rest_fields_controller        = new \Tainacan\API\EndPoints\REST_Fields_Controller();
$rest_taxonomies_controller    = new \Tainacan\API\EndPoints\REST_Taxonomies_Controller();
$rest_terms_controller         = new \Tainacan\API\EndPoints\REST_Terms_Controller();
$rest_filters_controller       = new \Tainacan\API\EndPoints\REST_Filters_Controller();
$rest_item_metadata_controller = new \Tainacan\API\EndPoints\REST_Item_Metadata_Controller();
$rest_logs_controller          = new \Tainacan\API\EndPoints\REST_Logs_Controller();
$rest_field_types_controller   = new \Tainacan\API\EndPoints\REST_Field_Types_Controller();
$rest_filter_types_controller  = new \Tainacan\API\EndPoints\REST_Filter_Types_Controller();
new \Tainacan\API\EndPoints\REST_Export_Controller();
// Add here other endpoints imports

?>