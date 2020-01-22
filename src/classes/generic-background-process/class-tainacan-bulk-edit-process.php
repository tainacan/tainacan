<?php
namespace Tainacan\GenericBackgroundProcess;
use Tainacan;
use Tainacan\Entities;

class Bulk_Edit_Process extends Generic_Process {

	private $meta_key = '_tnc_bulk';
	private $group_id = false;

	public function __construct($attributes = array()) {
		$this->array_attributes = array_merge($this->array_attributes, [
			'group_id',
			'bulk_edit_data'
		]);
		parent::__construct($attributes);
		$this->init_objects();
	}

	public function init_objects() {
		$this->items_repository = \Tainacan\Repositories\Items::get_instance();
		$this->metadatum_repository = \Tainacan\Repositories\Metadata::get_instance();
		$this->item_metadata_repository = \Tainacan\Repositories\Item_Metadata::get_instance();
		$this->steps = [
			[
				'name' => __('Bulk edit control metadada', 'tainacan'),
				'progress_label' => __('Creating bulk edit control metadada', 'tainacan'),
				'callback' => 'add_control_metadata'
			],[
				'name' => __('Bulk edit', 'tainacan'),
				'progress_label' => __('Running bulk edit', 'tainacan'),
				'callback' => 'main_process',
				'total' => $this->get_total_items()
			]
		];
	}

	public function create_bulk_edit($params) {
		if ( isset($params['group_id']) && !empty($params['group_id']) ) {
			$this->set_group_id($params['group_id']);
			return;
		}

		if (!isset($params['collection_id']) || !is_numeric($params['collection_id'])) {
			throw new \Exception('Collection ID must be informed when creating a group.');
		}

		$this->set_group_id(uniqid());
		if (isset($params['query']) && is_array($params['query'])) {
			$bulk_params = [
				'collection_id' 	=> $params['collection_id'],
				'query' 	=> $params['query'],
				'order' 	=> isset($params['query']['order']) ? $params['query']['order'] : 'DESC',
				'orderby' => isset($params['query']['orderby']) ? $params['query']['orderby'] : 'post_date'
			];
		} elseif (isset($params['items_ids']) && is_array($params['items_ids'])) {
			$items_ids = array_filter($params['items_ids'], 'is_integer');
			$bulk_params = [
				'collection_id' 	=> $params['collection_id'],
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
		$message = __('Bulk edit finished', 'tainacan');
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

			$post_per_page = 1;
			if ( isset($params['query']['posts_per_page']) && $params['query']['posts_per_page'] != -1 ) {
				$post_per_page = $params['query']['posts_per_page'] - $count;
				$params['query']['posts_per_page'] = $post_per_page;
			}
			if($post_per_page <= 0) {
				return false;
			}

			$query = $params['query'];
			$query['fields'] = 'ids';
			$query['posts_per_page'] = $post_per_page;
			$query['offset'] = $count++;
			$query['nopaging'] = false;

			$item_query = $itemsRepo->fetch($query, $params['collection_id']);
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

	private function get_total_items() {
		if (!$this->get_group_id()) return 0;
		$args = [
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			'meta_query' => array(
				array(
					'key' => $this->meta_key,
					'value' => $this->get_group_id(),
					'compare' => '=',
				)
			)
		];
		$item = $this->items_repository->fetch($args, [], 'WP_Query');
		return intval($item->found_posts);
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
		$item = $this->items_repository->fetch($args, [], 'WP_Query');
		$this->set_current_step_total($item->found_posts);
		if ($item->have_posts()) {
			$item->the_post();
			$item = new \Tainacan\Entities\Item($item->post);
			return $item;
		}
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

		$this->add_log( sprintf( __('bulk edit has process the item ID: "%d"', 'tainacan'), $item->get_id() ) );
		$add_steps = $this->$method($item);
		if ( is_int($add_steps) ) {
			$count = $count + $add_steps;
		}
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

			$serealize_erro = (object) array('err' => array());
			$erro = $item_metadata->get_errors();
			array_walk_recursive($erro, function($v, $k, &$t) {$t->err[] = $v;}, $serealize_erro);
			$this->add_error_log( __('errors: ', 'tainacan') . implode(", ", $serealize_erro->err) );

			return false;
		}
		return true;
	}

	private function clear_value(\Tainacan\Entities\Item $item) {
		$metadatum = $this->metadatum_repository->fetch($this->bulk_edit_data['metadatum_id']);
		$item_metadata = new Entities\Item_Metadata_Entity( $item, $metadatum );
		$item_metadata->set_value("");
		return $this->save_item_metadata($item_metadata, $item);
	}

	private function set_value(\Tainacan\Entities\Item $item) {
		$metadatum = $this->metadatum_repository->fetch($this->bulk_edit_data['metadatum_id']);
		$value = $this->bulk_edit_data['value'];

		$item_metadata = new Entities\Item_Metadata_Entity( $item, $metadatum );

		if($item_metadata->is_multiple()) {
			$value = is_array( $value ) ? $value : [$value];
			$item_metadata->set_value( $value );
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
				if ($pos !== false) {
					$values[$pos] = $new_value;
					$item_metadata->set_value( $metadatum->is_multiple() ? $values : $values[$pos] );
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
		if ('trash' != $item->get_status() ) {
			$this->add_error_log( sprintf( __('Items must be on trash to be deleted, item ID: "%d"', 'tainacan'), $item->get_id() ) );
			return false;
		}

		if ( !$this->items_repository->delete($item) ) {
			$this->add_error_log( sprintf( __('error on send to trash, item ID: "%d"', 'tainacan'), $item->get_id() ) );
			return false;
		}
		return -1;
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
		$serealize_erro = (object) array('err' => array());
		array_walk_recursive($item->get_errors(), create_function('&$v, $k, &$t', '$t->err[] = $v;'), $serealize_erro);
		$this->add_error_log( __('errors: ', 'tainacan') . implode(", ", $serealize_erro->err) );

		return false;
	}

	private function set_comment_status( \Tainacan\Entities\Item $item) {
		$value = $this->bulk_edit_data['value'];

		if ( ! in_array( $value, array( 'open', 'closed' ) ) ) {
			$this->add_error_log( __( "the status of comments must be 'open' or 'closed'", 'tainacan' ) );
			return false;
		}

		$item->set_comment_status($value);
		if($item->validate()) {
			$this->items_repository->update($item);
			return true;
		}

		$this->add_error_log( sprintf( __( 'Please verify, invalid value(s) to edit item ID: "%d"', 'tainacan' ), $item->get_id() ) );
		$serealize_erro = (object) array('err' => array());
		array_walk_recursive($item->get_errors(), create_function('&$v, $k, &$t', '$t->err[] = $v;'), $serealize_erro);
		$this->add_error_log( __('errors: ', 'tainacan') . implode(", ", $serealize_erro->err) );

		return false;
}

}
