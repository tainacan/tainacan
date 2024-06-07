<?php
    $allowed_html = [
        'div' => [
            'id' => true,
            'style' => true,
            'class' => true,
            'data-module' => true
        ]
    ];
    echo wp_kses( "<div id='tainacan-reports-app'  data-module='reports'></div>", $allowed_html );