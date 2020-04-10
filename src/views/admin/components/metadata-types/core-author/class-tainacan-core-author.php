<?php

namespace Tainacan\Metadata_Types;

use Tainacan\Entities\Metadatum;


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanMetadatumType
 */
class Core_Author extends Metadata_Type {

	function __construct(){
		// call metadatum type constructor
		parent::__construct();
		$this->set_primitive_type('user');
		$this->set_core(true);
		$this->set_related_mapped_prop('author_id');
		$this->set_component('tainacan-author');
		$this->set_name( __('Core Author', 'tainacan') );
		$this->set_description( __('The "Core Author" is a compulsory metadata automatically created for all collections by default.', 'tainacan') );
	}

	/**
	 * generate the metadata for this metadatum type
	 */
	public function form(){

	}
	
	/**
	 * Core author metadatum type is stored as the item author
	 *
	 * Lets validate it as the item title
	 *
	 * @param  TainacanEntitiesItem_Metadata_Entity $item_metadata
	 * @return bool Valid or not
	 *
	 * Quarantine - Core metadata should be validated as any other metadata
	 * and item title is no longer mandatory    
	 * public function validate(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) { }
	 */

	public function validate_options( Metadatum $metadatum ) {
		if ( !in_array($metadatum->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
			return true;

		if ( $metadatum->get_multiple() != 'no') {
			return ['multiple' => __('Core Metadata can not accept multiple values', 'tainacan')];
		}

		if ($metadatum->get_required() != 'no') {
			return ['multiple' => __('Core Author Metadata is required', 'tainacan')];
		}

		return true;
	}

	/**
	 * Get the value as a HTML string
	 * @return string
	 */
	public function get_value_as_html(\Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		$value = $item_metadata->get_value();
		$name = get_the_author_meta( 'display_name', $value );
		return apply_filters("tainacan-item-get-author-name", $name, $this);
	}
	
}