<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Helpers;
/**
 * Class TainacanFieldType
 */
class Date extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('date');
    }

    /**
     * @param $metadata
     * @return string
     */

    public function render( $metadata ){
        return '<tainacan-date name="'.$metadata->get_name().'"></tainacan-date>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){
        $approx_date = ( $this->options['approximate_date'] ) ? $this->options['approximate_date'] : '';
        ?>
        <tr>
            <td>
                <label><?php echo __('Approximate Date','tainacan'); ?></label><br/>
                <small><?php echo __('Allow format approximate date','tainacan'); ?></small>
            </td>
            <td>
                <?php Helpers\HtmlHelpers::radio_field( $approx_date, 'field_type_date[approximate_date]' ) ?>
            </td>
        </tr>
        <?php
    }
}