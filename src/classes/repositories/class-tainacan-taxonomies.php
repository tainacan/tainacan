<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


use \Respect\Validation\Validator as v;

/**
 * Class Tainacan_Taxonomies
 */
class Taxonomies extends Repository {
	use \Tainacan\Traits\Singleton_Instance;

	public $entities_type = '\Tainacan\Entities\Taxonomy';

	protected function init() {
		parent::__construct();
		add_action( 'tainacan-taxonomy-removed-from-collection', array( $this, 'removed_collection' ), 10, 2 );
		add_action( 'tainacan-taxonomy-added-to-collection', array( $this, 'added_collection' ), 10, 2 );
	}

	protected function _get_map() {
		$entity = $this->get_name();
		return apply_filters( "tainacan-get-map-$entity", [
			'name'            => [
				'map'         => 'post_title',
				'title'       => __( 'Name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Name of the taxonomy', 'tainacan' ),
				'on_error'    => __( 'The taxonomy name should be a text value and should not be empty.', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'description'     => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The taxonomy description', 'tainacan' ),
				'default'     => '',
				'validation'  => ''
			],
			'slug'            => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The taxonomy slug', 'tainacan' ),
				'validation'  => ''
			],
			'allow_insert'    => [
				'map'         => 'meta',
				'title'       => __( 'Allow insert', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Allow/Deny the creation of new terms in the taxonomy', 'tainacan' ),
				'on_error'    => __( 'Invalid insertion, allowed values are ( yes/no )', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'enum'		  => [ 'yes', 'no' ],
				'default'     => 'yes'
			],
			'hierarchical'    => [
				'map'         => 'meta',
				'title'       => __( 'Allow terms hierarchy', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Allow/Deny the existence of terms children to build a hierarchy', 'tainacan' ),
				'on_error'    => __( 'Invalid insertion, allowed values are ( yes/no )', 'tainacan' ),
				'validation'  => v::stringType()->in( [ 'yes', 'no' ] ), // yes or no
				'enum'		  => [ 'yes', 'no' ],
				'default'     => 'yes'
			],
			'enabled_post_types'    => [
				'map'         => 'meta_multi',
				'title'       => __( 'Enabled for post types', 'tainacan' ),
				'type'        => ['array', 'string'],
				'description' => __( 'Also enable this taxonomy for other WordPress post types', 'tainacan' ),
				'on_error'    => __( 'Error enabling this taxonomy for post types', 'tainacan' ),
				'validation'  => '',
				'default'	  => []
			],
			'collections_ids' => [
				'map'         => 'meta_multi',
				'title'       => __( 'Collections', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The IDs of collection where the taxonomy is used', 'tainacan' ),
				'validation'  => ''
			],
		] );
	}

	/**
	 * Get the labels for the custom post type of this repository
	 *
	 * @return array Labels in the format expected by register_post_type()
	 */
	public function get_cpt_labels() {
		return array(
			'name'               => __( 'Taxonomies', 'tainacan' ),
			'singular_name'      => __( 'Taxonomy', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new Taxonomy', 'tainacan' ),
			'edit_item'          => __( 'Edit Taxonomy', 'tainacan' ),
			'new_item'           => __( 'New Taxonomy', 'tainacan' ),
			'view_item'          => __( 'View Taxonomy', 'tainacan' ),
			'view_items'         => __( 'View Taxonomies', 'tainacan' ),
			'search_items'       => __( 'Search Taxonomies', 'tainacan' ),
			'not_found'          => __( 'No Taxonomies found ', 'tainacan' ),
			'not_found_in_trash' => __( 'No Taxonomies found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent Taxonomy:', 'tainacan' ),
			'all_items'			 => __( 'All Taxonomies', 'tainacan' ),
			'archives'			 => __( 'Taxonomies Archive', 'tainacan' ),
			'menu_name'          => __( 'Taxonomies', 'tainacan' )
		);
	}

	public function register_post_type() {
		$labels = $this->get_cpt_labels();
		$args   = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => tnc_enable_dev_wp_interface(),
			'show_in_menu'        => tnc_enable_dev_wp_interface(),
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			/* Translators: The Taxonomies slug - will be the URL for the collections archive */
			'rewrite'             => ['slug' => sanitize_title(_x('taxonomies', 'Slug: the string that will be used to build the URL', 'tainacan'))],
			'capabilities'        => (array) $this->get_capabilities(),
			'map_meta_cap'        => true,
			'show_in_rest'        => true,
			'show_in_nav_menus'   => true,
			'supports'            => [
				'title',
				'editor',
				'page-attributes'
			]
		);
		register_post_type( Entities\Taxonomy::get_post_type(), $args );
	}

	/**
	 * @param Entities\Taxonomy $taxonomy
	 *
	 * @return Entities\Entity
	 */
	public function insert( $taxonomy ) {
		$new_taxonomy = parent::insert( $taxonomy );
		$new_taxonomy->tainacan_register_taxonomy();
		
		flush_rewrite_rules( false ); // needed to activate taxonomy archive url
		
		// return a brand new object
		return $new_taxonomy;
	}

	/**
	 * fetch taxonomies based on ID or WP_Query args
	 *
	 * Taxonomies are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 * 
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Taxonomy object.  But if the post is not found or
	 * does not match the entity post type, it will return an empty array
	 *
	 * @param array $args WP_Query args | int $args the taxonomy id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return \WP_Query|Array an instance of wp query OR array of entities;
	 */
	public function fetch( $args = [], $output = null ) {
		// TODO: Pegar taxonomias registradas via código

		if ( is_numeric( $args ) ) {
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				try {
					return new Entities\Taxonomy( $existing_post );
				} catch (\Exception $e) {
					return [];
				}
			} else {
				return [];
			}
			
		} elseif ( is_array( $args ) ) {

			$args = array_merge( [
				'posts_per_page' => - 1,
			], $args );

			$args = $this->parse_fetch_args( $args );

			$args['post_type'] = Entities\Taxonomy::get_post_type();

			$args = apply_filters( 'tainacan-fetch-args', $args, 'taxonomies' );

			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}
	
	/**
	 * fetch taxonomies by collection, considering inheritance
	 *
	 * @param Entities\Collection $collection
	 * @param array $args WP_Query args plus disabled_metadata
	 *
	 * @return array Entities\Taxonomy
	 * @throws \Exception
	 */
	public function fetch_by_collection( Entities\Collection $collection, $args = [], $output = 'OBJECT' ) {
		$Tainacan_Metadata = Metadata::get_instance();
		
		// get all taxonomy metadata in this collection
		$taxonomy_metas = $Tainacan_Metadata->fetch_by_collection($collection, ['metadata_type' => 'Tainacan\Metadata_Types\Taxonomy']);
		
		$tax_ids = [];
		
		foreach ( $taxonomy_metas as $taxonomy_meta ) {
			$options = $taxonomy_meta->get_metadata_type_options();
			if (isset($options['taxonomy_id'])) {
				$tax_ids[] = $options['taxonomy_id'];
			}
		}
		
		if (empty($tax_ids)) {
			$tax_ids[] = 'please-return-nothing';
		}
		
		$newargs = [
			'post__in' => $tax_ids
		];
		
		$args = array_merge($args, $newargs);
		return $this->fetch($args, $output);

	}
	
	/**
	 * fetch taxonomies by DB Identifier
	 *
	 * @param string $db_identifier The db Identifier of the taxonomy. This is the internal WordPress taxonomy slug, something like tnc_123_tax
	 *
	 * @return Entities\Taxonomy|Array The entity when found. An empty array when nothing was found
	 * @throws \Exception
	 */
	public function fetch_by_db_identifier($db_identifier) {
		$id = $this->get_id_by_db_identifier($db_identifier);
		if ($id) {
			return $this->fetch( (int) $id );
		}
		return [];
	}

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}

	public function delete( Entities\Entity $taxonomy, $permanent = true ) {
		$taxonomy_name = $taxonomy->get_db_identifier();

		/* TODO: Investigate the cause of taxonomies aren't been registered
		 *
		 * This cause a 'invalid taxonomy' exception when try to delete permanently a taxonomy
		 *
		 * This condition is a temporary solution
		 */
		if ( taxonomy_exists( $taxonomy_name ) && $permanent ) {
			$unregistered = unregister_taxonomy( $taxonomy_name );

			if ( $unregistered instanceof \WP_Error ) {
				return $unregistered;
			}
		}
		
		return parent::delete($taxonomy, $permanent);
		
	}

	public function added_collection( $taxonomy_id, $collection_id ) {
		$id = $taxonomy_id;
		if ( ! empty( $id ) && is_numeric( $id ) && is_numeric($collection_id) ) {
			$tax = $this->fetch( (int) $id );

			if ( $tax instanceof Entities\Taxonomy ) {
				$tax->add_collection_id( $collection_id );
				
				if ( $tax->validate() )
					$this->insert( $tax );
			}
		}
		$this->update_taxonomy_registry_for_collection($taxonomy_id, $collection_id);
	}

	public function removed_collection( $taxonomy_id, $collection_id ) {
		$id = $taxonomy_id;
		if ( ! empty( $id ) && is_numeric( $id ) && is_numeric($collection_id) ) {
			$tax = $this->fetch( (int) $id );
			
			if ( $tax instanceof Entities\Taxonomy ) {
				$tax->remove_collection_id( $collection_id );

				if ( $tax->validate() )
					$this->insert( $tax );
			}
		}
		$this->update_taxonomy_registry_for_collection($taxonomy_id, $collection_id);
	}
	
	public function update_taxonomy_registry_for_collection($taxonomy_id, $collection_id) {
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		// if repository level metadatum, update all collections
		if ( $collection_id = $Tainacan_Metadata->get_default_metadata_attribute() ) {
			$this->register_taxonomies_for_all_collections();
		} else {
			// get all children, grand children, etc.
			$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
			$children_ids = $Tainacan_Collections->get_descendants_ids($collection_id);
			// register taxonomy for collection
			$tax_slug = Entities\Taxonomy::$db_identifier_prefix . $taxonomy_id;
			foreach ($children_ids as $child_id) {
				$collection_slug = Entities\Collection::$db_identifier_prefix . $child_id . Entities\Collection::$db_identifier_sufix;
				register_taxonomy_for_object_type( $tax_slug, $collection_slug );
			}
		}
		
		
		
	}
	
	public function register_taxonomies_for_all_collections($all_collections = null) {
		global $wpdb;
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();

		// TODO: This can be a problem in large repositories. 
		$collections = $all_collections != null ? $all_collections : $Tainacan_Collections->fetch( ['nopaging' => true], 'OBJECT' );
		if ( ! is_array( $collections ) ) {
			return;
		}

		$taxonomies_res = $wpdb->get_results("
			SELECT 
				meta.post_id as meta_id, meta.meta_value as tax_id, col.meta_value as collection_id 
			FROM 
				$wpdb->postmeta meta INNER JOIN (
					SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key='collection_id'
				) as col ON meta.post_id = col.post_id
			WHERE 
				meta.meta_key='_option_taxonomy_id' 
				AND meta.post_id IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key='metadata_type' and meta_value='Tainacan\\\Metadata_Types\\\Taxonomy');
		");

		foreach ($taxonomies_res as $tax_res) {
			// Aqui você pode acessar os valores de cada coluna
			$tax_id = $tax_res->tax_id;
			$collection_id = $tax_res->collection_id;

			$tax_db_identifier_by_id = $this->get_db_identifier_by_id($tax_id);
			if ($collection_id != 'default' ) {
				$collection_slug = Entities\Collection::$db_identifier_prefix . $collection_id . Entities\Collection::$db_identifier_sufix;
				register_taxonomy_for_object_type($tax_db_identifier_by_id ,$collection_slug);
			} else {
				foreach ( $collections as $collection ) {
					register_taxonomy_for_object_type($tax_db_identifier_by_id, $collection->get_db_identifier());
				}
			}
		}
	}
	
	public function get_db_identifier_by_id($id) {
		$prefix = Entities\Taxonomy::$db_identifier_prefix;
		return $prefix . $id;
	}
	
	public function get_id_by_db_identifier( $db_identifier ) {
		$prefix = \Tainacan\Entities\Taxonomy::$db_identifier_prefix;
		//$sufix  = \Tainacan\Entities\Taxonomy::$db_identifier_sufix;
		$id     = str_replace( $prefix, '', $db_identifier );
		//$id     = str_replace( $sufix, '', $id );
		if ( is_numeric( $id ) ) {
			return (int) $id;
		}

		return false;
	}
	
	/**
	* Check if a term already exists 
	*
	* @param Entities\Taxonomy $taxonomy The taxonomy object where to look for terms
	* @param string $term_name The term name 
	* @param int|null $parent The ID of the parent term to look for children or null to look for terms in any hierarchical position. Default is null 
	* @param bool $return_term whether to return the term object if it exists. default is to false 
	* 
	* @return bool|WP_Term return boolean indicating if term exists. If $return_term is true and term exists, return WP_Term object 
	*/
	public function term_exists(Entities\Taxonomy $taxonomy, $term_name, $parent = null, $return_term = false) {
		$TermsRepo = Terms::get_instance();
		return $TermsRepo->term_exists($term_name, $taxonomy, $parent, $return_term);
	}


}