<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Checkbox extends Filter_Type {

    function __construct(){
        parent::set_supported_types(['string','item']);
        $this->component = 'tainacan-filter-checkbox';
    }

    /**
     * @param $filter
     * @return string
     */

    public function render( $filter ){
        return '<tainacan-filter-checkbox name="'.$filter->get_name().'"
                                        filter_type="'.$filter->get_field()->get_field_type().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        field_id="'.$filter->get_field()->get_id().'"></tainacan-filter-selectbox>';
    }
}