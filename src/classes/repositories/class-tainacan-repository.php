<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;
use Tainacan\Entities\Entity;
use Tainacan;
use Tainacan\Repositories;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

abstract class Repository {
	public $entities_type = '\Tainacan\Entities\Entity';

	/**
	 * If set to false, no logs will be generated upon insertion or update
	 *
	 * use enable_logs() and disable_logs() to set the values
	 *
	 * @var bool
	 */
	protected $use_logs = true;

	/**
	 * Instance of Repository Logs
	 *
	 * @var Repositories\Logs
	 */
  protected $logs_repository;

  private $map = [];

	/**
	 * Disable creation of logs while inerting and updating entities
	 */
	public function disable_logs() {
		$this->use_logs = false;
	}

	/**
	 * Enable creation of logs while inserting and updating entities
	 * if it was disabled
	 */
	public function enable_logs() {
		$this->use_logs = true;
	}

	/**
	 * Get if creation of logs while inserting and updating entities are enable
	 */
	public function get_enabled_logs() {
		return $this->use_logs;
	}

	/**
	 * Register hooks
	 */
	protected function __construct() {
		$name = $this->get_name();
		add_action( 'init', array( &$this, 'register_post_type' ) );
		add_action( 'init', array( &$this, 'init_objects' ) );

		add_filter( "tainacan-get-map-$name", array( $this, 'get_default_properties' ) );
	}

	public function init_objects() {
		$this->logs_repository = Repositories\Logs::get_instance();
	}

	/**
	 * return properties map
	 *
	 * @return array properties map array, format like:
	 *        'id'             => [
	 *          'map'        => 'ID',
	 *          'title'       => __('ID', 'tainacan'),
	 *          'type'       => 'integer',
	 *          'description'=> __('Unique identifier', 'tainacan'),
	 *          'validation' => v::numeric(),
	 *      ],
	 *      'name'           =>  [
	 *          'map'        => 'post_title',
	 *          'title'       => __('Name', 'tainacan'),
	 *          'type'       => 'string',
	 *          'description'=> __('Name of the collection', 'tainacan'),
	 *          'validation' => v::stringType(),
	 *          'default'     => ''
	 *      ],
	 *      'slug'           =>  [
	 *          'map'        => 'post_name',
	 *          'title'       => __('Slug', 'tainacan'),
	 *          'type'       => 'string',
	 *          'description'=> __('A unique and sanitized string representation of the collection, used to build the collection URL', 'tainacan'),
	 *          'validation' => v::stringType(),
	 *      ],
	 */
  protected abstract function _get_map();

  public function get_map() {
    if (isset($this->map) && !empty($this->map)) {
      return $this->map;
    }
    $this->map = $this->_get_map();
    return $this->map;
  }

	/**
	 * Return repository name
	 *
	 * @return string The repository name
	 */
	public function get_name() {
		return str_replace( 'Tainacan\Repositories\\', '', get_class( $this ) );
	}

