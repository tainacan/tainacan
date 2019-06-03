<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Selectbox extends Filter_Type {

    function __construct(){
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

    /**
     * @param $filter
     * @return string
     */

    public function render( $filter ){
        return '<tainacan-filter-selectbox name="'.$filter->get_name().'"
                                        collection_id="'.$filter->get_collection_id().'"
                                        metadatum_id="'.$filter->get_metadatum_id().'"></tainacan-filter-selectbox>';
    }
}