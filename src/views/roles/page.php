<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$tainacan_page_roles_allowed_html = [
    'div' => [
        'id' => true,
        'style' => true,
        'class' => true,
        'data-module' => true
    ]
];
echo wp_kses( "<div id='tainacan-roles-app' class='tainacan-page-container-content' data-module='roles'></div>", $tainacan_page_roles_allowed_html );