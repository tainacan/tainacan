<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

const TAINACAN_CLI_DIR                    = __DIR__ . '/cli/';
const TAINACAN_API_DIR     				  = __DIR__ . '/api/';
const TAINACAN_OAIPMH_DIR                 = __DIR__ . '/oaipmh/';
const TAINACAN_TRAITS_DIR                 = __DIR__ . '/traits/';
const TAINACAN_VENDOR_DIR                 = __DIR__ . '/../vendor/';
const TAINACAN_MAPPERS_DIR                = __DIR__ . '/mappers/';
const TAINACAN_BACKGROUND_PROCESS_DIR     = __DIR__ . '/background-process/';
const TAINACAN_ENTITIES_DIR               = __DIR__ . '/background-process/entities/';
const TAINACAN_IMPORTER_DIR               = __DIR__ . '/background-process/importer/';
const TAINACAN_EXPORTER_DIR               = __DIR__ . '/background-process/exporter/';
const TAINACAN_GENERIC_PROCESS_DIR        = __DIR__ . '/background-process/generic-process/';
const TAINACAN_EXPOSERS_DIR               = __DIR__ . '/exposers/';
const TAINACAN_ENDPOINTS_DIR              = __DIR__ . '/api/endpoints/';
const TAINACAN_FILTER_TYPES_DIR           = __DIR__ . '/../views/admin/components/filter-types/';
const TAINACAN_REPOSITORIES_DIR           = __DIR__ . '/repositories/';
const TAINACAN_METADATA_TYPES_DIR         = __DIR__ . '/../views/admin/components/metadata-types/';

const DIRS = [
	TAINACAN_CLI_DIR,
	TAINACAN_API_DIR,
	TAINACAN_OAIPMH_DIR,
	TAINACAN_TRAITS_DIR,
	TAINACAN_CLASSES_DIR,
	TAINACAN_MAPPERS_DIR,
	TAINACAN_ENTITIES_DIR,
	TAINACAN_BACKGROUND_PROCESS_DIR,
	TAINACAN_IMPORTER_DIR,
	TAINACAN_EXPORTER_DIR,
	TAINACAN_GENERIC_PROCESS_DIR,
	TAINACAN_EXPOSERS_DIR,
	TAINACAN_ENDPOINTS_DIR,
	TAINACAN_FILTER_TYPES_DIR,
	TAINACAN_REPOSITORIES_DIR,
	TAINACAN_METADATA_TYPES_DIR
];

require_once('tainacan-utils.php');

require_once(TAINACAN_VENDOR_DIR . 'autoload.php');

require_once(TAINACAN_TRAITS_DIR . 'class-tainacan-singleton-instance.php');

require_once(TAINACAN_EXPOSERS_DIR . 'class-tainacan-exposers-handler.php');

require_once(TAINACAN_MAPPERS_DIR . 'class-tainacan-mappers-handler.php');

require_once(TAINACAN_BACKGROUND_PROCESS_DIR . 'class-tainacan-async-request.php');
require_once(TAINACAN_BACKGROUND_PROCESS_DIR . 'class-tainacan-background-process-base.php');
require_once(TAINACAN_BACKGROUND_PROCESS_DIR . 'class-tainacan-background-process.php');
require_once(TAINACAN_BACKGROUND_PROCESS_DIR . 'class-tainacan-background-process-heartbeat.php');
\Tainacan\Background_Process_Heartbeat::get_instance();

require_once(TAINACAN_IMPORTER_DIR . 'class-tainacan-bg-importer.php');
require_once(TAINACAN_IMPORTER_DIR . 'class-tainacan-importer.php');
require_once(TAINACAN_IMPORTER_DIR . 'class-tainacan-importer-handler.php');
\Tainacan\Importer_Handler::get_instance();

require_once(TAINACAN_EXPORTER_DIR . 'class-tainacan-bg-exporter.php');
require_once(TAINACAN_EXPORTER_DIR . 'class-tainacan-exporter-handler.php');
\Tainacan\Exporter_Handler::get_instance();
require_once(TAINACAN_EXPORTER_DIR . 'traits/class-tainacan-exporter-handler-cell.php');

require_once(TAINACAN_GENERIC_PROCESS_DIR . 'class-tainacan-bg-generic.php');
require_once(TAINACAN_GENERIC_PROCESS_DIR . 'class-tainacan-generic-process.php');
require_once(TAINACAN_GENERIC_PROCESS_DIR . 'class-tainacan-generic-handler.php');
\Tainacan\Generic_Process_Handler::get_instance();

spl_autoload_register('tainacan_autoload');

/**
 * Autoloader function for Tainacan classes.
 *
 * Handles automatic loading of Tainacan classes based on their namespace and class name.
 * Supports both simple classes and namespaced classes with proper directory mapping.
 *
 * @since 0.1.0
 *
 * @param string $class_name The fully qualified class name to load.
 * @return void
 */
