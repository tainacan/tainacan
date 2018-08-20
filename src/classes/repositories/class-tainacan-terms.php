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
				'validation'  => ''
			],
			'description'     => [
				'map'         => 'description',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The term description', 'tainacan' ),
				'default'     => '',
				'validation'  => ''
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
				'validation'  => v::numeric(),
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

		$is_update = false;
		$diffs     = [];
		if ( $term->get_id() ) {
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

					if ( ! empty( $term->WP_Term->{$mapped['map']} ) ) {
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

		// Now run through properties stored as postmeta
		foreach ( $map as $prop => $mapped ) {
			if ( $mapped['map'] == 'termmeta' ) {
				update_term_meta( $term_saved['term_id'], $prop, wp_slash( $term->get_mapped_property( $prop ) ) );
			}
		}

		// TODO: Log header image updates
		$this->logs_repository->insert_log( $term, $diffs, $is_update );

		do_action( 'tainacan-insert', $term, $diffs, $is_update );
		do_action( 'tainacan-insert-Term', $term );

		return new Entities\Term( $term_saved['term_id'], $term->get_taxonomy() );
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
				$tainacan_term->set_user( get_term_meta( $tainacan_term->get_id(), 'user', true ) );
				$return[] = $tainacan_term;
			}

			return $return;
		} elseif ( is_numeric( $args ) && ! empty( $cpt ) && ! is_array( $cpt ) ) { // if an id is passed taxonomy cannot be an array
			$wp_term       = get_term_by( 'id', $args, $cpt );
			$tainacan_term = new Entities\Term( $wp_term );
			$tainacan_term->set_user( get_term_meta( $tainacan_term->get_id(), 'user', true ) );

			return $tainacan_term;
		} else {
			return [];
		}
	}

	public function update( $object, $args = null ) {
		return $this->insert( $object );
	}

	/**
	 * @param Array $delete_args has ['term_id', 'taxonomy']
	 *
	 * @return bool|int|mixed|\WP_Error
	 */
	public function delete( $delete_args ) {
		$deleted = wp_delete_term( $delete_args['term_id'], $delete_args['taxonomy'] );

		if ( $deleted ) {
			$deleted_term_tainacan = new Entities\Term( $delete_args['term_id'], $delete_args['taxonomy'] );

			$this->logs_repository->insert_log( $deleted_term_tainacan, [], false, true );

			do_action( 'tainacan-deleted', $deleted_term_tainacan );
		}

		return $deleted;
	}

	/**
	 * @param $term_id
	 *
	 * @return mixed|void
	 */
	public function trash( $term_id ) {
	}

	public function register_post_type() {
	}
}