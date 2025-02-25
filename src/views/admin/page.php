<?php 
	$admin_options = json_encode(self::$admin_ui_options);

    $allowed_html = [
        'div' => [
            'id' => true,
            'style' => true,
            'class' => true,
            'data-module' => true,
            'data-options' => true
        ]
    ];
    echo wp_kses( "<div id='tainacan-admin-app' class='tainacan-page-container-content' data-module='admin' data-options='$admin_options'></div>", $allowed_html );