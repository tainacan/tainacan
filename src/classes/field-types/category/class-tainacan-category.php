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
		<div class="field">
			<label class="label">
				<?php _e('Category','tainacan'); ?>
			</label>
			
			<div class="control">
				<div class="select">
					<select name="field_type_options[taxonomy_id]">
						<?php foreach ($taxonomies as $tax): ?>
							
							<option value="<?php echo $tax->get_db_identifier(); ?>" <?php selected($this->get_option('taxonomy_id'), $tax->get_db_identifier()); ?> ><?php echo $tax->get_name(); ?></option>
							
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		
		<div class="field">
			<div class="control">
				<label class="checkbox">
					<input type="checkbox" name="field_type_options[allow_new_terms]" <?php checked(true, $this->get_option('allow_new_terms')); ?> >
					Allow
				</label>
			</div>
		</div>
		
        <?php
    }
	
	public function validate_options(\Tainacan\Entities\Field $field) {
		
		if ( !in_array($field->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
		if (empty($this->get_option('taxonomy_id')))
			return ['taxonomy_id' => __('Please select a category', 'tainacan')];
		
		global $Tainacan_Fields;
		
		$category_fields = $Tainacan_Fields->fetch([
			'collection_id' => $field->get_collection_id(),
			'field_type' => 'Tainacan\\Field_Types\\Category'
		], 'OBJECT');
		
		$category_fields = array_map(function ($field) {
			$fto = $field->get_field_type_object();
			return $fto->get_option('taxonomy_id');
		}, $category_fields);
		
		if (in_array($this->get_option('taxonomy_id'), $category_fields)) {
			return ['taxonomy_id' => __('You can not have 2 Category Fields using the same category in a collection', 'tainacan')];
		}
		
		return true;
		
	}
	
	/**
     * Validate item based on field type categories options
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
			
			if (false === $terms)
				return true;
			
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