<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Base namespace for all Tainacan REST API requests.
 *
 * WordPress default is 'wp/v2', but Tainacan uses its own namespace.
 *
 * @since 1.0.0
 */
const TAINACAN_REST_NAMESPACE = 'tainacan/v2';

/**
 * Initializes all Tainacan REST API controllers.
 *
 * Creates instances of all REST API endpoint controllers for Tainacan
 * functionality including items, collections, taxonomies, and more.
 *
 * @since 1.0.0
 */
$tainacan_rest_items_controller                = new \Tainacan\API\EndPoints\REST_Items_Controller();
$tainacan_rest_terms_controller                = new \Tainacan\API\EndPoints\REST_Terms_Controller();
$tainacan_rest_logs_controller                 = new \Tainacan\API\EndPoints\REST_Logs_Controller();
$tainacan_rest_roles_controller                = new \Tainacan\API\EndPoints\REST_Roles_Controller();
$tainacan_rest_facets_controller               = new \Tainacan\API\EndPoints\REST_Facets_Controller();
$tainacan_rest_reports_controller              = new \Tainacan\API\EndPoints\REST_Reports_Controller();
$tainacan_rest_filters_controller              = new \Tainacan\API\EndPoints\REST_Filters_Controller();
$tainacan_rest_exposers_controller             = new \Tainacan\API\EndPoints\REST_Exposers_Controller();
$tainacan_rest_bulkedit_controller             = new \Tainacan\API\EndPoints\REST_Bulkedit_Controller();
$tainacan_rest_metadata_controller             = new \Tainacan\API\EndPoints\REST_Metadata_Controller();
$tainacan_rest_importers_controller            = new \Tainacan\API\EndPoints\REST_Importers_Controller();
$tainacan_rest_exporters_controller            = new \Tainacan\API\EndPoints\REST_Exporters_Controller();
$tainacan_rest_taxonomies_controller           = new \Tainacan\API\EndPoints\REST_Taxonomies_Controller();
$tainacan_rest_collections_controller          = new \Tainacan\API\EndPoints\REST_Collections_Controller();
$tainacan_rest_filter_types_controller         = new \Tainacan\API\EndPoints\REST_Filter_Types_Controller();
$tainacan_rest_oaipmh_expose_controller        = new \Tainacan\API\EndPoints\REST_Oaipmh_Expose_Controller();
$tainacan_rest_item_metadata_controller        = new \Tainacan\API\EndPoints\REST_Item_Metadata_Controller();
$tainacan_rest_sequence_edit_controller        = new \Tainacan\API\EndPoints\REST_Sequence_Edit_Controller();
$tainacan_rest_metadata_types_controller       = new \Tainacan\API\EndPoints\REST_Metadata_Types_Controller();
$tainacan_rest_metadata_sections_controller    = new \Tainacan\API\EndPoints\REST_Metadata_Sections_Controller();
$tainacan_rest_metadatum_mappers_controller    = new \Tainacan\API\EndPoints\REST_Metadatum_Mappers_Controller();
$tainacan_rest_background_processes_controller = new \Tainacan\API\EndPoints\REST_Background_Processes_Controller();
// Add here other endpoints imports

?>
