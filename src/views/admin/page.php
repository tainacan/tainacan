<?php 

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$tainacan_page_admin_options = json_encode(self::$admin_ui_options);

$tainacan_page_admin_allowed_html = [
    'div' => [
        'id' => true,
        'style' => true,
        'class' => true,
        'data-module' => true,
        'data-options' => true
    ]
];
echo wp_kses( "<div id='tainacan-admin-app' class='tainacan-page-container-content' data-module='admin' data-options='$tainacan_page_admin_options'></div>", $tainacan_page_admin_allowed_html );