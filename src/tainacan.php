<?php

/*
Plugin Name: Tainacan
Plugin URI: https://github.com/tainacan/tainacan
Description: Transforme seu site Wordpress em um repositório digital
Author: Media Lab / UFG
Author URI: https://www.medialab.ufg.br
Version: 1.0
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

const API_DIR     = __DIR__ . '/api/';
const CLASSES_DIR = __DIR__ . '/classes/';
$TAINACAN_BASE_URL = plugins_url('', __FILE__);

require_once(CLASSES_DIR . 'tainacan-creator.php');
require_once(API_DIR     . 'tainacan-rest-creator.php');

require_once('dev-interface/class-tainacan-dev-interface.php');
$Tainacan_Dev_interface = new \Tainacan\DevInterface\DevInterface();

function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}
