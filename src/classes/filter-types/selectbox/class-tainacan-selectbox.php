<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Selectbox extends Filter_Type {

    function __construct(){
        parent::set_supported_types(['string']);
        $this->component = 'tainacan-filter-list';
    }

    /**
     * @param $field
     * @return string
     */

    public function render( $filter ){
        return '<tainacan-filter-list name="'.$filter->get_name().'"></tainacan-filter-list>';
    }
}