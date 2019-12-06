<?php
namespace Tainacan\GenericBackgroundProcess;
use Tainacan;
use Tainacan\Entities;

class Bulk_Edit_Process extends Generic_Process {

	private $meta_key = '_tnc_bulk';

	public function __construct($attributes = array()) {
		$this->array_attributes = array_merge($this->array_attributes, [
			'id',
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

	/**
	 * Internally used to filter WP_Query and build the INSERT statement. 
	 * Must be public because it is registered as a filter callback
	 */
	public function add_fields_to_query($fields, $wp_query) {
		global $wpdb;
		if ( $wp_query->get('fields') == 'ids' ) { // just to make sure we are in the right query
			$fields .= $wpdb->prepare( ", %s, %s", $this->meta_key, $this->get_id() );
		}
		return $fields;
	}

	public function create_bulk_edit($params) {
		if ( isset($params['id']) && !empty($params['id']) ) {
			$this->id = $params['id'];
			return;
		}

		global $wpdb;

		if (isset($params['query']) && is_array($params['query'])) {
			if (!isset($params['collection_id']) || !is_numeric($params['collection_id'])) {
				throw new \Exception('Collection ID must be informed when creating a group via query');
			}
			
			/**
			 * Here we use the fetch method to parse the parameter and use WP_Query
			 *
			 * However, we add a filter so the query is not executed. We just want WP_Query to build it for us
			 * and then we can use it to INSERT the postmeta with the bulk group ID
			 */
			
			// this avoids wp_query to run the query. We just want to build the query
			add_filter('posts_pre_query', '__return_empty_array');
			
			// this adds the meta key and meta value to the SELECT query so it can be used directly in the INSERT below
			add_filter('posts_fields_request', [$this, 'add_fields_to_query'], 10, 2);
			
			$itemsRepo = Repositories\Items::get_instance();
			$params['query']['fields'] = 'ids';
			$items_query = $itemsRepo->fetch($params['query'], $params['collection_id']);
			
			remove_filter('posts_pre_query', '__return_empty_array');
			remove_filter('posts_fields_request', [$this, 'add_fields_to_query']);
			
			$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) {$items_query->request}" );
			
			$bulk_params = [
				'orderby' => isset($params['query']['orderby']) ? $params['query']['orderby'] : 'post_date',
				'order' => isset($params['query']['order']) ? $params['query']['order'] : 'DESC'
			];
			
		} elseif (isset($params['items_ids']) && is_array($params['items_ids'])) {
			$items_ids = array_filter($params['items_ids'], 'is_integer');
			
			$insert_q = '';
			foreach ($items_ids as $item_id) {
				$insert_q .= $wpdb->prepare( "(%d, %s, %s),", $item_id, $this->meta_key, $this->get_id() );
			}
			$insert_q = rtrim($insert_q, ',');
			
			$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES $insert_q" );
			
			$bulk_params = [
				'orderby' => isset($params['options']['orderby']) ? $params['options']['orderby'] : 'post_date',
				'order' => isset($params['options']['order']) ? $params['options']['order'] : 'DESC'
			];
			
		}
		
		/**
		* This is stored to be used by the get_sequence_item_by_index() method, which is used 
		* by the sequence edit routine.
		*
		* For everything else, the order does not matter...
		*/
		$this->save_options($bulk_params);
		
		return;
	}

	public function save_options($value) {
		update_option('tainacan_bulk_' . $this->get_id(), $value);
	}
	
	public function get_options() {
		return get_option('tainacan_bulk_' . $this->get_id());
	}

	/**
	* return the number of items selected in the current bulk group
	* @return int number of items in the group
	*/
	public function count_posts() {
		global $wpdb;
		$id = $this->get_id();
		if (!empty($id)) {
			return (int) $wpdb->get_var( $wpdb->prepare("SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", $this->meta_key, $id) );
		}
		return 0;
	}

	public function set_id($id) {
		$this->id = $id;
	}

	public function get_id( ) {
		return $this->id;
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
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			'meta_query' => array(
				array(
					'key' => $this->meta_key,
					'value' => $this->id,
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
		$this->bulk_list_remove_item($item, $this->meta_key, $this->id);
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