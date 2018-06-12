<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Selectbox extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['string', 'long_string']);
        $this->set_component('tainacan-filter-selectbox');
    }

    /**
     * @param $filter
     * @return string
     */

    public function render( $filter ){
        return '<tainacan-filter-selectbox name="'.$filter->get_name().'"
                                        filter_type="'.$filter->get_metadatum()->get_metadata_type().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-selectbox>';
    }
}