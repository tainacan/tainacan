<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Taginput extends Filter_Type {

    function __construct(){
        $this->set_supported_types(['string','long_string','item']);
        $this->set_component('tainacan-filter-taginput');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <p class="has-text-gray">'. __('Selected values') . ': </p> 
                <div class="field selected-tags is-grouped-multiline is-grouped">
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('Collection') . ' 2 '._('item') . ' 13</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('Collection') . ' 3 '._('item') . ' 2</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                </div> 
                <div class="taginput control is-expanded has-selected">
                    <div class="taginput-container is-focusable"> 
                        <div class="autocomplete control">
                            <div class="control has-icon-right is-loading is-clearfix">
                                <input type="text" class="input" value="'. __('Item') . ' 9" > 
                            </div> 
                            <div class="dropdown-menu" style="">
                                <div class="dropdown-content">
                                    <a class="dropdown-item is-hovered">
                                        <span>'. __('Collection') . ' 2 <strong>'._('item') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span>'. __('Collection') . ' 3 <strong>'._('item') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span>'. __('Collection') . ' 3 <strong>'._('item') . ' 9</strong>8</span>
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
        return '<tainacan-filter-taginput name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum_id().'"></tainacan-filter-taginput>';
    }
}