	/**
	 *
	 * @param \Tainacan\Entities\Entity $obj
	 *
	 * @return \Tainacan\Entities\Entity | bool
	 * @throws \Exception
	 */
	public function insert( $obj ) {
		$obj_post_type = $obj->get_post_type();
		// validate
		$required_validation_statuses = ['publish', 'future', 'private', 'pending'];
		if (in_array( $obj->get_status(), apply_filters( 'tainacan-status-require-validation', $required_validation_statuses) ) && ! $obj->get_validated() ) {
			throw new \Exception( 'Entities must be validated before you can save them' );
			// TODO: Throw Warning saying you must validate object before insert()
		}

		$is_update = false;

		$diffs = [];
		do_action( 'tainacan-pre-insert', $obj );
		$obj_post_type = $obj->get_post_type();
		if( $obj_post_type != false )
			do_action( "tainacan-pre-insert-$obj_post_type", $obj );

		$map = $this->get_map();
		// First iterate through native post properties
		foreach ( $map as $prop => $mapped ) {
			if ( $mapped['map'] != 'meta' && $mapped['map'] != 'meta_multi' ) {
				$obj->WP_Post->{$mapped['map']} = $obj->get_mapped_property( $prop );
			}
		}
		$obj->WP_Post->post_type = $obj::get_post_type();

		if ( $obj instanceof Entities\Log && ! ( isset( $obj->WP_Post->post_status ) && in_array( $obj->WP_Post->post_status, [
					'publish',
					'pending'
				] ) ) ) {
			$obj->WP_Post->post_status = 'publish';
		}

		$sanitized_title = $this->sanitize_value($obj->get('name'));
		$sanitized_desc = $this->sanitize_value($obj->get('description'));
		if ( $obj instanceof Entities\Item ) {
			$sanitized_title = $this->sanitize_value($obj->get('title'));

			// get collection to determine post type
			$collection = $obj->get_collection();

			if (!$collection) {
				return false;
			}

			$post_t                  = $collection->get_db_identifier();
			$obj->WP_Post->post_type = $post_t;
			$obj_post_type = 'tainacan-item';
			do_action( "tainacan-pre-insert-$obj_post_type", $obj );
		}

		if ( ! $obj instanceof Entities\Log ) {
			$obj->WP_Post->post_title = $sanitized_title;
			$obj->WP_Post->post_content = $sanitized_desc;
		} else {
			$obj->WP_Post->post_title = $this->sanitize_value($obj->WP_Post->post_title);
			$obj->WP_Post->post_content = $this->sanitize_value($obj->WP_Post->post_content);
		}
		
		// wp_parse_args is used here to ensure an array is passed to wp_insert_post (instead of, for example an object of stdClass)
		$id = wp_insert_post( wp_parse_args( $obj->WP_Post ) );

		if ( $id instanceof \WP_Error || 0 === $id ) {
			return false;
		}

		// reset object
		$obj->WP_Post = get_post( $id );

		// Now run through properties stored as postmeta
		foreach ( $map as $prop => $mapped ) {
			if ( $mapped['map'] == 'meta' || $mapped['map'] == 'meta_multi' ) {
				$diffs = $this->insert_metadata( $obj, $prop, $diffs );
			}
		}
		update_post_meta( $id, '_user_edit_lastr', get_current_user_id() );

		do_action( 'tainacan-insert', $obj, $diffs, $is_update );
		if( $obj_post_type != false ) {
			do_action( "tainacan-insert-$obj_post_type", $obj );
		}

		// return a brand new object
		return new $this->entities_type( $obj->WP_Post );
	}

	/**
	 * Insert object property stored as postmeta into the database
	 *
	 * @param  \Tainacan\Entities $obj The entity object
	 * @param  string $prop the property name, as declared in the map of the repository
	 *
	 * @param $diffs
	 *
	 * @return null|false on error
	 */
	public function insert_metadata( $obj, $prop, $diffs ) {
		$map = $this->get_map();

		if ( ! array_key_exists( $prop, $map ) ) {
			return false;
		}

		if ( $map[ $prop ]['map'] == 'meta' ) {

			if ( $prop === '_thumbnail_id' ) {
				$diffs = $this->insert_thumbnail( $obj, $diffs );

				return $diffs;
			} else {
				update_post_meta( $obj->get_id(), $prop, $this->maybe_add_slashes( $obj->get_mapped_property( $prop ) ) );
			}

		} elseif ( $map[ $prop ]['map'] == 'meta_multi' ) {
			$values         = $obj->get_mapped_property( $prop );
			$current_values = get_post_meta( $obj->get_id(), $prop );

			if ( empty( $values ) || ! is_array( $values ) ) {
				$values = [];
			}

			if ( empty( $current_values ) || ! is_array( $current_values ) ) {
				$current_values = [];
			}

			$deleted = array_diff( $current_values, $values );
			$added   = array_diff( $values, $current_values );

			foreach ( $deleted as $del ) {
				delete_post_meta( $obj->get_id(), $prop, $del );
			}

			foreach ( $added as $add ) {
				add_post_meta( $obj->get_id(), $prop, $this->maybe_add_slashes( $add ) );
			}

		}

		return $diffs;
	}

	function maybe_add_slashes( $value ) {
		if ( is_string( $value ) ) {
			if( strpos( $value, '\\' ) !== false ) {
				return wp_slash( $this->sanitize_value($value) );
			}
			return $this->sanitize_value($value);
		}
		return $value;
	}

