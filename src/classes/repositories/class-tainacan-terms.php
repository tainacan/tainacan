<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use \Respect\Validation\Validator as v;

/**
 * Class Tainacan_Terms
 */
class Terms extends Repository {

	public $entities_type = '\Tainacan\Entities\Term';

	private static $instance = null;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
	}

	public function get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'term_id'         => [
				'map'         => 'term_id',
				'title'       => __( 'ID', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Unique identifier', 'tainacan' ),
				//'validation' => ''
			],
			'name'            => [
				'map'         => 'name',
				'title'       => __( 'Name', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'Name of the term', 'tainacan' ),
				'on_error'    => __( 'The name is empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'parent'          => [
				'map'         => 'parent',
				'title'       => __( 'Parent', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'The parent of the term', 'tainacan' ),
				'default'     => 0,
				//'validation'  => ''
			],
			'description'     => [
				'map'         => 'description',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The term description', 'tainacan' ),
				'default'     => '',
				//'validation'  => ''
			],
			'taxonomy'        => [
				'map'         => 'taxonomy',
				'title'       => __( 'Taxonomy', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The term taxonomy', 'tainacan' ),
				'on_error'    => __( 'The taxonomy is empty', 'tainacan' ),
				'validation'  => v::stringType()->notEmpty(),
			],
			'user'            => [
				'map'         => 'termmeta',
				'title'       => __( 'User', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'The term creator', 'tainacan' ),
				'on_error'    => __( 'The user is empty or invalid', 'tainacan' ),
				'default'     => get_current_user_id(),
				//'validation'  => v::numeric(),
			],
			'header_image_id' => [
				'map'         => 'termmeta',
				'title'       => __( 'Header Image', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The image to be used in term header', 'tainacan' ),
				'on_error'    => __( 'Invalid image', 'tainacan' ),
				//'validation' => v::numeric(),
				'default'     => ''
			],
			'hide_empty'      => [
				'map'         => 'hide_empty',
				'title'       => __( 'Hide empty', 'tainacan' ),
				'type'        => 'bool',
				'description' => __( 'Hide empty terms', 'tainacan' )
			]
		] );
	}

	public function get_default_properties( $map ) {
		$defaults = parent::get_default_properties( $map );
		//its uses the term_id and not id
		unset( $defaults['id'] );

		return $defaults;
	}

	/**
	 * @param Entities\Entity $term
	 *
	 * @return Entities\Entity|Entities\Term
	 * @throws \Exception
	 */
	public function insert( $term ) {

		if ( ! $term->get_validated() ) {
			throw new \Exception( 'Entities must be validated before you can save them' );
		}
		
		$is_update = false;
		$diffs     = [];
		if ( $term->get_id() && $this->use_logs) {
			$is_update = true;

			$old = $this->fetch( $term->get_id(), $term->get_taxonomy() );

			$diffs = $this->diff( $old, $term );
		}

		// First iterate through the native post properties
		$map = $this->get_map();
		foreach ( $map as $prop => $mapped ) {
			if ( $mapped['map'] != 'termmeta' ) {
				$term->WP_Term->{$mapped['map']} = $term->get_mapped_property( $prop );
			}
		}

		// save post and get its ID
		if ( isset( $term->WP_Term->term_id ) ) {

			$args = [];
			foreach ( $map as $prop => $mapped ) {
				if ( $mapped['map'] != 'termmeta' ) {
					$get_ = 'get_' . $prop;

					if ( $term->WP_Term->{$mapped['map']} ||
					     ($mapped['map'] == 'parent' && $term->WP_Term->{$mapped['map']} >= 0) ) {

						$args[ $mapped['map'] ] = $term->$get_();
					}
				}
			}

			$term_saved = wp_update_term( $term->get_id(), $term->get_taxonomy(), $args );
		} else {
			$term_saved = wp_insert_term( $term->get_name(), $term->get_taxonomy(), [
				'parent'      => $term->get_parent(),
				'description' => $term->get_description(),
			] );
		}
		
		if ( is_wp_error($term_saved) ) {
			throw new \Exception( 'Error adding term ' . $term->get_name() . ' - ' . $term_saved->get_error_message() );
		}

		// Now run through properties stored as postmeta
		foreach ( $map as $prop => $mapped ) {
			if ( $mapped['map'] == 'termmeta' ) {
				update_term_meta( $term_saved['term_id'], $prop, wp_slash( $term->get_mapped_property( $prop ) ) );
			}
		}

		if($this->use_logs){
			// TODO: Log header image updates
			$this->logs_repository->insert_log( $term, $diffs, $is_update );
		}


		do_action( 'tainacan-insert', $term, $diffs, $is_update );
		do_action( 'tainacan-insert-term', $term );

		return new Entities\Term( $term_saved['term_id'], $term->get_taxonomy() );
	}
	
	// TODO: Is this workaround ok to avoid getting htmlentities ?
	function get_mapped_property($entity, $prop) {
		$property = parent::get_mapped_property($entity, $prop);
		if ($prop == 'name' || $prop == 'description') {
			$property = wp_specialchars_decode($property);
		}
		return $property;
	}

	/**
	 * fetch terms based on ID or get terms args
	 *
	 * Terms are stored as WordPress regular terms. Check (@see https://developer.wordpress.org/reference/functions/get_terms/) get_terms() docs
	 * to learn all args accepted in the $args parameter
	 *
	 * The second paramater specifies from which taxonomies terms should be fetched.
	 * You can pass the Taxonomy ID or object, or an Array of IDs or taxonomies objects
	 *
	 * @param array $args WP_Query args || int $args the term id
	 * @param array $taxonomies Array Entities\Taxonomy || Array int terms IDs || int collection id || Entities\Taxonomy taxonomy object
	 *
	 * @return array of Entities\Term objects || Entities\Term
	 */
	public function fetch( $args = [], $taxonomies = [] ) {

		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

		if ( is_numeric( $taxonomies ) ) {
			$taxonomies = $Tainacan_Taxonomies->fetch( $taxonomies );
		}

		if ( $taxonomies instanceof Entities\Taxonomy ) {
			$cpt = $taxonomies->get_db_identifier();
		} elseif ( is_array( $taxonomies ) ) {
			$cpt = [];

			foreach ( $taxonomies as $taxonomy ) {
				if ( is_numeric( $taxonomy ) ) {
					$taxonomy = $Tainacan_Taxonomies->fetch( $taxonomy );
				}
				if ( $taxonomy instanceof Entities\Taxonomy ) {
					$cpt[] = $taxonomy->get_db_identifier();
				}
			}

		} elseif ( is_string( $taxonomies ) && is_numeric( $args ) ) { // if taxonomy is taxonomy_db_identifier
			$cpt = $taxonomies;

			$term = get_term_by( 'id', $args, $cpt );

			return new Entities\Term( $term );
		}

		if ( is_array( $args ) && ! empty( $cpt ) ) { // if an array of arguments is
			$args['taxonomy'] = $cpt;
			$terms  = get_terms( $args );
			$return = [];

			foreach ( $terms as $term ) {
				$tainacan_term = new Entities\Term( $term );
				$return[] = $tainacan_term;
			}

			return $return;
		} elseif ( is_numeric( $args ) && ! empty( $cpt ) && ! is_array( $cpt ) ) { // if an id is passed taxonomy cannot be an array
			$wp_term       = get_term_by( 'id', $args, $cpt );
			$tainacan_term = new Entities\Term( $wp_term );
			return $tainacan_term;
		} else {
			return [];
		}
	}

	public function update( $object, $args = null ) {
		return $this->insert( $object );
	}

	/**
	 * @param Entities\Term $term
	 * @param bool $permanent this parameter is not used by Terms repository
	 *
	 * @return bool|int|mixed|\WP_Error
	 */
	public function delete( Entities\Entity $term, $permanent = true ) {
		$deleted = $term;
		$return = wp_delete_term( $term->get_id(), $term->get_taxonomy() );

		if ( $deleted && $this->use_logs ) {
			$this->logs_repository->insert_log( $deleted, [], false, true );
			do_action( 'tainacan-deleted', $deleted );
			do_action( 'tainacan-deleted-term', $deleted );
		}

		return $return;
	}
	
	/**
	* Check if a term already exists 
	*
	* @param string $searched_term The term name (string) or term_id (integer). If term id is passed, parent is not considered. 
	* @param mixed $taxonomy The taxonomy ID, slug or Entity.
	* @param int $parent The ID of the parent term to look for children or null to look for terms in any hierarchical position. Default is null 
	* @param bool $return_term wether to return the term object if it exists. default is to false 
	* 
	* @return bool|WP_Term return boolean indicating if term exists. If $return_term is true and term exists, return WP_Term object 
	*/
	public function term_exists($searched_term, $taxonomy, $parent = null, $return_term = false) {
		
		$Tainacan_Taxonomies = \Tainacan\Repositories\Taxonomies::get_instance();

		if ( is_numeric( $taxonomy ) ) {
			$taxonomy_slug = $Tainacan_Taxonomies->get_db_identifier_by_id( $taxonomy );
		} elseif (is_string($taxonomy)) {
			$taxonomy_slug = $taxonomy;
		} elseif ( $taxonomy instanceof Entities\Taxonomy ) {
			$taxonomy_slug = $taxonomy->get_db_identifier();
		}

		if(is_int($searched_term)){
			
			$term = get_term_by( 'id', $searched_term, $taxonomy_slug );
			
			if ( ! $term ) {
				return false;
			}
			
		} else {
			$args = [
				'name'            => $searched_term,
				'taxonomy'        => $taxonomy_slug,
				'parent'          => $parent,
				'hide_empty'      => 0,
				'suppress_filter' => true
			];
			
			if (is_null($parent)) {
				unset($args['parent']);
			}
			
			$terms = get_terms($args);
			
			if (empty($terms)) {
				return false;
			}
			
			$term = $terms[0];
			
		}
		
		if ($return_term) {
			return $term;
		}
		
		return true;
		
	}


	public function register_post_type() {
	}
}