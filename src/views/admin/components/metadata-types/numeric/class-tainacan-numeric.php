<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Numeric extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_name( __('Numeric', 'tainacan') );
        $this->set_primitive_type('float');
        $this->set_component('tainacan-numeric');
        $this->set_form_component('tainacan-form-numeric');
        $this->set_description( __('A numeric value, integer or float', 'tainacan') );
        $this->set_preview_template('
            <div>
                <div class="control is-clearfix">
                    <input type="number" placeholder="3,1415" class="input"> 
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
}