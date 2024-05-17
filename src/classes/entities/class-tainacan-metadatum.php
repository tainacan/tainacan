<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents the Entity Metadatum
 */
class Metadatum extends Entity {
	protected
		$name,
		$slug,
		$order,
		$parent,
		$description,
		$description_bellow_name,
		$placeholder,
		$required,
		$multiple,
		$display,
		$cardinality,
		$collection_key,
		$default_value,
		$metadata_type,
		$metadata_type_options,
		$metadata_section_id;

	// Collection getter and setter declared here
	use \Tainacan\Traits\Entity_Collection_Relation;
	
	public $enabled_for_collection = true;
	
	static $post_type = 'tainacan-metadatum';
	

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::capability_type
	 * @var string
	 */
	protected static $capability_type = ['tainacan-metadatum', 'tainacan-metadata'];


	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Metadata';
	
	public function  __toString(){
		return apply_filters("tainacan-metadatum-to-string", $this->get_name(), $this);
	}


	/**
	 * @param $display
	 */
	function set_display( $display ){
		$this->set_mapped_property('display', $display);
	}

	/**
	 * @return mixed
	 */
	function get_display(){
		return $this->get_mapped_property('display');
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
	 * Return the metadatum order type
	 *
	 * @return string
	 */
	function get_order() {
		return $this->get_mapped_property('order');
	}

	/**
	 * Return the parent ID
	 *
	 * @return string
	 */
	function get_parent() {
		return $this->get_mapped_property('parent');
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
	 * Return the metadatum description_bellow_name
	 *
	 * @return string
	 */
	function get_description_bellow_name() {
		return $this->get_mapped_property('description_bellow_name');
	}

	/**
	 * Return the metadatum placeholder
	 *
	 * @return string
	 */
	function get_placeholder() {
		return $this->get_mapped_property('placeholder');
	}

	/**
	 * Return if is a required metadatum
	 *
	 * @return boolean
	 */
	function get_required(){
		return $this->get_mapped_property('required');
	}
	
	/**
	 * Return if is a multiple metadatum
	 *
	 * @return boolean
	 */
	function get_multiple(){
		return $this->get_mapped_property('multiple');
	}
	
	/**
	 * Return the cardinality
	 *
	 * @return string
	 */
	function get_cardinality(){
		return $this->get_mapped_property('cardinality');
	}
	
	/**
	 * Return if metadatum is key
	 *
	 * @return boolean
	 */
	function get_collection_key(){
		return $this->get_mapped_property('collection_key');
	}

	/**
	 * Return the metadatum default value
	 *
	 * @return string || integer
	 */
	function get_default_value(){
		return $this->get_mapped_property('default_value');
	}

	/**
	 * Return the an object child of \Tainacan\metadatum_Types\Metadata_Type with options
	 *
	 * @return \Tainacan\Metadata_Types\Metadata_Type|Object The metadatum type class with filled options
	 */
	function get_metadata_type_object(){
		$class_name = $this->get_metadata_type();

		if( !class_exists( $class_name ) ) {
			$class_name = "Tainacan\\Metadata_Types\\Text";
			if( !class_exists( $class_name ) ){
				return null;
			}
		}

		$object_type = new $class_name();
		$object_type->set_options( $this->get_mapped_property('metadata_type_options') );
		return $object_type;
	}

	/**
	 * Return the class name for the metadatum type
	 *
	 * @return string The
	 */
	function get_metadata_type(){
		return $this->get_mapped_property('metadata_type');
	}

	/**
	 * Return the actual options for the current metadatum type
	 *
	 * @return array Configurations for the metadatum type object
	 */
	function get_metadata_type_options(){
		$object = $this->get_metadata_type_object();
		if ($object) {
			return $object->get_options(); // merge with dedault metadata type options
		}
		return $this->get_mapped_property('metadata_type_options');
	}

	/**
	 * Return true if this metadatum allow community suggestions, false otherwise
	 * @return bool
	 */
	function get_accept_suggestion() {
		return $this->get_mapped_property('accept_suggestion');
	}
	
	/**
	 * Return array of exposer mapping configurations
	 * @return array
	 */
	public function get_exposer_mapping() {
		return $this->get_mapped_property('exposer_mapping');
	}
	
	/**
	 * Return the semantic_uri
	 *
	 * @return string
	 */
	function get_semantic_uri(){
		return $this->get_mapped_property('semantic_uri');
	}

	/**
	 * Return the metadata_section_id
	 *
	 * @return string
	 */
	function get_metadata_section_id() {
		$mapped_metadata_section_id = $this->get_mapped_property('metadata_section_id');
		if($this->is_repository_level())
			return $mapped_metadata_section_id;
		return is_array($mapped_metadata_section_id) ? $mapped_metadata_section_id[0] : $mapped_metadata_section_id;
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
	 * Set the metadatum slug
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
	 * Set manually the order of the metadatum
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_order($value) {
		$this->set_mapped_property('order', $value);
	}

	/**
	 * Set the metadatum parent ID
	 *
	 * @param [integer] $value The ID from parent
	 * @return void
	 */
	function set_parent($value) {
		$this->set_mapped_property('parent', $value);
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
	 * Set metadatum description_bellow_name
	 *
	 * @param [string] $value If the description will be displayed below the name instead of inside a tooltip (yes/no)
	 * @return void
	 */
	function set_description_bellow_name($value) {
		$this->set_mapped_property('description_bellow_name', $value);
	}

	/**
	 * Set metadatum placeholder
	 *
	 * @param [string] $value The text placeholder
	 * @return void
	 */
	function set_placeholder($value) {
		$this->set_mapped_property('placeholder', $value);
	}

	/**
	 * Allow the metadatum be required
	 *
	 * @param [boolean] $value
	 * @return void
	 */
	function set_required( $value ){
		$this->set_mapped_property('required', $value);
	}
	
	/**
	 * Allow multiple metadata
	 *
	 * @param [boolean] $value
	 * @return void
	 */
	function set_multiple( $value ){
		$this->set_mapped_property('multiple', $value);
	}
	
	/**
	 * The number of  possible metadata
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_cardinality( $value ){
		$this->set_mapped_property('cardinality', $value);
	}
	
	/**
	 * Define if the value is key on the collection
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_collection_key( $value ){
		$this->set_mapped_property('collection_key', $value);
	}


	/**
	 * Set default value
	 *
	 * @param [string || integer] $value
	 * @return void
	 */
	function set_default_value( $value ){
		$this->set_mapped_property('default_property', $value);
	}

	/**
	 * set the metadatum type class name
	 *
	 * @param string | \Tainacan\Metadata_Types\Metadata_Type $value The name of the class or the instance
	 */
	public function set_metadata_type( $value ){
		$this->set_mapped_property('metadata_type', ( is_object( $value ) ) ?  get_class( $value ) : $value ) ; // Encode to avoid backslaches removal
	}
	
	/**
	 * Set if this metadatum allow community suggestions
	 * @param bool $value
	 */
	function set_accept_suggestion( $value ) {
		$this->set_mapped_property('accept_suggestion', $value);
	}
	
	/**
	 * Set Metadatum type options
	 *
	 * @param [string || integer] $value
	 * @return void
	 */
	function set_metadata_type_options( $value ){
		$this->set_mapped_property('metadata_type_options', $value);
	}
	
	/**
	 * Set exposers mapping configuration for this metadatum
	 * @param array $value
	 */
	public function set_exposer_mapping( $value ) {
		$this->set_mapped_property('exposer_mapping', $value);
	}
	
	/**
	 * Set Semantic URI for the metadatum
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_semantic_uri( $value ){
		$this->set_mapped_property('semantic_uri', $value);
	}


	/**
	 * Set metadatum section ID for the metadatum
	 *
	 * @param [string] $value
	 * @return void
	 */
	function set_metadata_section_id($value) {
		if( !is_array($value) ) {
			$value = [$value];
		}
		return $this->set_mapped_property('metadata_section_id', $value);
	}
	
	/**
	 * Transient property used to store the status of the metadatum for a particular collection
	 *
	 * Used by the API to tell front end when a metadatum is disabled
	 * 
	 */
	public function get_enabled_for_collection() {
		return $this->enabled_for_collection;
	}
	public function set_enabled_for_collection($value) {
		$this->enabled_for_collection = $value;
	}
	

	// helpers

	/**
	 * Return true if is multiple, else return false
	 *
	 * @return boolean
	 */
	function is_multiple() {
		return $this->get_multiple() === 'yes';
	}

	/**
	 * Return true if is collection key, else return false
	 *
	 * @return boolean
	 */
	function is_collection_key() {
		return $this->get_collection_key() === 'yes';
	}

	/**
	 * Return true if is required, else return false
	 * 
	 * @return boolean
	 */
	function is_required() {
		return $this->get_required() === 'yes';
	}

	/**
	 * Return true if is repository level, else return false
	 * 
	 * @return boolean
	 */
	function is_repository_level() {
		return $this->get_collection_id() === 'default';
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
		$is_valid = parent::validate();

		if (false === $is_valid)
			return false;

		// You can't have a multiple metadatum inside a compound metadatum
		if ($this->get_parent() > 0) {
			if ( $this->is_multiple() ) {
				$this->add_error('multiple', __('Compound metadata do not support child metadata with multiple values', 'tainacan'));
				return false;
			}
		}

		// You cant have a required metadatum inside a conditional section
		if ( $this->is_required() ) {
			$meta_section_id = $this->get_metadata_section_id();
			if ($meta_section_id != \Tainacan\Entities\Metadata_Section::$default_section_slug) {
				$meta_section = new \Tainacan\Entities\Metadata_Section($meta_section_id);
				if($meta_section->is_conditional_section()) {
					$this->add_error('required', __('Metadata cannot be required into conditional section', 'tainacan'));
					return false;
				}
			}
		}
		
		// You cant have a taxonomy metadatum inside a multiple compound metadatum
		if ( $this->get_parent() > 0 && $this->get_metadata_type_object()->get_primitive_type() == 'term' ) {
			$parent_metadatum = new \Tainacan\Entities\Metadatum($this->get_parent());
			if ( $parent_metadatum->is_multiple() ) {
				$this->add_error('multiple', __('Taxonomy metadata cannot be used inside Compound metadata with multiple values', 'tainacan'));
				return false;
			}
		}
		if ( $this->get_metadata_type() == 'Tainacan\Metadata_Types\Compound' && $this->is_multiple() && $this->get_id() != null ) {
 
			$Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
			$children_taxonomy = $Tainacan_Metadata->fetch(
				[
					'parent' => $this->get_id(),
					'metadata_type' => 'Tainacan\Metadata_Types\Taxonomy',
					'post_status' => 'any'
				]
				, 'OBJECT');
			
			if ( sizeof($children_taxonomy) > 0 ) {
				$this->add_error('multiple', __('Taxonomy metadata cannot be used inside Compound metadata with multiple values', 'tainacan'));
				return false;
			}

			$meta_childrens = $Tainacan_Metadata->fetch(
				[
					'parent' => $this->get_id(),
					'post_status' => 'any'
				]
				, 'OBJECT');
			
			if ( sizeof($meta_childrens) > 0 ) {
				foreach($meta_childrens as $meta_children) {
					if( $meta_children->is_required() ) {
						$this->add_error('multiple', __("Compound metadata with multiple values can't have a children metadata set to as required", 'tainacan'));
						return false;
					}
				}
			}
		}
		
		$fto = $this->get_metadata_type_object();
		if (is_object($fto)) {
				$is_valid = $fto->validate_options($this);
		}
		
		if (true === $is_valid) {
			$this->set_as_valid();

			return true;
		}

		if (!is_array($is_valid)) {
			throw new \Exception( "Return of validate_options metadatum type method should be an Array in case of error" );
		}

		$this->add_error('metadata_type_options', $is_valid);
		
		return false;
	}
}
