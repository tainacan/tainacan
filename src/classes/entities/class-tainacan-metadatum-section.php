<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Metadatum
 */
class Metadatum_Section extends Entity {
	// Collection getter and setter declared here
	use \Tainacan\Traits\Entity_Collection_Relation;
	
	static $post_type = 'tainacan-metadatum-section';
	protected
		$name,
		$slug,
		$order,
		$description;
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::capability_type
	 * @var string
	 */
	protected static $capability_type = ['tainacan-metadatum-section'];


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
	 * Return the metadatum name
	 *
	 * @return string
	 */
	function get_name() {
		return $this->get_mapped_property('name');
	}
	
	/**
	 * Get metadatum slug
	 *
	 * @return string
	 */
	function get_slug() {
		return $this->get_mapped_property('slug');
	}

	/**
	 * Return the metadatum description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property('description');
	}

	/**
	 * Set the metadatum name
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
	 * Set metadatum description
	 *
	 * @param [string] $value The text description
	 * @return void
	 */
	function set_description($value) {
		$this->set_mapped_property('description', $value);
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
		$this->add_error($this->get_id(), __("Error", 'tainacan'));
		return false;
	}
}
