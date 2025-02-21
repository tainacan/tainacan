<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Item_Metadata extends Repository {
	use \Tainacan\Traits\Singleton_Instance;

	protected function init() {
		parent::__construct();
	}

	public $entities_type = '\Tainacan\Entities\Item_Metadata_Entity';

	/**
	 * @param Entities\Item_Metadata_Entity $item_metadata
	 *
	 * @return Entities\Entity|Entities\Item_Metadata_Entity
	 * @throws \Exception
	 */
	public function insert( $item_metadata ) {

		if ( ! $item_metadata->get_validated() ) {
			throw new \Exception( 'Entities must be validated before you can save them' );
			// TODO: Throw Warning saying you must validate object before insert()
		}
		
		do_action( 'tainacan-pre-insert', $item_metadata );
		do_action( 'tainacan-pre-insert-Item_Metadata_Entity', $item_metadata );
		
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
			// Create a post_metadata with value [] to compound metadata or return if it already exists 
			// to use as parent_meta_id for child metadata
			global $wpdb;
			$compounds = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s and meta_value = ''", $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id() ), ARRAY_A );
			if( is_array($compounds) && !empty($compounds) ) {
				$meta_id = $compounds[0]['meta_id'];
				$item_metadata->set_parent_meta_id((int)$meta_id);
			} else {
				$meta_id = add_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), '' );
				$item_metadata->set_parent_meta_id($meta_id);
			}
			return $item_metadata;
		} else {
			if ( $unique ) {
				$item_metadata_value = $this->sanitize_value( $item_metadata->get_value() );
				if ( !is_numeric($item_metadata->get_value()) && empty( $item_metadata->get_value() ) ) {
					if ( $item_metadata->get_metadatum()->get_parent() > 0 ) {
						delete_metadata_by_mid( 'post', $item_metadata->get_meta_id() );
						$this->upclean_compound_value( $item_metadata);
					}
					else
						delete_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id() );
				} elseif ( is_int( $item_metadata->get_meta_id() ) ) {
					update_metadata_by_mid( 'post', $item_metadata->get_meta_id(), $item_metadata_value );
				} else {

					/**
					 * When we are adding a metadatum that is child of another, this means it is inside a compound metadatum
					 *
					 * In that case, if the Item_Metadata object is not set with a meta_id, it means we want to create a new one
					 * and not update an existing. This is the case of a multiple compound metadatum.
					 */
					if ( $item_metadata->get_metadatum()->get_parent() > 0 && is_null( $item_metadata->get_meta_id() ) ) {
						$added_meta_id  = add_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), wp_slash( $item_metadata_value ) );
						$added_compound = $this->add_compound_value( $item_metadata, $added_meta_id );
					} else {
						update_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), wp_slash( $item_metadata_value ) );
					}

				}

			} else {
				delete_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id() );

				if ( is_array( $item_metadata->get_value() ) ) {
					$values = $item_metadata->get_value();
					// for relationship metadata do not allow the same item to be present more than once in the value.
					if ( $metadata_type->get_primitive_type() == 'item' ) {
						$values = array_unique($values);
					}
					foreach ( $values as $value ) {
						if ( !is_numeric($value) && empty($value) ) {
							continue;
						}
						$item_metadata_value = $this->sanitize_value( $value );
						add_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), wp_slash( $item_metadata_value ) );
					}
				}
			}
			
		}
		
		do_action( 'tainacan-insert', $item_metadata );
		do_action( 'tainacan-insert-Item_Metadata_Entity', $item_metadata );

		$new_entity = new Entities\Item_Metadata_Entity( $item_metadata->get_item(), $item_metadata->get_metadatum(), $item_metadata->get_meta_id(), $item_metadata->get_parent_meta_id() );

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
			$item->$set_method( $this->sanitize_value( is_array( $value ) ? $value[0] : $value ) );

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

				// We cannot simply use wp_set_object_terms() because it uses term_exists() which is not reliable 
				// see https://core.trac.wordpress.org/ticket/45333 and https://core.trac.wordpress.org/ticket/47099
				// $success = wp_set_object_terms( $item_metadata->get_item()->get_id(), $new_terms, $taxonomy->get_db_identifier() );
				
				$insert = [];
				if ( !is_array($new_terms) ) {
					$new_terms = [ $new_terms ];
				}
				foreach ( $new_terms as $new_term ) {
					if ( \is_object($new_term) && $new_term instanceof Entities\Term ) {
						$exists = $new_term->WP_Term;
					} else {
						$exists = Terms::get_instance()->term_exists($new_term, $taxonomy, null, true);
					}
					
					if ( $exists ) {
						$insert[] = $exists->term_id;
					} else {
						$create_term = new Entities\Term();
						$new_term = $this->sanitize_value($new_term);
						$create_term->set_name($new_term);
						$create_term->set_taxonomy( $taxonomy->get_db_identifier() );
						if ($create_term->validate()) { // Item_Metadata Entity was validated before, so this should be fine
							$created_term = Terms::get_instance()->insert($create_term);
							$insert[] = $created_term->get_id();
						}
					}
				}
				
				$success = wp_set_object_terms( $item_metadata->get_item()->get_id(), $insert, $taxonomy->get_db_identifier() );
				
			}
		}
	}

	/**
	 *
	 * @return null|int the meta id of the update compound metadata
	 */
	public function upclean_compound_value(Entities\Item_Metadata_Entity $item_metadata) {
		try {
			if ( ! $item_metadata->get_parent_meta_id() > 0 ) return null;
			$current_value = get_metadata_by_mid( 'post', $item_metadata->get_parent_meta_id() );
			if ( is_object( $current_value ) ) {
				$current_value = $current_value->meta_value;
			}
			if ( ! is_array( $current_value ) ) {
				return null;
			}

			global $wpdb;
			$meta_ids = implode(',', $current_value);
			$query = $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_ID IN ($meta_ids)", $item_metadata->get_item()->get_id() );

			$rows = $wpdb->get_results($query, ARRAY_A );

			if ( is_array( $rows ) ) {
				$upclean_values = array_map(function($row) {
					return intval($row['meta_id']);
				}, $rows);
				update_metadata_by_mid( 'post', $item_metadata->get_parent_meta_id(), $upclean_values );
			}
		} catch (\Exception $e) {
			error_log($e);
			return null;
		}
	}

	/**
	 *
	 * @return null|int the meta id of the created compound metadata
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

	public function delete_metadata( \Tainacan\Entities\Item_Metadata_Entity $item_metadata) {
		do_action( 'tainacan-pre-delete', $item_metadata, true );
		do_action( 'tainacan-pre-delete-Item_Metadata_Entity', $item_metadata, true );

		$metadata_type = $item_metadata->get_metadatum()->get_metadata_type_object();
		if ( $metadata_type->get_primitive_type() == 'term' ) {
			$item_metadata->set_value([]);
			$this->save_terms_metadatum_value( $item_metadata );
		} elseif ( $metadata_type->get_primitive_type() == 'compound' ) {
			$this->remove_compound_value($item_metadata->get_parent_meta_id() );
		} else {
			delete_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id() );
		}

		if ( $item_metadata ) {
			do_action( 'tainacan-deleted', $item_metadata, true );
			do_action( 'tainacan-deleted-Item_Metadata_Entity', $item_metadata, true );
		}
		return $item_metadata;
	}

	public function remove_compound_value($parent_meta_id ) {
		$current_childrens = get_metadata_by_mid( 'post', $parent_meta_id );
		if ( is_object( $current_childrens ) ) {
			$current_childrens = $current_childrens->meta_value;
		}

		if ( ! is_array( $current_childrens ) ) {
			$current_childrens = [];
		}
		foreach($current_childrens as $meta_children) {
			delete_metadata_by_mid('post', $meta_children);
		}
		delete_metadata_by_mid('post', $parent_meta_id);
		return $current_childrens;
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

			$meta_list = $Tainacan_Metadata->fetch_by_collection( $collection, $args );

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

			if( is_wp_error($terms) ) {
				return null;
			}
			
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
					if ( empty($row['meta_value']) )
						continue;
					$value = $this->extract_compound_value( maybe_unserialize( $row['meta_value'] ), $item_metadata->get_item(), $row['meta_id'] );
					if ( empty($value) )
						continue;
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
				} else {
					return "";
				}
			} else {
				return get_post_meta( $item_metadata->get_item()->get_id(), $item_metadata->get_metadatum()->get_id(), $unique );
			}

		}

	}

	public function create_default_value_metadata(Entities\Item $item) {
		$items_metadata = $item->get_metadata();
		foreach ($items_metadata as $item_metadata) {
			if( in_array($item->get_status(), ['auto-draft'] ) && !metadata_exists('post', $item->get_id(), $item_metadata->get_metadatum()->get_id()) ) {
				$metadatum = $item_metadata->get_metadatum();
				if ( $metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\User' ) {
					$options = $metadatum->get_metadata_type_options();
					if ( isset($options['default_author']) && $options['default_author'] == 'yes') {
						$value = $metadatum->is_multiple() ? [strval(get_current_user_id())] : strval(get_current_user_id());
						$item_metadata->set_value($value);
						if ( $item_metadata->validate() ) {
							if($item->can_edit()) {
								$this->insert($item_metadata);
							}
						} else {
							return false;
						}
					}
				}
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
				if ( is_object( $post_meta_object ) && get_post($post_meta_object->meta_key) !== null && get_post_status($post_meta_object->meta_key) !== 'trash' ) {
					$metadatum                            = new Entities\Metadatum( $post_meta_object->meta_key );
					$return_value[ $metadatum->get_id() ] = new Entities\Item_Metadata_Entity( $item, $metadatum, $id, (int)$compound_meta_id );
				}

			}
		}

		return $return_value;

	}

	public function register_post_type() {
	}

	protected function _get_map() {
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
