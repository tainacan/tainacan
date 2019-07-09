<?php

const TAINACAN_ENTITIES_DIR 	   = __DIR__ . '/entities/';
const TAINACAN_METADATA_TYPES_DIR  = __DIR__ . '/metadata-types/';
const TAINACAN_FILTER_TYPES_DIR = __DIR__ . '/filter-types/';
const TAINACAN_REPOSITORIES_DIR = __DIR__ . '/repositories/';
const TAINACAN_TRAITS_DIR 	   = __DIR__ . '/traits/';
const TAINACAN_VENDOR_DIR 	   = __DIR__ . '/../vendor/';
const TAINACAN_TAPI_DIR          = __DIR__ . '/../api/';
const TAINACAN_ENDPOINTS_DIR    = __DIR__ . '/../api/endpoints/';
const TAINACAN_IMPORTER_DIR      = __DIR__ . '/../importer/';
const TAINACAN_EXPORTER_DIR     = __DIR__ . '/../exporter/';
const TAINACAN_EXPOSERS_DIR		= __DIR__ . '/../exposers/';
const TAINACAN_MAPPERS_DIR		= __DIR__ . '/../mappers/';
const TAINACAN_OAIPMH_DIR		= __DIR__ . '/../oaipmh-expose/';
const TAINACAN_CLI_DIR		= __DIR__ . '/../cli/';

const DIRS = [
    TAINACAN_CLASSES_DIR,
    TAINACAN_ENTITIES_DIR,
    TAINACAN_METADATA_TYPES_DIR,
    TAINACAN_FILTER_TYPES_DIR,
    TAINACAN_REPOSITORIES_DIR,
    TAINACAN_TRAITS_DIR,
	TAINACAN_TAPI_DIR,
	TAINACAN_ENDPOINTS_DIR,
    TAINACAN_IMPORTER_DIR,
    TAINACAN_EXPORTER_DIR,
	TAINACAN_EXPOSERS_DIR,
	TAINACAN_MAPPERS_DIR,
    TAINACAN_OAIPMH_DIR,
	TAINACAN_CLI_DIR
];

require_once('libs/wp-async-request.php');
require_once('libs/wp-background-process.php');
require_once('class-tainacan-background-process.php');
require_once('tainacan-utils.php');
require_once(TAINACAN_IMPORTER_DIR . 'class-tainacan-bg-importer.php');

require_once(TAINACAN_VENDOR_DIR . 'autoload.php');
require_once(TAINACAN_IMPORTER_DIR . 'class-tainacan-importer.php');
require_once(TAINACAN_IMPORTER_DIR . 'class-tainacan-importer-handler.php');
require_once(TAINACAN_EXPOSERS_DIR . 'class-tainacan-exposers-handler.php');
require_once(TAINACAN_MAPPERS_DIR . 'class-tainacan-mappers-handler.php');

require_once(TAINACAN_EXPORTER_DIR . 'class-tainacan-bg-exporter.php');
require_once(TAINACAN_EXPORTER_DIR . 'class-tainacan-export-handler.php');

spl_autoload_register('tainacan_autoload');

