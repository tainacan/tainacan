<?php

namespace Tainacan\Repositories;

use Tainacan\Entities;
use Tainacan\Entities\Entity;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Implement a Logs system
 *
 * @author medialab
 *
 */
class Logs extends Repository {
	public $entities_type = '\Tainacan\Entities\Log';
	private static $instance = null;
	private $current_diff = null;
	private $current_deleting_entity;
	private $current_action;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	protected function __construct() {
		parent::__construct();

		add_action( 'tainacan-pre-insert', array( $this, 'pre_insert_entity' ) );
		
		add_action( 'tainacan-insert', array( $this, 'insert_entity' ) );
		add_action( 'tainacan-deleted', array( $this, 'delete_entity' ), 10, 2 );
		add_action( 'tainacan-pre-delete', array( $this, 'pre_delete_entity' ), 10, 2 );
		
		add_action( 'add_attachment', array( $this, 'insert_attachment' ) );
		add_action( 'delete_attachment', array( $this, 'pre_delete_attachment' ) );
		add_action( 'delete_post', array( $this, 'delete_attachment' ) );
		
		add_filter('tainacan-log-set-title', [$this, 'filter_log_title']);
	}

	protected function _get_map() {
		return apply_filters( 'tainacan-get-map-' . $this->get_name(), [
			'title'          => [
				'map'         => 'post_title',
				'title'       => __( 'Title', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The title of the log', 'tainacan' ),
				'on_error'    => __( 'The title should be a text value and not empty', 'tainacan' ),
				'validation'  => ''
			],
			'date'       => [
				'map'         => 'post_date',
				'title'       => __( 'Log date', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log date', 'tainacan' ),
			],
			'description'    => [
				'map'         => 'post_content',
				'title'       => __( 'Description', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log description' ),
				'default'     => '',
				'validation'  => ''
			],
			'slug'           => [
				'map'         => 'post_name',
				'title'       => __( 'Slug', 'tainacan' ),
				'type'        => 'string',
				'description' => __( 'The log slug' ),
				'validation'  => ''
			],
			'user_id'        => [
				'map'         => 'post_author',
				'title'       => __( 'User ID', 'tainacan' ),
				'type'        => 'integer',
				'description' => __( 'Unique identifier' ),
				'validation'  => ''
			],
			'item_id'        => [
				'map'         => 'meta',
				'title'       => __( 'Item ID', 'tainacan' ),
				'type'        => 'integer',
			],
			// 'value'          => [
			// 	'map'         => 'meta',
			// 	'title'       => __( 'Actual value', 'tainacan' ),
			// 	'type'        => 'string',
			// 	'description' => __( 'The actual log value' ),
			// 	'validation'  => ''
			// ],
			'log_diffs'      => [ // deprecated
				'map'         => 'meta',
				'title'       => __( 'Log differences', 'tainacan' ),
				'description' => __( 'Differences between old and new versions of object', 'tainacan' )
			],
			'collection_id'  => [
				'map'         => 'meta',
				'title'       => __( 'Log collection relationship', 'tainacan' ),
				'description' => __( 'The ID of the collection that this log is related to', 'tainacan' )
			],
			'object_id' => [
				'map'         => 'meta',
				'title'       => __( 'Log item relationship', 'tainacan' ),
				'description' => __( 'The id of the object that this log is related to', 'tainacan' ),
			],
			'object_type' => [
				'map'         => 'meta',
				'title'       => __( 'Log item relationship', 'tainacan' ),
				'description' => __( 'The type of the object that this log is related to', 'tainacan' ),
			],
			'old_value' => [
				'map'         => 'meta',
				'title'       => __( 'Old Value', 'tainacan' ),
			],
			'new_value' => [
				'map'         => 'meta',
				'title'       => __( 'New value', 'tainacan' ),
			],
			'action' => [
				'map'         => 'meta',
				'title'       => __( 'Action', 'tainacan' ),
			]
		] );
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Repository::register_post_type()
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Logs', 'tainacan' ),
			'singular_name'      => __( 'Log', 'tainacan' ),
			'add_new'            => __( 'Add new', 'tainacan' ),
			'add_new_item'       => __( 'Add new Log', 'tainacan' ),
			'edit_item'          => __( 'Edit Log', 'tainacan' ),
			'new_item'           => __( 'New Log', 'tainacan' ),
			'view_item'          => __( 'View Log', 'tainacan' ),
			'search_items'       => __( 'Search Logs', 'tainacan' ),
			'not_found'          => __( 'No Logs found ', 'tainacan' ),
			'not_found_in_trash' => __( 'No Logs found in trash', 'tainacan' ),
			'parent_item_colon'  => __( 'Parent Log:', 'tainacan' ),
			'menu_name'          => __( 'Logs', 'tainacan' )
		);
		$args   = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'public'              => false,
			'show_ui'             => tnc_enable_dev_wp_interface(),
			'show_in_menu'        => tnc_enable_dev_wp_interface(),
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'map_meta_cap'        => true,
			'capability_type'     => Entities\Log::get_capability_type(),
			'supports'            => [
				'title',
				'editor',
				'thumbnail',
			]
		);
		register_post_type( Entities\Log::get_post_type(), $args );
	}


	/**
	 * fetch logs based on ID or WP_Query args
	 *
	 * Logs are stored as posts. Check WP_Query docs
	 * to learn all args accepted in the $args parameter (@see https://developer.wordpress.org/reference/classes/wp_query/)
	 * You can also use a mapped property, such as name and description, as an argument and it will be mapped to the
	 * appropriate WP_Query argument
	 * 
	 * If a number is passed to $args, it will return a \Tainacan\Entities\Log object.  But if the post is not found or
	 * does not match the entity post type, it will return an empty array
	 *
	 * @param array $args WP_Query args || int $args the log id
	 * @param string $output The desired output format (@see \Tainacan\Repositories\Repository::fetch_output() for possible values)
	 *
	 * @return \WP_Query|Array an instance of wp query OR array of entities;
	 */
	public function fetch( $args = [], $output = null ) {
		if ( is_numeric( $args ) ) {
			
			$existing_post = get_post( $args );
			if ( $existing_post instanceof \WP_Post ) {
				try {
					return new Entities\Log( $existing_post );
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

			$args['post_type'] = Entities\Log::get_post_type();

			$args = apply_filters( 'tainacan_fetch_args', $args, 'logs' );

			$wp_query = new \WP_Query( $args );

			return $this->fetch_output( $wp_query, $output );
		}
	}

	public function update( $object, $new_values = null ) {
		return $this->insert( $object );
	}
	
	/**
	 * Feth most recent log
	 * @return Entities\Log The most recent Log entity
	 */
	public function fetch_last() {
		$args = [
			'post_type'      => Entities\Log::get_post_type(),
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'DESC'
		];

		$logs = $this->fetch( $args, 'OBJECT' );

		return array_pop( $logs );
	}

	/**
	 * Callback to generate log when attachments are added to any Tainacan entity
	 */
	public function insert_attachment( $post_ID ) {
		$attachment = get_post( $post_ID );
		$post       = $attachment->post_parent;

		if ( $post ) { // attached to a post

			$entity = Repository::get_entity_by_post( $post );

			if ( $entity ) { // attached to a tainacan entity
				
				$log = new Entities\Log();
				
				$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';
				
				if ( $entity instanceof Entities\Collection ) {
					$collection_id = $entity->get_id();
					$log->set_title( sprintf( __( 'New file was attached to Collection "%s"', 'tainacan'), $entity->get_name() ) );
				}
				if ( $entity instanceof Entities\Item ) {
					$log->set_item_id($entity->get_id());
					$log->set_title( sprintf( __( 'New file was attached to Item "%s"', 'tainacan'), $entity->get_title() ) );
				}
				
				$object_type = get_class($entity);
				$object_id = $entity->get_id();
				
				$diff = [];
				
				$log->set_collection_id($collection_id);
				$log->set_object_type($object_type);
				$log->set_object_id($object_id);
				$log->set_action('new-attachment');
				
				$title = __( sprintf('') , 'tainacan');
				
				$prepared = [
					'id'          => $attachment->ID,
					'title'       => $attachment->post_title,
					'description' => $attachment->post_content,
					'mime_type'   => $attachment->post_mime_type,
				];
				
				$log->set_new_value($prepared);
				
				if ( $log->validate() ) {
					$this->insert($log);
				}

			}
		}

	}
	
	/**
	 * Callback to generate log when attachments attached to any Tainacan entity are deleted
	 */
	public function pre_delete_attachment($attachment_id) {
		
		$attachment_post = get_post($attachment_id);
		
		$entity_post = get_post($attachment_post->post_parent);

		if ( $entity_post ) {

			$entity = Repository::get_entity_by_post( $entity_post );

			if ( $entity ) {
				
				$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';
				
				$log = new Entities\Log();
				
				if ( $entity instanceof Entities\Collection ) {
					$collection_id = $entity->get_id();
					$log->set_title( sprintf(__( 'File attached to Collection "%s" was removed', 'tainacan'), $entity->get_name() ) );
				}
				if ( $entity instanceof Entities\Item ) {
					$log->set_item_id($entity->get_id());
					$log->set_title( sprintf( __( 'File attached to Item "%s" was removed' , 'tainacan'), $entity->get_title() ) );
				}
				
				$object_type = get_class($entity);
				$object_id = $entity->get_id();
				
				$preapred = [
					'id'          => $attachment_id,
					'title'       => $attachment_post->post_title,
					'description' => $attachment_post->post_content,
				];
				
				$log->set_collection_id($collection_id);
				$log->set_object_type($object_type);
				$log->set_object_id($object_id);
				$log->set_old_value($preapred);
				$log->set_action('delete-attachment');
				
				$this->current_attachment_delete_log = $log;
				
			}
			
		}
	}
	
	/**
	 * Callback to generate log when attachments attached to any Tainacan entity are deleted
	 */
	public function delete_attachment($attachment_id) {
		if ( isset($this->current_attachment_delete_log) && $this->current_attachment_delete_log instanceof Entities\Log ) {
			$log = $this->current_attachment_delete_log;
			$att = $log->get_old_value();
			if ( is_array($att) && isset($att['id']) && $att['id'] == $attachment_id && $log->validate() ) {
				$this->insert($log);
			}
		}
	}
	
	/**
	 * Compare two repository entities and sets the current_diff property to be used in the insert hook
	 *
	 * @param Entity $unsaved The new entity that is going to be saved
	 *
	 * @return void
	 */
	public function pre_insert_entity( Entities\Entity $unsaved ) {

		if ( ! $unsaved->get_repository()->use_logs ) {
			return;
		}
		
		if ( $unsaved instanceof Entities\Item_Metadata_Entity ) {
			return $this->prepare_item_metadata_diff($unsaved);
		}
		
		// do not log a log
		if ( ( method_exists( $unsaved, 'get_post_type' ) && $unsaved->get_post_type() === 'tainacan-log' ) || $unsaved->get_status() === 'auto-draft' ) {
			return;
		}
		
		$creating = true;
		
		$old = null;
		
		if ( is_numeric( $unsaved->get_id() ) ) {
			if ( $unsaved instanceof Entities\Term ) {
				$old = $unsaved->get_repository()->fetch( $unsaved->get_id(), $unsaved->get_taxonomy() );
			} else {
				$old = $unsaved->get_repository()->fetch( $unsaved->get_id() );
			}
		}
		
		
		if ( $old instanceof Entities\Entity ) {
			
			if ( $old->get_status() !== 'auto-draft' ) {
				$creating = false;
			}
			
		}
		
		$diff = [
			'old' => [],
			'new' => []
		];
		
		$has_diff = false;
		
		if ( $creating ) {
			$diff['new'] = $unsaved->_toArray();
			$has_diff = true;
		} else {
			$map = $unsaved->get_repository()->get_map();
			
			foreach ( $map as $prop => $mapped ) {
				if ( $old->get( $prop ) != $unsaved->get( $prop ) ) {
					
					$diff['old'][$prop] = $old->get( $prop );
					$diff['new'][$prop] = $unsaved->get( $prop );
					$has_diff = true;
					
				}
			}
		}
		
		$diff = apply_filters( 'tainacan-entity-diff', $diff, $unsaved, $old );
		
		$this->current_diff = $has_diff ? $diff : false;
		$this->current_action = $creating ? 'create' : 'update';
		
	}
	
	
	private function prepare_item_metadata_diff( Entities\Entity $unsaved ) {
		
		$diff = [
			'old' => [],
			'new' => []
		];
		
		$old = new Entities\Item_Metadata_Entity($unsaved->get_item(), $unsaved->get_metadatum());
		
		add_filter('tainacan-item-metadata-get-multivalue-separator', [$this, '__temporary_multivalue_separator']);
		
		if ( $old instanceof Entities\Item_Metadata_Entity ) {
			$diff['old'] = \explode($this->__temporary_multivalue_separator(''), $old->get_value_as_string());
		}
		
		$diff['new'] = \explode($this->__temporary_multivalue_separator(''), $unsaved->get_value_as_string());
		
		remove_filter('tainacan-item-metadata-get-multivalue-separator', [$this, '__temporary_multivalue_separator']);
		
		$diff = apply_filters( 'tainacan-entity-diff', $diff, $unsaved, $old );
		
		$this->current_diff = $diff;
		$this->current_action = 'update-metadata-value';
		
	}
	
	public function __temporary_multivalue_separator($sep) {
		return '--xx--';
	}
	
	/**
	 * Callback to generate log when Tainacan entities are edited
	 */
	public function insert_entity( Entities\Entity $entity ) {

		if ( ! $entity->get_repository()->use_logs ) {
			return;
		}
		
		if ( $entity instanceof Entities\Item_Metadata_Entity ) {
			return $this->insert_item_metadata($entity);
		} 
		
		// do not log a log
		if ( ( method_exists( $entity, 'get_post_type' ) && $entity->get_post_type() === 'tainacan-log' ) || $entity->get_status() === 'auto-draft' ) {
			return false;
		}
		
		$log = new Entities\Log();
		$log->set_action($this->current_action);
		
		$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';
		
		$diff = $this->current_diff;
		
		if (false === $diff) {
			return;
		}
		
		if ( $entity instanceof Entities\Collection ) {
			
			$collection_id = $entity->get_id();
			
			if ($this->current_action == 'update') {
				if (isset($diff['new']['metadata_order'])) {
					$log->set_title( sprintf( __( 'Collection "%s" metadata order was updated', 'tainacan'), $entity->get_name() ) );
					$log->set_action('update-metadata-order');
				} elseif (isset($diff['new']['filters_order'])) {
					$log->set_title( sprintf( __( 'Collection "%s" filters order was updated', 'tainacan'), $entity->get_name() ) );
					$log->set_action('update-filters-order');
				} else {
					$log->set_title( sprintf( __( 'Collection "%s" was updated', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ($this->current_action == 'create') {
				$log->set_title( sprintf( __( 'Collection "%s" was created', 'tainacan'), $entity->get_name() ) );
			}
			
		} elseif ( $entity instanceof Entities\Item ) {
			
			$log->set_item_id($entity->get_id());
			
			if ($this->current_action == 'update') {
				if (isset($diff['new']['document'])) {
					$log->set_title( sprintf( __( 'Item "%s" document was updated', 'tainacan'), $entity->get_title() ) );
					$log->set_action('update-document');
				} elseif (isset($diff['new']['_thumbnail_id'])) {
					$log->set_title( sprintf( __( 'Item "%s" thumbnail was updated', 'tainacan'), $entity->get_title() ) );
					$log->set_action('update-thumbnail');
				} else {
					$log->set_title( sprintf( __( 'Item "%s" was updated', 'tainacan'), $entity->get_title() ) );
				}
			} elseif ($this->current_action == 'create') {
				$log->set_title( sprintf( __( 'Item "%1$s" was created with the ID %2$s', 'tainacan'), $entity->get_title(), $entity->get_id() ) );
			}
		} elseif ( $entity instanceof Entities\Filter ) {
			
			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'update') {
					$log->set_title( sprintf( __( 'Filter "%1$s" was updated in repository level', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'create') {
					$log->set_title( sprintf( __( 'Filter "%1$s" was added to the repository', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				if ($this->current_action == 'update') {
					$log->set_title( sprintf( __( 'Filter "%1$s" was updated in Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				} elseif ($this->current_action == 'create') {
					$log->set_title( sprintf( __( 'Filter "%1$s" was added to Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				}
			}
			
		} elseif ( $entity instanceof Entities\Metadatum ) {
			
			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'update') {
					$log->set_title( sprintf( __( 'Metadatum "%s" was updated in repository level', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'create') {
					$log->set_title( sprintf( __( 'Metadatum "%1$s" was added to the repository', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				if ($this->current_action == 'update') {
					$log->set_title( sprintf( __( 'Metadatum "%s" was updated in Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				} elseif ($this->current_action == 'create') {
					$log->set_title( sprintf( __( 'Metadatum "%1$s" was added to Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				}
			}
			
		} elseif ( $entity instanceof Entities\Taxonomy ) {
			
			if ($this->current_action == 'update') {
				$log->set_title( sprintf( __( 'Taxonomy "%s" was updated', 'tainacan'), $entity->get_name() ) );
			} elseif ($this->current_action == 'create') {
				$log->set_title( sprintf( __( 'Taxonomy "%1$s" was created', 'tainacan'), $entity->get_name() ) );
			}
			
		}  elseif ( $entity instanceof Entities\Term ) {
			
			$taxonomy = Taxonomies::get_instance()->fetch_by_db_identifier($entity->get_taxonomy());
			$tax_name = '';
			if ($taxonomy instanceof Entities\Taxonomy) {
				$tax_name = $taxonomy->get_name();
			}
			
			if ($this->current_action == 'update') {
				$log->set_title( sprintf( __( 'Term "%1$s" was updated in "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			} elseif ($this->current_action == 'create') {
				$log->set_title( sprintf( __( 'Term "%1$s" was added to "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			}
			
		}
		
		$object_type = get_class($entity);
		$object_id = $entity->get_id();
		
		$log->set_collection_id($collection_id);
		$log->set_object_type($object_type);
		$log->set_object_id($object_id);
		$log->set_old_value($diff['old']);
		$log->set_new_value($diff['new']);
		
		
		if ( $log->validate() ) {
			$this->insert($log);
		}
				
	}
	
	public function pre_delete_entity( Entities\Entity $entity, $permanent) {
		
		if ( ! $entity->get_repository()->use_logs ) {
			return;
		}
		
		// do not log a log
		if ( ( method_exists( $entity, 'get_post_type' ) && $entity->get_post_type() === 'tainacan-log' ) || $entity->get_status() === 'auto-draft' ) {
			return false;
		}
		
		$this->current_deleting_entity = $entity->_toArray();
		$this->current_action = $permanent ? 'delete' : 'trash';
		
	}
	
	public function delete_entity( Entities\Entity $entity, $permanent) {
		
		if ( ! $entity->get_repository()->use_logs ) {
			return;
		}
		
		// do not log a log
		if ( ( method_exists( $entity, 'get_post_type' ) && $entity->get_post_type() === 'tainacan-log' ) || $entity->get_status() === 'auto-draft' ) {
			return false;
		}
		
		$log = new Entities\Log();
		
		$collection_id = method_exists($entity, 'get_collection_id') ? $entity->get_collection_id() : 'default';
		
		if ( $entity instanceof Entities\Collection ) {
			
			$collection_id = $entity->get_id();
			
			if ($this->current_action == 'delete') {
				$log->set_title( sprintf( __( 'Collection "%s" was permanently deleted', 'tainacan'), $entity->get_name() ) );
			} elseif ($this->current_action == 'trash') {
				$log->set_title( sprintf( __( 'Collection "%s" was moved to trash', 'tainacan'), $entity->get_name() ) );
			}
			
		} elseif ( $entity instanceof Entities\Item ) {
			
			$log->set_item_id($entity->get_id());
			
			if ($this->current_action == 'delete') {
				$log->set_title( sprintf( __( 'Item "%1$s" (ID %2$s) was updated', 'tainacan'), $entity->get_title(), $entity->get_id() ) );
			} elseif ($this->current_action == 'trash') {
				$log->set_title( sprintf( __( 'Item "%1$s" (ID %2$s) was moved to trash', 'tainacan'), $entity->get_title(), $entity->get_id() ) );
			}
		} elseif ( $entity instanceof Entities\Filter ) {
			
			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'delete') {
					$log->set_title( sprintf( __( 'Filter "%s" was permanently deleted from the repository', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'trash') {
					$log->set_title( sprintf( __( 'Repository Filter "%1$s" was moved to trash', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				if ($this->current_action == 'delete') {
					$log->set_title( sprintf( __( 'Filter "%1$s" was permanently deleted from Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				} elseif ($this->current_action == 'trash') {
					$log->set_title( sprintf( __( 'Filter "%1$s" was moved to trash in Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				}
			}
			
		} elseif ( $entity instanceof Entities\Metadatum ) {
			
			if ( 'default' == $collection_id ) {
				if ($this->current_action == 'delete') {
					$log->set_title( sprintf( __( 'Metadatum "%s" was permanently deleted from the repository', 'tainacan'), $entity->get_name() ) );
				} elseif ($this->current_action == 'trash') {
					$log->set_title( sprintf( __( 'Repository Metadatum "%1$s" was moved to trash', 'tainacan'), $entity->get_name() ) );
				}
			} elseif ( is_numeric($collection_id) ) {
				if ($this->current_action == 'delete') {
					$log->set_title( sprintf( __( 'Metadatum "%1$s" was permanently deleted from Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				} elseif ($this->current_action == 'trash') {
					$log->set_title( sprintf( __( 'Metadatum "%1$s" was moved to trash in Collection "%2$s"', 'tainacan'), $entity->get_name(), $entity->get_collection()->get_name() ) );
				}
			}
			
		} elseif ( $entity instanceof Entities\Taxonomy ) {
			
			if ($this->current_action == 'delete') {
				$log->set_title( sprintf( __( 'Taxonomy "%s" was permanently deleted', 'tainacan'), $entity->get_name() ) );
			} elseif ($this->current_action == 'trash') {
				$log->set_title( sprintf( __( 'Taxonomy "%1$s" was moved to trash', 'tainacan'), $entity->get_name() ) );
			}
			
		}  elseif ( $entity instanceof Entities\Term ) {
			
			$taxonomy = Taxonomies::get_instance()->fetch_by_db_identifier($entity->get_taxonomy());
			$tax_name = '';
			if ($taxonomy instanceof Entities\Taxonomy) {
				$tax_name = $taxonomy->get_name();
			}
			
			if ($this->current_action == 'delete') {
				$log->set_title( sprintf( __( 'Term "%1$s" was permanently deleted from "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			} elseif ($this->current_action == 'trash') {
				$log->set_title( sprintf( __( 'Term "%1$s" was moved to trash in "%2$s" taxonomy', 'tainacan'), $entity->get_name(), $tax_name ) );
			}
			
		}
		
		
		$object_type = get_class($entity);
		$object_id = $entity->get_id();
		
		$diff = $this->current_diff;
		
		$log->set_collection_id($collection_id);
		$log->set_object_type($object_type);
		$log->set_object_id($object_id);
		$log->set_action($this->current_action);
		
		if ( $permanent ) {
			$log->set_old_value( $this->current_deleting_entity );
		} else {
			$log->set_old_value( ['status' => $entity->get_status()] );
			$log->set_new_value( ['status' => 'trash']  );
		}
		
		
		if ( $log->validate() ) {
			$this->insert($log);
		}
		
	}
	
	private function insert_item_metadata( Entities\Item_Metadata_Entity $entity ) {
		
		$log = new Entities\Log();
		
		$item_id = $entity->get_item()->get_id();
		$collection_id = $entity->get_item()->get_collection_id();
		$object_type = get_class($entity);
		$object_id = $entity->get_metadatum()->get_id();
		
		$diff = $this->current_diff;
		
		$log->set_collection_id($collection_id);
		$log->set_object_type($object_type);
		$log->set_object_id($object_id);
		$log->set_item_id($item_id);
		$log->set_old_value($diff['old']);
		$log->set_new_value($diff['new']);
		$log->set_action($this->current_action);
		
		$meta_name = $entity->get_metadatum()->get_name();
		$item_title = $entity->get_item()->get_title();
		
		$title = sprintf( __( 'Value for %1$s metadatum was updated in item "%2$s"', 'tainacan' ), $meta_name, $item_title );
		
		$log->set_title($title);
		
		if ( $log->validate() ) {
			$this->insert($log);
		}
		
	}
	
	public function filter_log_title($title) {
		if (defined('TAINACAN_DOING_IMPORT') && true === TAINACAN_DOING_IMPORT) {
			$_title = __('Importer', 'tainacan');
			$title .= " ($_title)";
		}
		return $title;
	}

}