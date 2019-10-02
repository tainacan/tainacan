<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Numeric_List_Interval extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['float']);
        $this->set_component('tainacan-filter-numeric-list-interval');
        $this->set_form_component('tainacan-filter-form-numeric-list-interval');
        $this->set_use_max_options(false);
        $this->set_default_options([
            'intervals' => []
        ]);
        $this->set_preview_template('
            <div>
                <div class="b-numberinput field is-grouped">
                    <p class="control">
                        <button type="button" class="button is-primary is-small">
                            <span class="icon is-small">
                                <i class="mdi mdi-minus"></i>
                            </span>
                        </button>
                    </p>
                    <div class="control is-small is-clearfix">
                        <input type="number" step="0.01" class="input is-small" value="6">
                    </div>
                    <p class="control">
                        <button type="button" class="button is-primary is-small">
                            <span class="icon is-small">
                                <i class="mdi mdi-plus"></i>
                            </span>
                        </button>
                    </p>
                </div>
                <p class="is-size-7 has-text-centered is-marginless">until</p> 
                <div class="b-numberinput field is-grouped">
                    <p class="control">
                        <button type="button" class="button is-primary is-small">
                            <span class="icon is-small">
                                <i class="mdi mdi-minus"></i>
                            </span>
                        </button>
                    </p>
                    <div class="control is-small is-clearfix">
                        <input type="number" step="0.01" class="input is-small" value="10">
                    </div>
                    <p class="control">
                        <button type="button" class="button is-primary is-small">
                            <span class="icon is-small">
                                <i class="mdi mdi-plus"></i>
                            </span>
                        </button>
                    </p>
                </div>
            </div>
        ');
    }
    
    public function get_form_labels() {
        return [
            'predefined_intervals' => [
                'title' => __('Predefined intervals','tainacan'),
                'description' => __('Predefined intervals','tainacan')
            ],
        ];
    }

    /**
     * @param $filter
     * @return string
     * @internal param $metadatum
     */
    public function render( $filter ) {
         return '<tainacan-filter-numeric-list-interval 
                                        name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-numeric-interval>';
    }
}