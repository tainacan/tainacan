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
		return 'Hello, my name is ' . $this->get_title();
	}

	public function _toArray() {
		$array_item = parent::_toArray();

		$array_item['thumbnail']         = $this->get_thumbnail();
		$array_item['_thumbnail_id']     = $this->get__thumbnail_id();
		$array_item['author_name']       = $this->get_author_name();
		$array_item['url']               = get_permalink( $this->get_id() );
		$array_item['creation_date']     = $this->get_date_i18n( explode( ' ', $array_item['creation_date'] )[0] );
		$array_item['modification_date'] = $this->get_date_i18n( explode( ' ', $array_item['modification_date'] )[0] );

		return $array_item;
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

		return $attachments_prepared;
	}


	/**
	 * @return string
	 */
	function get_author_name() {
		return get_the_author_meta( 'display_name', $this->get_author_id() );
	}

	/**
	 * @return array
	 */
	function get_thumbnail() {
		return array(
			'thumb'        => get_the_post_thumbnail_url( $this->get_id(), 'thumbnail' ),
			'full'         => get_the_post_thumbnail_url( $this->get_id(), 'full' ),
			'medium'       => get_the_post_thumbnail_url( $this->get_id(), 'medium' ),
			'medium_large' => get_the_post_thumbnail_url( $this->get_id(), 'medium_large' ),
			'large'        => get_the_post_thumbnail_url( $this->get_id(), 'large' ),
		);
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
	 * It will return all fields associeated with the collection this item is part of.
	 *
	 * If the item already has a value for any of the fields, it will be available.
	 *
	 * @return array Array of ItemMetadata objects
	 */
	function get_fields($args = []) {
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

		$arrayItemMetadata = $this->get_fields();
		if ( $arrayItemMetadata ) {
			foreach ( $arrayItemMetadata as $itemMetadata ) {
				
				// skip validation for Compound Fields
				if ( $itemMetadata->get_field()->get_field_type() == 'Tainacan\Field_Types\Compound' ) {
					continue;
				}

				if ( ! $itemMetadata->validate() ) {
					$errors = $itemMetadata->get_errors();
					$this->add_error( $itemMetadata->get_field()->get_id(), $errors );
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
	public function validate_core_fields() {
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
	 * Each metadata is a label with the field name and the value.
	 *
	 * If an ID, a slug or a Tainacan\Entities\Field object is passed, it returns only one metadata, otherwise
	 * it returns all metadata
	 *
	 * @param  int|string|Tainacan\Entities\Field $field Field object, ID or slug to retrieve only one field. empty returns all fields
	 * @param bool $hide_empty Wether to hide or not fields the item has no value to
	 *
	 * @return string        The HTML output
	 * @throws \Exception
	 */
	public function get_metadata_as_html($field = null, $hide_empty = true) {
		
		$Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
		$Tainacan_Fields = \Tainacan\Repositories\Fields::get_instance();
		
		$return = '';
		
		if (!is_null($field)) {
			
			$field_object = null;
			
			if ( $field instanceof \Tainacan\Entities\Field ) {
				$field_object = $field;
			} elseif ( is_int($field) ) {
				$field_object = $Tainacan_Fields->fetch($field);
			} elseif ( is_string($field) ) {
				$query = $Tainacan_Fields->fetch(['slug' => $field], 'OBJECT');
				if ( is_array($query) && sizeof($query) == 1 && isset($field[0])) {
					$field_object = $field[0];
				}
			}
			
			if ( $field_object instanceof \Tainacan\Entities\Field ) {
				$item_meta = new \Tainacan\Entities\Item_Metadata_Entity($this, $field_object);
				if ($item_meta->has_value() || !$hide_empty) {
					$return .= '<h3>' . $field_object->get_name() . '</h3>';
					$return .= $item_meta->get_value_as_html();
				}
				
			}
			
			return $return;
			
		}
		
		$fields = $this->get_fields();
		
		foreach ( $fields as $item_meta ) {
			if ($item_meta->has_value() || !$hide_empty) {
				$return .= '<h3>' . $item_meta->get_field()->get_name() . '</h3>';
				$return .= $item_meta->get_value_as_html();
			}
		}
		
		return $return;
		
	}
	
	public function get_document_html($img_size = 'large') {
		
		$type = $this->get_document_type();
		
		$output = '';
		
		if ( $type == 'url' ) {
			global $wp_embed;
			$output .= $wp_embed->autoembed($this->get_document());
		} elseif ( $type == 'text' ) {
			$output .= $this->get_document();
		} elseif ( $type == 'attachment' ) {
			
			if ( wp_attachment_is_image($this->get_document()) ) {
				
				$img = wp_get_attachment_image($this->get_document(), $img_size);
				$img_full = wp_get_attachment_url($this->get_document());
				
				$output .= sprintf("<a href='%s' target='blank'>%s</a>", $img_full, $img);
				
			} else {
				
				global $wp_embed;
				
				$url = wp_get_attachment_url($this->get_document());
				
				$embed = $wp_embed->autoembed($url);
				
				if ( $embed == $url ) {
					
					// No embed handler found
					// TODO: Add filter to allow customization
					$output .= sprintf("<a href='%s' target='blank'>%s</a>", $url, $url);
				} else {
					$output .= $embed;
				}
				
				
			}
			
		}
		
		return $output;
		
	}
	
}