<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Item
 */
class Item extends Entity {
	use \Tainacan\Traits\Entity_Collection_Relation;
	protected
		$terms,
		$diplay_name,
		$full,
		$_thumbnail_id,
		$modification_date,
		$creation_date,
		$author_id,
		$url,
		$id,
		$title,
		$order,
		$parent,
		$decription,
		$document_type,
		$document,
		$document_options,
		$collection_id;

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Items';

	/**
	 * {@inheritDoc}
	 */
	function __construct( $which = 0 ) {
		parent::__construct( $which );

		if ( $which !== 0 ) {
			$this->set_cap();
		}
	}

	public function __toString() {
		return apply_filters("tainacan-item-to-string", $this->get_title(), $this);
	}

	public function _toArray() {
		$array_item = parent::_toArray();

		$array_item['_thumbnail_id']     = $this->get__thumbnail_id();
		$array_item['author_name']       = $this->get_author_name();
		$array_item['url']               = get_permalink( $this->get_id() );
		$array_item['creation_date']     = $this->get_date_i18n( explode( ' ', $array_item['creation_date'] ?? '' )[0] );
		$array_item['modification_date'] = $this->get_date_i18n( explode( ' ', $array_item['modification_date'] ?? '' )[0] );
		$array_item['document_mimetype'] = $this->get_document_mimetype();
		return apply_filters('tainacan-item-to-array', $array_item, $this);
	}

	/**
	 * @param $value
	 */
	function set_terms( $value ) {
		$this->set_mapped_property( 'terms', $value );
	}

	/**
	 * @return mixed|null
	 */
	function get_terms() {
		return $this->get_mapped_property( 'terms' );
	}

	/**
	 * @param null $exclude
	 *
	 * @return array
	 */
	function get_attachments($exclude = null){
		$item_id = $this->get_id();

		if(!$exclude){
			$to_exclude = [get_post_thumbnail_id( $item_id )];
			if ($this->get_document_type() == 'attachment') {
				$to_exclude[] = $this->get_document();
			}
		} else {
			$to_exclude = $exclude;
		}

		$attachments_query = [
			'orderby'			=> 'menu_order',
			'order' 			=> 'ASC',
			'post_type'     	=> 'attachment',
			'posts_per_page' 	=> -1,
			'post_parent'   	=> $item_id,
			'exclude'       	=> $to_exclude,
		];

		$attachments = get_posts( $attachments_query );

		return apply_filters("tainacan-item-get-attachments", $attachments, $exclude, $this);

	}


	/**
	 * @return string
	 */
	function get_author_name() {
		$name = get_the_author_meta( 'display_name', $this->get_author_id() );
		return apply_filters("tainacan-item-get-author-name", $name, $this);
	}

	/**
	 * Gets the thumbnail list of files
	 *
	 * Each size is represented as an array in the format returned by
	 * @see https://developer.wordpress.org/reference/functions/wp_get_attachment_image_src/
	 *
	 * @return array
	 */
	function get_thumbnail() {

		$sizes = get_intermediate_image_sizes();
		$blurhash = $this->get_thumbnail_blurhash();

		array_unshift($sizes, 'full');

		foreach ( $sizes as $size ) {
			$thumbs[$size] = wp_get_attachment_image_src( $this->get__thumbnail_id(), $size );
			if (is_array($thumbs[$size]) && count($thumbs[$size]) == 4) {
				$thumbs[$size][] = $blurhash;
			}
		}
		return apply_filters("tainacan-item-get-thumbnail", $thumbs, $this);
	}

	function get_thumbnail_blurhash() {
		$attachment_metadata = wp_get_attachment_metadata($this->get__thumbnail_id());
		if($attachment_metadata != false && isset($attachment_metadata['image_meta'])) {
			$image_meta = $attachment_metadata['image_meta'];
			if($image_meta != false && isset($image_meta['blurhash'])) {
				return $image_meta['blurhash'];
			}
		}
		return \Tainacan\Media::get_instance()->get_default_image_blurhash();
	}

	/**
	 * @param $id
	 */
	function set__thumbnail_id( $id ) {
		$this->set_mapped_property( '_thumbnail_id', $id );
	}

	/**
	 * @return int|string
	 */
	function get__thumbnail_id() {
		$_thumbnail_id = $this->get_mapped_property("_thumbnail_id");
		if ( isset( $_thumbnail_id ) ) {
			return $_thumbnail_id;
		}

		return get_post_thumbnail_id( $this->get_id() );
	}

	/**
	 * @return mixed|null
	 */
	function get_modification_date() {
		return $this->get_mapped_property( 'modification_date' );
	}

	/**
	 * @return mixed|null
	 */
	function get_creation_date() {
		return $this->get_mapped_property( 'creation_date' );
	}

	/**
	 * @return mixed|null
	 */
	function get_author_id() {
		return $this->get_mapped_property( 'author_id' );
	}

	/**
	 * @return mixed|null
	 */
	function get_url() {
		return $this->get_mapped_property( 'url' );
	}

	/**
	 * Return the item ID
	 *
	 * @return integer
	 */
	function get_id() {
		return $this->get_mapped_property( 'id' );
	}

	/**
	 * Return the item title
	 *
	 * @return string
	 */
	function get_title() {
		return $this->get_mapped_property( 'title' );
	}

	/**
	 * Return the item order type
	 *
	 * @return string
	 */
	function get_order() {
		return $this->get_mapped_property( 'order' );
	}

	/**
	 * Return the parent ID
	 *
	 * @return integer
	 */
	function get_parent() {
		return $this->get_mapped_property( 'parent' );
	}

	/**
	 * Return the item description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property( 'description' );
	}

	/**
	 * Return the item document type
	 *
	 * @return string
	 */
	function get_document_type() {
		return $this->get_mapped_property( 'document_type' );
	}

