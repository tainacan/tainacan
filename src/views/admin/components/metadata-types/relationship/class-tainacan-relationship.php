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
				'title' => __( 'Related Metadatum', 'tainacan' ),
				'description' => __( 'Select the metadata to use as search criteria in the target collection and as a label when representing the relationship', 'tainacan' ),
			],
			'display_in_related_items' => [
				'title' =>__( 'Display in related items', 'tainacan' ),
				'description' => __( 'Include items on related item list.', 'tainacan' ),
			]
		];
	}

	/**
	 * Gets print-ready version of the options list in html
	 *
	 * Checks if at least one option exists, otherwise return an empty string
	 * 
	 * @return string An html content with labels and values for the options or an empty string
	 */
	public function get_options_as_html() {
		$options_as_html = '';
		$options = $this->get_options();
		
		if ( count($options) > 0 ) {

			// Remove this option that is not relevant for the user
			if ( isset($options['related_primitive_type']) )
				unset($options['related_primitive_type']);

			$form_labels = $this->get_form_labels();
				
			foreach($options as $option_label => $option_value) {

				if ( $option_value != '' ) {
					$options_as_html .= '<div class="field"><div class="label">' . ( isset($form_labels[$option_label]) && isset($form_labels[$option_label]['title']) ? $form_labels[$option_label]['title'] : $option_label ) .'</div>';
					
					$readable_option_value = '';

					switch($option_label) {
						
						case 'collection_id':
							$collection = \tainacan_collections()->fetch( (int) $option_value );
							if ( $collection instanceof \Tainacan\Entities\Collection )
								$readable_option_value = $collection->get_name();
							else
								$readable_option_value = $option_value;
						break;

						case 'search':
							$metadata = \tainacan_metadata()->fetch( (int) $option_value );
							if ( $metadata ) {
								$readable_option_value = $metadata;
							} else
								$readable_option_value = $option_value;
						break;

						case 'display_in_related_items':
							if ($option_value == 'yes')
								$readable_option_value = __('Yes', 'tainacan');
							else if ($option_value == 'no')
								$readable_option_value = __('No', 'tainacan');
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
		// empty is ok
		if  ( !empty($this->get_option('search')) && !is_numeric($this->get_option('search')) ) {
			return [
				'search' => __('Search option must be a numeric Metadatum ID','tainacan')
			];
		}
		// empty is ok
		if ( !empty($this->get_option('display_in_related_items')) && !in_array($this->get_option('display_in_related_items'), ['yes', 'no']) ) {
			return [
				'search' => __('Display in related items must be a option yes or no','tainacan')
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
					$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
					$item = $Tainacan_Items->fetch( (int) $item_id);
					
					$count++;
					
					if ( $item instanceof \Tainacan\Entities\Item ) {
						$return .= $prefix;
						$return .= $this->get_item_html($item);
						$return .= $suffix;

						if ( $count <= $total ) {
							$return .= $separator;
						}
					}

				} catch (\Exception $e) {
					// item not found
				}
			}
		} else {
			try {
				$item = new \Tainacan\Entities\Item($value);
				if ( $item instanceof \Tainacan\Entities\Item ) {
					$return .= $this->get_item_html($item);
				}
			} catch (\Exception $e) {
				// item not found 
			}
		}

		return $return;
	}

	private function get_item_html($item) {
		$return = '';
		$id = $item->get_id();
		
		$search_meta_id = $this->get_option('search');
		
		if ( $id && $search_meta_id ) {
			$link = get_permalink( (int) $id );
			
			$search_meta_id = $this->get_option('search');
			
			$metadatum = \Tainacan\Repositories\Metadata::get_instance()->fetch((int) $search_meta_id);
			
			$label = '';
			
			if ($metadatum instanceof \Tainacan\Entities\Metadatum) {
				$item_meta = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
				$label = $item_meta->get_value_as_string();
			}
			
			if ( empty($label) ) {
				$label = $item->get_title();
			}
			
			if (is_string($link)) {
				if ( is_user_logged_in() ||
					\is_post_status_viewable( $item->get_status() ) &&
					\is_post_status_viewable($item->get_collection()->get_status()) ) {
					$return = "<a data-linkto='item' data-id='$id' href='$link'>";
					$return.= $label;
					$return .= "</a>";
				} else {
					$return.= $label;
				}
			}
		}

		return $return;
	}

	/**
	 * Get related Collection object 
	 * @return \Tainacan\Entities\Collection|false The Collection object or false
	 */
	public function get_collection() {
		$collection_id = $this->get_option('collection_id');
		
		if ( is_numeric($collection_id) ) {
			$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $collection_id );
			if ( $collection instanceof \Tainacan\Entities\Collection ) {
				return $collection;
			}
		}
		
		return false;
	}

	/**
	 * Gets the options for this metadatum types, including default values for options
	 * that were not set yet.
	 * @return array Metadatum type options
	 */
	public function get_options() {
		$opt = parent::get_options();
		if ( isset($opt['search']) && !empty($opt['search']) ) {
			$search_id = $opt['search'];
			$metadata = \Tainacan\Repositories\Metadata::get_instance()->fetch($search_id, 'OBJECT');
			if( $metadata instanceof \Tainacan\Entities\Metadatum ) {
				$opt = array_merge(['related_primitive_type' => $metadata->get_metadata_type_object()->get_primitive_type()], $opt);

				if ($metadata->get_metadata_type() == 'Tainacan\Metadata_Types\Taxonomy') {
					$taxonomy_id = $metadata->get_metadata_type_options()['taxonomy_id'];
					return array_merge(['search_by_tax' => $taxonomy_id], $opt);
				}
			}
		}
		return $opt;
	}
}