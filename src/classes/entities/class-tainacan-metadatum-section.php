<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Metadatum
 */
class Metadatum_Section extends Entity {
	// Collection getter and setter declared here
	use \Tainacan\Traits\Entity_Collection_Relation;

	static $post_type = 'tainacan-metasection';
	protected
		$name,
		$slug,
		$description,
		$metadatum_list;

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Metadata_Section';
	
	public function __toString() {
		return apply_filters("tainacan-metadatum-section-to-string", $this->get_name(), $this);
	}

	/**
	 * Return the metadatum section name
	 *
	 * @return string
	 */
	function get_name() {
		return $this->get_mapped_property('name');
	}
	
	/**
	 * Get metadatum section slug
	 *
	 * @return string
	 */
	function get_slug() {
		return $this->get_mapped_property('slug');
	}

	/**
	 * Return the metadatum section description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property('description');
	}

	/**
	 * Return the metadatum_list of section
	 *
	 * @return [int]
	 */
	function get_metadatum_list() {
		return $this->get_mapped_property('metadatum_list');
	}

	/**
	 * Set the metadatum section name
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_name($value) {
		$this->set_mapped_property('name', $value);
	}

	/**
	 * Set the metadatum section slug
	 *
	 * If you dont set the metadatum slug, it will be set automatically based on the name and
	 * following WordPress default behavior of creating slugs for posts.
	 *
	 * If you set the slug for an existing one, WordPress will append a number at the end of in order
	 * to make it unique (e.g slug-1, slug-2)
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_slug($value) {
		$this->set_mapped_property('slug', $value);
	}

	/**
	 * Set metadatum section description
	 *
	 * @param [string] $value The text description
	 * @return void
	 */
	function set_description($value) {
		$this->set_mapped_property('description', $value);
	}


	/**
	 * Set metadatum list of the section
	 *
	 * @param [string|int] $value The array of list metadatum
	 * @return void
	 */
	function set_metadatum_list($value) {
		$this->set_mapped_property('metadatum_list', array_unique($value));
	}

	/**
	 * {@inheritdoc }
	 *
	 * Also validates the metadatum, calling the validate_options callback of the Metadatum Type
	 *
	 * @return bool valid or not
	 * @throws \Exception
	 */
	public function validate() {
		$no_errors = true;
		$metadatum_list = $this->get_metadatum_list();
		$name = $this->get_name();

		if ( !isset($name) ) {
			$this->add_error($this->get_id(), __("name is required", 'tainacan'));
			$no_errors = false;
		}
		if( !empty($metadatum_list) ) {
			foreach($metadatum_list as $metadatum_id) {
				if(get_post_type($metadatum_id) != \Tainacan\Entities\Metadatum::$post_type ) {
					$this->add_error($this->get_id(), __("is not a valid metadata", 'tainacan'));
					$no_errors = false;
				}
			}
		}
		return $no_errors;
	}
}
