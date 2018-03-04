<?php

namespace Tainacan\Field_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFieldType
 */
class Category extends Field_Type {

    function __construct(){
        // call field type constructor
        parent::__construct();
        parent::set_primitive_type('term');
        
        $this->set_default_options([
            'allow_new_terms' => false
        ]);
        
        // TODO: Set component depending on options. If multiple is checkbox. if single, select. etc.
        $this->component = 'tainacan-category';
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        $options = ( isset( $this->options['options'] ) ) ? $this->options['options'] : '';
        return '<tainacan-selectbox    
                                       options="' . $options . '"
                                       field_id ="'.$itemMetadata->get_field()->get_id().'" 
                                       item_id="'.$itemMetadata->get_item()->get_id().'"    
                                       value=\''.json_encode( $itemMetadata->get_value() ).'\'
                                       name="'.$itemMetadata->get_field()->get_name().'"></tainacan-selectbox>';
    }

    /**
     * generate the fields for this field type
     */
    public function form(){
        global $Tainacan_Taxonomies;
        $taxonomies = $Tainacan_Taxonomies->fetch([], 'OBJECT')
        
        // TODO: form incomplete and not tested
        
        ?>
        <tr>
            <td>
                <label><?php echo __('Category','tainacan'); ?></label><br/>
                <small><?php echo __('Select the category','tainacan'); ?></small>
            </td>
            <td>
                <select name="taxonomy_id">
                    <?php foreach ($taxonomies as $tax): ?>
                        
                        <option value="<?php echo $tax->get_db_identifier(); ?>" <?php selected($this->get_option('taxonomy_id'), $tax->get_db_identifier()); ?> ><?php echo $tax->get_name(); ?></option>
                        
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php
    }
	
	public function validate_options(Array $options) {
		return true;
		// TODO validate required and unique taxonomy
	}
	
	/**
     * Validate item based on field type categores options
     *
     * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
     * @return bool Valid or not
     */
    public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();
        $field = $item_metadata->get_field();
		
        if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

		$valid = true;
        
        if (false === $this->get_option('allow_new_terms')) {
			$terms = $item_metadata->get_value();
			if (!is_array($terms))
				$terms = array($terms);
			
			foreach ($terms as $term) {
				if (is_object($term) && $term instanceof \WP_Term) {
					$term = $term->term_id;
				}
				
				if (!term_exists($term)) {
					$valid = false;
					break;
				}
			}
			
		}
		
		return $valid;
        
    }
	
}