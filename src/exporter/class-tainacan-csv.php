<?php

namespace Tainacan\Exporter;
use Tainacan;

class CSV extends Exporter {

    public function __construct($attributes = array()) {
        parent::__construct($attributes);
    }

    public function process_item( $index, $collection_definition ) {
        $this->add_error_log('passou aqui!');
        return false;
    }
    
    public function options_form() {
        ob_start();
        ?>
        <div class="field">
        <p>Priemiro teste da construção de um Exporter! </p>
        </div>
        <?php
        return ob_get_clean();
    }
}