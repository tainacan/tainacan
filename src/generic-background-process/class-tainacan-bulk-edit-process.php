<?php
namespace Tainacan\GenericBackgroundProcess;
use Tainacan;
use Tainacan\Entities;

class Bulk_Edit_Process extends Generic_Process {

	private $meta_key = '_tnc_bulk';

	public function __construct($attributes = array()) {
		$this->array_attributes = array_merge($this->array_attributes, [
			'group_id',
			'bulk_edit_data'
		]);
		parent::__construct($attributes);
		$this->init_objects();
	}

	public function init_objects() {
		$this->steps = [
			[
				'name' => __('Create control metadada', 'tainacan'),
				'progress_label' => __('Generic process', 'tainacan'),
				'callback' => 'add_control_metadata'
			],[
				'name' => 'Main process',
				'progress_label' => 'Generic process',
				'callback' => 'main_process'
			]
		];
		$this->items_repository = \Tainacan\Repositories\Items::get_instance();
		$this->metadatum_repository = \Tainacan\Repositories\Metadata::get_instance();
		$this->item_metadata_repository = \Tainacan\Repositories\Item_Metadata::get_instance();
	}

	public function create_bulk_edit($params) {
		if ( isset($params['group_id']) && !empty($params['group_id']) ) {
			$this->group_id = $params['group_id'];
			return;
		}
		$this->group_id = uniqid();
		if (isset($params['query']) && is_array($params['query'])) {
			if (!isset($params['collection_id']) || !is_numeric($params['collection_id'])) {
				throw new \Exception('Collection ID must be informed when creating a group via query');
			}
			$bulk_params = [
				'query' 	=> $params['query'],
				'order' 	=> isset($params['query']['order']) ? $params['query']['order'] : 'DESC',
				'orderby' => isset($params['query']['orderby']) ? $params['query']['orderby'] : 'post_date'
			];
		} elseif (isset($params['items_ids']) && is_array($params['items_ids'])) {
			$items_ids = array_filter($params['items_ids'], 'is_integer');
			$bulk_params = [
				'items_ids' => $items_ids,
				'order' 		=> isset($params['options']['order']) ? $params['options']['order'] : 'DESC',
				'orderby' 	=> isset($params['options']['orderby']) ? $params['options']['orderby'] : 'post_date'
			];
		}
		$this->save_options($bulk_params);
		return;
	}

	public function save_options($value) {
		update_option('tainacan_bulk_' . $this->get_group_id(), $value);
	}
	
	public function get_options() {
		return get_option('tainacan_bulk_' . $this->get_group_id());
	}

	public function set_group_id($group_id) {
		$this->group_id = $group_id;
	}

	public function get_group_id( ) {
		return $this->group_id;
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

	private function bulk_list_remove_item($item) {
		return delete_post_meta( $item->get_id(), $this->meta_key, $this->get_group_id());
	}

	public function add_control_metadata() {
		$params = $this->get_options();

		if( !isset($params['control_metadata']) ) {
			$params['control_metadata'] = $this->get_id();
			$this->save_options($params);
		} elseif ($params['control_metadata'] === true) {
			$this->add_log( __('bulk edit control metadata has already been created', 'tainacan') );
			return false;
		} elseif( is_numeric($params['control_metadata']) && $params['control_metadata'] != $this->get_id() ) {
			$this->add_log( sprintf( __( 'waiting creating bulk edit control metadata by process ID: "%d"', 'tainacan' ), $params['control_metadata'] ) );
			return true;
		}

		if (isset($params['query']) && is_array($params['query'])) {
			$itemsRepo = \Tainacan\Repositories\Items::get_instance();
			$count = $this->get_in_step_count();

			$params['query']['fields'] = 'ids';
			$params['query']['posts_per_page'] = 1;
			$params['query']['offset'] = $count++;
			$params['query']['nopaging'] = false;

			$item_query = $itemsRepo->fetch($params['query'], $params['collection_id']);
			if(!$item_query->have_posts() ) {
				$params['control_metadata'] = true;
				$this->save_options($params);
				$this->add_log( __('bulk edit control metadata created', 'tainacan') );
				return false;
			}
			$item_id = $item_query->get_posts()[0];
			$this->add_log( sprintf( __( 'creating bulk edit control metadata for item: "%d"', 'tainacan' ), $item_id ) );
			add_post_meta($item_id, $this->meta_key, $this->get_group_id());
			return $count;
		} elseif (isset($params['items_ids']) && is_array($params['items_ids'])) {
			$items_ids = array_filter($params['items_ids'], 'is_integer');

			$count = $this->get_in_step_count();
			if( isset($items_ids[$count]) ) {
				$this->add_log( sprintf( __( 'creating bulk edit control metadata for item: "%d"', 'tainacan' ), $items_ids[$count] ) );
				add_post_meta($items_ids[$count++], $this->meta_key, $this->get_group_id());
				return $count;
			} else {
				$params['control_metadata'] = true;
				$this->save_options($params);
				$this->add_log( __('bulk edit control metadata created', 'tainacan') );
				return false;
			}
		}

		$this->add_error_log(__('wrong parameter on add bulk edit control metadata', 'tainacan'));
		$this->abort();
		return false;
	}

	private function bulk_list_get_item($count) {
		$args = [
			'perpage' => 1,
			'offset' => $count,
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			'meta_query' => array(
				array(
					'key' => $this->meta_key,
					'value' => $this->get_group_id(),
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
		$count = $this->get_in_step_count();
		$item = $this->bulk_list_get_item($count++);
		if($item == false) {
			return false;
		}

		$this->add_log( sprintf( __('bulk edit item ID: "%d"', 'tainacan'), $item->get_id() ) );
		$this->$method($item);
		return $count;
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

	private function trash_items(\Tainacan\Entities\Item $item) {
		if ( !$this->items_repository->trash($item) ) {
			$this->add_error_log( sprintf( __('error on send to trash, item ID: "%d"', 'tainacan'), $item->get_id() ) );
			return false;
		}
		return true;
	}

	private function untrash_items(\Tainacan\Entities\Item $item) {
		if ( !wp_untrash_post( $item->get_id() ) ) {
			$this->add_error_log( sprintf( __('error on untrash, item ID: "%d"', 'tainacan'), $item->get_id() ) );
			return false;
		}
		return true;
	}

	private function delete_items(\Tainacan\Entities\Item $item) {
		if ( !$this->items_repository->delete($item) ) {
			$this->add_error_log( sprintf( __('error on send to trash, item ID: "%d"', 'tainacan'), $item->get_id() ) );
			return false;
		}
		return true;
	}

	private function set_status(\Tainacan\Entities\Item $item) {
		$value = $this->bulk_edit_data['value'];
		$possible_values = ['trash', 'draft', 'publish', 'private'];

		if (!in_array($value, $possible_values)) {
			$this->add_error_log( __( 'Invalid status', 'tainacan' ) );
			return false;
		}

		$item->set("status", $value);
		if($item->validate()) {
			$this->items_repository->update($item);
			return true;
		}

		$this->add_error_log( sprintf( __( 'Please verify, invalid value(s) to edit item ID: "%d"', 'tainacan' ), $item->get_id() ) );
		$this->add_error_log($item->get_errors());
		return false;
	}

}