function tainacan_autoload($class_name){
	$class_path = explode('\\', $class_name);
	$class_name = end($class_path);

	if(count($class_path) == 1 ) {
		foreach(DIRS as $dir) {
			$file = $dir . 'class-'. strtolower(str_replace('_', '-' , $class_name)) . '.php';

			if(file_exists($file)) {
				require_once($file);
			}
		}
	}
	elseif ($class_path[0] == 'Tainacan') {
		$sliced = array_slice($class_path, 1, count($class_path) -2);
		if( isset( $class_path[1] ) && $class_path[1] === 'Importer' ) {
			$dir = TAINACAN_IMPORTER_DIR;
			$dir_import = strtolower(str_replace('_', '-' , $class_name));
			if (file_exists("$dir$dir_import/")) {
				$dir .= "$dir_import/";
			}
		} else if( isset( $class_path[1] ) && $class_path[1] === 'Exporter' ) {
			$dir = TAINACAN_EXPORTER_DIR;
		} else if( isset( $class_path[1] ) && $class_path[1] === 'Exposers' ){
			$dir = TAINACAN_EXPOSERS_DIR;
			if(count($class_path) > 3) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if( isset( $class_path[1] ) && $class_path[1] === 'Mappers' ){
			$dir = TAINACAN_MAPPERS_DIR;
			if(count($class_path) > 3) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if( isset( $class_path[1] ) && $class_path[1] === 'API' ){
			$dir = TAINACAN_TAPI_DIR;
			if(count($class_path) > 3) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if( isset( $class_path[1] ) && $class_path[1] === 'OAIPMHExpose' ){
            $dir = TAINACAN_OAIPMH_DIR;
            if(count($class_path) > 3) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if( isset( $class_path[1] ) && substr($class_path[1], 0, 3) === 'Cli' ){
			$dir = TAINACAN_CLI_DIR;
		}  else if($sliced) {
			$lower     = $sliced[0];
			$sliced[0] = strtolower( $lower );

			$dir = implode( DIRECTORY_SEPARATOR, $sliced ) . DIRECTORY_SEPARATOR;
			$dir = TAINACAN_CLASSES_DIR . str_replace( '_', '-', $dir );
		} else {
			$dir = TAINACAN_CLASSES_DIR;
		}

		if( in_array('Metadata_Types', $class_path) || in_array('Filter_Types', $class_path) ){
			$exceptions = ['taxonomytaginput','taxonomycheckbox'];
			if( in_array( strtolower( $class_name ), $exceptions) ){
				$dir.= 'taxonomy/';
			}else{
				$dir.= strtolower(str_replace('_', '-' , $class_name)).'/';
			}
		}

		$file = $dir . 'class-tainacan-'. strtolower(str_replace('_', '-' , $class_name)) . '.php';
			
		if(file_exists($file)) {
			require_once($file);
		}
	}
}

$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

//register metadatum types
$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Text');
$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Textarea');
$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Date');
$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Numeric');
$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Selectbox');
$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Relationship');
$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Taxonomy');
//$Tainacan_Metadata->register_metadata_type('Tainacan\Metadata_Types\Compound');

$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

//register filter type
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Custom_Interval');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Numeric');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Date');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Selectbox');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Autocomplete');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Taginput');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Checkbox');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\TaxonomyTaginput');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\TaxonomyCheckbox');

$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

$Tainacan_Logs = \Tainacan\Repositories\Logs::get_instance();

$Tainacan_Exposers = \Tainacan\Exposers_Handler::get_instance();

$Tainacan_Mappers = \Tainacan\Mappers_Handler::get_instance();

$Tainacan_Embed = \Tainacan\Embed::get_instance();

require_once(__DIR__ . '/../admin/class-tainacan-admin.php');
$Tainacan_Admin = \Tainacan\Admin::get_instance();

require_once(__DIR__ . '/../admin/class-tainacan-admin-hooks.php');
require_once(__DIR__ . '/../admin/admin-hooks-functions.php');
$Tainacan_Admin_Hooks = \Tainacan\Admin_Hooks::get_instance();

require_once(__DIR__ . '/../theme-helper/class-tainacan-theme-helper.php');
require_once(__DIR__ . '/../theme-helper/template-tags.php');
$Tainacan_Theme_Helper = \Tainacan\Theme_Helper::get_instance();

require_once(__DIR__ . '/../gutenberg-blocks/class-tainacan-gutenberg-block.php');

$Tainacan_Search_Engine = new \Tainacan\Search_Engine();
$Tainacan_Elastic_press = \Tainacan\Elastic_Press::get_instance();

require_once(__DIR__ . '/class-tainacan-background-process-heartbeat.php');
$Tainacan_Importer_Heartbeat = new \Tainacan\Background_Importer_Heartbeat();

$Tainacan_Capabilities = \Tainacan\Capabilities::get_instance();

if (class_exists('WP_CLI')) {
	$Tainacan_Cli = \Tainacan\Cli::get_instance();
}


?>
