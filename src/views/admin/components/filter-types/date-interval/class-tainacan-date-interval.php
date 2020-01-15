<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Date_Interval extends Filter_Type {

    function __construct(){
        $this->set_name( __('Date Interval', 'tainacan') );
        $this->set_supported_types(['date']);
        $this->set_component('tainacan-filter-date-interval');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <div class="datepicker control is-small">
                    <div class="dropdown is-bottom-left is-mobile-modal">
                        <div role="button" class="dropdown-trigger">
                            <div class="control has-icons-left is-small is-clearfix">
                                <input type="text" autocomplete="off" placeholder=" '. __('Select a date', 'tainacan') .'" class="input is-small">
                                <span class="icon is-left is-small"><i class="mdi mdi-calendar-today"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="is-size-7 has-text-centered is-marginless">until</p> 
                <div class="datepicker control is-small">
                    <div class="dropdown is-bottom-left is-mobile-modal">
                        <div role="button" class="dropdown-trigger">
                            <div class="control has-icons-left is-small is-clearfix">
                                <input type="text" autocomplete="off" placeholder=" '. __('Select a date', 'tainacan') .'" class="input is-small">
                                <span class="icon is-left is-small"><i class="mdi mdi-calendar-today"></i></span>
                            </div>
                        </div>
                    </div>
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
         return '<tainacan-filter-date-interval 
                                        name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-date-interval>';
    }
}