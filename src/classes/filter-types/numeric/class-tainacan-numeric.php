<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Numeric extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['float']);
        $this->set_component('tainacan-filter-numeric');
        $this->set_preview_template('
            <div>
                <div class="control is-small is-clearfix">
                    <input type="number" step="any" value="6" class="input is-small"> 
                </div> 
            </div>
        ');
    }

    /**
     * @param $filter
     * @return string
     * @internal param $metadatum
     */
    public function render( $filter ){
         return '<tainacan-filter-custom-interval 
                                        name="'.$filter->get_name().'"
                                        typeRange="numeric"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-custom-interval>';
    }
}