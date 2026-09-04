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
        $this->set_name( __('Selection', 'tainacan') );
        $this->set_description( __('A fixed list of values to choose from', 'tainacan') );
        $this->set_default_options([
            'input_type' => 'tainacan-selectbox',
        ]);
        $this->set_preview_template('
            <div>
                <div class="control is-expanded">
                    <span class="select is-fullwidth">
                        <select>
                            <option value="someValue">' . __('Select here...', 'tainacan') . '</option> 
                        </select>
                    </span>
                </div>
            </div>
        ');
    }

    /**
     * Checkbox-style inputs manage multiple values in a single control.
     *
     * @return bool
     */
    public function get_manage_multiple_input() {
        return in_array(
            $this->get_option( 'input_type' ),
            [
                'tainacan-selectbox-checkbox',
                'tainacan-selectbox-checkbox-button',
            ],
            true
        );
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
            ],
            'input_type' => [
                'title' => __( 'Input type', 'tainacan' ),
                'description' => __( 'The html type of the options list', 'tainacan' ),
            ]
        ];
    }

    /**
     * Gets print-ready version of the options list in html
     *
     * @return string An html content with labels and values for the options or an empty string
     */
    public function get_options_as_html() {
        $options_as_html = '';
        $options = $this->get_options();

        if ( count($options) > 0 ) {
            $form_labels = $this->get_form_labels();

            foreach($options as $option_label => $option_value) {
                if ( $option_value != '' && $option_label != 'options_separator' ) {
                    $options_as_html .= '<div class="field"><div class="label">' . ( isset($form_labels[$option_label]) && isset($form_labels[$option_label]['title']) ? $form_labels[$option_label]['title'] : $option_label ) .'</div>';

                    $readable_option_value = '';

                    switch($option_label) {
                        case 'input_type':
                            if ($option_value == 'tainacan-selectbox')
                                $readable_option_value = __('Selectbox', 'tainacan');
                            else if ($option_value == 'tainacan-selectbox-radio')
                                $readable_option_value = __('Radio', 'tainacan');
                            else if ($option_value == 'tainacan-selectbox-checkbox')
                                $readable_option_value = __('Checkbox', 'tainacan');
                            else if ($option_value == 'tainacan-selectbox-radio-button' || $option_value == 'tainacan-selectbox-checkbox-button')
                                $readable_option_value = __('Selection buttons', 'tainacan');
                            else
                                $readable_option_value = $option_value;
                        break;

                        default:
                            $readable_option_value = $option_value;
                    }

                    $options_as_html .= '<div class="value">' . $readable_option_value . '</div></div>';
                }
            }
        }
        return $options_as_html;
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

        $input_type = $this->get_option('input_type');
        $single_types = [ 'tainacan-selectbox', 'tainacan-selectbox-radio', 'tainacan-selectbox-radio-button' ];
        $multiple_types = [ 'tainacan-selectbox', 'tainacan-selectbox-checkbox', 'tainacan-selectbox-checkbox-button' ];

        if ( !$metadatum->is_multiple() && !in_array( $input_type, $single_types, true ) ) {
            return [
                'input_type' => __('A selection metadata that does not accept multiple values should use a selectbox or radio type input', 'tainacan')
            ];
        }

        if ( $metadatum->is_multiple() && !in_array( $input_type, $multiple_types, true ) ) {
            return [
                'input_type' => __('A selection metadata that accepts multiple values should use a selectbox or checkbox type input', 'tainacan')
            ];
        }

        return true;
    }
}
