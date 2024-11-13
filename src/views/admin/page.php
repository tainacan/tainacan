<?php 
    // @deprecated: use tainacan-admin-ui-options instead
    $admin_options = apply_filters('set_tainacan_admin_options', $_GET);
	$admin_options = apply_filters('tainacan-admin-ui-options', $_GET);
	$admin_options = json_encode($admin_options);

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