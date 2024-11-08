<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Item Metadatum Entity
 */
class Item_Metadata_Entity extends Entity {
	protected static $post_type = false;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Item_Metadata';
	
	protected
		$item,
		$metadatum,
		$parent_meta_id,
		$meta_id,
		$has_value,
		$value;
	
	/**
	 * 
	 * @param Item   $item    Item Entity
	 * @param Metadatum  $metadatum   Metadatum Entity
	 * @param int $meta_id ID for a specific meta row 
	 */
	function __construct(Item $item = null, Metadatum $metadatum = null, $meta_id = null, $parent_meta_id = null) {
		$this->set_item($item);
		$this->set_metadatum($metadatum);

		if (!is_null($meta_id) && is_int($meta_id)) {
			$this->set_meta_id($meta_id);
		}

		if (!is_null($parent_meta_id) && is_int($parent_meta_id)) {
			$this->set_parent_meta_id($parent_meta_id);

			//set the meta_id according to item metadatum and parent_meta_id
			$childrens = get_metadata_by_mid( 'post', $parent_meta_id );
			if ( is_object( $childrens ) ) {
				$childrens = $childrens->meta_value;
				if ( is_array($childrens) && !empty($childrens) ) {
					$childrens_in = implode(',', $childrens);
					global $wpdb;
					$item_metadata = $wpdb->get_results( $wpdb->prepare(
						"SELECT * FROM $wpdb->postmeta
							WHERE post_id = %d AND 
										meta_key = %s AND
										meta_id IN ($childrens_in)",
							$item->get_id(),
							$metadatum->get_id() 
					), ARRAY_A );
					if( is_array($item_metadata) && !empty($item_metadata) ) {
						$meta_id = (int)$item_metadata[0]['meta_id'];
						$this->set_meta_id($meta_id);
					}
				}
			}
		}
	}
	
