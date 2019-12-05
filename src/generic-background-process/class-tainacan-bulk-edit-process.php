<?php
namespace Tainacan\GenericBackgroundProcess;
use Tainacan;
use Tainacan\Entities;

class Bulk_Edit_Process extends Generic_Process {

	private $meta_key = '_tnc_bulk';

	public function __construct($attributes = array()) {
		$this->array_attributes = array_merge($this->array_attributes, [
			'bulk_id',
			'bulk_edit_data'
		]);
		parent::__construct($attributes);
		$this->init_objects();
	}

	public function init_objects() {
		$this->items_repository = \Tainacan\Repositories\Items::get_instance();
		$this->metadatum_repository = \Tainacan\Repositories\Metadata::get_instance();
		$this->item_metadata_repository = \Tainacan\Repositories\Item_Metadata::get_instance();
	}

	public function set_bulk_id($bulk_id) { //TODO bulk_id or ID? 
		$this->bulk_id = $bulk_id;
		$this->id = $this->bulk_id;
	}

	public function get_bulk_id( ) {
		return $this->bulk_id;
	}

	public function get_output() {
		$message = __('Bulk-edit end', 'tainacan');
		return $message;
	}

	public function set_bulk_edit_data($bulk_edit_data = false) {
		$this->bulk_edit_data = $bulk_edit_data;
	}

	public function get_bulk_edit_data() {
		return $this->bulk_edit_data;
	}

	private function bulk_list_remove_item($item, $meta_key, $meta_value) {
		return delete_post_meta( $item->get_id(), $meta_key, $meta_value);
	}

	private function bulk_list_get_item() {
		$args = [
			'perpage' => 1,
			'meta_query' => array(
				array(
					'key' => $this->meta_key,
					'value' => $this->bulk_id,
					'compare' => '=',
				)
			)
		];
		$item = $this->items_repository->fetch($args, [], 'OBJECT');
		if (is_array($item) && !empty($item))
			$item = $item[0];
		if ($item instanceof \Tainacan\Entities\Item)
			return $item;
		return false;
	}

	public function main_process() {
		$method = $this->bulk_edit_data['method'];
		if ( !method_exists($this, $method) ) {
			$this->add_error_log(__('method not exists', 'tainacan'));
			$this->abort();
			return false;
		}

		$item = $this->bulk_list_get_item();
		if($item == false) {
			return false;
		}

		$this->add_log( sprintf( __('bulk edit item ID: "%d"', 'tainacan'), $item->get_id() ) );
		$this->$method($item);
		$this->bulk_list_remove_item($item, $this->meta_key, $this->bulk_id);
		return $item->get_id();

	}

	private function save_item_metadata(\Tainacan\Entities\Item_Metadata_Entity $item_metadata, \Tainacan\Entities\Item $item) {
		if ( $item_metadata->validate() ) {
			if( $item->can_edit() ) {
				$updated_item_metadata = $this->item_metadata_repository->update( $item_metadata );
			} else {
				$this->add_error_log( sprintf( __('do not have permission to edit item ID: "%d"', 'tainacan'), $item->get_id() ) );
				return false;
			}
		} else {
			$this->add_error_log( sprintf( __( 'Please verify, invalid value(s) to edit item ID: "%d"', 'tainacan' ), $item->get_id() ) );
			$this->add_error_log($item_metadata->get_errors());
			return false;
		}
		return true;
	}

	private function set_value(\Tainacan\Entities\Item $item) {
		$metadatum = $this->metadatum_repository->fetch($this->bulk_edit_data['metadatum_id']);
		$value = $this->bulk_edit_data['value'];

		$item_metadata = new Entities\Item_Metadata_Entity( $item, $metadatum );

		if($item_metadata->is_multiple()) {
			$item_metadata->set_value( $value );
		} elseif(is_array($value)) {
			$item_metadata->set_value(implode(' ', $value));
		} else {
			$item_metadata->set_value($value);
		}

		return $this->save_item_metadata($item_metadata, $item);

	}

	private function add_value(\Tainacan\Entities\Item $item) {
		$metadatum_id = $this->bulk_edit_data['metadatum_id'];
		$metadatum = $this->metadatum_repository->fetch($metadatum_id);
		$value = $this->bulk_edit_data['value'];

		if (!$metadatum->is_multiple()) {
			$this->add_error_log( __( 'Unable to add a value to a metadata if it does not accept multiple values', 'tainacan' ) );
			return false;
		}
		if ($metadatum->is_collection_key()) {
			$this->add_error_log( __( 'Unable to add a value to a metadata set to be a collection key', 'tainacan' ) );
			return false;
		}

		$items_metadata = $item->get_metadata();
		
		foreach ($items_metadata as $item_metadata) {
			$metadatum = $item_metadata->get_metadatum();
			if($metadatum->get_id() == $metadatum_id) {
				$values = is_array($item_metadata->get_value()) ? $item_metadata->get_value() : [$item_metadata->get_value()];
				$values = array_merge($values, [$value]);
				$item_metadata->set_value( $values );
				return $this->save_item_metadata($item_metadata, $item);
			}
		}

		return false;
	}

	private function remove_value(\Tainacan\Entities\Item $item) {
		$metadatum_id = $this->bulk_edit_data['metadatum_id'];
		$metadatum = $this->metadatum_repository->fetch($metadatum_id);
		$value = $this->bulk_edit_data['value'];

		if ($metadatum->is_required()) {
			$this->add_error_log( __( 'Unable to remove a value from a required metadatum', 'tainacan' ) );
			return false;
		}
		if (!$metadatum->is_multiple()) {
			$this->add_error_log( __( 'Unable to remove a value from a metadata if it does not accept multiple values', 'tainacan' ) );
			return false;
		}

		$items_metadata = $item->get_metadata();
		
		foreach ($items_metadata as $item_metadata) {
			$metadatum = $item_metadata->get_metadatum();
			if($metadatum->get_id() == $metadatum_id) {
				$values = is_array($item_metadata->get_value()) ? $item_metadata->get_value() : [$item_metadata->get_value()];
				$pos = array_search($value, $values);
				unset($values[$pos]);
				$item_metadata->set_value( $values );
				return $this->save_item_metadata($item_metadata, $item);
			}
		}
		return false;
	}

	private function replace_value(\Tainacan\Entities\Item $item) {
		$metadatum_id = $this->bulk_edit_data['metadatum_id'];
		$metadatum = $this->metadatum_repository->fetch($metadatum_id);
		$old_value = $this->bulk_edit_data['old_value'];
		$new_value = $this->bulk_edit_data['value'];

		if ($metadatum->is_collection_key()) {
			$this->add_error_log( __( 'Unable to set a value to a metadata set to be a collection key', 'tainacan' ) );
			return false;
		}
		
		if ($new_value == $old_value) {
			$this->add_error_log( __( 'Old value and new value can not be the same', 'tainacan' ) );
			return false;
		}

		$items_metadata = $item->get_metadata();

		foreach ($items_metadata as $item_metadata) {
			$metadatum = $item_metadata->get_metadatum();
			if($metadatum->get_id() == $metadatum_id) {
				$values = is_array($item_metadata->get_value()) ? $item_metadata->get_value() : [$item_metadata->get_value()];
				$pos = array_search($old_value, $values);
				if ($pos != false) {
					$values[$pos] = $new_value;
					$item_metadata->set_value( $values );
					return $this->save_item_metadata($item_metadata, $item);
				}
				return false;
			}
		}
		return false;
	}

}