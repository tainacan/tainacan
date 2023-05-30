<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Metadatum
 */
class Metadata_Section extends Entity {
	// Collection getter and setter declared here
	use \Tainacan\Traits\Entity_Collection_Relation;

	static $post_type = 'tainacan-metasection';
	static $default_section_slug = 'default_section';

	protected
		$name,
		$slug,
		$description,
		$description_bellow_name,
		$is_conditional_section,
		$conditional_section_rules;

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Metadata_Sections';

	public $enabled_for_collection = true;

	public function __toString() {
		return apply_filters("tainacan-metadata-section-to-string", $this->get_name(), $this);
	}

	/**
	 * Get the entity ID
	 *
	 * @return integer
	 */
	public function get_id() {
		$id = $this->get_mapped_property('id');
		return isset($id) ? $id : static::$default_section_slug;
	}

	/**
	 * Return the metadata section name
	 *
	 * @return string
	 */
	function get_name() {
		return $this->get_mapped_property('name');
	}
	
	/**
	 * Get metadata section slug
	 *
	 * @return string
	 */
	function get_slug() {
		return $this->get_mapped_property('slug');
	}

	/**
	 * Return the metadata section description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property('description');
	}
	
	/**
	 * Return the metadatum description_bellow_name
	 *
	 * @return string
	 */
	function get_description_bellow_name() {
		return $this->get_mapped_property('description_bellow_name');
	}

	/**
	 * Return the metadata_list of section
	 *
	 * @return [int]
	 */
	function get_metadata_object_list($args = []) {
		$tainacan_metadata_sections = \Tainacan\Repositories\Metadata_Sections::get_instance();
		$metadata_section_id = $this->get_id();

		if ($metadata_section_id == static::$default_section_slug)
			return $tainacan_metadata_sections->get_default_section_metadata_object_list($this->get_collection(), $args);
		
		return $tainacan_metadata_sections->get_metadata_object_list($this->get_id(), $args);
	}

	/**
	 * Get if section is conditional.
	 *
	 * @return string "yes"|"no"
	 */
	function get_is_conditional_section() {
		return $this->get_mapped_property( 'is_conditional_section' );
	}

	/**
	 * get the rules of the conditional section
	 *
	 * @return array|object
	 */
	function get_conditional_section_rules() {
		return $this->get_mapped_property( 'conditional_section_rules' );
	}

	/**
	 * Checks if section is conditional
	 *
	 * @return boolean
	 */
	function is_conditional_section() {
		return $this->get_is_conditional_section() == 'yes';
	}


	/**
	 * Set the metadata section name
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_name($value) {
		$this->set_mapped_property('name', $value);
	}

	/**
	 * Set the metadata section slug
	 *
	 * If you dont set the metadata slug, it will be set automatically based on the name and
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
	 * Set metadata section description
	 *
	 * @param [string] $value The text description
	 * @return void
	 */
	function set_description($value) {
		$this->set_mapped_property('description', $value);
	}

	/**
	 * Set metadatum description_bellow_name
	 *
	 * @param [string] $value If the description will be displayed below the name instead of inside a tooltip (yes/no)
	 * @return void
	 */
	function set_description_bellow_name($value) {
		$this->set_mapped_property('description_bellow_name', $value);
	}


	/**
	 * set if section is conditional.
	 *
	 * @return void
	 */
	function set_is_conditional_section($value) {
		return $this->set_mapped_property( 'is_conditional_section', $value );
	}

	/**
	 * set the rules of the conditional section
	 *
	 * @return void
	 */
	function set_conditional_section_rules($value) {
		return $this->set_mapped_property( 'conditional_section_rules', $value );
	}


	/**
	 * Transient property used to store the status of the metadatum section for a particular collection
	 *
	 * Used by the API to tell front end when a metadatum section is disabled
	 * 
	 */
	public function get_enabled_for_collection() {
		return $this->enabled_for_collection;
	}
	public function set_enabled_for_collection($value) {
		$this->enabled_for_collection = $value;
	}
	

	/**
	 * {@inheritdoc }
	 *
	 * Also validates the metadata, calling the validate_options callback of the Metadatum Type
	 *
	 * @return bool valid or not
	 * @throws \Exception
	 */
	public function validate() {
		$no_errors = true;
		$name = $this->get_name();
		$collection = $this->get_collection();

		if ($this->is_conditional_section()) {
			$metadata_section_id = $this->get_id();
			if ($metadata_section_id == static::$default_section_slug) {
				$this->add_error('is_conditional_section', __("A conditional section cannot be enabled in default section", 'tainacan'));
				$no_errors = false;
			} else {
				$metadata_list = $this->get_metadata_object_list();
				$required_metadata_list = array_filter($metadata_list, function($m) {
					return $m->is_required();
				});
				if ( count($required_metadata_list) ) {
					$no_errors = false;
					foreach($required_metadata_list as $metadata) {
						$this->add_error('is_conditional_section', sprintf(__("Metadatum %s cannot be required inside a conditional section", 'tainacan'), $metadata->get_name()));
					}
				}
			}
		}

		if( empty($collection) ) {
			$this->add_error('required', __("Collection is required", 'tainacan'));
			$no_errors = false;
		}

		if ( !isset($name) ) {
			$this->add_error('required', __("Name is required", 'tainacan'));
			$no_errors = false;
		}
		if($no_errors) {
			$this->set_as_valid();
		}
		return $no_errors;
	}
}