	/**
	 * Prepare the output for the fetch() methods.
	 *
	 * Possible outputs are:
	 * WP_Query (default) - returns the WP_Object itself
	 * OBJECT - return an Array of Tainacan\Entities
	 *
	 * @param \WP_Query $WP_Query
	 * @param string $output `WP_Query` for a single WP_Query object or `OBJECT` for an array of Tainacan\Entities
	 *
	 * @return array|\WP_Query
	 */
	public function fetch_output( \WP_Query $WP_Query, $output = 'WP_Query' ) {

		if ( is_null( $output ) ) {
			$output = 'WP_Query';
		}

		if ( $output === 'WP_Query' ) {
			return $WP_Query;
		} else if ( $output === 'OBJECT' ) {
			$result = [];

			if ( $WP_Query->have_posts() ) {
				/**
				 * Using WordPress Loop here would cause problems
				 *
				 * @see https://core.trac.wordpress.org/ticket/18408
				 */
				foreach ( $WP_Query->posts as $p ) {
					$result[] = new $this->entities_type( $p->ID );
				}
			}

			return $result;
		}
	}

	/**
	 * Maps repository mapped properties to WP_Query arguments.
	 *
	 * This allows to build fetch arguments using both WP_Query arguments
	 * and the mapped properties for the repository.
	 *
	 * For example, you can use any of the following methods to browse collections by name:
	 * $TainacanCollections->fetch(['title' => 'test']);
	 * $TainacanCollections->fetch(['name' => 'test']);
	 *
	 * The property `name` is transformed into the native WordPress property `post_title`. (actually only title for query purpouses)
	 *
	 * Example 2, this also works with properties mapped to postmeta. The following methods are the same:
	 * $TainacanMetadatas->fetch(['required' => 'yes']);
	 * $TainacanMetadatas->fetch(['meta_query' => [
	 *     [
	 *         'key' => 'required',
	 *         'value' => 'yes'
	 *     ]
	 * ]]);
	 *
	 *
	 * @param  array $args [description]
	 *
	 * @return array $args new $args array with mapped properties
	 */
	public function parse_fetch_args( $args = [] ) {

		$map = $this->get_map();

		$wp_query_exceptions = [
			'ID'         => 'p',
			'post_title' => 'title'
		];

		$meta_query = [];

		foreach ( $map as $prop => $mapped ) {
			if ( array_key_exists( $prop, $args ) ) {
				$prop_value = $args[ $prop ];

				unset( $args[ $prop ] );

				if ( $mapped['map'] == 'meta' || $mapped['map'] == 'meta_multi' ) {
					$meta_query[] = [
						'key'   => $prop,
						'value' => $prop_value
					];
				} else {
					$prop_search_name          = array_key_exists( $mapped['map'], $wp_query_exceptions ) ? $wp_query_exceptions[ $mapped['map'] ] : $mapped['map'];
					$args[ $prop_search_name ] = $prop_value;
				}

			}
		}

		if ( isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) && ! empty( $args['meta_query'] ) ) {
			$meta_query = array_merge( $args['meta_query'], $meta_query );
		}

		$args['meta_query'] = $meta_query;

		// Map orderby parameter
		if ( isset( $args['orderby'] ) ) {
			if ( array_key_exists( $args['orderby'], $map ) ) {
				$args['orderby'] = $map[ $args['orderby'] ]['map'];
			}
		}


