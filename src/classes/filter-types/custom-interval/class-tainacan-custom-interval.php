<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Custom_Interval extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['float','date']);
        $this->set_component('tainacan-filter-custom-interval');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <div class="control is-small is-clearfix">
                    <input type="number" step="any" value="6" class="input is-small"> 
                </div> 
                <p class="is-size-7 has-text-centered is-marginless">until</p> 
                <div class="control is-small is-clearfix">
                    <input type="number" step="any" value="10" class="input is-small"> 
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
         $type = ( $filter->get_metadatum()->get_metadata_type() === 'Tainacan\Metadata_Types\Date' ) ? 'date' : 'numeric';
         return '<tainacan-filter-custom-interval 
                                        name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-custom-interval>';
    }
}