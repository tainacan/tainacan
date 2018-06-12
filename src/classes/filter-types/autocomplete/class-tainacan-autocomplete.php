<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Autocomplete extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['string','item']);
        $this->set_component('tainacan-filter-autocomplete');
    }

    /**
     * @param $filter
     * @return string
     */

    public function render( $filter ){
        return '<tainacan-filter-autocomplete name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        filter_type="'.$filter->get_metadatum()->get_metadata_type().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-autocomplete>';
    }
}