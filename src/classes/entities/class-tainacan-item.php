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

		$array_item['thumbnail']         = $this->get_thumbnail();
		$array_item['_thumbnail_id']     = $this->get__thumbnail_id();
		$array_item['author_name']       = $this->get_author_name();
		$array_item['url']               = get_permalink( $this->get_id() );
		$array_item['creation_date']     = $this->get_date_i18n( explode( ' ', $array_item['creation_date'] )[0] );
		$array_item['modification_date'] = $this->get_date_i18n( explode( ' ', $array_item['modification_date'] )[0] );

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
			$to_exclude = get_post_thumbnail_id( $item_id );
		} else {
			$to_exclude = $exclude;
		}

		$attachments_query = [
			'post_type'     => 'attachment',
			'post_per_page' => -1,
			'post_parent'   => $item_id,
			'exclude'       => $to_exclude,
		];

		$attachments = get_posts( $attachments_query );

		$attachments_prepared = [];
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$prepared = [
					'id'          => $attachment->ID,
					'title'       => $attachment->post_title,
					'description' => $attachment->post_content,
					'mime_type'   => $attachment->post_mime_type,
					'url'         => $attachment->guid,
				];

				array_push( $attachments_prepared, $prepared );
			}
		}

		return apply_filters("tainacan-item-get-attachments", $attachments_prepared, $exclude, $this);
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

        array_unshift($sizes, 'full');
        
        foreach ( $sizes as $size ) {
            $thumbs[$size] = wp_get_attachment_image_src( $this->get__thumbnail_id(), $size );
        }

        return apply_filters("tainacan-item-get-thumbnail", $thumbs, $this);
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
		return $this->get_collection()->get_items_capabilities();
	}
	
	/**
	 * Checks if comments are allowed for the current Collection.
	 * @return string "open"|"closed"
	 */
	public function get_comment_status() {
	    return apply_filters('comments_open', $this->get_mapped_property('comment_status'), $this->get_id());
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

		$arrayItemMetadata = $this->get_metadata();
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
	 *     @type bool        $hide_empty                Wether to hide or not metadata the item has no value to
	 *                                                  Default: true
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
		
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
		
		$return = '';
		
		$defaults = array(
			'metadata' => null,
			'metadata__in' => null,
			'metadata__not_in' => null,
			'exclude_title' => false,
			'exclude_description' => false,
			'exclude_core' => false,
			'hide_empty' => true,
			'before' => '<div class="metadata-type-$type">',
			'after' => '</div>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
			'before_value' => '<p>',
			'after_value' => '</p>',
		);
		$args = wp_parse_args($args, $defaults);

		if (!is_null($args['metadata'])) {
			
			$metadatum_object = null;
			
			if ( $metadatum instanceof \Tainacan\Entities\Metadatum ) {
				$metadatum_object = $metadatum;
			} elseif ( is_int($metadatum) ) {
				$metadatum_object = $Tainacan_Metadata->fetch($metadatum);
			} elseif ( is_string($metadatum) ) {
				$query = $Tainacan_Metadata->fetch(['slug' => $metadatum], 'OBJECT');
				if ( is_array($query) && sizeof($query) == 1 && isset($metadatum[0])) {
					$metadatum_object = $metadatum[0];
				}
			}
			
			if ( $metadatum_object instanceof \Tainacan\Entities\Metadatum ) {

				if ( is_array($args['metadata__not_in']) 
					&& (
						in_array($metadatum_object->get_slug(), $args['metadata__not_in']) ||
						in_array($metadatum_object->get_id(), $args['metadata__not_in'])
					)
				) {
					return $return;
				}
				
				$mto = $metadatum_object->get_metadata_type_object();
				$before = str_replace('$type', $mto->get_slug(), $args['before']);
				$return .= $before;
				
				$item_meta = new \Tainacan\Entities\Item_Metadata_Entity($this, $metadatum_object);
				if ($item_meta->has_value() || !$args['hide_empty']) {
					$return .= $args['before_title'] . $metadatum_object->get_name() . $args['after_title'];
					$return .= $args['before_value'] . $item_meta->get_value_as_html() . $args['after_value'];
				}
				
				$return .= $args['after'];
				
			}
			
			return $return;
			
		}



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

		
		$metadata = $this->get_metadata($query_args);
		
		foreach ( $metadata as $item_meta ) {

			$fto = $item_meta->get_metadatum()->get_metadata_type_object();
			
			$before = str_replace('$type', $fto->get_slug(), $args['before']);
			$return .= $before;

			if ( $fto->get_core() ) {
				if ( $args['exclude_core'] ) {
					continue;
				} elseif ( $args['exclude_title'] && $fto->get_related_mapped_prop() == 'title' ) {
					continue;
				} elseif ( $args['exclude_description'] && $fto->get_related_mapped_prop() == 'description' ) {
					continue;
				}
			}

			if ($item_meta->has_value() || !$args['hide_empty']) {
				$return .= $args['before_title'] . $item_meta->get_metadatum()->get_name() . $args['after_title'];
				$return .= $args['before_value'] . $item_meta->get_value_as_html() . $args['after_value'];
			}
			
			$return .= $args['after'];
			
		}
		
		return $return;
		
	}
	
	public function get_document_as_html($img_size = 'large') {
		
		$type = $this->get_document_type();
		
		$output = '';
		
		if ( $type == 'url' ) {
			global $wp_embed;
			$_embed = $wp_embed->autoembed($this->get_document());
			if ( $_embed == $this->get_document() ) {
				$_embed = sprintf('<a href="%s" target="blank">%s</a>', $this->get_document(), $this->get_document());
			}
			$output .= $_embed;
		} elseif ( $type == 'text' ) {
			$output .= $this->get_document();
		} elseif ( $type == 'attachment' ) {
			
			if ( wp_attachment_is_image($this->get_document()) ) {
				
				$img = wp_get_attachment_image($this->get_document(), $img_size);
				$img_full = wp_get_attachment_url($this->get_document());
				
				$image_attributes = wp_get_attachment_image_src( $this->get_document(), $img_size );
                $img = "<img style='max-width: 100%;' src='" . $image_attributes[0] . "' />";

				$output .= sprintf("<a href='%s' target='blank'>%s</a>", $img_full, $img);
				
			} else {
				
				global $wp_embed;
				
				$url = wp_get_attachment_url($this->get_document());
				
				$embed = $wp_embed->autoembed($url);
				
				if ( $embed == $url ) {
					$output .= sprintf("<a href='%s' target='blank'>%s</a>", $url, $url);
				} else {
					$output .= $embed;
				}
				
				
			}
			
		}
		
		return apply_filters("tainacan-item-get-document-as-html", $output, $img_size, $this);
		
	}
	
	/**
	* Gets the url to the edit page for this item 
	*/
	public function get_edit_url() {
		$collection_id = $this->get_collection_id();
		$id = $this->get_id();
		return admin_url("?page=tainacan_admin#/collections/$collection_id/items/$id/edit");
	}
	
}