<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Term
*/
class Term extends Entity {
	public $WP_Term;

	protected
		$term_id,
		$name,
		$parent,
		$description,
		$user,
		$header_image_id,
		$taxonomy;


	static $post_type = false;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Terms';

	/**
	 * Term constructor.
	 *
	 * @param int $which
	 * @param string $taxonomy
	 */
	function __construct($which = 0, $taxonomy = false ) {

		if ($taxonomy)
			$this->set_taxonomy( $taxonomy );

		if ( is_numeric( $which ) && $which > 0) {
			$post = get_term_by('id', $which, $taxonomy);
			
			if ( $post instanceof \WP_Term) {
				$this->WP_Term = get_term_by('id', $which, $taxonomy);
			}

		} elseif ( $which instanceof \WP_Term ) {
			$this->WP_Term = $which;
		} else {
			$this->WP_Term = new \StdClass();
		}
	}

	public function __toString() {
		return (string) apply_filters("tainacan-term-to-string", $this->get_name(), $this);
	}

	public function _toArray() {
		$term_array = parent::_toArray();

		$term_id = $term_array['term_id'];
		$taxonomy_slug = $this->get_taxonomy();
		$separator = strip_tags(apply_filters('tainacan-terms-hierarchy-html-separator', '>'));

		unset($term_array['term_id']);
		unset($term_array['status']);

		$term_array['id']             = $term_id;
		$term_array['thumbnail']	  = $this->get_thumbnail();
		$term_array['thumbnail_alt']  = get_post_meta( $this->get_header_image_id(), '_wp_attachment_image_alt', true );
		$term_array['thumbnail_id']	  = $this->get_header_image_id();
		$term_array['header_image']   = $this->get_header_image();
		$term_array['url']            = get_term_link( $this->get_id() );
		$term_array['hierarchy_path'] = get_term_parents_list($term_id, $taxonomy_slug, ['format'=>'name', 'separator'=>$separator, 'link'=>false, 'inclusive'=>false]);

		return apply_filters('tainacan-term-to-array', $term_array, $this);
	}

	// Getters

	/**
	 * Return the unique identifier
	 *
	 * @return integer
	 */
	function get_id() {
		return $this->get_term_id();
	}
	function get_term_id() {
		return $this->get_mapped_property('term_id');
	}

	/**
	 * Return the name
	 *
	 * @return string
	 */
	function get_name() {
		return $this->get_mapped_property('name');
	}

	/**
	 * Return the parent ID
	 *
	 * @return integer
	 */
	function get_parent() {
		return $this->get_mapped_property('parent');
	}

	/**
	 * Return the description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property('description');
	}

	/**
	 * Return the user ID
	 *
	 * @return integer
	 */
	function get_user() {
		return $this->get_mapped_property('user');
	}

	/**
	 * Return the taxonomy
	 *
	 * @return integer
	 */
	function get_taxonomy() {
		return $this->get_mapped_property('taxonomy');
	}
	
	/**
	 * Get Header Image ID attribute
	 *
	 * @return string
	 */
	function get_header_image_id() {
		return $this->get_mapped_property( 'header_image_id' );
	}

	/**
	 * @return false|string
	 */
	function get_header_image(){
		return wp_get_attachment_url( $this->get_header_image_id() );
	}

	/**
	 * @return false|string
	 */
	function get_url(){
		$url = get_term_link( $this->get_id() );
		if (is_wp_error($url))
			return "";
		return $url;
	}

	/**
	 * Gets the thumbnail 
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
			$thumbs[$size] = wp_get_attachment_image_src( $this->get_header_image_id(), $size );
			if (is_array($thumbs[$size]) && count($thumbs[$size]) == 4) {
				$thumbs[$size][] = $blurhash;
			}
		}
		return apply_filters("tainacan-term-get-thumbnail", $thumbs, $this);
	}

	function get_thumbnail_blurhash() {
		$attachment_metadata = wp_get_attachment_metadata($this->get_header_image_id());
		if($attachment_metadata != false && isset($attachment_metadata['image_meta'])) {
			$image_meta = $attachment_metadata['image_meta'];
			if($image_meta != false && isset($image_meta['blurhash'])) {
				return $image_meta['blurhash'];
			}
		}
		return \Tainacan\Media::get_instance()->get_default_image_blurhash();
	}

	// Setters

	/**
	 * Define the name
	 *
	 * @param [string] $value
	 */
	function set_name($value) {
		$this->set_mapped_property('name', $value);
	}

	/**
	 * Define the parent ID
	 *
	 * @param [integer] $value
	 */
	function set_parent($value) {
		$this->set_mapped_property('parent', $value);
	}

	/**
	 * Define the description
	 *
	 * @param [string] $value
	 */
	function set_description($value) {
		$this->set_mapped_property('description', $value);
	}

	/**
	 * Define the user associated
	 *
	 * @param [integer] $value
	 */
	function set_user($value) {
		$this->set_mapped_property('user', $value);
	}

	/**
	 * Define the taxonomy associated
	 *
	 * @param [integer] $value
	 */
	function set_taxonomy($value) {
		$this->set_mapped_property('taxonomy', $value);
	}
	
	/**
	 * Set Header Image ID
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	function set_header_image_id( $value ) {
		$this->set_mapped_property( 'header_image_id', $value );
	}
	
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::validate()
	 */
	function validate() {

		if (!parent::validate())
			return false;
		
		$parent = $this->get_parent();
		$name = $this->get_name();
		$taxonomy = $this->get_taxonomy();
		
		$repo = $this->get_repository();
		
		$term_exists = $repo->term_exists($name, $taxonomy, $parent, true);
		
		if (false !== $term_exists) {
			if ($this->get_id() != $term_exists->term_id) {
				$this->add_error( 'name', __('You cannot have two terms with the same name at the same level', 'tainacan') );
				return false;
			}
		}

		$this->set_as_valid();
		return true;

	}

	public function _toHtml() {
		$return = '';
		$id = $this->get_id();
		
		if ( $id ) {
			$link = get_term_link( (int) $id );
			
			if (is_string($link)) {
				$return = "<a data-linkto='term' data-id='$id' href='$link'>";
				$return.= $this->get_name();
				$return .= "</a>";
			}
		}

		return apply_filters('tainacan-term-to-html', $return, $this);		
	}
}
