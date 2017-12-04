<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Selectbox extends Field_Type {

    public $term_root;

    function __construct(){
        parent::set_primitive_type('');
    }

    /**
     * get the term root to mount the type
     *
     * @return mixed
     */
    public function get_term_root(){
        return $this->term_root;
    }

    /**
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){
        return '<tainacan-selectbox name="'.$metadata->get_name().'"></tainacan-selectbox>';
    }
}