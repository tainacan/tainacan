<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Taginput extends Filter_Type {

    function __construct(){
        $this->set_name( __('Tag Input', 'tainacan') );
        $this->set_supported_types(['string','long_string','item','user']);
        $this->set_component('tainacan-filter-taginput');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <p class="has-text-dark">'. __('Selected values', 'tainacan') . ': </p> 
                <div class="field selected-tags is-grouped-multiline is-grouped">
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('Collection', 'tainacan') . ' 2 '. __('item', 'tainacan') . ' 13</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('Collection', 'tainacan') . ' 3 '. __('item', 'tainacan') . ' 2</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                </div> 
                <div class="taginput control is-expanded has-selected">
                    <div class="taginput-container is-focusable"> 
                        <div class="autocomplete control">
                            <div class="control has-icon-right is-loading is-clearfix">
                                <input type="text" class="input" value="'. __('Item', 'tainacan') . ' 9" > 
                            </div> 
                            <div class="dropdown-menu" style="">
                                <div class="dropdown-content">
                                    <a class="dropdown-item is-hovered">
                                        <span>'. __('Collection', 'tainacan') . ' 2 <strong>'. __('item', 'tainacan') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span>'. __('Collection', 'tainacan') . ' 3 <strong>'. __('item', 'tainacan') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span>'. __('Collection', 'tainacan') . ' 3 <strong>'. __('item', 'tainacan') . ' 9</strong>8</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ');
    }
}