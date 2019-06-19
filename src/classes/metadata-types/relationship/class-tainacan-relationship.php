<?php

namespace Tainacan\Metadata_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Relationship extends Metadata_Type {

    function __construct(){
        // call metadatum type constructor
        parent::__construct();
        $this->set_primitive_type('item');
        $this->set_repository( \Tainacan\Repositories\Items::get_instance() );
        $this->set_component('tainacan-relationship');
        $this->set_form_component('tainacan-form-relationship');
        $this->set_name( __('Relationship', 'tainacan') );
        $this->set_description( __('A relationship with another item', 'tainacan') );
        $this->set_preview_template('
            <div>
                <div class="taginput control is-expanded has-selected">
                    <div class="taginput-container is-focusable"> 
                        <div class="autocomplete control">
                            <div class="control has-icon-right is-loading is-clearfix">
                                <input type="text" class="input" value="'. __('Item') . ' 9" > 
                            </div> 
                            <div class="dropdown-menu" style="">
                                <div class="dropdown-content">
                                    <a class="dropdown-item is-hovered">
                                        <span>'. __('Collection') . ' 2 <strong>'._('item') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span>'. __('Collection') . ' 3 <strong>'._('item') . ' 9</strong>9</span>
                                    </a>
                                    <a class="dropdown-item">
                                        <span>'. __('Collection') . ' 3 <strong>'._('item') . ' 9</strong>8</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ');
    }

    /**
     * @inheritdoc
     */
    public function get_form_labels(){
       return [
           'collection_id' => [
               'title' => __( 'Related Collection', 'tainacan' ),
               'description' => __( 'Select the collection to fetch items', 'tainacan' ),
           ],
           'search' => [
               'title' => __( 'Metadata to search', 'tainacan' ),
               'description' => __( 'Select the metadata to use as search criteria in the target collection', 'tainacan' ),
           ],
           'repeated' => [
               'title' =>__( 'Allow repeated items', 'tainacan' ),
               'description' => __( 'Allows different items to be related to the same item selected in another collection.', 'tainacan' ),
           ]
       ];
    }

    /**
     * @param $itemMetadata \Tainacan\Entities\Item_Metadata_Entity The instace of the entity itemMetadata
     * @return string
     */

    public function render( $itemMetadata ){
        return '<tainacan-relationship 
                            collection_id="' . $this->get_options()['collection_id'] . '"
                            metadatum_id ="'.$itemMetadata->get_metadatum()->get_id().'" 
                            item_id="'.$itemMetadata->get_item()->get_id().'"    
                            value=\''.json_encode( $itemMetadata->get_value() ).'\'  
                            name="'.$itemMetadata->get_metadatum()->get_name().'"></tainacan-relationship>';
    }
    
    public function validate_options(\Tainacan\Entities\Metadatum $metadatum) {
        if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        if (!empty($this->get_option('collection_id')) && !is_numeric($this->get_option('collection_id'))) {
            return [
                'collection_id' => __('Invalid collection ID','tainacan')
            ];
        } else if( empty($this->get_option('collection_id'))) {
            return [
                'collection_id' => __('The related collection is required','tainacan')
            ];
        }
		
		if  ( !is_array($this->get_option('search')) ) {
			return [
                'search' => __('Search option must be an array','tainacan')
            ];
		}
		
        return true;
    }
	
	/**
	 * Return the value of an Item_Metadata_Entity using a metadatum of this metadatum type as an html string
	 * @param  Item_Metadata_Entity $item_metadata 
	 * @return string The HTML representation of the value, containing one or multiple items names, linked to the item page
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		
		$value = $item_metadata->get_value();
		
		$return = '';
		
		if ( $item_metadata->is_multiple() ) {
			
			$count = 1;
			$total = sizeof($value);
			$prefix = $item_metadata->get_multivalue_prefix();
			$suffix = $item_metadata->get_multivalue_suffix();
			$separator = $item_metadata->get_multivalue_separator();
			
			foreach ( $value as $item_id ) {
				
				try {
					
					//$item = new \Tainacan\Entities\Item($item_id);
					$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
					$item = $Tainacan_Items->fetch( (int) $item_id);
					
					
					$count ++;
					
					if ( $item instanceof \Tainacan\Entities\Item ) {
						
						$return .= $prefix;
						
						$return .= $item->_toHtml();
						
						$return .= $suffix;
						
						if ( $count <= $total ) {
							$return .= $separator;
						}
						
					}
					
					
				} catch (Exception $e) {
					// item not found 
				}
				
			}
			
		} else {
			
			try {
				
				$item = new \Tainacan\Entities\Item($value);
				
				if ( $item instanceof \Tainacan\Entities\Item ) {
					$return .= $item->_toHtml();
				}
				
			} catch (Exception $e) {
				// item not found 
			}
			
		}
		
		return $return;
		
	}
	
}