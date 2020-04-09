<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Selectbox extends Filter_Type {

    function __construct(){
        $this->set_name( __('Select Box', 'tainacan') );
        $this->set_supported_types(['string', 'long_string']);
        $this->set_component('tainacan-filter-selectbox');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <div class="control is-expanded">
                    <span class="select is-fullwidth">
                        <select>
                            <option value="someValue">' . __('Select here...') . '</option> 
                        </select>
                    </span>
                </div>
            </div>
        ');
    }
}