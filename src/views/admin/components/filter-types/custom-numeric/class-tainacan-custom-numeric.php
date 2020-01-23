<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Custom_Numeric extends Filter_Type {

    function __construct(){
        $this->set_name( __('Custom Numeric', 'tainacan') );
        $this->set_supported_types(['float']);
        $this->set_component('tainacan-filter-custom-numeric');
        $this->set_script('
            console.log("Script do filtro")
        ');
        $this->set_template('
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
                                <a class="dropdown-item is-active">=&nbsp; ' . __('Equal', 'tainacan') .'</a>
                                <a class="dropdown-item">≠&nbsp; '. __('Not equal', 'tainacan') .'</a>
                                <a class="dropdown-item">&gt;&nbsp; '. __('Greater than', 'tainacan') .'</a>
                                <a class="dropdown-item">≥&nbsp; '. __('Greater than or equal to', 'tainacan') .'</a>
                                <a class="dropdown-item">&lt;&nbsp; '. __('Less than', 'tainacan') .'</a>
                                <a class="dropdown-item">≤&nbsp;  '. __('Less than or equal to', 'tainacan') .'</a>
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
                        <input type="number" step="0.01" class="input is-small" value="" placeholder="' . __('Type a number', 'tainacan') . '">
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
        ');
        $this->set_use_max_options(false);
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
                                    <a class="dropdown-item is-active">=&nbsp; ' . __('Equal', 'tainacan') .'</a>
                                    <a class="dropdown-item">≠&nbsp; '. __('Not equal', 'tainacan') .'</a>
                                    <a class="dropdown-item">&gt;&nbsp; '. __('Greater than', 'tainacan') .'</a>
                                    <a class="dropdown-item">≥&nbsp; '. __('Greater than or equal to', 'tainacan') .'</a>
                                    <a class="dropdown-item">&lt;&nbsp; '. __('Less than', 'tainacan') .'</a>
                                    <a class="dropdown-item">≤&nbsp;  '. __('Less than or equal to', 'tainacan') .'</a>
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
        ');
    }

    /**
     * @param $filter
     * @return string
     * @internal param $metadatum
     */
    public function render( $filter ){
        return '';
    }


    /**
     * @param \Tainacan\Entities\Filter $filter
     * @return array|bool true if is validate or array if has error
     */
    public function validate_options(\Tainacan\Entities\Filter $filter) {
        if ( !in_array($filter->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        return true;
    }
}