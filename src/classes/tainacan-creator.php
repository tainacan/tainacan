<?php

const ENTITIES_DIR 	   = __DIR__ . '/entities/';
const FIELD_TYPES_DIR  = __DIR__ . '/field-types/';
const FILTER_TYPES_DIR = __DIR__ . '/filter-types/';
const REPOSITORIES_DIR = __DIR__ . '/repositories/';
const TRAITS_DIR 	   = __DIR__ . '/traits/';
const VENDOR_DIR 	   = __DIR__ . '/../vendor/';
const ENDPOINTS_DIR    = __DIR__ . '/../api/endpoints/';

const DIRS = [
    CLASSES_DIR,
    ENTITIES_DIR,
    FIELD_TYPES_DIR,
    FILTER_TYPES_DIR,
    REPOSITORIES_DIR,
    TRAITS_DIR,
	ENDPOINTS_DIR,
];

require_once(VENDOR_DIR . 'autoload.php');

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
        $dir = strtolower(CLASSES_DIR.implode(DIRECTORY_SEPARATOR, array_slice($class_path, 1, count($class_path) -2) )).'/';
        $dir = str_replace('_', '-', $dir);

        if( in_array('Field_Types', $class_path) ){
            $dir.= strtolower(str_replace('_', '-' , $class_name)).'/';
        }

        $file = $dir . 'class-tainacan-'. strtolower(str_replace('_', '-' , $class_name)) . '.php';

        if(file_exists($file)) {
            require_once($file);
        }
    }
}

global $Tainacan_Collections;
$Tainacan_Collections = new \Tainacan\Repositories\Collections();

global $Tainacan_Item_Metadata;
$Tainacan_Item_Metadata = new \Tainacan\Repositories\Item_Metadata();

global $Tainacan_Metadatas;
$Tainacan_Metadatas = new \Tainacan\Repositories\Metadatas();

//register field types
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Text');
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Textarea');
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Date');
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Numeric');
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Selectbox');
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Relationship');
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Radio');
$Tainacan_Metadatas->register_field_type('\Tainacan\Field_type\Checkbox');

global $Tainacan_Filters;
$Tainacan_Filters = new \Tainacan\Repositories\Filters();

global $Tainacan_Taxonomies;
$Tainacan_Taxonomies = new \Tainacan\Repositories\Taxonomies();

global $Tainacan_Items;
$Tainacan_Items = new \Tainacan\Repositories\Items();

global $Tainacan_Terms;
$Tainacan_Terms = new \Tainacan\Repositories\Terms();

global $Tainacan_Logs;
$Tainacan_Logs = new \Tainacan\Repositories\Logs();

?>