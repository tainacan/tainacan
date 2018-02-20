<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

    public $delimiter;

    public function __construct() {
        parent::__construct();
    }

    /**
     *
     */
    public function get_fields_source(){
        // TODO: Implement get_fields_source() method.
    }

    /**
     *
     */
    public function process_item(){
        // TODO: process single item
    }

    /**
     * @return mixed
     */
    public function get_options(){
        // TODO: Implement get_options() method.
    }
}