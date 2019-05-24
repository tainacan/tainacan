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
        $this->set_form_component('tainacan-filter-form-numeric');
        $this->set_use_max_options(false);
        $this->set_default_options([
            'step' => 1
        ]);
        $this->set_preview_template('
            <div>
                <div>
                    <div class="numeric-filter-container">
                        <div class="dropdown is-active">
                            <div role="button" class="dropdown-trigger">
                                <button class="button is-white">
                                    <span class="icon is-small">
                                        <i>=</i>
                                    </span>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"></i>
                                    </span>
                                </button>
                            </div>
                            <div class="background" style="display: none;"></div>
                            <div class="dropdown-menu" style="display: none;">
                                <div role="list" class="dropdown-content">
                                    <a class="dropdown-item is-active">=&nbsp; Equal</a>
                                    <a class="dropdown-item">≠&nbsp; Not equal</a>
                                    <a class="dropdown-item">&gt;&nbsp; Greater than</a>
                                    <a class="dropdown-item">≥&nbsp; Greater than or equal to</a>
                                    <a class="dropdown-item">&lt;&nbsp; Less than</a>
                                    <a class="dropdown-item">≤&nbsp; Less than or equal to</a>
                                </div>
                            </div>
                        </div>
                    <div class="b-numberinput field is-grouped">
                        <p class="control">
                            <button type="button" class="button is-primary is-small">
                                <span class="icon is-small">
                                    <i class="mdi mdi-minus"></i>
                                </span>
                            </button>
                        </p>
                        <div class="control is-small is-clearfix">
                            <input type="number" step="0.01" class="input is-small" value="1.5">
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
            </div>
            </div>
        ');
    }

    /**
     * @inheritdoc
     */
    public function get_form_labels(){
        return [
            'step' => [
                'title' => __( 'Step', 'tainacan' ),
                'description' => __( 'The amount to be increased or decreased when clicking on filter control buttons.', 'tainacan' ),
            ]
        ];
    }
    /**
     * @param $filter
     * @return string
     * @internal param $metadatum
     */
    public function render( $filter ){

        return '<tainacan-filter-numeric
                                        step="' . $this->get_option('step') . '" 
                                        name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum()->get_id().'"></tainacan-filter-custom-interval>';
    }


    /**
     * @param \Tainacan\Entities\Filter $filter
     * @return array|bool true if is validate or array if has error
     */
    public function validate_options(\Tainacan\Entities\Filter $filter) {
        if ( !in_array($filter->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        if ( empty($this->get_option('step')) ) {
            return [
                'step' => __('"Step" value is required','tainacan')
            ];
        }

        return true;
    }
}