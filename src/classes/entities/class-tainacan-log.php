<?php

namespace Tainacan\Entities;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Represents entity Log
 */
class Log extends Entity {

	protected
		$title,
		$order,
		$description,
		$blog_id,
		$user_id,
		$date,
		$user_name,
		$collection_id,
		$item_id,
		$object_type,
		$object_id,
		$old_value,
		$new_value;

	static $post_type = 'tainacan-log';
	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Entities\Entity::repository
	 * @var string
	 */
	protected $repository = 'Logs';

	public function __construct( $which = 0 ) {
		parent::__construct( $which );

		if ( is_int( $which ) && $which == 0 ) {
			$this->set_user_id();
		}
	}

	public function __toString() {
		return apply_filters("tainacan-log-to-string", $this->get_title(), $this);
	}

	/**
	 * @return array
	 */
	public function _toArray() {
		$array_log = parent::_toArray();

		$array_log['user_name']     = $this->get_user_name();

		return apply_filters('tainacan-log-to-array', $array_log, $this);
	}


	/**
	 * @param $collection_id
	 */
	function set_collection_id($collection_id){
		$this->set_mapped_property('collection_id', $collection_id);
	}

	/**
	 * @return mixed|null
	 */
	function get_collection_id(){
		return $this->get_mapped_property('collection_id');
	}

	/**
	 * Return the Log title
	 *
	 * @return string
	 */
	function get_title() {
		return $this->get_mapped_property( 'title' );
	}

	/**
	 * @return string
	 */
	function get_user_name() {
		return get_the_author_meta( 'display_name', $this->get_user_id() );
	}

	/**
	 * Return the log date
	 *
	 * @return mixed|null
	 */
	function get_date() {
		return $this->get_mapped_property( 'date' );
	}

	/**
	 * Return the log slug
	 *
	 * @return mixed|null
	 */
	function get_slug() {
		return $this->get_mapped_property( 'slug' );
	}

	/**
	 * Return the Log description
	 *
	 * @return string
	 */
	function get_description() {
		return $this->get_mapped_property( 'description' );
	}

	/**
	 * Return User Id of who make the action
	 *
	 * @return int User Id of logged action
	 */
	function get_user_id() {
		return $this->get_mapped_property( 'user_id' );
	}


	/**
	 * Get old value of log entry object
	 *
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function get_old_value() {
		return $this->get_mapped_property( 'old_value' );
	}

	/**
	 * Get new value of log entry object
	 *
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function get_new_value() {
		return $this->get_mapped_property( 'new_value' );
	}

	/**
	 * Set log tittle
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	public function set_title( $value ) {
		$this->set_mapped_property( 'title', apply_filters('tainacan-log-set-title', $value) );
	}

	/**
	 * Define the Log description
	 *
	 * @param [string] $value
	 *
	 * @return void
	 */
	public function set_description( $value ) {
		$this->set_mapped_property( 'description', $value );
	}

	/**
	 * Define the user ID of log entry
	 *
	 * @param [integer] $value
	 *
	 * @return void
	 */
	public function set_user_id( $value = 0 ) {
		if ( 0 == $value ) {
			$value = get_current_user_id();
		}
		$this->set_mapped_property( 'user_id', $value );
	}

	/**
	 * Set old value of log entry
	 *
	 * @param [mixed] $value
	 *
	 * @return void
	 */
	public function set_old_value( $value ) {
		$this->set_mapped_property( 'old_value', $value );
	}

	/**
	 * Set new value of log entry
	 *
	 * @param [mixed] $value
	 *
	 * @return void
	 */
	public function set_new_value( $value ) {
		$this->set_mapped_property( 'new_value', $value );
	}

	/**
	 * @return mixed|null
	 */
	public function get_log_diffs(){
		return $this->get_mapped_property('log_diffs');
	}

	public function get_object_type() {
		return $this->get_mapped_property('object_type');
	}

	public function set_object_type($value) {
		$this->set_mapped_property('object_type', $value);
	}

	public function get_object_id() {
		return $this->get_mapped_property('object_id');
	}

	public function set_object_id($value) {
		$this->set_mapped_property('object_id', $value);
	}

	/**
	 * @param $item_id
	 */
	public function set_item_id($item_id){
		$this->set_mapped_property('item_id', $item_id);
	}

	/**
	 * @return mixed
	 */
	public function get_item_id(){
		return $this->get_mapped_property('item_id');
	}

	public function get_action() {
		return $this->get_mapped_property('action');
	}

	public function set_action($value) {
		$this->set_mapped_property('action', $value);
	}

	/**
	 * {@inheritDoc}
	 * @see \Tainacan\Repositories\Logs::approve
	 */
	public function approve() {
		$repository = $this->get_repository();

		return $repository->approve( $this );
	}
}
