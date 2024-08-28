<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TaxonomySelectbox
 */
class TaxonomySelectbox extends Filter_Type {

    function __construct(){
        $this->set_name( __('Selectbox', 'tainacan') );
        $this->set_supported_types(['term']);
        $this->set_component('tainacan-filter-taxonomy-selectbox');
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