		return $args;

	}

	/**
	 * Return default properties
	 *
	 * @param array $map
	 *
	 * @return array
	 */
	public function get_default_properties( $map ) {
		if ( is_array( $map ) ) {
			$defaults = array(
				'status' => array(
					'map'         => 'post_status',
					'title'       => __( 'Status', 'tainacan' ),
					'type'        => 'string',
					'description' => __( 'Status for control of visibility', 'tainacan' ),
					//'validation'	=> v::stringType(),
				),
				'id'     => array(
					'map'         => 'ID',
					'title'       => __( 'ID', 'tainacan' ),
					'type'        => 'integer',
					'description' => __( 'Unique identifier', 'tainacan' ),
					//'validation' => v::numeric(),
				),
			);

			return array_merge( $defaults, $map );
		}

		return $map;
	}

	/**
	 * return the value for a mapped property from database
	 *
	 * @param Entities\Entity $entity
	 * @param string $prop id of property
	 *
	 * @return mixed property value
	 */
	public function get_mapped_property( $entity, $prop ) {

		$map = $this->get_map();

		if ( ! array_key_exists( $prop, $map ) ) {
			return null;
		}

		$mapped   = $map[ $prop ]['map'];
		$property = '';
		if ( $mapped == 'meta' ) {
			$property = isset( $entity->WP_Post->ID ) ? get_post_meta( $entity->WP_Post->ID, $prop, true ) : null;
		} elseif ( $mapped == 'meta_multi' ) {
			$property = isset( $entity->WP_Post->ID ) ? get_post_meta( $entity->WP_Post->ID, $prop, false ) : null;
		} elseif ( $mapped == 'termmeta' ) {
			$property = isset( $entity->WP_Term->term_id ) ? get_term_meta( $entity->WP_Term->term_id, $prop, true ) : null;
		} elseif ( isset( $entity->WP_Post ) ) {
			$property = isset( $entity->WP_Post->$mapped ) ? $entity->WP_Post->$mapped : null;
		} elseif ( isset( $entity->WP_Term ) ) {
			$property = isset( $entity->WP_Term->$mapped ) ? $entity->WP_Term->$mapped : null;
		}

		if ( empty( $property ) && array_key_exists( 'default', $map[ $prop ] ) ) {
			$property = $map[ $prop ]['default'];
		}

		return $property;
	}

	/**
	 * Return array of collections db identifiers
	 *
	 * @return array[]
	 */
	public static function get_collections_db_identifiers() {
		$Tainacan_Collections = \Tainacan\Repositories\Collections::get_instance();
		$collections          = $Tainacan_Collections->fetch( [], 'OBJECT' );
		$cpts                 = [];
		foreach ( $collections as $col ) {
			$cpts[] = $col->get_db_identifier();
		}

		return $cpts;
	}

	/**
	 *
	 * @param integer|\WP_Post $post |Entity
	 *
	 * @throws \Exception
	 * @return \Tainacan\Entities\Entity|boolean
	 */
	public static function get_entity_by_post( $post ) {
		if ( is_numeric( $post ) || is_array( $post ) ) {
			$post = get_post( $post );
		} elseif ( is_object( $post ) && $post instanceof Entity ) {
			return $post;
		}

		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		$post_type = $post->post_type;

		return self::get_entity_by_post_type( $post_type, $post );
	}

	/**
	 *
	 * @param string $post_type
	 * @param integer|\WP_Post $post optional post ID or WordPress post data for creation of Entity
	 *
	 * @throws \Exception
	 * @return \Tainacan\Entities\Entity|boolean the entity for post_type, with data if $post is given or false
	 */
	public static function get_entity_by_post_type( $post_type, $post = 0 ) {
		$prefix                  = substr( $post_type, 0, strlen( Entities\Collection::$db_identifier_prefix ) );
		$item_metadata           = Repositories\Item_Metadata::get_instance();
		$item_metadata_entity    = new $item_metadata->entities_type( null, null );
		$item_metadata_post_type = $item_metadata_entity::get_post_type();

		// Is it a collection Item?
		if ( $prefix == Entities\Collection::$db_identifier_prefix ) {
			$cpts = self::get_collections_db_identifiers();
			if ( in_array( $post_type, $cpts ) ) {
				return $entity = new \Tainacan\Entities\Item( $post );
			} else {
				throw new \Exception( 'Collection object not found for this post' );
			}
		} elseif ( $post_type === $item_metadata_post_type ) {
			return new Entities\Item_Metadata_Entity( null, null );
		} else {
			$Tainacan_Collections = Repositories\Collections::get_instance();
			$Tainacan_Filters     = Repositories\Filters::get_instance();
			$Tainacan_Logs        = Repositories\Logs::get_instance();
			$Tainacan_Metadata    = Repositories\Metadata::get_instance();
			$Tainacan_Taxonomies  = Repositories\Taxonomies::get_instance();
			$Tainacan_Terms       = Repositories\Terms::get_instance();
			$Tainacan_Metadata_Sections = Repositories\Metadata_Sections::get_instance();

			$tnc_globals = [
				$Tainacan_Collections,
				$Tainacan_Metadata,
				$Tainacan_Filters,
				$Tainacan_Taxonomies,
				$Tainacan_Terms,
				$Tainacan_Logs,
				$Tainacan_Metadata_Sections
			];
			foreach ( $tnc_globals as $tnc_repository ) {
				$tnc_entity       = new $tnc_repository->entities_type();
				$entity_post_type = $tnc_entity::get_post_type();

				if ( $entity_post_type == $post_type ) {
					return new $tnc_repository->entities_type( $post );
				}
			}
		}

		return false;
	}

	/**
	 * Return Entity's Repository
	 *
	 * @param Entity $entity
	 *
	 * @return \Tainacan\Repositories\Repository|bool return the entity Repository or false
	 */
	public static function get_repository( $entity ) {
		$post_type = $entity->get_post_type();
		$prefix    = substr( $post_type, 0, strlen( Entities\Collection::$db_identifier_prefix ) );

		// its is a collection Item?
		if ( $prefix == Entities\Collection::$db_identifier_prefix ) {
			$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

			return $Tainacan_Items;
		} else {
			$Tainacan_Collections   = \Tainacan\Repositories\Collections::get_instance();
			$Tainacan_Metadata      = \Tainacan\Repositories\Metadata::get_instance();
			$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
			$Tainacan_Filters       = \Tainacan\Repositories\Filters::get_instance();
			$Tainacan_Taxonomies    = \Tainacan\Repositories\Taxonomies::get_instance();
			$Tainacan_Terms         = \Tainacan\Repositories\Terms::get_instance();
			$Tainacan_Logs          = \Tainacan\Repositories\Logs::get_instance();

			$tnc_globals = [
				$Tainacan_Collections,
				$Tainacan_Metadata,
				$Tainacan_Item_Metadata,
				$Tainacan_Filters,
				$Tainacan_Taxonomies,
				$Tainacan_Terms,
				$Tainacan_Logs
			];
			foreach ( $tnc_globals as $tnc_repository ) {
				$tnc_entity       = new $tnc_repository->entities_type();
				$entity_post_type = $tnc_entity::get_post_type();

				if ( $entity_post_type == $post_type ) {
					return $tnc_repository;
				}
			}
		}

		return false;
	}

	/**
	 * Fetch one Entity based on query args.
	 *
	 * Note: Does not work with Item_Metadata Repository
	 *
	 * @param array $args Query Args as expected by fetch
	 *
	 * @return false|\Tainacan\Entities The entity or false if none was found
	 */
	public function fetch_one( $args ) {
		if ( $this->get_name() == 'Item_Metadata' ) {
			return false;
		}

		$args['posts_per_page'] = 1;

		$results = $this->fetch( $args, 'OBJECT' );

		if ( is_array( $results ) && sizeof( $results ) > 0 && $results[0] instanceof \Tainacan\Entities\Entity ) {
			return $results[0];
		}

		return false;
	}

	/**
	 * Shortcut to delete($entity, false)
	 *
	 * @param Entities\Entity $entity
	 *
	 * @return mixed|Entity @see https://developer.wordpress.org/reference/functions/wp_delete_post/
	 */
	public function trash( Entities\Entity $entity ) {
		return $this->delete( $entity, false );
	}

	/**
	 * @param Entities\Entity $entity
	 * @param bool $permanent If false, sendo to trash, if true, permanently delete. Default true
	 *
	 * @return mixed|Entity @see https://developer.wordpress.org/reference/functions/wp_delete_post/
	 */
	public function delete( Entities\Entity $entity, $permanent = true ) {

		$post_type = $entity->get_post_type();
		do_action( 'tainacan-pre-delete', $entity, $permanent );
		do_action( "tainacan-pre-delete-$post_type", $entity, $permanent );

		if ($permanent === true) {
			$return = wp_delete_post( $entity->get_id(), $permanent );
		} elseif ($permanent === false) {
			$return = wp_trash_post( $entity->get_id() );
		}


		if ( $return instanceof \WP_Post && $this->use_logs ) {

			$post_type = $entity->get_post_type();
			do_action( 'tainacan-deleted', $entity, $permanent );
			do_action( "tainacan-deleted-$post_type", $entity, $permanent );

			$return = $this->get_entity_by_post($return);

		}

		return $return;
	}

	/**
	 * @param $args
	 *
	 * @return mixed
	 */
	public abstract function fetch( $args, $output = null );

	/**
	 * @param $object
	 *
	 * @return mixed
	 */
	public abstract function update( $object, $new_values = null );

	/**
	 * @return mixed
	 */
	public abstract function register_post_type();

	/**
	 * Check if $user can edit/create a entity
	 *
	 * @param Entities\Entity $entity
	 * @param int|\WP_User|null $user default is null for the current user
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function can_edit( Entities\Entity $entity, $user = null ) {
		if ( is_null( $user ) ) {
			$user = get_current_user_id();
		} elseif ( is_object( $user ) ) {
			$user = $user->ID;
		}
		$entity_cap = $entity->get_capabilities();

		if ( ! isset( $entity_cap->edit_post ) ) {
			return false;
		}

		if ( is_integer( $entity->get_id() ) ) {
			return user_can( $user, $entity_cap->edit_post, $entity->get_id() );
		} else {
			// creating new
			return user_can( $user, $entity_cap->edit_posts );
		}

	}

	/**
	 * Check if $user can read the entity
	 *
	 * @param Entities\Entity $entity
	 * @param int|\WP_User|null $user default is null for the current user
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function can_read( Entities\Entity $entity, $user = null ) {
		if ( is_null($entity) )
			return false;

		if ( is_null( $user ) ) {
			$user = get_current_user_id();
			if ( ! $user ) {
				$status = get_post_status($entity->get_id());
				$post_status_obj = get_post_status_object($status);
				return $post_status_obj->public;
			}
		} elseif ( is_object( $user ) ) {
			$user = $user->ID;
		}
		$entity_cap = $entity->get_capabilities();

		if ( ! isset( $entity_cap->read ) ) {
			$prefix = Entities\Collection::$db_identifier_prefix;
			$sufix = Entities\Collection::$db_identifier_sufix;
			$is_a_item = preg_match('/^'. $prefix . '[0-9]*' . $sufix . '$/i', $entity->WP_Post->post_type);
			if ( $entity->get_post_type() === false && !$is_a_item) { // Allow read of not post entities
				return true;
			}

			return false;
		}

		return user_can( $user, $entity_cap->read_post, $entity->get_id() );
	}

	/**
	 * Check if $user can delete the entity
	 *
	 * @param Entities\Entity $entity
	 * @param int|\WP_User|null $user default is null for the current user
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function can_delete( $entity, $user = null ) {
		if ( is_null( $user ) ) {
			$user = get_current_user_id();
		} elseif ( is_object( $user ) ) {
			$user = $user->ID;
		}
		$entity_cap = $entity->get_capabilities();

		if ( ! isset( $entity_cap->delete_post ) ) {
			return false;
		}

		return user_can( $user, $entity_cap->delete_post, $entity->get_id() );
	}

	/**
	 * Check if $user can publish entity
	 *
	 * @param Entities\Entity $entity
	 * @param int|\WP_User|null $user default is null for the current user
	 *
	 * @return boolean
	 * @throws \Exception
	 */
	public function can_publish(Entities\Entity $entity, $user = null) {
		if ( is_null( $user ) ) {
			$user = get_current_user_id();
		} elseif ( is_object( $user ) ) {
			$user = $user->ID;
		}
		$entity_cap = $entity->get_capabilities();

		if ( ! $user || ! isset( $entity_cap->publish_posts ) ) {
			return false;
		}

		return user_can( $user, $entity_cap->publish_posts );

	}

	/**
	 * Removes duplicates from multidimensional array
	 *
	 * @param $array
	 * @param $key
	 *
	 * @return array
	 */
	function unique_multidimensional_array( $array, $key ) {
		$temp_array = array();
		$i          = 0;
		$key_array  = array();

		foreach ( $array as $val ) {
			if ( ! in_array( $val->$key, $key_array ) ) {
				$key_array[ $i ]  = $val->$key;
				$temp_array[ $i ] = $val;
			}

			$i ++;
		}

		return $temp_array;
	}

	/**
	 * Inserts or update thumbnail for items and collections and return an array
	 * with old thumbnail and new thumbnail
	 *
	 * @param $obj
	 * @param $diffs
	 *
	 * @return mixed
	 */
	private function insert_thumbnail( $obj, $diffs ) {
		if ( ! get_post_thumbnail_id( $obj->WP_Post->ID ) ) {
			// was added a thumbnail

			$settled = set_post_thumbnail( $obj->WP_Post, (int) $obj->get__thumbnail_id() );

			if ( $settled ) {

				$thumbnail_url = get_the_post_thumbnail_url( $obj->WP_Post->ID );

				$diffs['thumbnail'] = [
					'new'             => $thumbnail_url,
					'old'             => '',
					'diff_with_index' => 0,
				];

			}

		} else {

			// was updated a thumbnail

			$old_thumbnail = get_the_post_thumbnail_url( $obj->WP_Post->ID );

			$fid = $obj->get__thumbnail_id();

			if ( ! $fid ) {
				$settled = delete_post_thumbnail( $obj->WP_Post );
			} else {
				$settled = set_post_thumbnail( $obj->WP_Post, (int) $fid );
			}

			if ( $settled ) {

				$thumbnail_url = get_the_post_thumbnail_url( $obj->WP_Post->ID );

				$diffs['thumbnail'] = [
					'new'             => $thumbnail_url,
					'old'             => $old_thumbnail,
					'diff_with_index' => 0,
				];
			}
		}

		$thumbnail_id = $obj->get__thumbnail_id();
		if($thumbnail_id) {
			$tmp_src = wp_get_attachment_image_src( $thumbnail_id, 'tainacan-medium' );
			$file_name = get_attached_file( $thumbnail_id );
			$blurhash = \Tainacan\Media::get_instance()->get_image_blurhash($file_name, $tmp_src[1], $tmp_src[2]);
			$attachment_metadata = \wp_get_attachment_metadata($thumbnail_id);
			if($attachment_metadata != false && isset($attachment_metadata['image_meta']) && is_array($attachment_metadata['image_meta'])) {
				$attachment_metadata['image_meta'] = array_merge($attachment_metadata['image_meta'], ['blurhash' => $blurhash]);
			}
			wp_update_attachment_metadata($thumbnail_id, $attachment_metadata);
		}

		return $diffs;
	}

	/**
	 * Get IDs for all children, grand children till the depth parameter is reached
	 * @param  int|\Tainacan\Entities\Entity $id The Entity ID or object
	 * @param  bool|int $depth The maximum depth to llok for descendants. default is false = no limit
	 * @return array     Array of IDs
	 */
	public function get_descendants_ids($id, $depth = false) {
		$object = $id;
		if (is_integer($id)) {
			$object = $this->fetch($id);
		}

		if ( ! $object instanceof \Tainacan\Entities\Entity) {
			return [];
		}

		global $wpdb;
		$go_deeper = false === $depth || (is_integer($depth) && $depth > 1);
		$new_depth = is_integer($depth) ? $depth - 1 : $depth;

		$children = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = %s", $object->get_id(), $object->get_post_type() ) );

		if ($go_deeper && sizeof($children) > 0) {
			$gchildren = [];
			foreach ($children as $child) {
				$_gchildren = $this->get_descendants_ids((int) $child, $new_depth);
				if (!empty($_gchildren)) {
					$gchildren = array_merge($gchildren, $_gchildren);
				}
			}
			$children = array_merge($children, $gchildren);

		}

		return $children;

	}

	/**
	 * Get the capabilities list for the post type of the entity
	 *
	 * @uses get_post_type_capabilities to get the list.
	 *
	 * This method is usefull for getting the capabilities of the entity post type
	 * regardless if it has been already registered or not.
	 *
	 * @return object Object with all the capabilities as member variables.
	 */
	public function get_capabilities() {

		$entity = new $this->entities_type();
		return $entity->get_capabilities();

	}

	protected function sanitize_value($content) {
		if( $content == null ) {
			return '';
		}
		if (is_numeric($content) || empty($content ) ) {
			return $content;
		}

		$allowed_html = wp_kses_allowed_html('post');
		unset($allowed_html["a"]);
	
		return trim(wp_kses($content, $allowed_html));
	}

}

