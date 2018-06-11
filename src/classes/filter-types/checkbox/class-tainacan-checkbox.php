<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Checkbox extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['string','item']);
        $this->set_component('tainacan-filter-checkbox');
    }

    /**
     * @param $filter
     * @return string
     */

    public function render( $filter ){
        return '<tainacan-filter-checkbox name="'.$filter->get_name().'"
                                        filter_type="'.$filter->get_metadatum()->get_metadata_type().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-selectbox>';
    }
}