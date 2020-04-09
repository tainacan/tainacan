<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class AuthorTaginput extends Filter_Type {

    function __construct(){
        $this->set_name( __('Author Tag Input', 'tainacan') );
        $this->set_supported_types(['user']);
        $this->set_component('tainacan-filter-author-taginput');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <p class="has-text-gray">'. __('Selected values') . ': </p> 
                <div class="field selected-tags is-grouped-multiline is-grouped">
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('User') . ' 2</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                    <div>
                        <div class="tags has-addons">
                            <span class="tag"><span>'. __('User') . ' 4</span></span> 
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                </div> 
                <div class="taginput control is-expanded has-selected">
                    <div class="taginput-container is-focusable"> 
                        <div class="autocomplete control">
                            <div class="control has-icon-right is-loading is-clearfix">
                                <input type="text" class="input" value="'. __('User') . ' " > 
                            </div> 
                            <div class="dropdown-menu" style="">
                                <div class="dropdown-content">
                                    <a class="dropdown-item is-hovered">
                                        <span><strong>'._('User') . ' </strong>1</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span><strong>'._('User') . ' </strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span><strong>'._('User') . ' </strong>8</span>
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