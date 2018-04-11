<?php

const ENTITIES_DIR 	   = __DIR__ . '/entities/';
const FIELD_TYPES_DIR  = __DIR__ . '/field-types/';
const FILTER_TYPES_DIR = __DIR__ . '/filter-types/';
const REPOSITORIES_DIR = __DIR__ . '/repositories/';
const TRAITS_DIR 	   = __DIR__ . '/traits/';
const VENDOR_DIR 	   = __DIR__ . '/../vendor/';
const TAPI_DIR          = __DIR__ . '/../api/';
const ENDPOINTS_DIR    = __DIR__ . '/../api/endpoints/';
const HELPERS_DIR      = __DIR__ . '/../helpers/';
const IMPORTER_DIR      = __DIR__ . '/../importer/';
const EXPOSERS_DIR		= __DIR__ . '/../exposers/';

const DIRS = [
    CLASSES_DIR,
    ENTITIES_DIR,
    FIELD_TYPES_DIR,
    FILTER_TYPES_DIR,
    REPOSITORIES_DIR,
    TRAITS_DIR,
	TAPI_DIR,
	ENDPOINTS_DIR,
    IMPORTER_DIR,
	EXPOSERS_DIR
];

require_once(VENDOR_DIR . 'autoload.php');
require_once(HELPERS_DIR . 'class-tainacan-helpers-html.php');
require_once(IMPORTER_DIR . 'class-tainacan-importer.php');
require_once(EXPOSERS_DIR . 'class-tainacan-exposers.php');

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

    	if( isset( $class_path[1] ) && $class_path[1] === 'Importer' ){
            $dir = IMPORTER_DIR;
    	} else if( isset( $class_path[1] ) && $class_path[1] === 'Exposers' ){
    		$dir = EXPOSERS_DIR;
    		if(count($class_path) > 3) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
    	} else if($sliced) {
		    $lower     = $sliced[0];
		    $sliced[0] = strtolower( $lower );

		    $dir = implode( DIRECTORY_SEPARATOR, $sliced ) . DIRECTORY_SEPARATOR;
		    $dir = CLASSES_DIR . str_replace( '_', '-', $dir );
	    } else {
		    $dir = CLASSES_DIR;
	    }

        if( in_array('Field_Types', $class_path) || in_array('Filter_Types', $class_path) ){
    	    $exceptions = ['categorytaginput','categorycheckbox','categoryselectbox'];
    	    if( in_array( strtolower( $class_name ), $exceptions) ){
                $dir.= 'category/';
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

$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();

//register field types
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Text');
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Textarea');
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Date');
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Numeric');
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Selectbox');
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Relationship');
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Category');
$Tainacan_Fields->register_field_type('Tainacan\Field_Types\Compound');

$Tainacan_Filters = \Tainacan\Repositories\Filters::get_instance();

//register filter type
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Custom_Interval');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Selectbox');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Autocomplete');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Taginput');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\Checkbox');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\CategoryTaginput');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\CategoryCheckbox');
$Tainacan_Filters->register_filter_type('Tainacan\Filter_Types\CategorySelectbox');

$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();

$Tainacan_Logs = \Tainacan\Repositories\Logs::get_instance();

global $Tainacan_Exposers;
$Tainacan_Exposers = new \Tainacan\Exposers\Exposers();

?>
