<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Item_Metadata extends Repository {

	protected function __construct() {
		parent::__construct();
	}

	public $entities_type = '\Tainacan\Entities\Item_Metadata_Entity';

	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param Entities\Entity $item_metadata
	 *
	 * @return Entities\Entity|Entities\Item_Metadata_Entity
	 * @throws \Exception
	 */
	public function insert( $item_metadata ) {

		if ( ! $item_metadata->get_validated() ) {
			throw new \Exception( 'Entities must be validated before you can save them' );
			// TODO: Throw Warning saying you must validate object before insert()
		}

		$is_update = true;
		
		$new = $item_metadata->get_value();

		$diffs = [];

		if ($this->use_logs) {
			$old = get_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), true );
			if($old != $new) {
				$diffs['value'] = [
					'new'             => $new,
					'old'             => $old,
					'diff_with_index' => [],
				];
			} else {
				$diffs['value'] = [];
			}
		}

		$unique = ! $item_metadata->is_multiple();

		$metadata_type = $item_metadata->get_metadatum()->get_metadata_type_object();

		if ( $metadata_type->get_core() ) {
			$this->save_core_metadatum_value( $item_metadata );
			// Core metadata are also stored as regular metadata (in the code following below)
			// This is useful to create queries via filters, advanced search or APIs
			// if you can search for title and content with meta_query as if they were regular metadata
		}

		if ( $metadata_type->get_primitive_type() == 'term' ) {
			$this->save_terms_metadatum_value( $item_metadata );
		} elseif ( $metadata_type->get_primitive_type() == 'compound' ) {
			// do nothing. Compound values are updated when its child metadata are updated
			return $item_metadata;
		} else {
			if ( $unique ) {

				if ( is_int( $item_metadata->get_meta_id() ) ) {
					update_metadata_by_mid( 'post', $item_metadata->get_meta_id(), wp_slash( $item_metadata->get_value() ) );
				} else {

					/**
					 * When we are adding a metadatum that is child of another, this means it is inside a compound metadatum
					 *
					 * In that case, if the Item_Metadata object is not set with a meta_id, it means we want to create a new one
					 * and not update an existing. This is the case of a multiple compound metadatum.
					 */
					if ( $item_metadata->get_metadatum()->get_parent() > 0 && is_null( $item_metadata->get_meta_id() ) ) {
						$added_meta_id  = add_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), wp_slash( $item_metadata->get_value() ) );
						$added_compound = $this->add_compound_value( $item_metadata, $added_meta_id );
					} else {
						update_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), wp_slash( $item_metadata->get_value() ) );
					}

				}

			} else {
				delete_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id() );

				if ( is_array( $item_metadata->get_value() ) ) {
					$values = $item_metadata->get_value();

					foreach ( $values as $value ) {
						add_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), wp_slash( $value ) );
					}
				}
			}
			
			if ($this->use_logs) {
				$this->logs_repository->insert_log( $item_metadata, $diffs, $is_update );
			}

			do_action( 'tainacan-insert', $item_metadata, $diffs, $is_update );
			do_action( 'tainacan-insert-Item_Metadata_Entity', $item_metadata );
		}

		$new_entity = new Entities\Item_Metadata_Entity( $item_metadata->get_item(), $item_metadata->get_metadatum() );

		if ( isset( $added_compound ) && is_int( $added_compound ) ) {
			$new_entity->set_parent_meta_id( $added_compound );
		}

		if ( isset( $added_meta_id ) && is_int( $added_meta_id ) ) {
			$new_entity->set_meta_id( $added_meta_id );
		}

		return $new_entity;

	}

	/**
	 * @param Entities\Item_Metadata_Entity $item_metadata
	 *
	 * @throws \Exception
	 */
	public function save_core_metadatum_value( \Tainacan\Entities\Item_Metadata_Entity $item_metadata ) {
		$metadata_type = $item_metadata->get_metadatum()->get_metadata_type_object();

		if ( $metadata_type->get_core() ) {
			$item       = $item_metadata->get_item();
			$set_method = 'set_' . $metadata_type->get_related_mapped_prop();

			$value = $item_metadata->get_value();
			$item->$set_method( is_array( $value ) ? $value[0] : $value );

			if ( $item->validate_core_metadata() ) {
				$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
				$Tainacan_Items->insert( $item );
			} else {
				throw new \Exception( 'Item metadata should be validated beforehand' );
			}
		}
	}

	/**
	 * @param $item_metadata
	 *
	 * @throws \Exception
	 */
	public function save_terms_metadatum_value( $item_metadata ) {
		$metadata_type = $item_metadata->get_metadatum()->get_metadata_type_object();
		if ( $metadata_type->get_primitive_type() == 'term' ) {
			$new_terms = $item_metadata->get_value();
			$taxonomy  = new Entities\Taxonomy( $metadata_type->get_option( 'taxonomy_id' ) );

			if ( $taxonomy ) {
				if ( $this->use_logs  ) {
					$old = wp_get_object_terms(  $item_metadata->get_item()->get_id(), $taxonomy->get_db_identifier(), [
						'fields' => 'names'
					] );
				}

				// We can not simply use wp_set_object_terms() because it uses term_exists() which is not reliable 
				// see https://core.trac.wordpress.org/ticket/45333 and https://core.trac.wordpress.org/ticket/47099
				// $success = wp_set_object_terms( $item_metadata->get_item()->get_id(), $new_terms, $taxonomy->get_db_identifier() );
				
				$insert = [];
				foreach ( (array) $new_terms as $new_term ) {
					$exists = Terms::get_instance()->term_exists($new_term, $taxonomy, null, true);
					if ( $exists ) {
						$insert[] = $exists->term_id;
					} else {
						$create_term = new Entities\Term();
						$create_term->set_name($new_term);
						$create_term->set_taxonomy( $taxonomy->get_db_identifier() );
						if ($create_term->validate()) { // Item_Metadata Entity was validated before, so this should be fine
							$created_term = Terms::get_instance()->insert($create_term);
							$insert[] = $created_term->get_id();
						}
					}
				}
				
				$success = wp_set_object_terms( $item_metadata->get_item()->get_id(), $insert, $taxonomy->get_db_identifier() );
				
				if ( $this->use_logs && ! $success instanceof \WP_Error ) {

					$new = get_terms(array(
						'taxonomy'   => $taxonomy->get_db_identifier(),
						'hide_empty' => false,
						'object_ids' => $item_metadata->get_item()->get_id(),
						'fields'     => 'names',
					));

					$diffs[ 'value' ] = [
						'new'             => $new,
						'old'             => $old,
						'diff_with_index' => []
					];

					if($this->use_logs){
						$this->logs_repository->insert_log( $item_metadata, $diffs, true );
						//do_action( 'tainacan-insert', $item_metadata, $diffs, true );
					}
				}
			}
		}
	}

	/**
	 *
	 * @return null|ind the meta id of the created compound metadata
	 */
	public function add_compound_value( Entities\Item_Metadata_Entity $item_metadata, $meta_id ) {

		$current_value = get_metadata_by_mid( 'post', $item_metadata->get_parent_meta_id() );

		if ( is_object( $current_value ) ) {
			$current_value = $current_value->meta_value;
		}

		if ( ! is_array( $current_value ) ) {
			$current_value = [];
		}

		if ( ! in_array( $meta_id, $current_value ) ) {
			$current_value[] = $meta_id;
		}

		if ( $item_metadata->get_parent_meta_id() > 0 ) {
			update_metadata_by_mid( 'post', $item_metadata->get_parent_meta_id(), $current_value );
		} elseif ( $item_metadata->get_metadatum()->get_parent() > 0 ) {
			return add_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_parent(), $current_value );
		}


	}

	/**
	 * Fetch Item Metadatum objects related to an Item
	 *
	 * @param Entities\Item $object
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function fetch( $object, $output = null, $args = [] ) {
		if ( $object instanceof Entities\Item ) {
			$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

			$collection = $object->get_collection();

			if ( ! $collection instanceof Entities\Collection ) {
				return [];
			}

			$meta_list = $Tainacan_Metadata->fetch_by_collection( $collection, $args, 'OBJECT' );

			$return = [];

			if ( is_array( $meta_list ) ) {
				foreach ( $meta_list as $meta ) {
					$return[] = new Entities\Item_Metadata_Entity( $object, $meta );
				}
			}

			return $return;
		} else {
			return [];
		}
	}

	/**
	 * Get the value for a Item metadatum.
	 *
	 * @param Entities\Item_Metadata_Entity $item_metadata
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function get_value( Entities\Item_Metadata_Entity $item_metadata ) {
		$unique = ! $item_metadata->is_multiple();

		$metadata_type = $item_metadata->get_metadatum()->get_metadata_type_object();
		if ( $metadata_type->get_core() ) {
			$item = $item_metadata->get_item();

			$get_method = 'get_' . $metadata_type->get_related_mapped_prop();

			return $item->$get_method();

		} elseif ( $metadata_type->get_primitive_type() == 'term' ) {

			if ( is_numeric( $metadata_type->get_option( 'taxonomy_id' ) ) ) {
				//$taxonomy = new Entities\Taxonomy( $metadata_type->get_option( 'taxonomy_id' ) );
				$taxonomy = Taxonomies::get_instance()->fetch( (int) $metadata_type->get_option( 'taxonomy_id' ) );
				if ( $taxonomy instanceof Entities\Taxonomy && $taxonomy->can_read() ) {
					$taxonomy_slug = $taxonomy->get_db_identifier();
				} else {
					return null;
				}
			} else {
				return null;
			}

			$terms = wp_get_object_terms( $item_metadata->get_item()->get_id(), $taxonomy_slug );

			if ( $unique ) {
				$terms = reset( $terms );

				if ( false !== $terms ) {
					$terms = new Entities\Term( $terms );
				}
			}

			if ( is_array( $terms ) ) {
				$terms_array = [];
				foreach ( $terms as $term ) {
					$terms_array[] = new Entities\Term( $term );
				}

				return $terms_array;
			}

			return $terms;

		} elseif ( $metadata_type->get_primitive_type() == 'compound' ) {

			global $wpdb;
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id() ),
				ARRAY_A );

			$return_value = [];

			if ( is_array( $rows ) ) {

				foreach ( $rows as $row ) {
					$value = $this->extract_compound_value( maybe_unserialize( $row['meta_value'] ), $item_metadata->get_item(), $row['meta_id'] );
					if ( $unique ) {
						$return_value = $value;
						break;
					} else {
						$return_value[] = $value;
					}
				}

			}

			return $return_value;

		} else {
			if ( is_int( $item_metadata->get_meta_id() ) ) {
				$value = get_metadata_by_mid( 'post', $item_metadata->get_meta_id() );
				if ( is_object( $value ) && isset( $value->meta_value ) ) {
					return $value->meta_value;
				}
			} else {
				return get_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), $unique );
			}

		}

	}

	/**
	 * Transforms the array saved as meta_value with the IDs of post_meta saved as a value for compound metadata
	 * and converts it into an array of Item Metadatada Entitites
	 *
	 * @param array $ids The array of post_meta ids
	 * @param Entities\Item $item The item this post_meta is related to
	 * @param $compound_meta_id
	 *
	 * @return array An array of Item_Metadata_Entity objects
	 * @throws \Exception
	 */
	private function extract_compound_value( array $ids, Entities\Item $item, $compound_meta_id ) {

		$return_value = [];

		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				$post_meta_object = get_metadata_by_mid( 'post', $id );
				if ( is_object( $post_meta_object ) ) {
					$metadatum                            = new Entities\Metadatum( $post_meta_object->meta_key );
					$return_value[ $metadatum->get_id() ] = new Entities\Item_Metadata_Entity( $item, $metadatum, $id, $compound_meta_id );
				}

			}
		}

		return $return_value;

	}

	public function register_post_type() {
	}

	public function get_map() {
		return [];
	}

	public function get_default_properties( $map ) {
		return [];
	}

	/**
	 * @param $object
	 *
	 * @param null $new_values
	 *
	 * @return mixed
	 */
	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	/**
	 * Suggest a value to be inserted as a item Metadatum value, return a pending log
	 *
	 * @param Entities\Item_Metadata_Entity $item_metadata
	 *
	 * @return Entities\Log
	 */
	public function suggest( $item_metadata ) {
		return Entities\Log::create( false, '', $item_metadata, null, 'pending' );
	}
}
