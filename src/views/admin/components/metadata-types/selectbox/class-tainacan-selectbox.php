<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Selectbox extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('string');
        $this->set_component('tainacan-selectbox');
        $this->set_form_component('tainacan-form-selectbox');
        $this->set_name( __('Selectbox', 'tainacan') );
        $this->set_description( __('A selectbox with a fixed list of value to choose one from', 'tainacan') );
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
     * @inheritdoc
     */
    public function get_form_labels(){
        return [
            'options_separator' => [
                'title' => __( 'Options separator', 'tainacan' ),
                'description' => __( 'Character to separate options in the text input below.', 'tainacan' ),
            ],
            'options' => [
                'title' => __( 'Options', 'tainacan' ),
                'description' => __( 'Creates options for what is selected. Type the "options separator" character to add a new one.', 'tainacan' ),
            ]
        ];
    }

    /**
     * @param \Tainacan\Entities\Metadatum $metadatum
     * @return array|bool true if is validate or array if has error
     */
    public function validate_options(\Tainacan\Entities\Metadatum $metadatum) {
        if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        if ( empty($this->get_option('options')) ) {
            return [
                'options' => __('Required options','tainacan')
            ];
        }

        return true;
    }
}