	/**
	 * Return the item document options
	 *
	 * @return string
	 */
	function get_document_options() {
		return $this->get_mapped_property( 'document_options' );
	}

	/**
	 * Return the document mimetype
	 *
	 * @return string
	 */
	function get_document_mimetype() {
		return $this->get_document_type() == 'attachment' ? get_post_mime_type($this->get_document()) : $this->get_document_type();
	}

	/**
	 * Return the item document
	 *
	 * @return string
	 */
	function get_document() {
		return $this->get_mapped_property( 'document' );
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::get_db_identifier()
	 */
	public function get_db_identifier() {
		return $this->get_mapped_property( 'collection_id' );
	}

	/**
	 * Use especial Item capabilities
	 * {@inheritDoc}
	 *
	 * @see \Tainacan\Entities\Entity::get_capabilities()
	 */
	public function get_capabilities() {
		if( is_null($this->get_collection())) {
			return (object) [];
		}
		return $this->get_collection()->get_items_capabilities();
	}

	/**
	 * Checks if comments are allowed for the current Collection.
	 * @return string "open"|"closed"
	 */
	public function get_comment_status() {
		$comment_status = $this->get_mapped_property('comment_status');
		$comment_status_filtered = apply_filters('comments_open', $comment_status == 'open', $this->get_id()) == true ? 'open' : 'closed';
		return $comment_status_filtered;
	}

	/**
	 * Define the title
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_title( $value ) {
		$this->set_mapped_property( 'title', $value );
	}

	/**
	 * Define the order type
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_order( $value ) {
		$this->set_mapped_property( 'order', $value );
	}

	/**
	 * Define the creation date
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_creation_date( $value ) {
		$this->set_mapped_property( 'creation_date', $value );
	}

	/**
	 * Define the parent ID
	 *
	 * @param [integer] $value
	 *
	 * @return void
	 */
	function set_parent( $value ) {
		$this->set_mapped_property( 'parent', $value );
	}

	/**
	 * Define the document type
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_document_type( $value ) {
		$this->set_mapped_property( 'document_type', $value );
	}

	/**
	 * Define the document options
	 *
	 * @param [object] $value
	 *
	 * @return void
	 */
	function set_document_options( $value ) {
		$this->set_mapped_property( 'document_options', $value );
	}

	/**
	 * Define the document
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_document( $value ) {
		$this->set_mapped_property( 'document', $value );
	}

	/**
	 * Define the description
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_description( $value ) {
		$this->set_mapped_property( 'description', $value );
	}

	/**
	 * Return a List of ItemMetadata objects
	 *
	 * It will return all metadata associeated with the collection this item is part of.
	 *
	 * If the item already has a value for any of the metadata, it will be available.
	 *
	 * @return array Array of ItemMetadata objects
	 */
	function get_metadata($args = []) {
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();

		return $Tainacan_Item_Metadata->fetch( $this, 'OBJECT', $args );

	}

	/**
	 * set meta cap object
	 */
	protected function set_cap() {
		$item_collection = $this->get_collection();
		if ( $item_collection ) {
			$this->cap = $item_collection->get_items_capabilities();
		}
	}

	/**
	 * Sets if comments are allowed for the current Item.
	 *
	 * @param $value string "open"|"closed"
	 */
	public function set_comment_status( $value ) {
		$this->set_mapped_property('comment_status', $value);
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::validate()
	 */
	function validate() {

		if ( ! in_array( $this->get_status(), apply_filters( 'tainacan-status-require-validation', [
			'publish',
			'future',
			'private'
		] ) ) ) {
			return true;
		}

		$is_valid = true;

		if ( parent::validate() === false ) {
			$is_valid = false;
		}

		$arrayItemMetadata = $this->get_metadata(['parent'=>'any', 'include_control_metadata_types' => 'true']);
		if ( $arrayItemMetadata ) {
			foreach ( $arrayItemMetadata as $itemMetadata ) {

				// skip validation for Compound Metadata
				if ( $itemMetadata->get_metadatum()->get_metadata_type() == 'Tainacan\Metadata_Types\Compound' ) {
					continue;
				}

				if ( ! $itemMetadata->validate() ) {
					$errors = $itemMetadata->get_errors();
					$this->add_error( $itemMetadata->get_metadatum()->get_id(), $errors );
					$is_valid = false;
				}
			}

			if($is_valid){
				$this->set_as_valid();
			}

			return $is_valid;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::validate()
	 */
	public function validate_core_metadata() {
		if ( ! in_array( $this->get_status(), apply_filters( 'tainacan-status-require-validation', [
			'publish',
			'future',
			'private'
		] ) ) ) {
			return true;
		}

		return parent::validate();
	}


	public function _toHtml() {
		$return = '';
		$id = $this->get_id();

		if ( $id ) {
			$link = get_permalink( (int) $id );

			if (is_string($link)) {
				$return = "<a data-linkto='item' data-id='$id' href='$link'>";
				$return.= $this->get_title();
				$return .= "</a>";
			}
		}

		return $return;
	}

	/**
	 * Return the item metadata as a HTML string to be used as output.
	 *
	 * Each metadata is a label with the metadatum name and the value.
	 *
	 * If an ID, a slug or a Tainacan\Entities\Metadatum object is passed in the 'metadata' argument, it returns only one metadata, otherwise
	 * it returns all metadata
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments.
	 *
	 * 	   @type mixed		 $metadata					Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata
	 *
	 *     @type array		 $metadata__in				Array of metadata IDs or Slugs to be retrieved. Default none
	 *
	 *     @type array		 $metadata__not_in			Array of metadata IDs (slugs not accepted) to excluded. Default none
	 *
	 *     @type bool		 $exclude_title				Exclude the Core Title Metadata from result. Default false
	 *
	 *     @type bool		 $exclude_description		Exclude the Core Description Metadata from result. Default false
	 *
	 *     @type bool		 $exclude_core				Exclude Core Metadata (title and description) from result. Default false
	 *
	 *     @type bool        $hide_empty                Whether to hide or not metadata the item has no value to
	 *                                                  Default: true
	 *     @type string      $empty_value_message       Message string to display if $hide_empty is false and there is not metadata value.
	 *                                                  Default: ''
	 *     @type bool        $display_slug_as_class     Show metadata slug as a class in the div before the metadata block
	 *                                                  Default: false
	 *     @type string      $before                    String to be added before each metadata block
	 *                                                  Default '<div class="metadata-type-$type">' where $type is the metadata type slug
	 *     @type string      $after		                String to be added after each metadata block
	 *                                                  Default '</div>'
	 *     @type string      $before_title              String to be added before each metadata title
	 *                                                  Default '<h3>'
	 *     @type string      $after_title               String to be added after each metadata title
	 *                                                  Default '</h3>'
	 *     @type string      $before_value              String to be added before each metadata value
	 *                                                  Default '<p>'
	 *     @type string      $after_value               String to be added after each metadata value
	 *                                                  Default '</p>'
	 * }
	 *
	 * @return string        The HTML output
	 * @throws \Exception
	 */
	public function get_metadata_as_html($args = array()) {

		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();

		$return = '';

		$defaults = array(
			'metadata' 				=> null,
			'metadata__in' 			=> null,
			'metadata__not_in' 		=> null,
			'exclude_title' 		=> false,
			'exclude_description' 	=> false,
			'exclude_core' 			=> false,
			'hide_empty' 			=> true,
			'empty_value_message' 	=> '',
			'display_slug_as_class' => false,
			'before' 				=> '<div class="metadata-type-$type" $id>',
			'after' 				=> '</div>',
			'before_title' 			=> '<h3>',
			'after_title' 			=> '</h3>',
			'before_value' 			=> '<p>',
			'after_value' 			=> '</p>',
			'metadatum_index'		=> null
		);
		$args = wp_parse_args($args, $defaults);
		$item_metadata = array();
		
		// If a single metadata is passed, we use it instead of fetching more
		if ( !is_null($args['metadata']) ) {

			$metadatum = $args['metadata'];
			$metadatum_object = null;

			// A metadatum object was passed
			if ( $metadatum instanceof \Tainacan\Entities\Metadatum ) {
				$metadatum_object = $metadatum;

			// A metadatum ID was passed
			} elseif ( is_int($metadatum) ) {
				$metadatum_object = $Tainacan_Metadata->fetch($metadatum);
				
			// A metadatum slug was passed
			} elseif ( is_string($metadatum) ) {
				$query = $Tainacan_Metadata->fetch(['slug' => $metadatum], 'OBJECT');
				if ( is_array($query) && sizeof($query) == 1 && isset($metadatum[0])) {
					$metadatum_object = $metadatum[0];
				}
			}

			// Some checks to see if things are really ok
			if ( !($metadatum_object instanceof \Tainacan\Entities\Metadatum) ) {
				return $return;
			} else {
				// Makes sure the current Metadatum is desired
				if ( is_array($args['metadata__not_in'])
					&& (
						in_array($metadatum_object->get_slug(), $args['metadata__not_in']) ||
						in_array($metadatum_object->get_id(), $args['metadata__not_in'])
					)
				) {
					return $return;
				}
			}

			// Add it to the array which will be looped below
			$item_metadata[] = new \Tainacan\Entities\Item_Metadata_Entity($this, $metadatum_object);

		// If not single metadatum is passed, we query them
		} else {

			// Build query args ready to be passed to the API fetch
			$query_args = [];
			$post__in = [];
			$post__not_in = [];
			$post__name_in = [];
			if (is_array($args['metadata__in'])) {
				$post__in[] = -1; // If metadata__in is an empty array, this forces empty result
				foreach ($args['metadata__in'] as $meta) {
					if (is_numeric($meta)) {
						$post__in[] = $meta;
					} elseif (is_string($meta)) {
						$post__name_in[] = $meta;
					}
				}
			}
			if (is_array($args['metadata__not_in'])) {
				foreach ($args['metadata__not_in'] as $meta) {
					if (is_integer($meta)) {
						$post__not_in[] = $meta;
					}
				}

			}
			
			if (sizeof($post__in) > 0) {
				$query_args['post__in'] = $post__in;
			}
			if (sizeof($post__not_in) > 0) {
				$query_args['post__not_in'] = $post__not_in;
			}
			if (sizeof($post__name_in) > 0) {
				$query_args['post__name_in'] = $post__name_in;
			}
	

			// Get the item metadata objects from the item repository
			$item_metadata = $this->get_metadata($query_args);
		}
		
		// Loop item metadata to print their "values" as html
		$metadatum_index = is_numeric($args['metadatum_index']) && count($item_metadata) === 1 ? $args['metadatum_index'] : 0;
		foreach ( $item_metadata as $item_metadatum ) {

			// Gets the metadata type object to perform some checks
			$metadata_type_object = $item_metadatum->get_metadatum()->get_metadata_type_object();

			// Core metadata may not be desired as they may be displayed differently
			if ( $metadata_type_object->get_core() ) {
				if ( $args['exclude_core'] ) {
					continue;
				} elseif ( $args['exclude_title'] && $metadata_type_object->get_related_mapped_prop() == 'title' ) {
					continue;
				} elseif ( $args['exclude_description'] && $metadata_type_object->get_related_mapped_prop() == 'description' ) {
					continue;
				}
			}

			// Get the metadatum representation in html, with its label and value
			$return .= $this->get_item_metadatum_as_html($item_metadatum, $args, $metadatum_index);
			$metadatum_index++;
		}

		// Returns the html content created by the function
		return wp_kses_tainacan($return);
	}

	/**
	 * Return a single item metadata as a HTML string to be used as output.
	 *
	 * Each metadata is a label with the metadatum name and the value.
	 *
	 * This function expects a $item_metadatum object. For a more generic approach, check the get_metadata_as_html function
	 *
	 * @param object 	$item_metadatum					The Item Metadatum object
	 * @param array|string $args {
	 *     Optional. Array or string of arguments.
	 * 
	 *     @type bool        $hide_empty                Whether to hide or not metadata the item has no value to
	 *                                                  Default: true
	 *     @type string      $empty_value_message       Message string to display if $hide_empty is false and there is not metadata value.
	 *                                                  Default: ''
	 *     @type bool        $display_slug_as_class     Show metadata slug as a class in the div before the metadata block
	 *                                                  Default: false
	 *     @type string      $before                    String to be added before each metadata block
	 *                                                  Default '<div class="metadata-type-$type">' where $type is the metadata type slug
	 *     @type string      $after		                String to be added after each metadata block
	 *                                                  Default '</div>'
	 *     @type string      $before_title              String to be added before each metadata title
	 *                                                  Default '<h3>'
	 *     @type string      $after_title               String to be added after each metadata title
	 *                                                  Default '</h3>'
	 *     @type string      $before_value              String to be added before each metadata value
	 *                                                  Default '<p>'
	 *     @type string      $after_value               String to be added after each metadata value
	 *                                                  Default '</p>'
	 * }
	 * @param int			 $section_index				The Metadatum index, if passed from an array
	 *
	 * @return string        The HTML output
	 */
	public function get_item_metadatum_as_html($item_metadatum, $args = array(), $metadatum_index = null) {

		$return = '';

		$defaults = array(
			'hide_empty' 			=> true,
			'empty_value_message' 	=> '',
			'display_slug_as_class' => false,
			'before' 				=> '<div class="metadata-type-$type" $id>',
			'after' 				=> '</div>',
			'before_title' 			=> '<h3>',
			'after_title' 			=> '</h3>',
			'before_value' 			=> '<p>',
			'after_value' 			=> '</p>'
		);
		$args = wp_parse_args($args, $defaults);

		if ($item_metadatum->has_value() || !$args['hide_empty']) {

			// Gets the metadata type object to use it if we need the slug
			$metadata_type_object = $item_metadatum->get_metadatum()->get_metadata_type_object();

			// Get metadatum wrapper tag. 
			$before = str_replace('$type', $metadata_type_object->get_slug(), $args['before']);

			// Adds class with slug and adds metadatum id
			if ($args['display_slug_as_class']) {
				if ( !strpos($before, 'class="') ) {
					$before = str_replace('>', ' class="metadata-slug-'. $item_metadatum->get_metadatum()->get_slug() . '">', $before);
				} else
					$before = str_replace('class="', 'class="metadata-slug-'. $item_metadatum->get_metadatum()->get_slug() . ' ', $before);
			}
			$before = str_replace('$id', ' id="metadata-id-' . $item_metadatum->get_metadatum()->get_id() . '"', $before);

			// Let theme authors tweak the wrapper opener
			$metadata_type = $item_metadatum->get_metadatum()->get_metadata_type();
			$metadatum_id = $item_metadatum->get_metadatum()->get_id();
			$before = apply_filters( 'tainacan-get-item-metadatum-as-html-before', $before, $item_metadatum );
			$before = apply_filters( "tainacan-get-item-metadatum-as-html-before--type-$metadata_type", $before, $item_metadatum );
			$before = apply_filters( "tainacan-get-item-metadatum-as-html-before--id-$metadatum_id", $before, $item_metadatum );
			if ( is_numeric($metadatum_index) ) {
				$before = apply_filters( 'tainacan-get-item-metadatum-as-html-before--index-' . $metadatum_index, $before, $item_metadatum );
			}

			// Renders the metadatum opener
			$return .= $before;

			// Renders the metadatum name
			$metadatum_title_before = $args['before_title'];
			$metadatum_title_before = apply_filters( 'tainacan-get-item-metadatum-as-html-before-title', $metadatum_title_before, $item_metadatum );
			
			$metadatum_status_info = '';
			if ( $item_metadatum->get_metadatum()->get_status() != 'publish' ) {
				$metadatum_status_object = get_post_status_object( $item_metadatum->get_metadatum()->get_status() );
				$metadatum_status_info = ( $metadatum_status_object && $metadatum_status_object->label ? __( $metadatum_status_object->label, 'tainacan') : $item_metadatum->get_metadatum()->get_status() ) . ': ';
			}
			
			$metadatum_title_after = $args['after_title'];
			$metadatum_title_after = apply_filters( 'tainacan-get-item-metadatum-as-html-after-title', $metadatum_title_after, $item_metadatum );
			$return .= $metadatum_title_before . $metadatum_status_info . $item_metadatum->get_metadatum()->get_name() . $metadatum_title_after;
			
			// Renders the metadatum value
			$metadatum_value_before = $args['before_value'];
			$metadatum_value_before = apply_filters( 'tainacan-get-item-metadatum-as-html-before-value', $metadatum_value_before, $item_metadatum );
			$metadatum_value_after = $args['after_value'];
			$metadatum_value_after = apply_filters( 'tainacan-get-item-metadatum-as-html-after-value', $metadatum_value_after, $item_metadatum );
			$return .= $metadatum_value_before . ( $item_metadatum->has_value() ? $item_metadatum->get_value_as_html() : $args['empty_value_message'] ) . $metadatum_value_after;

			$after = $args['after'];

			// Let theme authors tweak the wrapper closer
			if ( is_numeric($metadatum_index) ) {
				$after = apply_filters( 'tainacan-get-item-metadatum-as-html-after--index-' . $metadatum_index, $after, $item_metadatum );
			}
			$metadata_type = $item_metadatum->get_metadatum()->get_metadata_type();
			$metadatum_id = $item_metadatum->get_metadatum()->get_id();
			$after = apply_filters( "tainacan-get-item-metadatum-as-html-after--id-$metadatum_id", $after, $item_metadatum );
			$after = apply_filters( "tainacan-get-item-metadatum-as-html-after--type-$metadata_type", $after, $item_metadatum );
			$after = apply_filters( 'tainacan-get-item-metadatum-as-html-after', $after, $item_metadatum );
			
			// Closes the wrapper
			$return .= $after;
		}

		// Returns the html content created by the function
		return $return;
	}

	/**
	 * Gets the document as a html. May be a text, link, iframe, image, audio...
	 */
	public function get_document_as_html($img_size = 'large') {

		$type = $this->get_document_type();
		$document_options = $this->get_document_options();

		$output = '';
		
		if ( $type == 'url' ) {
			global $wp_embed;
			$_embed = $wp_embed->autoembed($this->get_document());
			$url = $this->get_document();

			if ( esc_url($_embed) == esc_url($url) ) {

				if ( $document_options && isset($document_options['forced_iframe']) && $document_options['forced_iframe'] === true ) {
			
					// URL points to an image file
					if (isset($document_options['is_image']) && $document_options['is_image'] === true) {
						$_embed = sprintf('<a href="%s" target="blank"><img src="%s" /></a>', $url, $url);

					// URL points to a content that is not an image
					} else {
						$tainacan_embed = \Tainacan\Embed::get_instance();
						$iframe_width = isset($document_options['forced_iframe_width']) ? $document_options['forced_iframe_width'] : '600';
						$iframe_height = isset($document_options['forced_iframe_height']) ? $document_options['forced_iframe_height'] : '450';

						$_embed = $tainacan_embed->add_responsive_wrapper( sprintf('<iframe src="%s" style="border: 0" width="%s" height="%s"></iframe>', $url, $iframe_width, $iframe_height) );
					}
				} else {
					$_embed = sprintf('<a href="%s" target="blank">%s</a>', $url, $url);
				}
			} else {
				$tainacan_embed = \Tainacan\Embed::get_instance();
				$_embed = $tainacan_embed->add_responsive_wrapper($_embed);
			}
			$output .= $_embed;
		} elseif ( $type == 'text' ) {
			$output .= '<article>' . $this->get_document() . '</article>';
		} elseif ( $type == 'attachment' ) {

			if ( wp_attachment_is_image($this->get_document()) ) {

				$img_full = wp_get_attachment_url($this->get_document());			
				$img = wp_get_attachment_image( $this->get_document(), $img_size );

				$output .= sprintf("<a href='%s' target='blank'>%s</a>", $img_full, $img);

			} else {

				global $wp_embed;

				$url = wp_get_attachment_url($this->get_document());

				$embed = $wp_embed->autoembed($url);

				if ( esc_url($embed) == esc_url($url) ) {
					$output .= sprintf("<a href='%s' target='blank'>%s</a>", $url, $url);
				} else {
					$tainacan_embed = \Tainacan\Embed::get_instance();
					$embed = $tainacan_embed->add_responsive_wrapper($embed);
					$output .= $embed;
				}
			}

		}

		return apply_filters("tainacan-item-get-document-as-html", wp_kses_tainacan($output), $img_size, $this);
	}

	/**
	 * Gets the attachment as a html. May be an iframe, image, audio...
	 */
	public function get_attachment_as_html($attachment, $img_size = 'large') {

		$output = '';

		if ( wp_attachment_is_image($attachment) ) {

			$img = wp_get_attachment_image($attachment, $img_size);
			$img_full = wp_get_attachment_url($attachment);

			$output .= sprintf("<a href='%s' target='blank'>%s</a>", $img_full, $img);
			
		} else {

			global $wp_embed;

			$url = wp_get_attachment_url($attachment);

			$embed = $wp_embed->autoembed($url);

			if ( esc_url($embed) == esc_url($url) ) {
				$output .= sprintf("<a href='%s' target='blank'>%s</a>", $url, $url);
			} else {
				$tainacan_embed = \Tainacan\Embed::get_instance();
				$embed = $tainacan_embed->add_responsive_wrapper($embed);
				$output .= $embed;
			}
		}
		return apply_filters("tainacan-item-get-attachment-as-html", wp_kses_tainacan($output), $img_size, $this);

	}


	/**
	* Gets the url to the edit page for this item
	*/
	public function get_edit_url() {
		$collection_id = $this->get_collection_id();
		$id = $this->get_id();
		return admin_url("?page=tainacan_admin#/collections/$collection_id/items/$id/edit");
	}

	/**
	* Gets the Document url of this item
	*/
	public function get_document_download_url() {
		$type = $this->get_document_type();

		$link = null;

		if ( $type == 'url' ) {
			$link = $this->get_document();
		} elseif ( $type == 'text' ) {
			$link = $this->get_document();
		} elseif ( $type == 'attachment' ) {
			if ( wp_attachment_is_image($this->get_document()) ) {
				$link = wp_get_attachment_url($this->get_document());
			} else {
				$link = wp_get_attachment_url($this->get_document());
			}
		}

		return esc_url($link);
	}

	/**
	 * Return related items withs the item
	 *
	 * @return array
	 */
	public function get_related_items($args = []) {
		$Tainacan_Items = \Tainacan\Repositories\Items::get_instance();
		$related_items = $Tainacan_Items->fetch_related_items($this, $args);
		return $related_items;
	}

	/**
	 * Return the item metadata sections as a HTML string to be used as output.
	 *
	 * Each metadata section is a label with the list of its metadata name and value.
	 *
	 * If an ID, a slug or a Tainacan\Entities\Metadata_Section object is passed in the 'metadata_section' argument, it returns only one metadata section, otherwise
	 * it returns all metadata section
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments.
	 *
	 * 	   @type mixed		 $metadata_section				Metadatum object, ID or slug to retrieve only one metadatum. empty returns all metadata_sections
	 *
	 *     @type array		 $metadata_sections__in			Array of metadata_sections IDs or Slugs to be retrieved. Default none
	 *
	 *     @type array		 $metadata_sections__not_in		Array of metadata_sections IDs (slugs not accepted) to excluded. Default none
	 * 
	 *     @type bool		 $hide_name						Do not display the Metadata Section name. Default false
	 *
	 *     @type bool		 $hide_description				Do not display the Metadata Section description. Default true
	 *
	 *     @type bool        $hide_empty                	Whether to hide or not metadata sections if there are no metadata list or they are empty
	 *                                                  	Default: true
	 *     @type string      $empty_metadata_list_message 	Message string to display if $hide_empty is false and there is not metadata section metadata list.
	 *                                                  	Default: ''
	 *     @type string      $before                    	String to be added before each metadata section block
	 *                                                  	Default '<section class="metadata-section-slug-$slug" id="$id">'
	 *     @type string      $after		                	String to be added after each metadata section block
	 *                                                  	Default '</section>'
	 *     @type string      $before_name              		String to be added before each metadata section name
	 *                                                  	Default '<h2 id="metadata-section-$slug">'
	 *     @type string      $after_name               		String to be added after each metadata section name
	 *                                                  	Default '</h2>'
	 * 	   @type string      $before_description            String to be added before each metadata section description
	 *                                                  	Default '<p>'
	 *     @type string      $after_description             String to be added after each metadata section description
	 *                                                  	Default '</p>'
	 *     @type string      $before_metadata_list      	String to be added before each metadata section inner metadata list
	 *                                                  	Default '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">'
	 *     @type string      $after_metadata_list       	String to be added after each metadata section inner metadata list
	 *                                                  	Default '</div>'
	 *	   @type array		$metadata_list_args				Arguments to be passed to the get_metadata_as_html function when calling section metadata
	 * }
	 *
	 * @return string        The HTML output
	 */
	public function get_metadata_sections_as_html($args = array()) {

		$Tainacan_Metadata_Sections = \Tainacan\Repositories\Metadata_Sections::get_instance();

		$return = '';

		$defaults = array(
			'metadata_section' 				=> null,
			'metadata_sections__in' 		=> null,
			'metadata_sections__not_in' 	=> null,
			'hide_name' 					=> false,
			'hide_description' 				=> true,
			'hide_empty' 					=> true,
			'empty_metadata_list_message' 	=> '',
			'before' 						=> '<section class="metadata-section-slug-$slug" id="metadata-section-$id">',
			'after' 						=> '</section>',
			'before_name' 					=> '<h2 id="metadata-section-$slug">',
			'after_name' 					=> '</h2>',
			'before_metadata_list' 			=> '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">',
			'after_metadata_list' 			=> '</div>',
			'metadata_list_args' 			=> []
		);
		$args = wp_parse_args($args, $defaults);
		$metadata_sections = array();

		// If a single metadata section is passed, we use it instead of fetching more
		if ( !is_null($args['metadata_section']) ) {
			
			$metadata_section = $args['metadata_section'];
			$metadata_section_object = null;
			
			// A metadata section object was passed
			if ( $metadata_section instanceof \Tainacan\Entities\Metadata_Section ) {
				$metadata_section_object = $metadata_section;

			// A metadata section ID was passed
			} elseif ( is_numeric($metadata_section) ) {
				$metadata_section_object = $Tainacan_Metadata_Sections->fetch($metadata_section);

			// The default metadata section was passed
			} elseif ( $metadata_section == \Tainacan\Entities\Metadata_Section::$default_section_slug ) {
				$metadata_section_object = $Tainacan_Metadata_Sections->get_default_section($this->get_collection_id());
	
			// A metadata section slug was passed
			} elseif ( is_string($metadata_section) ) {
				$query = $Tainacan_Metadata_Sections->fetch(['slug' => $metadata_section], 'OBJECT');
				if ( is_array($query) && sizeof($query) == 1 && isset($metadata_section[0]) ) {
					$metadata_section_object = $metadata_section[0];
				}
			}

			// Some checks to see if things are really ok
			if ( !($metadata_section_object instanceof \Tainacan\Entities\Metadata_Section) ) {
				return $return;
			} else {
				// Makes sure the current Metadata Section is desired
				if ( is_array($args['metadata_sections__not_in'])
					&& (
						in_array($metadata_section_object->get_slug(), $args['metadata_sections__not_in']) ||
						in_array($metadata_section_object->get_id(), $args['metadata_sections__not_in'])
					)
				) {
					return $return;
				}
			}

			// Add it to the array which will be looped below
			$metadata_sections[] = $metadata_section_object;

		// If not single metadata section is passed, we query them
		} else {

			// Build query args ready to be passed to the API fetch
			$query_args = [];
			$post__in = [];
			$post__not_in = [];
			$post__name_in = [];
			if (is_array($args['metadata_sections__in'])) {
				$post__in[] = -1; // If metadata_sections__in is an empty array, this forces empty result
				foreach ($args['metadata_sections__in'] as $metadata_section) {
					if (is_numeric($metadata_section) || $metadata_section === \Tainacan\Entities\Metadata_Section::$default_section_slug) {
						$post__in[] = $metadata_section;
					} elseif (is_string($metadata_section)) {
						$post__name_in[] = $metadata_section;
					}
				}
			}
			if (is_array($args['metadata_sections__not_in'])) {
				foreach ($args['metadata_sections__not_in'] as $metadata_section) {
					if (is_integer($metadata_section) || $metadata_section === \Tainacan\Entities\Metadata_Section::$default_section_slug) {
						$post__not_in[] = $metadata_section;
					}
				}
			}

			if (sizeof($post__in) > 0) {
				$query_args['post__in'] = $post__in;
			}
			if (sizeof($post__not_in) > 0) {
				$query_args['post__not_in'] = $post__not_in;
			}
			if (sizeof($post__name_in) > 0) {
				$query_args['post__name_in'] = $post__name_in;
			}
			
			// Get metadata section objects from the metadata sections repository
			$metadata_sections = $Tainacan_Metadata_Sections->fetch_by_collection($this->get_collection(), $query_args);
		}

		// Loop metadata sections to print their "values" as html
		$section_index = 0;
		foreach ( $metadata_sections as $metadata_section_object ) {
			$return .= $this->get_metadata_section_as_html($metadata_section_object, $args, $section_index);
			$section_index++;
		}

		// Returns the html content created by the function
		return $return;
	}

	/**
	 * Return a single item metadata section as a HTML string to be used as output.
	 *
	 * A metadata section is a label with the list of its metadata name and value.
	 *
	 * This function expects a $metadata_section object. For a more generic approach, check the get_metadata_sections_as_html function
	 *
	 * @param \Tainacan\Entities\Metadata_Section $metadata_section					The Metadata Section object
	 * @param array|string $args {
	 *     Optional. Array or string of arguments.
	 * 
	 *     @type bool		 $hide_name						Do not display the Metadata Section name. Default false
	 *
	 *     @type bool		 $hide_description				Do not display the Metadata Section description. Default true
	 *
	 *     @type bool        $hide_empty                	Whether to hide or not metadata sections if there are no metadata list or they are empty
	 *                                                  	Default: true
	 *     @type string      $empty_metadata_list_message 	Message string to display if $hide_empty is false and there is not metadata section metadata list.
	 *                                                  	Default: ''
	 *     @type string      $before                    	String to be added before each metadata section block
	 *                                                  	Default '<section class="metadata-section-slug-$slug" id="$id">'
	 *     @type string      $after		                	String to be added after each metadata section block
	 *                                                  	Default '</section>'
	 *     @type string      $before_name              		String to be added before each metadata section name
	 *                                                  	Default '<h2 id="metadata-section-$slug">'
	 *     @type string      $after_name               		String to be added after each metadata section name
	 *                                                  	Default '</h2>'
	 * 	   @type string      $before_description            String to be added before each metadata section description
	 *                                                  	Default '<p>'
	 *     @type string      $after_description             String to be added after each metadata section description
	 *                                                  	Default '</p>'
	 *     @type string      $before_metadata_list      	String to be added before each metadata section inner metadata list
	 *                                                  	Default '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">'
	 *     @type string      $after_metadata_list       	String to be added after each metadata section inner metadata list
	 *                                                  	Default '</div>'
	 * 
	 *	   @type array		$metadata_list_args				Arguments to be passed to the get_metadata_as_html function when calling section metadata
	 * }
	 * @param int			$section_index					The Metadata Section index, if passed from an array
	 *
	 * @return string        The HTML output
	 */
	public function get_metadata_section_as_html($metadata_section, $args = array(), $section_index = null) {

		$return = '';

		if ( $metadata_section->is_conditional_section() ) {
			$rules = $metadata_section->get_conditional_section_rules();

			if ( !empty($rules) ) {
				foreach ( $rules as $meta_id => $meta_values_conditional ) {
					$meta_values = [];
					$metadatum = new \Tainacan\Entities\Metadatum($meta_id);
					$metadatum_type = $metadatum->get_metadata_type_object();

					if ( $metadatum_type->get_primitive_type() == 'term' ) {
						$item_metadata = new \Tainacan\Entities\Item_Metadata_Entity($this, $metadatum);
						
						if ( $metadatum->is_multiple() )  {
							$term_values = $item_metadata->get_value();
							$meta_values = array_map(function($term) {
								return $term->get_id();
							}, $term_values);
						} else {
							$term_values = $item_metadata->get_value();
							$meta_values = $term_values == false ? [] : [ $term_values->get_id() ];
						}

					} else {
						$item_id = $this->get_id();
						$meta_values = get_post_meta( $item_id, $meta_id );
					}

					if ( !array_intersect($meta_values, $meta_values_conditional) )
						return $return;
				}
			}
		}

		$defaults = array(
			'hide_name' 					=> false,
			'hide_description' 				=> true,
			'hide_empty' 					=> true,
			'empty_metadata_list_message' 	=> '',
			'before' 						=> '<section class="metadata-section-slug-$slug" id="metadata-section-$id">',
			'after' 						=> '</section>',
			'before_name' 					=> '<h2 id="metadata-section-$slug">',
			'after_name' 					=> '</h2>',
			'before_metadata_list' 			=> '<div class="metadata-section__metadata-list" aria-labelledby="metadata-section-$slug">',
			'after_metadata_list' 			=> '</div>',
			'metadata_list_args' 			=> []
		);
		$args = wp_parse_args($args, $defaults);

		// Gets the metadata section inner metadata list
		$metadata_section_metadata_list = $metadata_section->get_metadata_object_list();
		$has_metadata_list = (is_array($metadata_section_metadata_list) && count($metadata_section_metadata_list) > 0 );
		
		if ( $has_metadata_list || !$args['hide_empty'] ) {

			// Slug and ID are used in numerous situations
			$section_slug = $metadata_section->get_slug();
			$section_id = $metadata_section->get_id();

			// Get section wrapper tag
			$before = $args['before'];
			$before = str_replace('$id', $section_id, $before);
			$before = str_replace('$slug', $section_slug, $before);

			// Let theme authors tweak the wrapper opener
			$before = apply_filters( 'tainacan-get-metadata-section-as-html-before', $before, $metadata_section );
			$before = apply_filters( 'tainacan-get-metadata-section-as-html-before--id-' . $section_id, $before, $metadata_section );
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$before = apply_filters( 'tainacan-get-metadata-section-as-html-before--index-' . $section_index, $before, $metadata_section );	
			}

			// Renders the wrapper opener
			$return .= $before;

			// Adds section label (name)
			if ( !$args['hide_name'] ) {

				// Get section name wrapper
				$before_name = $args['before_name'];
				$before_name = str_replace('$id', $section_id, $before_name);
				$before_name = str_replace('$slug', $section_slug, $before_name);

				// Let theme authors tweak the name wrapper
				$before_name = apply_filters( 'tainacan-get-metadata-section-as-html-before-name', $before_name, $metadata_section );
				$before_name = apply_filters( 'tainacan-get-metadata-section-as-html-before-name--id-' . $section_id, $before_name, $metadata_section );
				if ( is_numeric($section_index) && $section_index >= 0 ) {
					$before_name = apply_filters( 'tainacan-get-metadata-section-as-html-before-name--index-' . $section_index, $before_name, $metadata_section );	
				}

				// Get section name closer
				$after_name = $args['after_name'];

				// Let theme authors tweak the name wrapper
				$after_name = apply_filters( 'tainacan-get-metadata-section-as-html-after-name', $after_name, $metadata_section );
				$after_name = apply_filters( 'tainacan-get-metadata-section-as-html-after-name--id-' . $section_id, $after_name, $metadata_section );
				if ( is_numeric($section_index) && $section_index >= 0 ) {
					$after_name = apply_filters( 'tainacan-get-metadata-section-as-html-after-name--index-' . $section_index, $after_name, $metadata_section );	
				}

				// Renders the metadata section name
				$return .= $before_name . $metadata_section->get_name() . $after_name;
			}

			// Adds section description
			if ( !$args['hide_description'] ) {
				$return .= $args['before_description'] . $metadata_section->get_description() . $args['after_description'];
			}

			// Gets the section metadata list wrapper
			$before_metadata_list = $args['before_metadata_list'];
			$before_metadata_list = str_replace('$id', $section_id, $before_metadata_list);
			$before_metadata_list = str_replace('$slug', $section_slug, $before_metadata_list);

			// Let theme authors tweak the metadata list wrapper
			$before_description = isset($args['before_description']) ? $args['before_description'] : '';
			$before_description = apply_filters( 'tainacan-get-metadata-section-as-html-before-metadata-list', $before_description, $metadata_section );
			$before_description = apply_filters( 'tainacan-get-metadata-section-as-html-before-metadata-list--id-' . $section_id, $before_description, $metadata_section );
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$before_description = apply_filters( 'tainacan-get-metadata-section-as-html-before-metadata-list--index-' . $section_index, $before_description, $metadata_section );	
			}

			// Renders the section metadata list wrapper
			$return .= $before_metadata_list . $before_description;

			// Renders the section metadata list, using Items' get_metadata_as_html()
			// Note that this is already escaped in the calling function
			if ($has_metadata_list) {
				$has_some_metadata_value = false;
				$metadatum_index = 0;
				foreach( $metadata_section_metadata_list as $metadata_object) {
					$the_metadata_list = $this->get_metadata_as_html( wp_parse_args($args['metadata_list_args'], [ 'metadata' => $metadata_object, 'metadatum_index' => $metadatum_index ]) );
					if (!$has_some_metadata_value && !empty($the_metadata_list))
						$has_some_metadata_value = true;

					$return .= $the_metadata_list;
					$metadatum_index++;
				}

				// If no metadata value was found, this section may not be necessary
				if (!$has_some_metadata_value && $args['hide_empty'])
					return '';

			} else {
				$return .= $args['empty_metadata_list_message'];
			}
			// Gets the wrapper closer
			$after_metadata_list = $args['after_metadata_list'];

			// Let theme authors tweak the metadata list closer
			$after_description = isset($args['after_description']) ? $args['after_description'] : '';
			$after_description = apply_filters( 'tainacan-get-metadata-section-as-html-after-metadata-list', $after_description, $metadata_section );
			$after_description = apply_filters( 'tainacan-get-metadata-section-as-html-after-metadata-list--id-' . $section_id, $after_description, $metadata_section );
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$after_description = apply_filters( 'tainacan-get-metadata-section-as-html-after-metadata-list--index-' . $section_index, $after_description, $metadata_section );	
			}
			
			// Renders the section metadata list wrapper
			$return .= $after_description . $after_metadata_list;

			// Gets the wrapper closer
			$after = $args['after'];

			// Let theme authors tweak the wrapper closer
			if ( is_numeric($section_index) && $section_index >= 0 ) {
				$after = apply_filters( 'tainacan-get-metadata-section-as-html-after--index-' . $section_index, $after, $metadata_section );	
			}
			$after = apply_filters( 'tainacan-get-metadata-section-as-html-after--id-' . $section_id, $after, $metadata_section );
			$after = apply_filters( 'tainacan-get-metadata-section-as-html-after', $after, $metadata_section );
			
			// Closes the wrapper
			$return .= $after;
		}

		// Returns the html content created by the function
		return $return;
	}
}
