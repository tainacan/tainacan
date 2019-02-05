<?php

const TAINACAN_REST_NAMESPACE = 'tainacan/v2';

//$rest_controller               = new \Tainacan\API\REST_Controller();
$rest_collections_controller     = new \Tainacan\API\EndPoints\REST_Collections_Controller();
$rest_items_controller           = new \Tainacan\API\EndPoints\REST_Items_Controller();
$rest_metadata_controller        = new \Tainacan\API\EndPoints\REST_Metadata_Controller();
$rest_taxonomies_controller      = new \Tainacan\API\EndPoints\REST_Taxonomies_Controller();
$rest_terms_controller           = new \Tainacan\API\EndPoints\REST_Terms_Controller();
$rest_filters_controller         = new \Tainacan\API\EndPoints\REST_Filters_Controller();
$rest_item_metadata_controller   = new \Tainacan\API\EndPoints\REST_Item_Metadata_Controller();
$rest_logs_controller            = new \Tainacan\API\EndPoints\REST_Logs_Controller();
$rest_metadata_types_controller = new \Tainacan\API\EndPoints\REST_Metadata_Types_Controller();
$rest_filter_types_controller    = new \Tainacan\API\EndPoints\REST_Filter_Types_Controller();
$rest_importers_controller    = new \Tainacan\API\EndPoints\REST_Importers_Controller();
$rest_exporters_controller    = new \Tainacan\API\EndPoints\REST_Exporters_Controller();
$rest_background_processes_controller    = new \Tainacan\API\EndPoints\REST_Background_Processes_Controller();
$rest_bulkedit_controller    = new \Tainacan\API\EndPoints\REST_Bulkedit_Controller();
$rest_exposers_controller    = new \Tainacan\API\EndPoints\REST_Exposers_Controller();
new \Tainacan\API\EndPoints\REST_Export_Controller();
new \Tainacan\API\EndPoints\REST_Metadatum_Mappers_Controller();
$rest_facets_controller         = new \Tainacan\API\EndPoints\REST_Facets_Controller();
$rest_oaipmh_expose_controller = new \Tainacan\API\EndPoints\REST_Oaipmh_Expose_Controller();
// Add here other endpoints imports

?>