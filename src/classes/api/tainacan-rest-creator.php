<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Base namespace for all rest api requests. WordPress default is 'wp/v2'.
 */
const TAINACAN_REST_NAMESPACE = 'tainacan/v2';

/**
 * Tainacan REST APIs initialization.
 */
$rest_items_controller                = new \Tainacan\API\EndPoints\REST_Items_Controller();
$rest_terms_controller                = new \Tainacan\API\EndPoints\REST_Terms_Controller();
$rest_logs_controller                 = new \Tainacan\API\EndPoints\REST_Logs_Controller();
$rest_roles_controller                = new \Tainacan\API\EndPoints\REST_Roles_Controller();
$rest_facets_controller               = new \Tainacan\API\EndPoints\REST_Facets_Controller();
$rest_reports_controller              = new \Tainacan\API\EndPoints\REST_Reports_Controller();
$rest_filters_controller              = new \Tainacan\API\EndPoints\REST_Filters_Controller();
$rest_exposers_controller             = new \Tainacan\API\EndPoints\REST_Exposers_Controller();
$rest_bulkedit_controller             = new \Tainacan\API\EndPoints\REST_Bulkedit_Controller();
$rest_metadata_controller             = new \Tainacan\API\EndPoints\REST_Metadata_Controller();
$rest_importers_controller            = new \Tainacan\API\EndPoints\REST_Importers_Controller();
$rest_exporters_controller            = new \Tainacan\API\EndPoints\REST_Exporters_Controller();
$rest_taxonomies_controller           = new \Tainacan\API\EndPoints\REST_Taxonomies_Controller();
$rest_collections_controller          = new \Tainacan\API\EndPoints\REST_Collections_Controller();
$rest_filter_types_controller         = new \Tainacan\API\EndPoints\REST_Filter_Types_Controller();
$rest_oaipmh_expose_controller        = new \Tainacan\API\EndPoints\REST_Oaipmh_Expose_Controller();
$rest_item_metadata_controller        = new \Tainacan\API\EndPoints\REST_Item_Metadata_Controller();
$rest_sequence_edit_controller        = new \Tainacan\API\EndPoints\REST_Sequence_Edit_Controller();
$rest_metadata_types_controller       = new \Tainacan\API\EndPoints\REST_Metadata_Types_Controller();
$rest_metadata_sections_controller    = new \Tainacan\API\EndPoints\REST_Metadata_Sections_Controller();
$rest_metadatum_mappers_controller    = new \Tainacan\API\EndPoints\REST_Metadatum_Mappers_Controller();
$rest_background_processes_controller = new \Tainacan\API\EndPoints\REST_Background_Processes_Controller();
// Add here other endpoints imports

?>
