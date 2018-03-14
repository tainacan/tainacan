<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class CategoryCheckbox extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['term']);
        $this->set_component('tainacan-filter-category-checkbox');
    }

    /**
     * @param $filter
     * @return string
     */

    public function render( $filter ){
        return '<tainacan-filter-category-checkbox name="'.$filter->get_name().'"
                                        filter_type="'.$filter->get_field()->get_field_type().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        field_id="'.$filter->get_field()->get_id().'"></tainacan-filter-checkbox>';
    }
}