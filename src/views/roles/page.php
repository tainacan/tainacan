<?php
    $allowed_html = [
        'div' => [
            'id' => true,
            'style' => true,
            'class' => true,
            'data-module' => true
        ]
    ];
    echo wp_kses( "<div id='tainacan-roles-app' data-module='roles'></div>", $allowed_html );