	/**
	 * Gets the string used before each value when concatenating multiple values 
	 * to display item metadata value as html or string 
	 * 
	 * @return string 
	 */
	public function get_multivalue_prefix() {
		$metadatum = $this->get_metadatum();
		$value = '';
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				
				if ( method_exists($fto, 'get_multivalue_prefix') ) {
					$value = $fto->get_multivalue_prefix();
				}
				
			}
		}
		return apply_filters('tainacan-item-metadata-get-multivalue-prefix', $value, $this);
	}
	
	/**
	 * Gets the string used after each value when concatenating multiple values 
	 * to display item metadata value as html or string 
	 * 
	 * @return string 
	 */
	public function get_multivalue_suffix() {
		$metadatum = $this->get_metadatum();
		$value = '';
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				
				if ( method_exists($fto, 'get_multivalue_suffix') ) {
					$value = $fto->get_multivalue_suffix();
				}
				
			}
		}
		return apply_filters('tainacan-item-metadata-get-multivalue-suffix', $value, $this);
	}
	
	/**
	 * Gets the string used in between each value when concatenating multiple values 
	 * to display item metadata value as html or string 
	 * 
	 * @return string 
	 */
	public function get_multivalue_separator() {
		$metadatum = $this->get_metadatum();
		$value = '<span class="multivalue-separator"> | </span>';
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				
				if ( method_exists($fto, 'get_multivalue_separator') ) {
					$value = $fto->get_multivalue_separator();
				}
				
			}
		}
		return apply_filters('tainacan-item-metadata-get-multivalue-separator', $value, $this);
	}
	
	/**
	 * Get the value as a HTML string, with markup and links
	 * @return string
	 */
	public function get_value_as_html() {
		$metadatum = $this->get_metadatum();
		
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				if ( method_exists($fto, 'get_value_as_html') ) {
					return $fto->get_value_as_html($this);
				}
			}
		}
		
		$value = $this->get_value();

		if ($value === false)
			return '';
		
		$return = '';
		
		if ( $this->is_multiple() ) {
			$total = sizeof($value);
			$count = 0;
			$prefix = $this->get_multivalue_prefix();
			$suffix = $this->get_multivalue_suffix();
			$separator = $this->get_multivalue_separator();
			
			foreach ($value as $v) {
				$return .= $prefix;
				$return .= (string) $v;
				$return .= $suffix;
				$count ++;
				if ($count < $total)
					$return .= $separator;
			}

		} else {
			$return = (string) $value;
		}

		return $return;
	}

	/**
	 * Get the value as a plain text string
	 * @return string
	 */
	public function get_value_as_string() {
		$metadatum = $this->get_metadatum();
		if (is_object($metadatum)) {
			$fto = $metadatum->get_metadata_type_object();
			if (is_object($fto)) {
				if ( method_exists($fto, 'get_value_as_string') ) {
					return $fto->get_value_as_string($this);
				}
			}
		}
		return strip_tags($this->get_value_as_html());
	}
	
	/**
	 * Get value as an array
	 * @return [type] [description]
	 */
	public function get_value_as_array() {
		$value = $this->get_value();
		$primitive_type = $this->get_metadatum()->get_metadata_type_object()->get_primitive_type();
		$return = [];

		if ($this->is_multiple()) {
			foreach ($value as $v) {
				if( is_array($v) ) {
					$options = $this->get_metadatum()->get_metadata_type_object()->get_options();
					$order = $options['children_order'];
					$compounds = [];
					$compounds_not_ordinate = [];
					foreach ($v as $metadatum_id => $itemMetadata) {
						if ( $itemMetadata instanceof Item_Metadata_Entity ) {
							$index = array_search( $metadatum_id, array_column( $order, 'id' ) );
							if ( $index !== false ) {
								$compounds[$index] = array_merge( ['metadatum_id' => $metadatum_id], $itemMetadata->_toArray() );
							} else {
								$compounds_not_ordinate[] = array_merge( ['metadatum_id' => $metadatum_id], $itemMetadata->_toArray() );
							}
						}
					}
					ksort( $compounds );
					$return[] = array_merge($compounds, $compounds_not_ordinate);
				} else if ( $v instanceof Term || $v instanceof Item_Metadata_Entity ) {
					$return[] = $v->_toArray();
				} else {
					$return[] = $v;
				}
			}
		} else {
			if ($primitive_type === 'compound') {
				$compounds = [];
				$compounds_not_ordinate = [];
				$options = $this->get_metadatum()->get_metadata_type_object()->get_options();
				$order = $options['children_order'];

				//dealing with categories
				if( isset( $options['children_objects'] ) ) {
					foreach ( $options['children_objects'] as $child ) {
						$metadata = new Metadatum( $child['id'] );
						$itemMetadata = new self( $this->get_item(), $metadata );
						$child_primitive_type = $metadata->get_metadata_type_object()->get_primitive_type();
						if ( $itemMetadata instanceof Item_Metadata_Entity && $child_primitive_type === 'term' ) {
							$compounds[] = array_merge( ['metadatum_id' => $child['id']], $itemMetadata->_toArray() );
						}
					}
				}

				if( is_array($value) ) {
					foreach ($value as $itemMetadata) {
						$child_primitive_type = $itemMetadata->get_metadatum()->get_metadata_type_object()->get_primitive_type();
						if ( $itemMetadata instanceof Item_Metadata_Entity && $child_primitive_type !== 'term' ) {
							$metadatum_id = $itemMetadata->get_metadatum()->get_id();
							$index = array_search( $metadatum_id, array_column( $order, 'id' ) );
							if ( $index !== false ) {
								$compounds[$index] = array_merge( ['metadatum_id' => $metadatum_id], $itemMetadata->_toArray() );
							} else {
								$compounds_not_ordinate[] = array_merge( ['metadatum_id' => $metadatum_id], $itemMetadata->_toArray() );
							}
						}
					}
				}
				ksort( $compounds );
				$return = array_merge($compounds, $compounds_not_ordinate);
			} else if ( $value instanceof Term || $value instanceof Item_Metadata_Entity ) {
				$return = $value->_toArray();
			} else {
				$return = $value;
			}
		}

		return $return;
	}
	
	/**
	 * Convert the object to an Array
	 *
	 * @param bool $formatted_values Whether to add or not values formatted as html and string to the response
	 * @param bool $cascade Whether to add or not Item and Metadatum Entities as arrays to the response
	 * 
	 * @return array the representation of this object as an array
	 */
	public function _toArray( $formatted_values = true, $cascade = false ){
		$as_array = [];
		
		$as_array['value'] = $this->get_value_as_array();
		$as_array['parent_meta_id'] = $this->get_parent_meta_id();
		if ( $formatted_values ) {
			$as_array['value_as_html'] = $this->get_value_as_html();
			$as_array['value_as_string'] = $this->get_value_as_string();

			if($this->get_metadatum()->get_metadata_type_object()->get_primitive_type() === 'date'){
				$as_array['date_i18n'] = $this->get_date_i18n($this->get_value_as_string());
			}
		}
		
		if ( $cascade ) {
			$as_array['item']  = $this->get_item()->_toArray();
			$as_array['metadatum'] = $this->get_metadatum()->_toArray();
		}
		

		return apply_filters('tainacan-item-metadata-to-array', $as_array, $this);
	}
	
	/**
	 * Define the item
	 *
	 * @param Item $item
	 * @return void
	 */
	function set_item(Item $item = null) {
		$this->item = $item;
	}
	
	/**
	 * Define the metadatum value
	 *
	 * @param [integer | string] $value
	 * @return void
	 */
	function set_value($value) {
		$this->value = $value;
	}
	
	/**
	 * Define the metadatum
	 *
	 * @param Metadatum $metadatum
	 * @return void
	 */
	function set_metadatum(Metadatum $metadatum = null) {
		$this->metadatum = $metadatum;
	}
	
	/**
	 * Set the specific meta ID for this metadata.
	 *
	 * When this value is set, get_value() will use it to fetch the value from 
	 * the post_meta table, instead of considering the item and metadatum IDs
	 * 
	 * @param int $meta_id the ID of a specifica post_meta row
	 */
	function set_meta_id($meta_id) {
		if (is_int($meta_id)) {
			$this->meta_id = $meta_id;
			return true;
			// TODO: Should we check here to see if this meta_id is really from this metadatum and item?
		}
		return false;
	}
	
	/**
	 * Set parent_meta_id. Used when a item_metadata is inside a compound Metadatum 
	 *
	 * When you have a multiple compound metadatum, this indicates of which instace of the value this item_metadata is attached to
	 * 
	 * @param [type] $parent_meta_id [description]
	 */
	function set_parent_meta_id($parent_meta_id) {
		if (is_int($parent_meta_id)) {
			$this->parent_meta_id = $parent_meta_id;
			return true;
			// TODO: Should we check here to see if this meta_id is really from this metadatum and item?
		}
		return false;
	}
	
	/**
	 * Return the item
	 *
	 * @return Item
	 */
	function get_item() {
		return $this->item;
	}
	
	/**
	 * Return the metadatum
	 *
	 * @return Metadatum
	 */
	function get_metadatum() {
		return $this->metadatum;
	}
	
	/**
	 * Return the meta_id
	 *
	 * @return Metadatum
	 */
	function get_meta_id() {
		return isset($this->meta_id) ? $this->meta_id : null;
	}
	
	/**
	 * Return the meta_id
	 *
	 * @return Metadatum
	 */
	function get_parent_meta_id() {
		return isset($this->parent_meta_id) ? $this->parent_meta_id : 0;
	}
	
	/**
	 * Return the metadatum value
	 *
	 * @return string|integer|Array
	 */
	function get_value() {
		if (isset($this->value))
			return $this->value;
		
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		return $Tainacan_Item_Metadata->get_value($this);
	}
	
	/**
	 * Check whether the item has a value stored in the database or not
	 *
	 * @return bool
	 */
	function has_value() {
		if (isset($this->has_value))
			return $this->has_value;
		
		$value = $this->get_value();
		$this->has_value = (is_array($value)) ? !empty(array_filter($value)) : (is_numeric($value) || !empty($value));
		return $this->has_value;
	}
	
	/**
	 * Return true if metadatum is multiple, else return false
	 *
	 * @return boolean
	 */
	function is_multiple() {
		return $this->get_metadatum()->is_multiple();
	}
	
	/**
	 * Return true if metadatum is key
	 *
	 * @return boolean
	 */
	function is_collection_key() {
		return $this->get_metadatum()->is_collection_key();
	}
	
	/**
	 * Return true if metadatum is required
	 *
	 * @return boolean
	 */
	function is_required() {
		return $this->get_metadatum()->is_required();
	}
	
	/**
	 * Returns whether metadata value is valid
	 *
	 * @return boolean
	 */
	function validate() {
		$value = $this->get_value();
		$metadatum = $this->get_metadatum();
		$item = $this->get_item();

		if (!isset($metadatum)) {
			$this->add_error('not_found', ['metadatum not found'] );
			return false;
		}

		if (empty($value) && $value !== '0' && $value !== 0) {
			if ($this->is_required()) {
				$validation_statuses = ['publish', 'future', 'private'];
				if (in_array($item->get_status(), apply_filters( 'tainacan-status-require-validation', $validation_statuses) )) {
					// translators: %s = metadatum name. ex: Title is required
					$this->add_error( 'required', sprintf( __('%s is required', 'tainacan'), $metadatum->get_name() ) );
					return false;
				} else {
					return $this->set_as_valid();
				}
			} else {
				return $this->set_as_valid();
			}
		}

		$classMetadatumType = $metadatum->get_metadata_type_object();
		if (is_object($classMetadatumType)) {
			if( method_exists ( $classMetadatumType , 'validate' ) ){
				if( ! $classMetadatumType->validate( $this ) ) {
					$this->add_error('metadata_type_error', $classMetadatumType->get_errors() );
					return false;
				}
			}
		}

		if ($this->is_multiple()) {
			if (is_array($value)) {
				$cardinality = $metadatum->get_cardinality();
				if ( !empty($cardinality) && $cardinality > 1 && count($value) > $cardinality ) {
					$this->add_error( 'invalid', sprintf( __('Metadatum %s is set to accept a maximum of %s values.', 'tainacan'), $metadatum->get_name(), $cardinality ) );
					return false;
				}
				// if its required, at least one must be filled
				$one_filled = false;
				$valid = true;
				$dupe_array = array();
				foreach($value as $val) {
					if (!empty($val))
						$one_filled = true;

					if ($this->is_collection_key()) {
						if ( !isset($dupe_array[$val]) ) {
							$dupe_array[$val] = 1;
						} else
						if (++$dupe_array[$val] > 1) {
							$this->add_error( 'key_exists', sprintf( __('%s is a collection key and there is another item with the same value', 'tainacan'), $metadatum->get_name() ) );
							return false;
						}

						$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
						$test = $Tainacan_Items->fetch([
							'meta_query' => [
								[
									'key'   => $this->metadatum->get_id(),
									'value' => $val
								]
							],
							'post__not_in' => [$item->get_id()]
						], $item->get_collection());

						if ($test->have_posts()) {
							// translators: %s = metadatum name. ex: Register ID is a collection key and there is another item with the same value
							$this->add_error( 'key_exists', sprintf( __('%s is a collection key and there is another item with the same value', 'tainacan'), $metadatum->get_name() ) );
							return false;
						}
					}
				}

				if ($this->is_required() && !$one_filled) {
					// translators: %s = metadatum name. ex: Title is required
					$this->add_error( 'required', sprintf( __('%s is required', 'tainacan'), $metadatum->get_name() ) );
					return false;
				}

				if (!$valid) {
					// translators: %s = metadatum name. ex: Title is invalid
					$this->add_error( 'invalid', sprintf( __('%s is invalid', 'tainacan'), $metadatum->get_name() ) );
					return false;
				}

				$this->set_as_valid();
				return true;
			} else {
				// translators: %s = metadatum name. ex: Title is invalid
				$this->add_error( 'invalid', sprintf( __('%s is invalid', 'tainacan'), $metadatum->get_name() ) );
				return false;
			}
		} else {

			if( is_array($value) ){
				// translators: %s = metadatum name. ex: Title accepts only one single value and not a list of values
				$this->add_error( 'not_multiple', sprintf( __('%s accepts only one single value and not a list of values', 'tainacan'), $metadatum->get_name() ) );
				return false;
			}
			
			if ($this->is_collection_key()) {
				$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
				if($metadatum->get_parent()) {
					$patent_metadatum = \tainacan_metadata()->fetch( $metadatum->get_parent(), 'OBJECT' );
					if($patent_metadatum->is_multiple()) {
						global $wpdb;
						$test = get_post_meta( $item->get_id(), $this->metadatum->get_id(), true);
						$rows = $wpdb->get_results(
							$wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s AND meta_value = %s AND meta_id <> %d",
							$item->get_id(), $this->metadatum->get_id(), $value, $this->get_meta_id()),
						ARRAY_A );
						if ( is_array( $rows ) &&  count($rows) > 0 ) {
							if( !$this->get_meta_id() ) {
								$current_meta_value = array_column($rows, "meta_value");
								if ( count($current_meta_value) !== count(array_unique($current_meta_value)) ) {
									// translators: %s = metadatum name. ex: Register ID is a collection key and there is another item with the same value
									$this->add_error( 'key_exists', sprintf( __('%s is a collection key and there is another item with the same value', 'tainacan'), $metadatum->get_name() ) );
									return false;
								}
							} else {
								// translators: %s = metadatum name. ex: Register ID is a collection key and there is another item with the same value
								$this->add_error( 'key_exists', sprintf( __('%s is a collection key and there is another item with the same value', 'tainacan'), $metadatum->get_name() ) );
								return false;
							}
						}
					}
				}
				$test = $Tainacan_Items->fetch([
					'meta_query' => [
						[
							'key'   => $this->metadatum->get_id(),
							'value' => $value
						],
					],
					'post__not_in' => [$item->get_id()]
				], $item->get_collection());

				if ($test->have_posts()) {
					// translators: %s = metadatum name. ex: Register ID is a collection key and there is another item with the same value
					$this->add_error( 'key_exists', sprintf( __('%s is a collection key and there is another item with the same value', 'tainacan'), $metadatum->get_name() ) );
					return false;
				}
			}

			$this->set_as_valid();
			return true;
		}
	}
}