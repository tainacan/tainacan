<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;
use Tainacan\Entities\Item_Metadata_Entity;
use Tainacan\Repositories\Metadata;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Taxonomy extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('term');
        $this->set_repository( \Tainacan\Repositories\Terms::get_instance() );
        
        $this->set_default_options([
            'allow_new_terms' => 'no'
        ]);

        $this->set_form_component('tainacan-form-taxonomy');
		$this->set_component('tainacan-taxonomy');
		$this->set_name( __('Taxonomy', 'tainacan') );
        $this->set_description( __('A metadatum to use a taxonomy in this collection', 'tainacan') );
		$this->set_preview_template('
			<div>
				<div>
					<p class="has-text-gray">'. __('Selected terms') . ': </p> 
					<div class="field selected-tags is-grouped-multiline is-grouped">
						<div>
							<div class="tags has-addons">
								<span class="tag"><span>'. __('Term') . ' 2</span></span> 
								<a class="tag is-delete"></a>
							</div>
						</div>
						<div>
							<div class="tags has-addons">
								<span class="tag"><span>'. __('Term') . ' 3</span></span> 
								<a class="tag is-delete"></a>
							</div>
						</div>
					</div> 
					<div>
						<label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
							<input type="checkbox" value="option1">
							<span class="check"></span>
							<span class="control-label">'. __('Term') . ' 1</span>
						</label> 
						<br>
					</div>
					<div>
						<label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
							<input type="checkbox" checked value="option2">
							<span class="check"></span> 
							<span class="control-label">'. __('Term') . ' 2</span>
						</label> 
					</div>
					<div>
						<label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
							<input type="checkbox" checked value="option3">
							<span class="check"></span> 
							<span class="control-label">'. __('Term') . ' 3</span>
						</label> 
					</div>
				</div> 
				<a class="add-new-term">'. __('View all') . '</a>
			</div>
		');
	}

    /**
     * @inheritdoc
     */
    public function get_form_labels(){
        return [
            'taxonomy_id' => [
                'title' => __( 'Related Collection', 'tainacan' ),
                'description' => __( 'Select the collection to fetch items', 'tainacan' ),
            ],
            'input_type' => [
                'title' => __( 'Input type', 'tainacan' ),
                'description' => __( 'The html type of the terms list ', 'tainacan' ),
            ],
            'allow_new_terms' => [
                'title' => __( 'Allow new terms', 'tainacan' ),
                'description' => __( 'Allows to create new terms', 'tainacan' ),
            ]
        ];
    }

    /**
     * @param $itemMetadata Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        $options = ( isset( $this->get_options()['options'] ) ) ? $this->get_options()['options'] : '';
        return '<tainacan-selectbox    
                                       options="' . $options . '"
                                       metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                                       item_id="'.$itemMetadata->get_item()->get_id().'"    
                                       value=\''.json_encode( $itemMetadata->get_value() ).'\'
                                       name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-selectbox>';
    }
	
	public function validate_options( Metadatum $metadatum) {
		
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;
		
		if (empty($this->get_option('taxonomy_id')))
			return ['taxonomy_id' => __('Please select a taxonomy', 'tainacan')];
		
		$Tainacan_Metadata = Metadata::get_instance();
		
		// Check taxonomy visibility 
		$status = get_post_status( $this->get_option('taxonomy_id') );
		$post_status_obj = get_post_status_object($status);
		if ( ! $post_status_obj->public ) {
			$meta_status_obj = get_post_status_object($metadatum->get_status());
			if ( $meta_status_obj->public ) {
				return ['taxonomy_id' => __('This metadatum can not be public because chosen taxonomy is not.', 'tainacan')];
			}
		}
		
		$taxonomy_metadata = $Tainacan_Metadata->fetch([
			'collection_id' => $metadatum->get_collection_id(),
			'metadata_type' => 'Tainacan\\Metadata_Types\\Taxonomy'
		], 'OBJECT');
		
		$taxonomy_metadata = array_map(function ($metadatum_map) {
			$fto = $metadatum_map->get_metadata_type_object();
			return [ $metadatum_map->get_id() => $fto->get_option('taxonomy_id') ];
		}, $taxonomy_metadata);

		if( is_array( $taxonomy_metadata ) ){
            foreach ($taxonomy_metadata as $metadatum_id => $taxonomy_metadatum) {
                if ( is_array( $taxonomy_metadatum ) && key($taxonomy_metadatum) != $metadatum->get_id()
                    && in_array($this->get_option('taxonomy_id'), $taxonomy_metadatum)) {
                    return ['taxonomy_id' => __('You can not have 2 taxonomy metadata using the same taxonomy in a collection.', 'tainacan')];
                }
		    }
        }
		
		return true;
		
	}

	/**
	 * Validate item based on metadatum type taxonomies options
	 *
	 * @param Item_Metadata_Entity $item_metadata
	 *
	 * @return bool Valid or not
	 */
    public function validate( Item_Metadata_Entity $item_metadata) {
        
        $item = $item_metadata->get_item();

        if ( !in_array($item->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

		$valid = true;
        
        if ('no' === $this->get_option('allow_new_terms') || false === $this->get_option('allow_new_terms')) { //support legacy bug when it was saved as false
			$terms = $item_metadata->get_value();
			
			if (false === $terms)
				return true;
			
			if (!is_array($terms))
				$terms = array($terms);
			
			foreach ($terms as $term) {
				if (is_object($term) && $term instanceof \Tainacan\Entities\Term) {
					$term = $term->get_id();
				}
				
				// TODO term_exists is not fully reliable. Use $terms_repository->term_exists. see issue #159
				if (!term_exists($term)) {
					$valid = false;
					break;
				}
			}
			
		}
		
		return $valid;
        
    }
	
	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, containing one or multiple terms, separated by comma, linked to term page
	 */
	public function get_value_as_html(Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		
		$return = '';
		
		if ( $item_metadata->is_multiple() ) {
			
			$count = 1;
			$total = sizeof($value);
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			
			foreach ( $value as $term ) {
				
				$count ++;
				
				if ( $term instanceof \Tainacan\Entities\Term ) {
					$return .= $prefix;
					
					$return .= $term->_toHtml();
					
					$return .= $suffix;
					
					if ( $count <= $total ) {
						$return .= $separator;
					}
					
				}

			}
			
		} else {
			
			if ( $value instanceof \Tainacan\Entities\Term ) {
				$return .= $value->_toHtml();
			}
			
		}
		
		return $return;
		
	}
	
	public function _toArray() {
		
		$array = parent::_toArray();
		
		if ( isset($array['options']['taxonomy_id']) ) {
			$array['options']['taxonomy'] = \Tainacan\Repositories\Taxonomies::get_instance()->get_db_identifier_by_id( $array['options']['taxonomy_id'] );
		}
		
		return $array;
		
	}
	
}