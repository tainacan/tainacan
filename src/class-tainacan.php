<?php
/*
Plugin Name: Tainacan
Plugin URI: 
Description: Lorem Ipsum
Author: MediaLab UFG
Version: 10.9.8.7.6.5.4
*/

const ENTITIES_DIR 	   = __DIR__ . '/classes/entities/';
const FIELD_TYPES_DIR  = __DIR__ . '/classes/field-types/';
const FILTER_TYPES_DIR = __DIR__ . '/classes/filter-types/';
const REPOSITORIES_DIR = __DIR__ . '/classes/repositories/';
const TRAITS_DIR 	   = __DIR__ . '/classes/traits/';
const CLASSES_DIR 	   = __DIR__ . '/classes/';
const VENDOR_DIR 	   = __DIR__ . '/../vendor/';

const DIRS = [
    CLASSES_DIR, 
    ENTITIES_DIR, 
    FIELD_TYPES_DIR,
    FILTER_TYPES_DIR,
    REPOSITORIES_DIR, 
    TRAITS_DIR,
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
		//var_dump($dir);
		$file = $dir . 'class-tainacan-'. strtolower(str_replace('_', '-' , $class_name)) . '.php';
		//var_dump($file);
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

/**
 * 
 *
 * nos loops instancia a Classes
 *
 * as classes no plural em repositories (talvez troccar esse nome pra não confundir) 
 * lidam com registro de post type, incialiação
 * e tem o metodo find() pra busca, q usa o WP_Query, mas itera e substitui por objetos
 * certos, aí talvez não precise instanciar na mão
 * Nessas classes tb vão ter metodos, sõ ativos se quisermos ver a interface dev padrao do WP
 * q vai criar os metaboxes
 * e tb os pre_get_posts... 
 * 
 *
 * 
 * as classe em entities mapeiam suas propriedades para o esquema do WP, e não tem nenhuma lõgica,
 * sõ são objetos com propriedades, collection pode acessar seus metadados. item pode 
 * aessar sua coleção e metdados
 * talvez ter um getter que tenta passar a propriedade buscada pra dentro da propriedade o objeto wp,
 * usando o mapeamento ao contrãrio. assim um tema padrão não quebra
 *
 * 
 * Repository (não confundir) tem as opções gerais do repo, como o slug padrão das coisas (colecoes, item...)
 *
 * Vai no banco:
 * Collections**
 * Metadata
 * Taxonomies
 * Items**
 * Filters
 * 
 * ** Items e Collections vão aparecer na hierarquia de templates e podem ter loops
 *
 * $collections ou $items registra os post types das coleções?
 *
 * db_identifier das coleções não pode mudar, mesmo q mude nome e slug
 *
 * essas classes tem q ter um esquema de validação, (filtro, unicidade)
 * 
 * $Collections->add(), find(), get()
 *
 * $collection->getItems(), getItem(), addItem(), deleteItem()
 *
 * metadados registrado via codigo deinem ibase_add_user
 * colecoes registradas via cõdigo passam o objeto inteiro e marcamos de algum jeito q não são editaveis
 * (source)
 *
 * 
 */


function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}