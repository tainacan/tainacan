<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Numeric_List_Interval extends Filter_Type {

    function __construct(){
        $this->set_name( __('Numeric Interval List', 'tainacan') );
        $this->set_supported_types(['float']);
        $this->set_component('tainacan-filter-numeric-list-interval');
        $this->set_form_component('tainacan-filter-form-numeric-list-interval');
        $this->set_use_max_options(false);
        $this->set_default_options([
            'intervals' => [],
            'showIntervalOnTag' => false
        ]);
        $this->set_preview_template('
            <div class="collapse show">
                <div class="dropdown is-active">
                    <div role="button" class="dropdown-trigger">
                        <button class="button is-white">
                            List
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"></i>
                            </span>
                        </button>
                    </div>
                    <div class="background"></div>
                    <div class="dropdown-menu">
                        <div role="list" class="dropdown-content">
                            <a class="dropdown-item is-active">Top 10</a>
                            <a class="dropdown-item">Top 20</a>
                            <a class="dropdown-item">Top 30</a>
                        </div>
                    </div>
                </div>
            </div>
        ');
    }
    
    public function get_form_labels() {
        return [
            'intervals' => [
                'title' => __('Predefined intervals','tainacan'),
                'description' => __('A list of predefined intervals that the filter will offer on a select box.','tainacan')
            ],
            'showIntervalOnTag' => [
                'title' => __('Interval on tags', 'tainacan'),
                'description' => __('Whether the applyed interval values should appear on filter tags.')
            ]
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
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-form-numeric-list-interval>';
    }
}