function tainacan_autoload($class_name) {
	
	if ( strpos($class_name, 'Tainacan\\') !== 0 ) {
		return;
	}

	$class_path = explode('\\', $class_name);
	$class_name = end($class_path);
	
	if ( count($class_path) == 1 ) {
		foreach(DIRS as $dir) {
			$file = $dir . 'class-'. strtolower(str_replace('_', '-' , $class_name)) . '.php';

			if ( file_exists($file) ) {
				require_once($file);
			} else {
				error_log( 'Tainacan: Class ' . $class_name . ' not found in ' . $file );
			}
		}
	} elseif ( $class_path[0] == 'Tainacan' ) {
		$sliced = array_slice($class_path, 1, count($class_path) -2);

		if ( isset( $class_path[1] ) && $class_path[1] === 'Importer' ) {
			$dir = TAINACAN_IMPORTER_DIR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'GenericProcess' ) {
			$dir = TAINACAN_GENERIC_PROCESS_DIR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'Exporter' ) {
			$dir = TAINACAN_EXPORTER_DIR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'Exposers' ) {
			$dir = TAINACAN_EXPOSERS_DIR;
			if ( count($class_path) > 3 ) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'Mappers' ) {
			$dir = TAINACAN_MAPPERS_DIR;
			if ( count($class_path) > 3 ) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'API' ) {
			$dir = TAINACAN_API_DIR;
			if ( count($class_path) > 3 ) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'OAIPMHExpose' ) {
			$dir = TAINACAN_OAIPMH_DIR;
			if ( count($class_path) > 3 ) $dir .= strtolower($class_path[2]).DIRECTORY_SEPARATOR;
		} else if ( isset( $class_path[1] ) && substr($class_path[1], 0, 3) === 'Cli' ) {
			$dir = TAINACAN_CLI_DIR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'Metadata_Types' ) {
			$dir = TAINACAN_METADATA_TYPES_DIR;
		} else if ( isset( $class_path[1] ) && $class_path[1] === 'Filter_Types' ) {
			$dir = TAINACAN_FILTER_TYPES_DIR;
		} else if ( $sliced ) {
			$lower     = $sliced[0];
			$sliced[0] = strtolower( $lower );

			$dir = implode( DIRECTORY_SEPARATOR, $sliced ) . DIRECTORY_SEPARATOR;
			$dir = TAINACAN_CLASSES_DIR . str_replace( '_', '-', $dir );
		} else {
			$dir = TAINACAN_CLASSES_DIR;
		}

		if ( in_array('Metadata_Types', $class_path) || in_array('Filter_Types', $class_path) ) {
			$exceptions = ['taxonomytaginput','taxonomycheckbox','taxonomyselectbox'];
			if ( in_array( strtolower( $class_name ), $exceptions) ) {
				$dir.= 'taxonomy/';
			} else {
				$dir.= strtolower(str_replace('_', '-' , $class_name)).'/';
			}
		}

		$file = $dir . 'class-tainacan-'. strtolower(str_replace('_', '-' , $class_name)) . '.php';

		if ( file_exists($file) ) {
			require_once($file);
		}
	}
}

\Tainacan\Repositories\Collections::get_instance();

\Tainacan\Repositories\Item_Metadata::get_instance();

\Tainacan\Metadata_Types\Metadata_Type_Helper::get_instance();

\Tainacan\Repositories\Metadata_Sections::get_instance();

\Tainacan\Filter_Types\Filter_Type_Helper::get_instance();

\Tainacan\Repositories\Taxonomies::get_instance();

\Tainacan\Repositories\Items::get_instance();

\Tainacan\Repositories\Terms::get_instance();

\Tainacan\Repositories\Logs::get_instance();

\Tainacan\Exposers_Handler::get_instance();

\Tainacan\Mappers_Handler::get_instance();

\Tainacan\Embed::get_instance();

$Tainacan_Search_Engine = new \Tainacan\Search_Engine();

\Tainacan\Elastic_Press::get_instance();

\Tainacan\Roles::get_instance();

\Tainacan\Private_Files::get_instance();

\Tainacan\Media::get_instance();

if ( class_exists('WP_CLI') ) {
	Tainacan\Cli::get_instance();
}

require_once(TAINACAN_API_DIR . 'tainacan-rest-creator.php');

require_once(__DIR__ . '/../views/tainacan-pages-creator.php');

require_once(__DIR__ . '/../views/admin/classes/hooks/class-tainacan-admin-hooks.php');
require_once(__DIR__ . '/../views/admin/classes/hooks/admin-hooks-functions.php');
\Tainacan\Admin_Hooks::get_instance();

require_once(__DIR__ . '/../views/admin/classes/hooks/class-tainacan-component-hooks.php');
\Tainacan\Component_Hooks::get_instance();

require_once(__DIR__ . '/../views/admin/classes/hooks/class-tainacan-plugin-hooks.php');
\Tainacan\Plugin_Hooks::get_instance();

require_once(__DIR__ . '/theme-helper/class-tainacan-theme-helper.php');
require_once(__DIR__ . '/theme-helper/template-tags.php');
\Tainacan\Theme_Helper::get_instance();

require_once(__DIR__ . '/../views/gutenberg-blocks/class-tainacan-gutenberg-block.php');

include_once('tainacan-loaders.php');
