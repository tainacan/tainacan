<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Helpers;

/**
 * Class TainacanFieldType
 */
class Relationship extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('');
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-relationship name="'.$itemMetadata->get_metadata()->get_name().'"></tainacan-relationship>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){
        ?>
        <tr>
            <td>
                <label><?php echo __('Collection related','tainacan'); ?></label><br/>
                <small><?php echo __('Select the collection to fetch items','tainacan'); ?></small>
            </td>
            <td>
                <?php Helpers\HtmlHelpers::collections_dropdown( $this->options['collection_id'], 'field_type_relationship[collection_id]' ); ?>
            </td>
        </tr>
        <?php if( $this->options['collection_id'] ): ?>
            <tr>
                <td>
                    <label><?php echo __('Metadata for search','tainacan'); ?></label><br/>
                    <small><?php echo __('Selected metadata to help in the search','tainacan'); ?></small>
                </td>
                <td>
                    <?php Helpers\HtmlHelpers::metadata_checkbox_list(
                        $this->options['collection_id'],
                        ( isset( $this->options['search'] ) ) ? $this->options['search'] : '',
                        'field_type_relationship[search][]'
                    ) ?>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td>
                <label><?php echo __('Allow repeated items','tainacan'); ?></label><br/>
                <small><?php echo __('Allow/Block selected items in this relationship','tainacan'); ?></small>
            </td>
            <td>
                <?php Helpers\HtmlHelpers::radio_field( ( isset( $this->options['repeated'] ) ) ? $this->options['repeated'] : 'yes', 'field_type_relationship[repeated]' ) ?>
            </td>
        </tr>
        <?php if( isset( $this->options['collection_id'] ) ): ?>
            <?php

            //filter only related metadata
            $args = array( 'meta_query' => array ( array(
                'key'     => 'field_type',
                'value'   => 'Tainacan\Field_Types\Relationship',
            ) ) );

            ?>
            <tr>
                <td>
                    <label><?php echo __('Inverse','tainacan'); ?></label><br/>
                    <small><?php echo __('Select the relationship inverse for this metadata','tainacan'); ?></small>
                </td>
                <td>
                    <?php Helpers\HtmlHelpers::metadata_dropdown(
                            $this->options['collection_id'],
                            ( isset( $this->options['inverse'] ) ) ? $this->options['inverse'] : '',
                            'field_type_relationship[inverse]',
                            $args
                          ) ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php
    }
}