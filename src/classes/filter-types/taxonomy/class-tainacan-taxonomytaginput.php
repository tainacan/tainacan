<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class Taginput
 */
class TaxonomyTaginput extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['term']);
        $this->set_component('tainacan-filter-taxonomy-taginput');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <p class="has-text-gray">'. __('Selected values') . ': </p> 
                <div class="field selected-tags is-grouped-multiline is-grouped">
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('Value') . ' 21</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('Value') . ' 7</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                </div> 
                <div class="taginput control is-expanded has-selected">
                    <div class="taginput-container is-focusable"> 
                        <div class="autocomplete control">
                            <div class="control has-icon-right is-loading is-clearfix">
                                <input type="text" class="input" value="'. __('Value') . ' 9" > 
                            </div> 
                            <div class="dropdown-menu" style="">
                                <div class="dropdown-content">
                                    <a class="dropdown-item is-hovered">
                                        <span><strong>'._('Value') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span><strong>'._('Value') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span><strong>'._('Value') . ' 9</strong>8</span>
                                    </a>
                                </div>
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
     */

    public function render( $filter ){
        return '<tainacan-filter-taxonomy-taginput name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum_id().'"></tainacan-filter-taxonomy-taginput>';
    }
}